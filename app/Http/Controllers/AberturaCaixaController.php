<?php

namespace App\Http\Controllers;

use App\Models\AberturaCaixa;
use App\Models\ConfigNota;
use App\Models\SangriaCaixa;
use App\Models\SuprimentoCaixa;
use App\Models\Usuario;
use App\Models\Venda;
use App\Models\VendaCaixa;
use Illuminate\Http\Request;
use Dompdf\Dompdf;
use NFePHP\DA\NFe\ComprovanteFechamentoCaixa;

class AberturaCaixaController extends Controller
{
    protected $empresa_id = null;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->empresa_id = $request->empresa_id;

            if (!session('user_logged')) {
                return redirect("/login");
            }

            return $next($request);
        });
    }

    /* =====================================================
        ABRIR CAIXA
    ===================================================== */

    public function store(Request $request)
    {
        $usuario_id = get_id_user();

        $verify = $this->verificaAberturaCaixa();

        if ($verify != -1) {
            session()->flash("flash_warning", "Voc¨º j¨¢ possui um caixa aberto.");
            return redirect()->back();
        }

        try {

            $ultimaVendaNfce = VendaCaixa::where('empresa_id', $this->empresa_id)
                ->where('usuario_id', $usuario_id)
                ->orderBy('id', 'desc')
                ->first();

            $ultimaVendaNfe = Venda::where('empresa_id', $this->empresa_id)
                ->where('usuario_id', $usuario_id)
                ->orderBy('id', 'desc')
                ->first();

            AberturaCaixa::create([
                'usuario_id' => $usuario_id,
                'valor' => __convert_value_bd($request->valor),
                'empresa_id' => $this->empresa_id,
                'primeira_venda_nfe' => $ultimaVendaNfe?->id ?? 0,
                'primeira_venda_nfce' => $ultimaVendaNfce?->id ?? 0,
                'status' => 0,
                'filial_id' => $request->filial_id == -1 ? null : $request->filial_id,
            ]);

            session()->flash("flash_sucesso", "Caixa aberto com sucesso!");

        } catch (\Exception $e) {

            session()->flash("flash_erro", "Erro: " . $e->getMessage());
            __saveLogError($e, $this->empresa_id);
        }

        return redirect()->back();
    }

    /* =====================================================
        VERIFICA SE USU0†9RIO TEM CAIXA ABERTO
    ===================================================== */

    private function verificaAberturaCaixa()
    {
        $ab = AberturaCaixa::where('empresa_id', $this->empresa_id)
            ->where('usuario_id', get_id_user())
            ->where('status', 0)
            ->orderBy('id', 'desc')
            ->first();

        return $ab ? $ab->valor : -1;
    }

    /* =====================================================
        INDEX
    ===================================================== */

    public function index()
    {
        $usuario_id = get_id_user();

        $config = ConfigNota::where('empresa_id', $this->empresa_id)->first();
        if (!$config) {
            session()->flash('flash_erro', 'Configure o emitente');
            return redirect()->route('configNF.index');
        }

        $abertura = AberturaCaixa::where('empresa_id', $this->empresa_id)
            ->where('usuario_id', $usuario_id)
            ->where('status', 0)
            ->orderBy('id', 'desc')
            ->first();

        $caixa = $abertura ? $this->getCaixaAberto($usuario_id) : [];

        $usuario = Usuario::findOrFail($usuario_id);

        return view('caixa.index', compact(
            'config',
            'abertura',
            'caixa',
            'usuario'
        ));
    }

    /* =====================================================
        DADOS DO CAIXA ABERTO
    ===================================================== */

    private function getCaixaAberto($usuario_id)
    {
        $abertura = AberturaCaixa::where('empresa_id', $this->empresa_id)
            ->where('usuario_id', $usuario_id)
            ->where('status', 0)
            ->orderBy('id', 'desc')
            ->first();

        if (!$abertura) return [];

        $ultimaVendaCaixa = VendaCaixa::where('empresa_id', $this->empresa_id)
            ->where('usuario_id', $usuario_id)
            ->orderBy('id', 'desc')
            ->first();

        $ultimaVenda = Venda::where('empresa_id', $this->empresa_id)
            ->where('usuario_id', $usuario_id)
            ->orderBy('id', 'desc')
            ->first();

        $vendasPdv = VendaCaixa::whereBetween('id', [
                $abertura->primeira_venda_nfce + 1,
                $ultimaVendaCaixa?->id ?? 0
            ])
            ->where('empresa_id', $this->empresa_id)
            ->where('usuario_id', $usuario_id)
            ->get();

        $vendas = Venda::whereBetween('id', [
                $abertura->primeira_venda_nfe + 1,
                $ultimaVenda?->id ?? 0
            ])
            ->where('empresa_id', $this->empresa_id)
            ->where('usuario_id', $usuario_id)
            ->get();

        $suprimentos = SuprimentoCaixa::whereBetween('created_at', [
                $abertura->created_at,
                now()
            ])
            ->where('empresa_id', $this->empresa_id)
            ->where('usuario_id', $usuario_id)
            ->get();

        $sangrias = SangriaCaixa::whereBetween('created_at', [
                $abertura->created_at,
                now()
            ])
            ->where('empresa_id', $this->empresa_id)
            ->where('usuario_id', $usuario_id)
            ->get();

        return [
            'vendas' => $this->agrupaVendas($vendas, $vendasPdv),
            'suprimentos' => $suprimentos,
            'sangrias' => $sangrias,
            'somaTiposPagamento' => $this->somaTiposPagamento(
                $this->agrupaVendas($vendas, $vendasPdv)
            )
        ];
    }

    /* =====================================================
        DETALHES
    ===================================================== */

public function detalhes(Request $request, $id)
{
    if (!session()->has('user_logged')) {
        return redirect('/login')
            ->with('flash_erro', 'Sessão expirada. Faça login novamente.');
    }

    $user = session('user_logged');

    $empresa_id = is_object($user) ? $user->empresa_id : $user['empresa'];
    $isAdmin = is_object($user) ? $user->adm : $user['adm'];
    $usuario_id = is_object($user) ? $user->id : $user['id'];

    // O caixa sempre precisa pertencer à empresa logada.
    // Usuários comuns só podem consultar caixas abertos por eles.
    $abertura = AberturaCaixa::where('id', $id)
        ->where('empresa_id', $empresa_id)
        ->when($isAdmin != 1, function ($query) use ($usuario_id) {
            return $query->where('usuario_id', $usuario_id);
        })
        ->firstOrFail();

    $inicio = $abertura->created_at;
    $fim = $abertura->updated_at;
    $usuarioCaixaId = $abertura->usuario_id;

    // Mesmo quando um administrador consulta o caixa, as movimentações
    // exibidas devem ser somente do usuário que abriu esse caixa.
    $vendasPdv = VendaCaixa::whereBetween('id', [
            $abertura->primeira_venda_nfce + 1,
            $abertura->ultima_venda_nfce
        ])
        ->where('empresa_id', $empresa_id)
        ->where('usuario_id', $usuarioCaixaId)
        ->get();

    $vendasNfe = Venda::whereBetween('id', [
            $abertura->primeira_venda_nfe + 1,
            $abertura->ultima_venda_nfe
        ])
        ->where('empresa_id', $empresa_id)
        ->where('usuario_id', $usuarioCaixaId)
        ->get();

    $vendas = $this->agrupaVendas($vendasNfe, $vendasPdv);
    $somaTiposPagamento = $this->somaTiposPagamento($vendas);

    $suprimentos = SuprimentoCaixa::whereBetween('created_at', [$inicio, $fim])
        ->where('empresa_id', $empresa_id)
        ->where('usuario_id', $usuarioCaixaId)
        ->get();

    $sangrias = SangriaCaixa::whereBetween('created_at', [$inicio, $fim])
        ->where('empresa_id', $empresa_id)
        ->where('usuario_id', $usuarioCaixaId)
        ->get();

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
    // Verifica sess0Š0o
    if (!session()->has('user_logged')) {
        return redirect('/login')
            ->with('flash_erro', 'Sess0Š0o expirada. Fa0Š4a login novamente.');
    }

    // Dados do usu¨¢rio logado
    $user = session('user_logged');

    $empresa_id = is_object($user) ? $user->empresa_id : $user['empresa'];
    $isAdmin = is_object($user) ? $user->adm : $user['adm'];
    $user_id = is_object($user) ? $user->id : $user['id'];

    // Filtros
    $start_date = $request->start_date;
    $end_date = $request->end_date;

    // Consulta
    $data = AberturaCaixa::where('empresa_id', $empresa_id)

        // Se N0‡1O for admin, mostra apenas os caixas dele
        ->when($isAdmin != 1, function ($query) use ($user_id) {
            return $query->where('usuario_id', $user_id);
        })

        // Filtro data inicial
        ->when(!empty($start_date), function ($query) use ($start_date) {
            return $query->whereDate('created_at', '>=', $start_date);
        })

        // Filtro data final
        ->when(!empty($end_date), function ($query) use ($end_date) {
            return $query->whereDate('created_at', '<=', $end_date);
        })

        ->orderBy('created_at', 'desc')
        ->paginate(env('PAGINACAO', 15));

    return view('caixa.list', compact('data'));
}

 /* ===================================================== */
/* IMPRIMIR A4                                           */
/* ===================================================== */

public function imprimir($id)
{
    // Verifica sess0Š0o
    if (!session()->has('user_logged')) {
        return redirect('/login')
            ->with('flash_erro', 'Sess0Š0o expirada. Fa0Š4a login novamente.');
    }

    // Dados do usu¨¢rio logado
    $user = session('user_logged');

    $empresa_id = is_object($user) ? $user->empresa_id : $user['empresa'];
    $isAdmin = is_object($user) ? $user->adm : $user['adm'];
    $usuario_id = is_object($user) ? $user->id : $user['id'];

    // Busca abertura
    $abertura = AberturaCaixa::where('id', $id)
        ->where('empresa_id', $empresa_id)

        // Se N0‡1O for admin, s¨® pode acessar o pr¨®prio caixa
        ->when($isAdmin != 1, function ($query) use ($usuario_id) {
            return $query->where('usuario_id', $usuario_id);
        })

        ->firstOrFail();

    $inicio = $abertura->created_at;
    $fim    = $abertura->updated_at;

    // VENDAS PDV
    $vendasPdv = VendaCaixa::whereBetween('id', [
            $abertura->primeira_venda_nfce + 1,
            $abertura->ultima_venda_nfce
        ])
        ->where('empresa_id', $empresa_id)

        // Se N0‡1O for admin, filtra pelo usu¨¢rio
        ->when($isAdmin != 1, function ($query) use ($usuario_id) {
            return $query->where('usuario_id', $usuario_id);
        })

        ->get();

    // VENDAS NFE
    $vendasNfe = Venda::whereBetween('id', [
            $abertura->primeira_venda_nfe + 1,
            $abertura->ultima_venda_nfe
        ])
        ->where('empresa_id', $empresa_id)

        // Se N0‡1O for admin, filtra pelo usu¨¢rio
        ->when($isAdmin != 1, function ($query) use ($usuario_id) {
            return $query->where('usuario_id', $usuario_id);
        })

        ->get();

    // Junta vendas
    $vendas = $this->agrupaVendas($vendasNfe, $vendasPdv);

    // Soma tipos pagamento
    $somaTiposPagamento = $this->somaTiposPagamento($vendas);

    // Suprimentos
    $suprimentos = SuprimentoCaixa::whereBetween('created_at', [$inicio, $fim])
        ->where('empresa_id', $empresa_id)

        // Se N0‡1O for admin, filtra pelo usu¨¢rio
        ->when($isAdmin != 1, function ($query) use ($usuario_id) {
            return $query->where('usuario_id', $usuario_id);
        })

        ->get();

    // Sangrias
    $sangrias = SangriaCaixa::whereBetween('created_at', [$inicio, $fim])
        ->where('empresa_id', $empresa_id)

        // Se N0‡1O for admin, filtra pelo usu¨¢rio
        ->when($isAdmin != 1, function ($query) use ($usuario_id) {
            return $query->where('usuario_id', $usuario_id);
        })

        ->get();

    // Usu¨¢rio do caixa
    $usuario = Usuario::findOrFail($abertura->usuario_id);

    // Configura0Š40Š0o
    $config = ConfigNota::where('empresa_id', $empresa_id)->first();

    // Renderiza HTML
    $html = view('caixa.relatorio', compact(
        'abertura',
        'vendas',
        'suprimentos',
        'sangrias',
        'usuario',
        'config',
        'somaTiposPagamento'
    ))->render();

    // PDF
    $domPdf = new Dompdf([
        "enable_remote" => true
    ]);

    $domPdf->loadHtml($html);
    $domPdf->setPaper("A4");
    $domPdf->render();

    return $domPdf->stream("Relatorio_caixa.pdf", [
        "Attachment" => false
    ]);
}
 public function imprimir80($id)
{
    // =========================================
    // VERIFICA SESS0‡1O
    // =========================================

    if (!session()->has('user_logged')) {
        return redirect('/login')
            ->with('flash_erro', 'Sess0Š0o expirada. Fa0Š4a login novamente.');
    }

    // =========================================
    // USU0†9RIO LOGADO
    // =========================================

    $user = session('user_logged');

    $empresa_id = is_object($user) ? $user->empresa_id : $user['empresa'];
    $isAdmin    = is_object($user) ? $user->adm : $user['adm'];
    $usuario_id = is_object($user) ? $user->id : $user['id'];

    // =========================================
    // ABERTURA
    // =========================================

    $abertura = AberturaCaixa::where('id', $id)
        ->where('empresa_id', $empresa_id)

        ->when($isAdmin != 1, function ($query) use ($usuario_id) {
            return $query->where('usuario_id', $usuario_id);
        })

        ->first();

    if (!$abertura) {
        return redirect('/403');
    }

    $inicio = $abertura->created_at;
    $fim    = $abertura->updated_at;

    // =========================================
    // VENDAS PDV
    // =========================================

    $vendasPdv = VendaCaixa::whereBetween('id', [
            $abertura->primeira_venda_nfce + 1,
            $abertura->ultima_venda_nfce
        ])
        ->where('empresa_id', $empresa_id)

        ->when($isAdmin != 1, function ($query) use ($usuario_id) {
            return $query->where('usuario_id', $usuario_id);
        })

        ->get();

    // =========================================
    // VENDAS NFE
    // =========================================

    $vendasNfe = Venda::whereBetween('id', [
            $abertura->primeira_venda_nfe + 1,
            $abertura->ultima_venda_nfe
        ])
        ->where('empresa_id', $empresa_id)

        ->when($isAdmin != 1, function ($query) use ($usuario_id) {
            return $query->where('usuario_id', $usuario_id);
        })

        ->get();

    // =========================================
    // AGRUPA VENDAS
    // =========================================

    $vendas = $this->agrupaVendas($vendasNfe, $vendasPdv);

    // =========================================
    // SOMA PAGAMENTOS
    // =========================================

    $somaTiposPagamento = $this->somaTiposPagamento($vendas);

    // =========================================
    // TOTAL VENDAS
    // =========================================

    $somaVendas = 0;

    foreach ($vendas as $v) {

        if (
            $v->estado != 'CANCELADO' &&
            !$v->rascunho &&
            !$v->consignado
        ) {

            $total = $v->valor_total;

            if (!isset($v->cpf)) {
                $total = $total - $v->desconto + $v->acrescimo;
            }

            $somaVendas += $total;
        }
    }

    // =========================================
    // SUPRIMENTOS
    // =========================================

    $suprimentos = SuprimentoCaixa::whereBetween('created_at', [
            $inicio,
            $fim
        ])
        ->where('empresa_id', $empresa_id)

        ->when($isAdmin != 1, function ($query) use ($usuario_id) {
            return $query->where('usuario_id', $usuario_id);
        })

        ->get();

    // =========================================
    // SANGRIAS
    // =========================================

    $sangrias = SangriaCaixa::whereBetween('created_at', [
            $inicio,
            $fim
        ])
        ->where('empresa_id', $empresa_id)

        ->when($isAdmin != 1, function ($query) use ($usuario_id) {
            return $query->where('usuario_id', $usuario_id);
        })

        ->get();

    // =========================================
    // CONFIG
    // =========================================

    $config = ConfigNota::where('empresa_id', $empresa_id)->first();

    // =========================================
    // USU0†9RIO
    // =========================================

    $usuario = Usuario::find($abertura->usuario_id);

    // =========================================
    // HTML DO PDF
    // =========================================

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

    // =========================================
    // DOMPDF
    // =========================================

    $dompdf = new Dompdf([
        'enable_remote' => true
    ]);

    $dompdf->loadHtml($html);

    // 80mm
    $dompdf->setPaper([0, 0, 226.77, 2000]);

    $dompdf->render();

    // =========================================
    // DOWNLOAD PDF
    // =========================================

    $pdf = $dompdf->output();

    return response()->streamDownload(
        function () use ($pdf) {
            echo $pdf;
        },
        'fechamento_caixa.pdf',
        [
            'Content-Type' => 'application/pdf',
        ]
    );
}
    /* ===================================================== */
    /* M0‡7TODOS AUXILIARES                                    */
    /* ===================================================== */

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

            if ($v->estado_emissao == 'CANCELADO') continue;

            if (!isset($tipos[$v->tipo_pagamento])) continue;

            if ($v->tipo_pagamento != 99) {

                if (isset($v->NFcNumero)) {

                    if (!$v->rascunho && !$v->consignado) {
                        $tipos[$v->tipo_pagamento] += $v->valor_total;
                    }

                } else {

                    if ($v->duplicatas && count($v->duplicatas) > 0) {

                        foreach ($v->duplicatas as $d) {
                            $key = Venda::getTipoPagamentoNFe($d->tipo_pagamento);
                            $tipos[$key] += $d->valor_integral;
                        }

                    } else {
                        $tipos[$v->tipo_pagamento] += $v->valor_total;
                    }
                }

            } else {

                if ($v->fatura) {
                    foreach ($v->fatura as $f) {
                        $tipos[trim($f->forma_pagamento)] += $f->valor;
                    }
                }
            }
        }

        return $tipos;
    }
}