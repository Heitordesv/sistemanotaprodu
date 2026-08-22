<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\PedidoEcommerce;
use App\Models\ConfigEcommerce;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PedidoEcommerceController extends Controller
{
    private function empresaIdAtual(): int
    {
        $authUser = auth()->user();

        if ($authUser && !empty($authUser->empresa_id)) {
            return (int) $authUser->empresa_id;
        }

        $user = session('user_logged');

        if (is_object($user)) {
            $empresaId = $user->empresa_id ?? $user->empresa ?? null;
        } elseif (is_array($user)) {
            $empresaId = $user['empresa_id'] ?? $user['empresa'] ?? null;
        } else {
            $empresaId = null;
        }

        abort_if(empty($empresaId), 403, 'Empresa não identificada na sessão.');

        return (int) $empresaId;
    }

    private function pedidoDaEmpresa(int $id, array $with = []): PedidoEcommerce
    {
        return PedidoEcommerce::with($with)
            ->where('empresa_id', $this->empresaIdAtual())
            ->findOrFail($id);
    }

    public function index(Request $request)
    {
        $empresaId = $this->empresaIdAtual();

        $query = PedidoEcommerce::with(['cliente', 'itens.produtoEcommerce.produto', 'empresa'])
            ->where('empresa_id', $empresaId);

        if ($request->filled('search')) {
            $search = trim((string) $request->search);

            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhereHas('cliente', fn ($c) => $c->where('nome', 'like', "%{$search}%"))
                    ->orWhere('cpf_cnpj', 'like', "%{$search}%");
            });
        }

        if ($request->filled('periodo')) {
            $query->where('created_at', '>=', match ($request->periodo) {
                'hoje' => now()->startOfDay(),
                '7' => now()->subDays(7),
                '30' => now()->subDays(30),
                default => now()->subYear(),
            });
        }

        if ($request->filled('status')) {
            $query->where('status_pagamento', $request->status);
        }

        $resumo = [
            'total_hoje' => (clone $query)->whereDate('created_at', today())->sum('valor_total'),
            'pendentes' => (clone $query)->where('status_pagamento', 'pending')->count(),
            'pagos' => (clone $query)->where('status_pagamento', 'approved')->count(),
            'total_geral' => (clone $query)->count(),
        ];

        $pedidos = $query->orderByDesc('id')->paginate(20);

        return view('pedidos_ecommerce.index', compact('pedidos', 'resumo'));
    }

    public function show($id)
    {
        $pedido = $this->pedidoDaEmpresa((int) $id, [
            'cliente',
            'endereco',
            'empresa.cidade',
            'itens.produto.produto',
            'itens.produto.galeria',
        ]);

        $config_ecomercres = ConfigEcommerce::where('empresa_id', $pedido->empresa_id)->first();

        return view('pedidos_ecommerce.show', compact('pedido', 'config_ecomercres'));
    }

    private function extrairDDDTelefone($numeroCompleto)
    {
        $numeros = preg_replace('/\D/', '', (string) $numeroCompleto);

        if (strlen($numeros) > 11 && substr($numeros, 0, 2) === '55') {
            $numeros = substr($numeros, 2);
        }

        $ddd = substr($numeros, 0, 2) ?: '00';
        $telefone = substr($numeros, 2);

        if (strlen($telefone) === 9 && substr($telefone, 0, 1) === '9') {
            $telefone = substr($telefone, 1);
        }

        return [
            'ddd' => (string) $ddd,
            'telefone' => (string) $telefone,
        ];
    }

    private function getCorreiosToken(int $empresaId): ?string
    {
        $config = ConfigEcommerce::where('empresa_id', $empresaId)->first();

        if (!$config) {
            Log::error("Configuração da empresa não encontrada para gerar token Correios. Empresa ID: {$empresaId}");
            return null;
        }

        $usuario = $config->correios_usuario;
        $senha = $config->correios_senha;
        $cartao = $config->correios_cartao;

        if (!$usuario || !$senha || !$cartao) {
            Log::error("Credenciais dos Correios incompletas para empresa ID {$empresaId}");
            return null;
        }

        $accessBasic = base64_encode("{$usuario}:{$senha}");
        $urlAuth = 'https://api.correios.com.br/token/v1/autentica/cartaopostagem';

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic ' . $accessBasic,
            ])->post($urlAuth, ['numero' => (string) $cartao]);

            if ($response->failed()) {
                Log::error('Erro na autenticação dos Correios: ' . $response->body());
                return null;
            }

            return $response->json('token');
        } catch (\Exception $e) {
            Log::error('Exceção ao gerar token dos Correios: ' . $e->getMessage());
            return null;
        }
    }

    public function etiqueta($id)
    {
        $pedido = $this->pedidoDaEmpresa((int) $id, [
            'cliente',
            'endereco',
            'empresa.cidade',
            'itens.produtoEcommerce.produto',
        ]);

        $configEcommerce = ConfigEcommerce::where('empresa_id', $pedido->empresa_id)->first();

        if (!$configEcommerce) {
            return response()->json([
                'error' => 'Configuração da loja não encontrada.',
                'message' => 'Configure os dados do e-commerce e dos Correios antes de gerar a etiqueta.',
            ], 422);
        }

        try {
            $token = $this->getCorreiosToken((int) $pedido->empresa_id);

            if (!$token) {
                return response()->json([
                    'error' => 'Não foi possível conectar aos Correios.',
                    'message' => 'Confira usuário, senha e cartão de postagem configurados para esta empresa.',
                ], 401);
            }

            if (!$pedido->cliente || !$pedido->endereco || !$pedido->empresa) {
                return response()->json([
                    'error' => 'Pedido incompleto.',
                    'message' => 'O pedido precisa ter cliente, endereço de entrega e empresa preenchidos para gerar a etiqueta.',
                ], 422);
            }

            $telDest = $this->extrairDDDTelefone($pedido->cliente->telefone ?? $pedido->cliente->celular ?? '');
            $telRemet = $this->extrairDDDTelefone($pedido->empresa->telefone ?? '');
            $servico = strtolower((string) $pedido->tipo_frete) === 'sedex' ? '03220' : '03298';

            $itensDeclaracao = [];
            $pesoTotalGramas = 0;
            $maxAltura = 0;
            $maxLargura = 0;
            $maxComprimento = 0;

            foreach ($pedido->itens as $item) {
                $produto = $item->produtoEcommerce->produto ?? null;
                $nomeOriginal = $item->produtoEcommerce->nome ?? $produto->nome ?? 'Produto';
                $nomeItem = substr(preg_replace('/[^A-Za-z0-9 ]/', '', $nomeOriginal), 0, 50);
                $valorItem = max(0.01, (float) ($item->produtoEcommerce->valor ?? $produto->valor_venda ?? 0.01));
                $quantidade = max(1, (int) $item->quantidade);
                $pesoItemGramas = $produto ? (float) ($produto->peso_bruto ?? $produto->peso_liquido ?? 0.1) * 1000 : 100;

                $pesoTotalGramas += $pesoItemGramas * $quantidade;

                $altura = $produto ? (float) ($produto->altura ?? 15) : 15;
                $largura = $produto ? (float) ($produto->largura ?? 15) : 15;
                $comprimento = $produto ? (float) ($produto->comprimento ?? 20) : 20;

                $maxAltura = max($maxAltura, $altura);
                $maxLargura = max($maxLargura, $largura);
                $maxComprimento = max($maxComprimento, $comprimento);

                $itensDeclaracao[] = [
                    'conteudo' => $nomeItem ?: 'Produto',
                    'quantidade' => $quantidade,
                    'valor' => $valorItem,
                    'peso' => $pesoItemGramas,
                ];
            }

            $pesoFinal = $pesoTotalGramas > 0 ? $pesoTotalGramas : 500;
            $altFinal = $maxAltura >= 2 ? $maxAltura : 15;
            $larFinal = $maxLargura >= 11 ? $maxLargura : 15;
            $comFinal = $maxComprimento >= 16 ? $maxComprimento : 20;

            $payload = [
                'codigoServico' => $servico,
                'destinatario' => [
                    'cpfCnpj' => preg_replace('/\D/', '', (string) ($pedido->cliente->cpf ?? $pedido->cliente->cpf_cnpj ?? '')),
                    'nome' => substr((string) ($pedido->cliente->nome ?? 'Cliente'), 0, 50),
                    'dddTelefone' => $telDest['ddd'],
                    'telefone' => $telDest['telefone'],
                    'email' => substr((string) ($pedido->cliente->email ?? 'contato@cliente.com.br'), 0, 50),
                    'endereco' => [
                        'cep' => preg_replace('/\D/', '', (string) ($pedido->endereco->cep ?? '')),
                        'logradouro' => substr((string) ($pedido->endereco->rua ?? $pedido->endereco->logradouro ?? ''), 0, 40),
                        'numero' => substr((string) ($pedido->endereco->numero ?? 'SN'), 0, 10),
                        'bairro' => substr((string) ($pedido->endereco->bairro ?? ''), 0, 20),
                        'cidade' => substr((string) ($pedido->endereco->cidade ?? ''), 0, 30),
                        'uf' => strtoupper((string) ($pedido->endereco->uf ?? '')),
                    ],
                ],
                'remetente' => [
                    'cpfCnpj' => preg_replace('/\D/', '', (string) ($pedido->empresa->cpf_cnpj ?? '')),
                    'nome' => substr((string) ($pedido->empresa->nome_fantasia ?? $pedido->empresa->nome ?? 'Empresa'), 0, 50),
                    'dddTelefone' => $telRemet['ddd'],
                    'telefone' => $telRemet['telefone'],
                    'email' => substr((string) ($pedido->empresa->email ?? 'financeiro@empresa.com.br'), 0, 50),
                    'endereco' => [
                        'cep' => preg_replace('/\D/', '', (string) ($pedido->empresa->cep ?? '')),
                        'logradouro' => substr((string) ($pedido->empresa->rua ?? 'Rua Empresa'), 0, 40),
                        'numero' => substr((string) ($pedido->empresa->numero ?? 'SN'), 0, 10),
                        'bairro' => substr((string) ($pedido->empresa->bairro ?? ''), 0, 20),
                        'cidade' => substr((string) ($pedido->empresa->cidade->nome ?? ''), 0, 30),
                        'uf' => strtoupper((string) ($pedido->empresa->cidade->uf ?? '')),
                    ],
                ],
                'numeroCartaoPostagem' => (string) $configEcommerce->correios_cartao,
                'pesoInformado' => (int) $pesoFinal,
                'codigoFormatoObjetoInformado' => 2,
                'alturaInformada' => (int) $altFinal,
                'larguraInformada' => (int) $larFinal,
                'comprimentoInformado' => (int) $comFinal,
                'cienteObjetoNaoProibido' => '1',
                'numeroNotaFiscal' => (string) $pedido->id,
                'itensDeclaracaoConteudo' => $itensDeclaracao,
            ];

            $response = Http::withToken($token)
                ->post('https://api.correios.com.br/prepostagem/v1/prepostagens', $payload);

            if ($response->failed()) {
                $erroDetalhado = $response->json();

                return response()->json([
                    'error' => 'Os Correios recusaram os dados do pedido.',
                    'message' => $this->traduzirErroCorreios($erroDetalhado),
                    'debug' => $erroDetalhado,
                ], 400);
            }

            $idPrePostagem = $response->json('id');

            if (!$idPrePostagem) {
                return response()->json([
                    'error' => 'Pré-postagem não confirmada.',
                    'message' => 'Os Correios não devolveram o identificador da pré-postagem.',
                ], 422);
            }

            $resRotulo = Http::withToken($token)
                ->post('https://api.correios.com.br/prepostagem/v1/prepostagens/rotulo/assincrono/pdf', [
                    'idCorreios' => (string) $configEcommerce->correios_usuario,
                    'numeroCartaoPostagem' => (string) $configEcommerce->correios_cartao,
                    'tipoRotulo' => 'P',
                    'formatoRotulo' => 'ET',
                    'imprimeRemetente' => 'S',
                    'idsPrePostagem' => [$idPrePostagem],
                ]);

            if ($resRotulo->failed()) {
                return response()->json([
                    'error' => 'Falha ao solicitar a etiqueta.',
                    'message' => 'O pedido foi registrado, mas os Correios não conseguiram iniciar a geração do PDF.',
                ], 400);
            }

            $idRecibo = $resRotulo->json('idRecibo');

            if (!$idRecibo) {
                return response()->json([
                    'error' => 'Recibo não encontrado.',
                    'message' => 'Os Correios não devolveram o código de processamento do documento.',
                ], 400);
            }

            $dadosEtiqueta = null;

            for ($i = 0; $i < 12; $i++) {
                sleep(3);

                $resDown = Http::withToken($token)
                    ->get("https://api.correios.com.br/prepostagem/v1/prepostagens/rotulo/download/assincrono/{$idRecibo}");

                if (!$resDown->successful()) {
                    continue;
                }

                $jsonDown = $resDown->json();

                if (!empty($jsonDown['dados'])) {
                    $dadosEtiqueta = $jsonDown['dados'];
                    break;
                }

                if (($jsonDown['status'] ?? '') === 'ERRO') {
                    return response()->json([
                        'error' => 'Erro no processamento do PDF.',
                        'message' => 'Os Correios informaram um erro ao tentar gerar o arquivo final.',
                    ], 422);
                }
            }

            if (!$dadosEtiqueta) {
                return response()->json([
                    'error' => 'O PDF demorou muito para ser gerado.',
                    'message' => 'O sistema dos Correios está lento. Tente gerar a etiqueta novamente em instantes.',
                ], 408);
            }

            $pdfNome = 'Etiqueta_Pedido_' . $pedido->id . '.pdf';

            return response(base64_decode($dadosEtiqueta))
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="' . $pdfNome . '"');
        } catch (\Exception $e) {
            Log::error('Erro Etiqueta Correios: ' . $e->getMessage());

            return response()->json([
                'error' => 'Erro interno no sistema.',
                'message' => 'Ocorreu uma falha inesperada ao gerar a etiqueta.',
            ], 500);
        }
    }

    private function traduzirErroCorreios($erro)
    {
        $msg = json_encode($erro);

        if (str_contains($msg, 'RTL-076') || str_contains($msg, 'UF do remetente')) {
            return 'O Estado (UF) do remetente não combina com o CEP informado. Confira o endereço cadastrado da empresa.';
        }

        if (str_contains($msg, 'CEP de destino invalido')) {
            return 'O CEP do cliente está incorreto ou não existe na base dos Correios.';
        }

        if (str_contains($msg, 'Peso informado excede')) {
            return 'O pacote está pesado demais para o serviço selecionado. Verifique o peso dos produtos.';
        }

        if (str_contains($msg, 'comprimento') || str_contains($msg, 'largura') || str_contains($msg, 'altura')) {
            return 'As dimensões do pacote estão fora dos limites permitidos pelos Correios.';
        }

        if (str_contains($msg, 'cpfCnpj') || str_contains($msg, 'documento')) {
            return 'O CPF ou CNPJ informado do cliente ou da empresa é inválido.';
        }

        if (str_contains($msg, 'logradouro')) {
            return 'O endereço possui caracteres inválidos ou está incompleto. Verifique rua e bairro.';
        }

        $msgExtraida = $erro['msgs'][0] ?? 'Dados de endereço ou documentos inválidos.';

        return 'Os Correios recusaram os dados: ' . $msgExtraida;
    }

    public function declaracaoConteudo($id)
    {
        $pedido = $this->pedidoDaEmpresa((int) $id, [
            'cliente',
            'endereco',
            'empresa.cidade',
            'itens.produtoEcommerce.produto',
        ]);

        $config_ecomercres = ConfigEcommerce::where('empresa_id', $pedido->empresa_id)->first();
        $pdf = Pdf::loadView('pedidos_ecommerce.declaracao_print', compact('pedido', 'config_ecomercres'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream("Declaracao_{$pedido->id}.pdf");
    }

    public function danfeSimulada($id)
    {
        $pedido = $this->pedidoDaEmpresa((int) $id, [
            'cliente',
            'endereco',
            'empresa.cidade',
            'itens.produtoEcommerce.produto',
        ]);

        $config = DB::table('config_notas')->where('empresa_id', $pedido->empresa_id)->first();
        $chave = str_pad($pedido->id, 44, '0', STR_PAD_LEFT);
        $pdf = Pdf::loadView('pedidos_ecommerce.danfe', compact('pedido', 'chave', 'config'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream("DANFE_{$pedido->id}.pdf");
    }

    public function alterarStatus($id, $status, $tipo)
    {
        $statusPagamentoPermitidos = ['pending', 'approved', 'canceled', 'rejected', 'refunded'];
        $statusPedidoPermitidos = ['novo', 'preparacao', 'enviado', 'entregue', 'cancelado'];

        try {
            $pedido = $this->pedidoDaEmpresa((int) $id);

            if ($tipo === 'pagamento') {
                abort_unless(in_array($status, $statusPagamentoPermitidos, true), 422, 'Status de pagamento inválido.');
                $pedido->status_pagamento = $status;
            } elseif ($tipo === 'pedido') {
                abort_unless(in_array($status, $statusPedidoPermitidos, true), 422, 'Status do pedido inválido.');
                $pedido->status = $status;
            } else {
                abort(422, 'Tipo de atualização inválido.');
            }

            $pedido->save();

            return redirect()->back()->with('success', 'Status do pedido atualizado com sucesso.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            abort(404);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar pedido e-commerce: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Não foi possível atualizar o status do pedido.');
        }
    }
}