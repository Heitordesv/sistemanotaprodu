<?php

namespace App\Http\Controllers;

use App\Models\AberturaCaixa;
use App\Models\ConfigNota;
use App\Models\SangriaCaixa;
use App\Models\SuprimentoCaixa;
use App\Models\Usuario;
use App\Models\Venda;
use App\Models\VendaCaixa;
use Dompdf\Dompdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AberturaCaixaController extends Controller
{
    protected $empresa_id = null;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->empresa_id = $request->empresa_id;

            if (!session('user_logged')) {
                return redirect('/login');
            }

            return $next($request);
        });
    }

    /**
     * Abre uma sessão independente para o operador atual.
     *
     * Regra: a empresa pode ter vários caixas abertos ao mesmo tempo,
     * mas cada operador pode possuir somente uma abertura ativa.
     */
    public function store(Request $request)
    {
        $usuarioId = (int) get_id_user();
        $empresaId = (int) $this->empresa_id;

        try {
            $abertura = DB::transaction(function () use ($request, $usuarioId, $empresaId) {
                // Serializa somente as tentativas de abertura deste operador.
                // Usuários diferentes continuam podendo abrir caixas simultaneamente.
                Usuario::query()
                    ->where('id', $usuarioId)
                    ->where('empresa_id', $empresaId)
                    ->lockForUpdate()
                    ->firstOrFail();

                $aberturaExistente = AberturaCaixa::query()
                    ->where('empresa_id', $empresaId)
                    ->where('usuario_id', $usuarioId)
                    ->where('status', 0)
                    ->lockForUpdate()
                    ->orderByDesc('id')
                    ->first();

                if ($aberturaExistente) {
                    throw new \DomainException(
                        'Você já possui o Caixa #' . $aberturaExistente->id . ' aberto.'
                    );
                }

                $ultimaVendaNfceId = (int) (VendaCaixa::query()
                    ->where('empresa_id', $empresaId)
                    ->where('usuario_id', $usuarioId)
                    ->max('id') ?? 0);

                $ultimaVendaNfeId = (int) (Venda::query()
                    ->where('empresa_id', $empresaId)
                    ->where('usuario_id', $usuarioId)
                    ->max('id') ?? 0);

                return AberturaCaixa::create([
                    'usuario_id' => $usuarioId,
                    'valor' => __convert_value_bd($request->valor),
                    'empresa_id' => $empresaId,
                    'primeira_venda_nfe' => $ultimaVendaNfeId,
                    'primeira_venda_nfce' => $ultimaVendaNfceId,
                    'status' => 0,
                    'filial_id' => (int) $request->filial_id === -1
                        ? null
                        : ($request->filial_id ?: null),
                ]);
            });

            session()->flash(
                'flash_sucesso',
                'Caixa #' . $abertura->id . ' aberto com sucesso para o seu usuário!'
            );
        } catch (\DomainException $e) {
            session()->flash('flash_warning', $e->getMessage());
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Erro ao abrir o caixa: ' . $e->getMessage());
            __saveLogError($e, $empresaId);
        }

        return redirect()->back();
    }

    private function verificaAberturaCaixa()
    {
        return AberturaCaixa::query()
            ->where('empresa_id', $this->empresa_id)
            ->where('usuario_id', get_id_user())
            ->where('status', 0)
            ->orderByDesc('id')
            ->first();
    }

    public function index()
    {
        $usuarioId = (int) get_id_user();

        $config = ConfigNota::where('empresa_id', $this->empresa_id)->first();
        if (!$config) {
            session()->flash('flash_erro', 'Configure o emitente');
            return redirect()->route('configNF.index');
        }

        $abertura = AberturaCaixa::with(['usuario', 'filial'])
            ->where('empresa_id', $this->empresa_id)
            ->where('usuario_id', $usuarioId)
            ->where('status', 0)
            ->orderByDesc('id')
            ->first();

        $caixa = $abertura ? $this->dadosDaAbertura($abertura) : [];
        $usuario = Usuario::findOrFail($usuarioId);

        return view('caixa.index', compact(
            'config',
            'abertura',
            'caixa',
            'usuario'
        ));
    }

    /**
     * Retorna somente as movimentações pertencentes ao operador e ao intervalo
     * desta abertura. É usado pela tela, detalhes e relatórios para evitar
     * divergência entre os diferentes pontos do sistema.
     */
    private function dadosDaAbertura(AberturaCaixa $abertura): array
    {
        $empresaId = (int) $abertura->empresa_id;
        $usuarioId = (int) $abertura->usuario_id;
        $caixaAberto = (int) $abertura->status === 0;
        $fim = $caixaAberto ? now() : $abertura->updated_at;

        if ($caixaAberto) {
            $ultimaVendaNfceId = (int) (VendaCaixa::query()
                ->where('empresa_id', $empresaId)
                ->where('usuario_id', $usuarioId)
                ->max('id') ?? 0);

            $ultimaVendaNfeId = (int) (Venda::query()
                ->where('empresa_id', $empresaId)
                ->where('usuario_id', $usuarioId)
                ->max('id') ?? 0);
        } else {
            $ultimaVendaNfceId = (int) $abertura->ultima_venda_nfce;
            $ultimaVendaNfeId = (int) $abertura->ultima_venda_nfe;
        }

        $vendasPdv = collect();
        if ($ultimaVendaNfceId > (int) $abertura->primeira_venda_nfce) {
            $vendasPdv = VendaCaixa::query()
                ->where('empresa_id', $empresaId)
                ->where('usuario_id', $usuarioId)
                ->where('id', '>', (int) $abertura->primeira_venda_nfce)
                ->where('id', '<=', $ultimaVendaNfceId)
                ->get();
        }

        $vendasNfe = collect();
        if ($ultimaVendaNfeId > (int) $abertura->primeira_venda_nfe) {
            $vendasNfe = Venda::query()
                ->where('empresa_id', $empresaId)
                ->where('usuario_id', $usuarioId)
                ->where('id', '>', (int) $abertura->primeira_venda_nfe)
                ->where('id', '<=', $ultimaVendaNfeId)
                ->get();
        }

        $suprimentos = SuprimentoCaixa::query()
            ->where('empresa_id', $empresaId)
            ->where('usuario_id', $usuarioId)
            ->whereBetween('created_at', [$abertura->created_at, $fim])
            ->get();

        $sangrias = SangriaCaixa::query()
            ->where('empresa_id', $empresaId)
            ->where('usuario_id', $usuarioId)
            ->whereBetween('created_at', [$abertura->created_at, $fim])
            ->get();

        $vendas = $this->agrupaVendas($vendasNfe, $vendasPdv);

        return [
            'vendas' => $vendas,
            'suprimentos' => $suprimentos,
            'sangrias' => $sangrias,
            'somaTiposPagamento' => $this->somaTiposPagamento($vendas),
        ];
    }

    private function dadosSessaoLogada(): array
    {
        $user = session('user_logged');

        if (!$user) {
            abort(401, 'Sessão expirada.');
        }

        return [
            'empresa_id' => (int) (is_object($user) ? $user->empresa_id : $user['empresa']),
            'usuario_id' => (int) (is_object($user) ? $user->id : $user['id']),
            'is_admin' => (bool) (
                (is_object($user) ? ($user->adm ?? false) : ($user['adm'] ?? false))
                || (is_object($user) ? ($user->super ?? false) : ($user['super'] ?? false))
            ),
        ];
    }

    private function aberturaAutorizada(int $id): AberturaCaixa
    {
        $sessao = $this->dadosSessaoLogada();

        return AberturaCaixa::with(['usuario', 'filial'])
            ->where('id', $id)
            ->where('empresa_id', $sessao['empresa_id'])
            ->when(!$sessao['is_admin'], function ($query) use ($sessao) {
                return $query->where('usuario_id', $sessao['usuario_id']);
            })
            ->firstOrFail();
    }

    public function detalhes(Request $request, $id)
    {
        if (!session()->has('user_logged')) {
            return redirect('/login')
                ->with('flash_erro', 'Sessão expirada. Faça login novamente.');
        }

        $abertura = $this->aberturaAutorizada((int) $id);
        $dados = $this->dadosDaAbertura($abertura);

        $vendas = $dados['vendas'];
        $suprimentos = $dados['suprimentos'];
        $sangrias = $dados['sangrias'];
        $somaTiposPagamento = $dados['somaTiposPagamento'];

        return view('caixa.detalhes', compact(
            'abertura',
            'vendas',
            'suprimentos',
            'sangrias',
            'somaTiposPagamento'
        ));
    }

    public function list(Request $request)
    {
        if (!session()->has('user_logged')) {
            return redirect('/login')
                ->with('flash_erro', 'Sessão expirada. Faça login novamente.');
        }

        $sessao = $this->dadosSessaoLogada();
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $data = AberturaCaixa::with(['usuario', 'filial'])
            ->where('empresa_id', $sessao['empresa_id'])
            ->when(!$sessao['is_admin'], function ($query) use ($sessao) {
                return $query->where('usuario_id', $sessao['usuario_id']);
            })
            ->when(!empty($startDate), function ($query) use ($startDate) {
                return $query->whereDate('created_at', '>=', $startDate);
            })
            ->when(!empty($endDate), function ($query) use ($endDate) {
                return $query->whereDate('created_at', '<=', $endDate);
            })
            // Os abertos aparecem primeiro para o administrador visualizar
            // Caixa X e Caixa Y separadamente antes do histórico fechado.
            ->orderBy('status', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate(env('PAGINACAO', 15))
            ->withQueryString();

        return view('caixa.list', compact('data'));
    }

    public function imprimir($id)
    {
        if (!session()->has('user_logged')) {
            return redirect('/login')
                ->with('flash_erro', 'Sessão expirada. Faça login novamente.');
        }

        $abertura = $this->aberturaAutorizada((int) $id);
        $dados = $this->dadosDaAbertura($abertura);

        $vendas = $dados['vendas'];
        $suprimentos = $dados['suprimentos'];
        $sangrias = $dados['sangrias'];
        $somaTiposPagamento = $dados['somaTiposPagamento'];
        $usuario = $abertura->usuario ?: Usuario::findOrFail($abertura->usuario_id);
        $config = ConfigNota::where('empresa_id', $abertura->empresa_id)->first();

        $html = view('caixa.relatorio', compact(
            'abertura',
            'vendas',
            'suprimentos',
            'sangrias',
            'usuario',
            'config',
            'somaTiposPagamento'
        ))->render();

        $domPdf = new Dompdf(['enable_remote' => true]);
        $domPdf->loadHtml($html);
        $domPdf->setPaper('A4');
        $domPdf->render();

        return $domPdf->stream('Relatorio_caixa_' . $abertura->id . '.pdf', [
            'Attachment' => false,
        ]);
    }

    public function imprimir80($id)
    {
        if (!session()->has('user_logged')) {
            return redirect('/login')
                ->with('flash_erro', 'Sessão expirada. Faça login novamente.');
        }

        $abertura = $this->aberturaAutorizada((int) $id);
        $dados = $this->dadosDaAbertura($abertura);

        $vendas = $dados['vendas'];
        $suprimentos = $dados['suprimentos'];
        $sangrias = $dados['sangrias'];
        $somaTiposPagamento = $dados['somaTiposPagamento'];
        $usuario = $abertura->usuario ?: Usuario::find($abertura->usuario_id);
        $config = ConfigNota::where('empresa_id', $abertura->empresa_id)->first();

        $somaVendas = 0;
        foreach ($vendas as $v) {
            if (
                strtoupper((string) ($v->estado ?? '')) !== 'CANCELADO'
                && strtoupper((string) ($v->estado_emissao ?? '')) !== 'CANCELADO'
                && !$v->rascunho
                && !$v->consignado
            ) {
                $total = (float) $v->valor_total;

                if (!isset($v->cpf)) {
                    $total = $total - (float) $v->desconto + (float) $v->acrescimo;
                }

                $somaVendas += $total;
            }
        }

        $html = view('caixa.relatorio80', compact(
            'abertura',
            'vendas',
            'suprimentos',
            'sangrias',
            'usuario',
            'config',
            'somaTiposPagamento',
            'somaVendas'
        ))->render();

        $dompdf = new Dompdf(['enable_remote' => true]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper([0, 0, 226.77, 2000]);
        $dompdf->render();

        $pdf = $dompdf->output();

        return response()->streamDownload(
            function () use ($pdf) {
                echo $pdf;
            },
            'fechamento_caixa_' . $abertura->id . '.pdf',
            ['Content-Type' => 'application/pdf']
        );
    }

    private function agrupaVendas($vendas, $vendasPdv)
    {
        $temp = [];

        foreach ($vendas as $v) {
            $v->tipo = 'VENDA';
            $temp[] = $v;
        }

        foreach ($vendasPdv as $v) {
            $v->tipo = 'PDV';
            $temp[] = $v;
        }

        return $temp;
    }

    private function preparaTipos()
    {
        $temp = [];

        foreach (VendaCaixa::tiposPagamento() as $key => $tp) {
            $temp[$key] = 0;
        }

        return $temp;
    }

    private function somaTiposPagamento($vendas)
    {
        $tipos = $this->preparaTipos();

        foreach ($vendas as $v) {
            if (
                strtoupper((string) ($v->estado_emissao ?? '')) === 'CANCELADO'
                || strtoupper((string) ($v->estado ?? '')) === 'CANCELADO'
            ) {
                continue;
            }

            if ((string) $v->tipo_pagamento !== '99') {
                if (!isset($tipos[$v->tipo_pagamento])) {
                    continue;
                }

                if (isset($v->NFcNumero)) {
                    if (!$v->rascunho && !$v->consignado) {
                        $tipos[$v->tipo_pagamento] += (float) $v->valor_total;
                    }
                    continue;
                }

                if ($v->duplicatas && count($v->duplicatas) > 0) {
                    foreach ($v->duplicatas as $d) {
                        $key = Venda::getTipoPagamentoNFe($d->tipo_pagamento);
                        if (isset($tipos[$key])) {
                            $tipos[$key] += (float) $d->valor_integral;
                        }
                    }
                } else {
                    $tipos[$v->tipo_pagamento] += (float) $v->valor_total;
                }

                continue;
            }

            if ($v->fatura) {
                foreach ($v->fatura as $f) {
                    $key = trim((string) $f->forma_pagamento);
                    if (isset($tipos[$key])) {
                        $tipos[$key] += (float) $f->valor;
                    }
                }
            }
        }

        return $tipos;
    }
}
