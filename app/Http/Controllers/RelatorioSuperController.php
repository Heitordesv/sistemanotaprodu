<?php

namespace App\Http\Controllers;

use App\Models\Contador;
use App\Models\Cte;
use App\Models\Empresa;
use App\Models\Mdfe;
use App\Models\Plano;
use App\Models\UsuarioAcesso;
use App\Models\Venda;
use App\Models\VendaCaixa;
use App\Models\Produto;
use App\Models\Cliente;
use App\Models\OrdemServico;

use Illuminate\Http\Request;
use Dompdf\Dompdf;
use NFePHP\Common\Certificate;
use Carbon\Carbon;

class RelatorioSuperController extends Controller
{
    public function index()
    {
        $empresas = Empresa::all();
        $planos = Plano::all();
        $contador = Contador::all();
        return view('relatorio_super.index', compact('empresas', 'planos', 'contador'));
    }

    public function empresas(Request $request)
    {
        $empresaId = $request->empresa;
        $status = $request->status;
        $planoId = $request->plano;

        $empresas = Empresa::query();

        if ($empresaId) {
            $empresas->where('id', $empresaId);
        }

        if ($planoId) {
            $empresas->whereHas('planoEmpresa', function ($q) use ($planoId) {
                $q->where('plano_id', $planoId);
            });
        }

        $empresas = $empresas->get()->filter(function ($e) use ($status) {
            if ($status == 'todos') return true;
            if ($status == 2 && !$e->planoEmpresa) return true;
            return $e->status() == $status;
        });

        $html = view('relatorio_super.empresas', [
            'empresa' => $empresaId,
            'plano' => $planoId,
            'empresas' => $empresas,
            'status' => $status,
            'title' => 'Relatório de Empresas'
        ])->render();

        $this->gerarPDF($html, "Relatório de empresas.pdf", 'landscape');
    }

    public function extratoCliente(Request $request)
    {
        if (!$request->empresa) {
            session()->flash('flash_erro', 'Selecione uma empresa');
            return redirect()->back();
        }

        $empresa = Empresa::findOrFail($request->empresa);
        $acessos = $this->totalizaAcessos($request, $empresa);
        $totalNfe = $this->totalizaNFe($request);
        $totalNfce = $this->totalizaNFCe($request);
        $totalCte = $this->totalizaCTe($request);
        $totalMdfe = $this->totalizaMDFe($request);
        $totalVendas = $this->totalizaVendas($request);
        $totalVendasCaixa = $this->totalizaVendasCaixa($request);

        $html = view('relatorio_super.extrato_cliente', [
            'empresa' => $empresa,
            'acessos' => $acessos,
            'totalNfe' => $totalNfe,
            'totalNfce' => $totalNfce,
            'totalCte' => $totalCte,
            'totalMdfe' => $totalMdfe,
            'totalVendas' => $totalVendas,
            'totalizaVendasCaixa' => $totalVendasCaixa,
            'data_inicial' => $request->start_date,
            'data_final' => $request->end_date,
            'title' => 'Extrato de Cliente'
        ])->render();

        $this->gerarPDF($html, "Relatório de extrato de cliente.pdf");
    }

  public function historicoAcessos(Request $request)
{
    // Exclui empresa com id = 1
    $empresas = Empresa::where('id', '!=', 1)
        ->orderBy('id', 'desc')
        ->get();

    $data = [];

    foreach ($empresas as $e) {
        $request->empresa = $e->id;

        $acessos = $this->totalizaAcessos($request, $e);
        $totalNfe = $this->totalizaNFe($request);
        $totalNfce = $this->totalizaNFCe($request);
        $totalBruto = $this->totalizaVendasBruta($request);
        $totalizaOrdemServico = $this->totalizaOrdemServico($request);
        $totalProdutos = $this->totalizaProdutos($request);
        $totalClientes = $this->totalizaClientes($request);

        if ($acessos > 0) {
            $data[] = [
                'empresa' => $e->nome_fantasia,
                'cpf_cnpj' => $e->cpf_cnpj,
                'telefone' => $e->telefone,
                'acessos' => $acessos,
                'nfes' => $totalNfe,
                'nfces' => $totalNfce,
                'ordenservico' => $totalizaOrdemServico,
                'produtos' => $totalProdutos,
                'clientes' => $totalClientes,
                'bruto' => $totalBruto,
                'data_cadastro' => Carbon::parse($e->created_at)->format('d/m/Y H:i'),
                'plano_nome' => $e->planoEmpresa ? $e->planoEmpresa->plano->nome : '--',
                'plano_valor' => $e->planoEmpresa ? $e->planoEmpresa->valor : 0,
            ];
        }
    }

    // Ordena do maior para o menor número de acessos
    usort($data, fn($a, $b) => $b['acessos'] <=> $a['acessos']);

    $html = view('relatorio_super.extrato_acessos', [
        'data' => $data,
        'data_inicial' => $request->data_inicial,
        'data_final' => $request->data_final,
        'title' => 'Histórico de Acessos',
    ])->render();

    $this->gerarPDF($html, "Relatório de extrato de acessos.pdf", 'landscape');
}

    public function certificados(Request $request)
    {
        $data_inicial = $request->start_date;
        $data_final = $request->end_date;
        $status = $request->status;
        $dataHoje = date('Y-m-d');

        $empresas = Empresa::all();
        $temp = [];

        foreach ($empresas as $e) {
            if ($e->certificado) {
                $infoCertificado = Certificate::readPfx($e->certificado->arquivo, $e->certificado->senha);
                $publicKey = $infoCertificado->publicKey;
                $e->vencimento = $publicKey->validTo->format('Y-m-d');
                $e->vencido = strtotime($dataHoje) > strtotime($e->vencimento);

                if ($data_inicial && $data_final) {
                    if ((strtotime($e->vencimento) > strtotime($data_inicial)) &&
                        (strtotime($e->vencimento) < strtotime($data_final))) {
                        $temp[] = $e;
                    }
                } elseif ($status != 'TODOS') {
                    if ($status == 1 && $e->vencido) {
                        $temp[] = $e;
                    } elseif ($status == 2 && !$e->vencido) {
                        $temp[] = $e;
                    }
                } else {
                    $temp[] = $e;
                }
            }
        }

        usort($temp, fn($a, $b) => strtotime($a->vencimento) <=> strtotime($b->vencimento));

        $html = view('relatorio_super.relatorio_certificados', [
            'data_inicial' => $data_inicial,
            'data_final' => $data_final,
            'empresas' => $temp,
            'status' => $status,
            'title' => 'Relatório de Certificados'
        ])->render();

        $this->gerarPDF($html, "Relatório de certificados.pdf", 'landscape');
    }

    public function empresasContador(Request $request)
    {
        if (!$request->contador) {
            session()->flash("flash_erro", "Relatório sem registro!");
            return redirect('/relatorioSuper');
        }

        $contador = Contador::findOrFail($request->contador);
        $empresas = Empresa::where('contador_id', $request->contador)->get();
        $dataHoje = date('Y-m-d');

        foreach ($empresas as $e) {
            if ($e->certificado) {
                $infoCertificado = Certificate::readPfx($e->certificado->arquivo, $e->certificado->senha);
                $publicKey = $infoCertificado->publicKey;
                $e->vencimento = $publicKey->validTo->format('Y-m-d');
                $e->vencido = strtotime($dataHoje) > strtotime($e->vencimento);
            }
        }

        $html = view('relatorio_super.empresas_contador', [
            'contador' => $contador,
            'empresas' => $empresas,
            'title' => 'Relatório de empresas contador ' . $contador->razao_social
        ])->render();

        $this->gerarPDF($html, "Relatório de empresas contador.pdf");
    }

    // === Totalizações ===
    private function totalizaAcessos($request, $empresa)
    {
        $cont = 0;
        foreach ($empresa->usuarios as $u) {
            if ($request->start_date && $request->end_date) {
                $cont += UsuarioAcesso::where('usuario_id', $u->id)
                    ->whereBetween('created_at', [$request->start_date, $request->end_date])
                    ->count();
            } else {
                $cont += $u->acessos->count();
            }
        }
        return $cont;
    }

    private function totalizaNFe($request)
    {
        $vendas = Venda::where('empresa_id', $request->empresa)
            ->where('estado_emissao', 'APROVADO')
            ->where('numero_nfe', '>', 0);

        if ($request->start_date && $request->end_date) {
            $vendas->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }

        return $vendas->count();
    }

    private function totalizaNFCe($request)
    {
        $vendas = VendaCaixa::where('empresa_id', $request->empresa)
            ->where('estado_emissao', 'APROVADO') // corrigido
            ->where('numero_nfce', '>', 0);

        if ($request->start_date && $request->end_date) {
            $vendas->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }

        return $vendas->count();
    }

    private function totalizaCTe($request)
    {
        $vendas = Cte::where('empresa_id', $request->empresa)
            ->where('cte_numero', '>', 0);

        if ($request->start_date && $request->end_date) {
            $vendas->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }

        return $vendas->count();
    }

    private function totalizaMDFe($request)
    {
        $vendas = Mdfe::where('empresa_id', $request->empresa)
            ->where('mdfe_numero', '>', 0);

        if ($request->start_date && $request->end_date) {
            $vendas->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }

        return $vendas->count();
    }

    private function totalizaVendas($request)
    {
        $vendas = Venda::where('empresa_id', $request->empresa)
            ->where('estado_emissao', '!=', 'CANCELADO');

        if ($request->start_date && $request->end_date) {
            $vendas->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }

        return $vendas->count();
    }

    private function totalizaVendasCaixa($request)
    {
        $vendas = VendaCaixa::where('empresa_id', $request->empresa)
            ->where('estado_emissao', '!=', 'CANCELADO'); // corrigido

        if ($request->start_date && $request->end_date) {
            $vendas->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }

        return $vendas->count();
    }

    private function totalizaVendasBruta($request)
    {
        $soma = Venda::where('empresa_id', $request->empresa)
            ->where('estado_emissao', '!=', 'CANCELADO')
            ->sum('valor_total');

        $soma += VendaCaixa::where('empresa_id', $request->empresa)
            ->where('estado_emissao', '!=', 'CANCELADO') // corrigido
            ->sum('valor_total');

        return $soma;
    }


    private function gerarPDF($html, $nomeArquivo, $orientacao = 'portrait')
{
    $domPdf = new Dompdf(['enable_remote' => true]);
    $domPdf->loadHtml($html);
    $domPdf->setPaper('A4', $orientacao);
    $domPdf->render();
    $domPdf->stream($nomeArquivo, ['Attachment' => false]);
}

private function totalizaProdutos($request)
{
    return Produto::where('empresa_id', $request->empresa)->count();
}

private function totalizaClientes($request)
{
    return Cliente::where('empresa_id', $request->empresa)->count();
}
private function totalizaOrdemServico($request)
{
    return OrdemServico::where('empresa_id', $request->empresa)->count();
}
}


