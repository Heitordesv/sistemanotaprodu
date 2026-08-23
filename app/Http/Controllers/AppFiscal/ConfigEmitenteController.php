<?php

namespace App\Http\Controllers\AppFiscal;

use Illuminate\Http\Request;
use App\Models\ConfigNota;
use App\Models\Produto;
use App\Models\Venda;
use App\Models\NaturezaOperacao;
use App\Services\FiscalCertificateService;

class ConfigEmitenteController extends Controller
{
    public function __construct(private FiscalCertificateService $certificates)
    {
    }

    public function index(Request $request)
    {
        $config = ConfigNota::where('empresa_id', $request->empresa_id)->first();

        if ($config) {
            $config->cnpj = str_replace(' ', '', $config->cnpj);
        }

        return response()->json([
            'config' => $config,
            'certificado_configurado' => $this->certificates->forEmpresa((int) $request->empresa_id) !== null,
            'dados' => $this->dadosParaCadastro((int) $request->empresa_id),
        ], 200);
    }

    public function salvar(Request $request)
    {
        $empresaId = (int) $request->empresa_id;
        $config = ConfigNota::where('empresa_id', $empresaId)->first();

        $res = false;
        if ($config == null) {
            $data = [
                'empresa_id' => $empresaId,
                'razao_social' => $request->razao_social,
                'nome_fantasia' => $request->nome_fantasia,
                'cnpj' => $request->cnpj,
                'ie' => $request->ie,
                'logradouro' => $request->logradouro,
                'numero' => $request->numero,
                'bairro' => $request->bairro,
                'municipio' => $request->municipio,
                'codMun' => $request->codMun,
                'pais' => $request->pais,
                'codPais' => $request->codPais,
                'fone' => $request->fone,
                'cep' => $request->cep,
                'UF' => $request->UF,
                'CST_CSOSN_padrao' => $request->CST_CSOSN_padrao,
                'CST_COFINS_padrao' => $request->CST_COFINS_padrao,
                'CST_PIS_padrao' => $request->CST_PIS_padrao,
                'CST_IPI_padrao' => $request->CST_IPI_padrao,
                'frete_padrao' => $request->frete_padrao,
                'tipo_pagamento_padrao' => $request->tipo_pagamento_padrao,
                'nat_op_padrao' => $request->nat_op_padrao,
                'ambiente' => $request->ambiente,
                'cUF' => ConfigNota::getCodUF($request->UF),
                'ultimo_numero_nfe' => $request->ultimo_numero_nfe,
                'ultimo_numero_nfce' => $request->ultimo_numero_nfce,
                'ultimo_numero_cte' => $request->ultimo_numero_cte,
                'ultimo_numero_mdfe' => $request->ultimo_numero_mdfe,
                'numero_serie_nfe' => $request->numero_serie_nfe,
                'numero_serie_nfce' => $request->numero_serie_nfce,
                'csc' => $request->filled('csc') ? $request->csc : '',
                'csc_id' => $request->csc_id,
                'certificado_a3' => false,
            ];
            $res = ConfigNota::create($data);
        } else {
            $config->razao_social = $request->razao_social;
            $config->nome_fantasia = $request->nome_fantasia;
            $config->cnpj = $request->cnpj;
            $config->ie = $request->ie;
            $config->logradouro = $request->logradouro;
            $config->numero = $request->numero;
            $config->bairro = $request->bairro;
            $config->municipio = $request->municipio;
            $config->codMun = $request->codMun;
            $config->pais = $request->pais;
            $config->codPais = $request->codPais;
            $config->fone = $request->fone;
            $config->cep = $request->cep;
            $config->UF = $request->UF;
            $config->CST_CSOSN_padrao = $request->CST_CSOSN_padrao;
            $config->CST_COFINS_padrao = $request->CST_COFINS_padrao;
            $config->CST_PIS_padrao = $request->CST_PIS_padrao;
            $config->CST_IPI_padrao = $request->CST_IPI_padrao;
            $config->frete_padrao = $request->frete_padrao;
            $config->tipo_pagamento_padrao = $request->tipo_pagamento_padrao;
            $config->nat_op_padrao = $request->nat_op_padrao;
            $config->ambiente = $request->ambiente;
            $config->cUF = ConfigNota::getCodUF($request->UF);
            $config->ultimo_numero_nfe = $request->ultimo_numero_nfe;
            $config->ultimo_numero_nfce = $request->ultimo_numero_nfce;
            $config->ultimo_numero_cte = $request->ultimo_numero_cte;
            $config->ultimo_numero_mdfe = $request->ultimo_numero_mdfe;
            $config->numero_serie_nfe = $request->numero_serie_nfe;
            $config->numero_serie_nfce = $request->numero_serie_nfce;
            if ($request->filled('csc')) {
                $config->csc = $request->csc;
            }
            $config->csc_id = $request->csc_id;
            $res = $config->save();
        }

        return response()->json([
            'ok' => (bool) $res,
            'certificado_configurado' => $this->certificates->forEmpresa($empresaId) !== null,
        ], 200);
    }

    public function dadosParaCadastro($empresa_id)
    {
        return [
            'listaCSTCSOSN' => $this->itetable(Produto::listaCSTCSOSN()),
            'listaCST_PIS_COFINS' => $this->itetable(Produto::listaCST_PIS_COFINS()),
            'listaCST_IPI' => $this->itetable(Produto::listaCST_IPI()),
            'ufs' => $this->itetable(ConfigNota::estados()),
            'tiposPagamento' => $this->itetable(Venda::tiposPagamento()),
            'tiposFrete' => $this->itetable(ConfigNota::tiposFrete()),
            'naturezas' => NaturezaOperacao::where('empresa_id', $empresa_id)->get(),
        ];
    }

    private function itetable($array)
    {
        $temp = [];
        foreach ($array as $key => $a) {
            $temp[] = [
                'cod' => $key,
                'value' => $a,
            ];
        }

        return $temp;
    }

    public function dadosCertificado(Request $request)
    {
        $dados = $this->certificates->publicInfoForEmpresa((int) $request->empresa_id);

        return $dados !== null
            ? response()->json($dados, 200)
            : response()->json(['configurado' => false], 404);
    }

    public function salvarCertificado(Request $request)
    {
        $request->validate([
            'file' => 'required|string',
            'senha' => 'required|string|max:255',
        ]);

        try {
            $this->certificates->replaceForEmpresa(
                (int) $request->empresa_id,
                (string) $request->file,
                (string) $request->senha
            );

            return response()->json([
                'ok' => true,
                'certificado' => $this->certificates->publicInfoForEmpresa((int) $request->empresa_id),
            ], 201);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'ok' => false,
                'message' => 'Não foi possível salvar o certificado digital.',
            ], 422);
        }
    }
}
