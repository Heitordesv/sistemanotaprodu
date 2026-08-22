<?php

namespace App\Http\Controllers\AppFiscal;

use Illuminate\Http\Request;
use App\Models\ConfigNota;
use App\Models\Produto;
use App\Models\Venda;
use App\Models\Certificado;
use App\Models\NaturezaOperacao;
use NFePHP\Common\Certificate;

class ConfigEmitenteController extends Controller
{
    public function index(Request $request){

        $config = ConfigNota::
        where('empresa_id', $request->empresa_id)
        ->first();

        $config->cnpj = str_replace(" ", "", $config->cnpj);

        $data = [
            'config' => $config,
            'dados' => $this->dadosParaCadastro($request->empresa_id)
        ];
        return response()->json($data, 200);
    }

    public function salvar(Request $request){
        $config = ConfigNota::
        where('empresa_id', $request->empresa_id)
        ->first();

        $res = false;
        if($config == null){
            //create

            $data = [
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
                'csc' => $request->csc,
                'csc_id' => $request->csc_id,
                'certificado_a3' => false
            ];
            $res = ConfigNota::create($data);

        }else{
            //update

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
            $config->csc = $request->csc;
            $config->csc_id = $request->csc_id;
            $res = $config->save();

        }
        return response()->json($res, 200);
    }

    public function dadosParaCadastro($empresa_id){
        $data = [
            'listaCSTCSOSN' => $this->itetable(Produto::listaCSTCSOSN()),
            'listaCST_PIS_COFINS' => $this->itetable(Produto::listaCST_PIS_COFINS()),
            'listaCST_IPI' => $this->itetable(Produto::listaCST_IPI()),
            'ufs' => $this->itetable(ConfigNota::estados()),
            'tiposPagamento' => $this->itetable(Venda::tiposPagamento()),
            'tiposFrete' => $this->itetable(ConfigNota::tiposFrete()),
            'naturezas' => NaturezaOperacao::where('empresa_id', $empresa_id)->get()
        ];
        return $data;
    }

    private function itetable($array){
        $temp = [];
        foreach($array as $key => $a){
            $t = [
                'cod' => $key,
                'value' => $a
            ];
            array_push($temp, $t);
        }
        return $temp;
    }

    public function dadosCertificado(){
        $certificado = Certificado::first();
        if($certificado != null){
            $dados = $this->getInfoCertificado($certificado);
            return response()->json($dados, 200);

        }else{
            return response()->json("nada", 403);
        }
    }

    private function getInfoCertificado($certificado){

        $infoCertificado = Certificate::readPfx($certificado->arquivo, $certificado->senha);

        $publicKey = $infoCertificado->publicKey;

        $inicio =  $publicKey->validFrom->format('Y-m-d H:i:s');
        $expiracao =  $publicKey->validTo->format('Y-m-d H:i:s');

        return [
            'serial' => $publicKey->serialNumber,
            'inicio' => \Carbon\Carbon::parse($inicio)->format('d-m-Y H:i'),
            'expiracao' => \Carbon\Carbon::parse($expiracao)->format('d-m-Y H:i'),
            'id' => $publicKey->commonName
        ];

    }

    public function salvarCertificado(Request $request){
        $certificado = Certificado::truncate();
        // return response()->json($request->file, 201);

        $res = Certificado::create([
            'senha' => $request->senha,
            'arquivo' => $request->file
        ]);

        return response()->json($res, 201);
    }
}