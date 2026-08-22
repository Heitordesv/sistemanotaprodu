<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Mdfe;
use App\Models\Cidade;
use App\Models\ConfigNota;
use App\Models\Venda;
use Illuminate\Http\Request;
use App\Services\MDFeService;

class MdfeController extends Controller
{
    public function linhaInfoDescarregamento(Request $request)
    {
        try {
            $tp_und_transp = $request->tp_und_transp;
            $id_und_transp = $request->id_und_transp;
            $quantidade_rateio = $request->quantidade_rateio;
            $quantidade_rateio_carga = $request->quantidade_rateio_carga;
            $chave_nfe = $request->chave_nfe;
            $chave_cte = $request->chave_cte;
            $municipio_descarregamento = $request->municipio_descarregamento;
            $lacres_transporte = $request->lacres_transporte;
            $lacres_unidade = $request->lacres_unidade;

            $cidade = Cidade::findOrFail($municipio_descarregamento);

            return view('mdfe.partials.row_info', compact(
                'tp_und_transp',
                'id_und_transp',
                'quantidade_rateio',
                'quantidade_rateio_carga',
                'chave_nfe',
                'chave_cte',
                'municipio_descarregamento',
                'lacres_transporte',
                'lacres_unidade',
                'cidade'
            ));
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 401);
        }
    }

    public function transmitir(Request $request)
    {

        $item = Mdfe::findOrFail($request->id);
        $config = ConfigNota::where('empresa_id', $request->empresa_id)
            ->first();

        if ($config == null) {
            return response()->json("Configure o emitente", 401);
        }

        try {
            $cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);
            $mdfe_service = new MDFeService([
                "atualizacao" => date('Y-m-d h:i:s'),
                "tpAmb" => (int)$config->ambiente,
                "razaosocial" => $config->razao_social,
                "siglaUF" => $config->cidade->uf,
                "cnpj" => $cnpj,
                "inscricaomunicipal" => $config->inscricao_municipal,
                "codigomunicipio" => $config->cidade->codigo,
                "schemes" => "PL_MDFe_300a",
                "versao" => '3.00'
            ], $config);

            if ($item->estado_emissao == 'rejeitado' || $item->estado_emissao == 'novo') {

                $mdfe = $mdfe_service->gerar($item);
                if (!isset($mdfe['erros_xml'])) {

                    $signed = $mdfe_service->sign($mdfe['xml']);
                    $resultado = $mdfe_service->transmitir($signed);

                    if (!isset($resultado['erro'])) {
                        $item->chave = $resultado['chave'];
                        $item->estado_emissao = 'aprovado';
                        $item->protocolo = $resultado['protocolo'];

                        $item->mdfe_numero = $mdfe['numero'];
                        $config->ultimo_numero_mdfe = $mdfe['numero'];
                        $config->save();
                        $item->save();

                        $file = file_get_contents(public_path('xml_mdfe/') . $resultado['chave'] . '.xml');

                        return response()->json("[" . $resultado['cStat'] . "] " . $resultado['chave'] . " - " . $resultado['protocolo'], 200);
                    } else {
                        $item->estado_emissao = 'rejeitado';
                        $item->save();
                        return response()->json($resultado['cStat'] . " - " . $resultado['message'], 403);
                    }
                } else {
                    return response()->json($mdfe['erros_xml'], 401);
                }
            }
        } catch (\Exception $e) {
            return response()->json($e->getMessage() . ", l: " . $e->getLine() . ", f: " . $e->getFile(), 404);
        }
    }

    public function consultar(Request $request)
    {
        $mdfe = Mdfe::findOrFail($request->id);

        if ($mdfe->estado_emissao == 'aprovado' || $mdfe->estado_emissao == 'cancelado') {
            $config = ConfigNota::where('empresa_id', $request->empresa_id)
                ->first();
            $cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);

            $mdfe_service = new MDFeService([
                "atualizacao" => date('Y-m-d h:i:s'),
                "tpAmb" => (int)$config->ambiente,
                "razaosocial" => $config->razao_social,
                "siglaUF" => $config->cidade->uf,
                "cnpj" => $cnpj,
                "inscricaomunicipal" => $config->inscricao_municipal,
                "codigomunicipio" => $config->cidade->codigo,
                "schemes" => "PL_MDFe_300a",
                "versao" => '3.00'
            ], $config);

            $mdfe = Mdfe::find($request->id);
            $result = $mdfe_service->consultar($mdfe->chave);

            return response()->json($result, 200);
        } else {
            return response()->json("Erro ao consultar", 404);
        }
    }

    public function cancelar(Request $request)
    {
        $mdfe = Mdfe::findOrFail($request->id);

        if ($mdfe->estado_emissao == 'aprovado') {
            $config = ConfigNota::where('empresa_id', $mdfe->empresa_id)
                ->first();
            $cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);

            $mdfe_service = new MDFeService([
                "atualizacao" => date('Y-m-d h:i:s'),
                "tpAmb" => (int)$config->ambiente,
                "razaosocial" => $config->razao_social,
                "siglaUF" => $config->cidade->uf,
                "cnpj" => $cnpj,
                "inscricaomunicipal" => $config->inscricao_municipal,
                "codigomunicipio" => $config->cidade->codigo,
                "schemes" => "PL_MDFe_300a",
                "versao" => '3.00'
            ], $config);

            $result = $mdfe_service->cancelar($mdfe->chave, $mdfe->protocolo, $request->motivo);

            if ($result->infEvento->cStat == '101' || $result->infEvento->cStat == '135' || $result->infEvento->cStat == '155') {
                $mdfe->estado_emissao = 'cancelado';
                $mdfe->save();
                return response()->json($result, 200);
            } else {

                return response()->json($result, 401);
            }
        } else {
            return response()->json("Erro a MDF-e precisa estar atutorizada para cancelar", 404);
        }
    }

    public function vendasAprovadas(Request $request)
    {
        try {
            $start_date = $request->get('start_date');
            $end_date = $request->get('end_date');
            $data = Venda::orderBy('created_at', 'desc')
                ->where('empresa_id', $request->empresa_id)
                ->when(!empty($start_date), function ($query) use ($start_date) {
                    return $query->whereDate('created_at', '>=', $start_date);
                })
                ->when(!empty($end_date), function ($query) use ($end_date) {
                    return $query->whereDate('created_at', '<=', $end_date);
                })
                ->get();

            return view('mdfe/lista_venda', compact('data'));
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 401);
        }
    }

}
