<?php

namespace App\Http\Controllers;

use App\Models\ConfigEcommerce;
use App\Models\PedidoEcommerce;
use App\Services\EcommerceCorreiosService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PedidoEcommerceSecurityController extends PedidoEcommerceController
{
    public function __construct(private EcommerceCorreiosService $correiosService)
    {
    }

    private function empresaIdAtual(): int
    {
        $authEmpresa = auth()->user()->empresa_id ?? null;
        if ($authEmpresa) {
            return (int) $authEmpresa;
        }

        $sessao = session('user_logged');
        if (is_array($sessao)) {
            return (int) ($sessao['empresa_id'] ?? $sessao['empresa'] ?? 0);
        }

        if (is_object($sessao)) {
            return (int) ($sessao->empresa_id ?? $sessao->empresa ?? 0);
        }

        return 0;
    }

    private function pedidoDaEmpresa($id, array $with = []): PedidoEcommerce
    {
        $empresaId = $this->empresaIdAtual();
        abort_if($empresaId <= 0, 403, 'Empresa não identificada.');

        return PedidoEcommerce::with($with)
            ->where('id', $id)
            ->where('empresa_id', $empresaId)
            ->firstOrFail();
    }

    public function index(Request $request)
    {
        $empresaId = $this->empresaIdAtual();
        abort_if($empresaId <= 0, 403, 'Empresa não identificada.');

        $query = PedidoEcommerce::query()
            ->with([
                'cliente',
                'empresa',
                'itens.produto.produto',
                'itens.produto.galeria',
            ])
            ->where('empresa_id', $empresaId);

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $documento = preg_replace('/\D/', '', $search);

            $query->where(function ($q) use ($search, $documento) {
                if (ctype_digit($search)) {
                    $q->orWhere('id', (int) $search);
                } else {
                    $q->orWhere('id', 'like', '%' . $search . '%');
                }

                $q->orWhere('transacao_id', 'like', '%' . $search . '%')
                    ->orWhereHas('cliente', function ($cliente) use ($search, $documento) {
                        $cliente->where(function ($c) use ($search, $documento) {
                            $c->where('nome', 'like', '%' . $search . '%')
                                ->orWhere('sobre_nome', 'like', '%' . $search . '%')
                                ->orWhere('email', 'like', '%' . $search . '%')
                                ->orWhere('telefone', 'like', '%' . $search . '%');

                            if ($documento !== '') {
                                $c->orWhere('cpf', 'like', '%' . $documento . '%');
                            }
                        });
                    });
            });
        }

        if ($request->filled('periodo')) {
            $inicio = match ((string) $request->periodo) {
                'hoje' => now()->startOfDay(),
                '7' => now()->subDays(7)->startOfDay(),
                '30' => now()->subDays(30)->startOfDay(),
                default => null,
            };

            if ($inicio) {
                $query->where('created_at', '>=', $inicio);
            }
        }

        if ($request->filled('status')) {
            $statusPagamento = (string) $request->status;
            $permitidos = ['approved', 'pending', 'in_process', 'canceled', 'rejected', 'refunded'];

            if (in_array($statusPagamento, $permitidos, true)) {
                if ($statusPagamento === 'pending') {
                    $query->whereIn('status_pagamento', ['', 'pending', 'in_process']);
                } else {
                    $query->where('status_pagamento', $statusPagamento);
                }
            }
        }

        if ($request->filled('andamento')) {
            $andamento = (string) $request->andamento;
            $mapa = [
                'novo' => ['novo', '0', 0],
                'preparacao' => ['preparacao', 'preparação', '1', '3', 1, 3],
                'cancelado' => ['cancelado', 'canceled', 'cancelled', '2', 2],
                'enviado' => ['enviado', '4', 4],
                'entregue' => ['entregue', 'finalizado', '5', 5],
            ];

            if (isset($mapa[$andamento])) {
                $query->whereIn('status', $mapa[$andamento]);
            }
        }

        $resumoQuery = PedidoEcommerce::query()->where('empresa_id', $empresaId);
        $resumo = [
            'total_hoje' => (clone $resumoQuery)->whereDate('created_at', today())->sum('valor_total'),
            'pendentes' => (clone $resumoQuery)->whereIn('status_pagamento', ['', 'pending', 'in_process'])->count(),
            'pagos' => (clone $resumoQuery)->where('status_pagamento', 'approved')->count(),
            'total_geral' => (clone $query)->count(),
        ];

        $pedidos = $query
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('pedidos_ecommerce.index', compact('pedidos', 'resumo'));
    }

    public function show($id)
    {
        $this->pedidoDaEmpresa($id);
        return parent::show($id);
    }

    public function etiqueta($id)
    {
        $pedido = $this->pedidoDaEmpresa($id);
        $config = ConfigEcommerce::where('empresa_id', $pedido->empresa_id)->first();

        if (!$config) {
            return response()->json([
                'error' => 'Configuração da loja não encontrada.',
                'message' => 'Configure a Loja Online antes de gerar a etiqueta.',
            ], 422);
        }

        try {
            $resultado = $this->correiosService->gerarOuConsultarEtiqueta($pedido, $config);

            for ($tentativa = 0; $tentativa < 4 && ($resultado['status'] ?? null) === 'processing'; $tentativa++) {
                sleep(1);
                $resultado = $this->correiosService->consultarRotulo($pedido->fresh(), $config);
            }

            if (($resultado['status'] ?? null) === 'ready') {
                return response($resultado['pdf'], 200, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="' . $resultado['filename'] . '"',
                    'Cache-Control' => 'private, no-store, max-age=0',
                ]);
            }

            return response()->json([
                'error' => 'Etiqueta ainda em processamento.',
                'message' => 'Os Correios ainda estão preparando o PDF. Clique em Gerar etiqueta novamente em alguns instantes.',
            ], 408);
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'error' => 'Não foi possível gerar a etiqueta.',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function alterarStatus($id, $status, $tipo)
    {
        $pedido = $this->pedidoDaEmpresa($id);

        if ($tipo === 'pedido' && in_array($status, ['preparacao', 'enviado', 'entregue'], true)) {
            if ($pedido->status_pagamento_normalizado !== 'approved') {
                return redirect()->back()->with('error', 'Confirme o pagamento antes de avançar o pedido para preparação, envio ou entrega.');
            }
        }

        if ($tipo === 'pedido' && $status === 'entregue' && $pedido->status_operacional !== 'enviado') {
            return redirect()->back()->with('error', 'Marque o pedido como enviado antes de concluir a entrega.');
        }

        try {
            return parent::alterarStatus($id, $status, $tipo);
        } catch (\Throwable $e) {
            Log::error('Falha ao alterar status do pedido e-commerce.', [
                'pedido_id' => $id,
                'empresa_id' => $this->empresaIdAtual(),
                'status' => $status,
                'tipo' => $tipo,
                'erro' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Não foi possível atualizar o pedido.');
        }
    }

    public function danfeSimulada($id)
    {
        $pedido = $this->pedidoDaEmpresa($id, ['cliente', 'endereco', 'empresa.cidade', 'itens.produto.produto']);

        if (!$pedido->cliente || !$pedido->endereco || !$pedido->empresa) {
            abort(422, 'O pedido precisa ter cliente, endereço e empresa completos para gerar o DANFE.');
        }

        $config = DB::table('config_notas')->where('empresa_id', $pedido->empresa_id)->first();
        if (!$config) {
            abort(422, 'Configure os dados fiscais da empresa antes de gerar o DANFE do pedido.');
        }

        if ($pedido->itens->isEmpty()) {
            abort(422, 'O pedido não possui itens para gerar o DANFE.');
        }

        return parent::danfeSimulada($id);
    }

    public function declaracaoConteudo($id)
    {
        $pedido = $this->pedidoDaEmpresa($id, ['cliente', 'endereco', 'empresa.cidade', 'itens.produto.produto']);

        if (!$pedido->cliente || !$pedido->endereco || !$pedido->empresa) {
            abort(422, 'O pedido precisa ter cliente, endereço e empresa completos para gerar a declaração de conteúdo.');
        }

        if ($pedido->itens->isEmpty()) {
            abort(422, 'O pedido não possui itens para gerar a declaração de conteúdo.');
        }

        return parent::declaracaoConteudo($id);
    }
}