<?php

namespace App\Http\Controllers;

use App\Models\WsAdicionaisCat;
use App\Models\Cat_ws;
use App\Models\Item;
use App\Models\ConfigNota;
use App\Models\WsAdicionalItemGratis;
use Illuminate\Http\Request;


class AdicionalgratisController extends Controller
{
    /**
     * Exibe as categorias relacionadas ao usuário.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Buscando a configuração da nota fiscal para a empresa
        $config = ConfigNota::where('empresa_id', $request->empresa_id)->first();

        // Se a configuração não for encontrada, retorna uma resposta em JSON
        if (!$config) {
            return response()->json([
                'data' => [],
                'message' => 'Configuração não encontrada.'
            ]);
        }

        // Pega o ID do usuário associado à configuração
        $userId = $config->user_id;

        // Busca as categorias associadas ao usuário
        $categorias = Cat_ws::where('user_id', $userId)
            ->orderBy('nome_cat')  // Ordena por nome da categoria
            ->get();

        $coplementoAdd = WsAdicionalItemGratis::where('user_id', $config->user_id)
            ->orderBy('nome_adicional_gratis')
            ->get();

        // Retorna a view com as categorias
        return view('add_adicionais_gratis.index', compact('categorias', 'coplementoAdd'));
    }

   public function getItensPorCategoria(Request $request)
    {
        // Busca os itens associados à categoria

        $categorias = $request->input('categorias', []);

        if (empty($categorias)) {
            return response()->json(['items' => []]);
        }

        $config = ConfigNota::where('empresa_id', $request->empresa_id)->first();
        $itens = WsAdicionaisCat::with('Item')->where('user_id', $config->user_id)->whereIn('id_cat', $categorias)->where('pay', 0)->get();  // Obtém id e nome do item, para usá-los no front-end

        // Retorna os itens encontrados como resposta JSON
        return response()->json($itens);
    }

    public function store(Request $request)
    {
        // Validação dos dados recebidos
        $request->validate([
            'nome_adicional_gratis' => 'required|string|max:255',
            'categorias_adicional' => 'required|string', // Recebe string "203,200"
            'itens_adicional' => 'required|string',      // Recebe string "480,481,482,483"
        ]);
        
        $name = trim(strip_tags($request->nome_adicional_gratis));

        // Transforma as strings em arrays
        $listCats = array_filter(explode(',', $request->categorias_adicional));
        $listItens = array_filter(explode(',', $request->itens_adicional));

        // Busca configuração da empresa
        $config = ConfigNota::where('empresa_id', $request->empresa_id)->first();
        if (!$config) {
            return back()->with('error', 'Configuração não encontrada.');
        }

        $userId = $config->user_id;

        // Busca os itens
        $itens = WsAdicionaisCat::whereIn('id', $listItens)
            ->where('user_id', $userId)
            ->get();

        if ($itens->isEmpty()) {
            return back()->with('error', 'Nenhum item encontrado.');
        }

        // Salva os adicionais grátis
        $successCount = 0;

        foreach ($listCats as $catId) {
            foreach ($itens as $item) {
                
                if ((int)$item->id_cat === (int)$catId) {
                   
                    $adicional = new WsAdicionalItemGratis();
                    $adicional->nome_adicional_gratis = $name;
                    $adicional->categorias_adicional_gratis = $catId;
                    $adicional->id_adicionais_cat = $item->id;
                    $adicional->user_id = $userId;
                    $adicional->status_adicional_gratis = 1; // ativo

                    $adicional->save();
                    $successCount++;
                }
            }
        }
        
        if ($successCount > 0) {
            return redirect()->route('add_adicionais_gratis.index')
                ->with('success', 'Condições vinculadas com sucesso.');
        }

        return back()->with('error', 'Nenhuma categoria foi vinculada.');
    }

public function atualizarDisponibilidade(Request $request)
{
    try {
        // Corrigido para pegar o nome certo do campo do form
        $adicional = WsAdicionalItemGratis::findOrFail($request->id_adicional_gratis);

        // Alternar o status
        $adicional->status_adicional_gratis = $adicional->status_adicional_gratis == 1 ? 0 : 1;
        $adicional->save();

        $statusMessage = $adicional->status_adicional_gratis == 1 ? 'ativado' : 'pausado';

        return redirect()->route('add_adicionais_gratis.index')
                         ->with('success', "Disponibilidade do adicional {$statusMessage} com sucesso!");
    } catch (\Exception $e) {
        return redirect()->route('add_adicionais_gratis.index')
                         ->with('error', 'Erro ao atualizar a disponibilidade do adicional: ' . $e->getMessage());
    }
}

      public function destroy($adicional)
    {

       $adicional = WsAdicionalItemGratis::findOrFail($adicional);

        try {
            $adicional->delete();
            session()->flash('flash_sucesso', 'Apagado com sucesso');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado: ' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return back()->with('error', 'Nenhuma iten foi excluido.');
    }



}