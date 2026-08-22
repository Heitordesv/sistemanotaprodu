<?php

namespace App\Http\Controllers;

use App\Models\WsAdicionaisCat;
use App\Models\Cat_ws;
use App\Models\Item;
use App\Models\ConfigNota;
use App\Models\WsAdicionalItem;
use Illuminate\Http\Request;


class AdicionalController extends Controller
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

    if (!$config) {
        return response()->json([
            'data' => [],
            'message' => 'Configuração não encontrada.'
        ]);
    }

    $userId = $config->user_id;

    // Categorias (sem paginação)
    $categorias = Cat_ws::where('user_id', $userId)
        ->orderBy('nome_cat')
        ->get();

   $coplementoAdd = WsAdicionalItem::where('user_id', $userId)
    ->orderBy('id_adicionais', 'desc') // ordem decrescente pelo ID
    ->paginate(30);

    return view('add_adicionais_pagos.index', compact('categorias', 'coplementoAdd'));
}

   public function getItensPorCategoria(Request $request)
    {
        // Busca os itens associados à categoria

        $categorias = $request->input('categorias', []);

        if (empty($categorias)) {
            return response()->json(['items' => []]);
        }

        $config = ConfigNota::where('empresa_id', $request->empresa_id)->first();
        $itens = WsAdicionaisCat::with('Item')->where('user_id', $config->user_id)->whereIn('id_cat', $categorias)->where('pay', 1)->get();  // Obtém id e nome do item, para usá-los no front-end

        // Retorna os itens encontrados como resposta JSON
        return response()->json($itens);
    }

 public function store(Request $request)
{
    // Validação dos dados recebidos
    $request->validate([
        'nome_adicional' => 'required|string|max:255',
        'categorias_adicional' => 'required|string', // Recebe string "203,200"
        'itens_adicional' => 'required|string',      // Recebe string "480,481,482,483"
        'valor_adicional' => 'required|numeric',
        'medida_adicional' => 'required|string',
    ]);

    $name = trim(strip_tags($request->nome_adicional));
    $valor = $request->valor_adicional;
    $medida = $request->medida_adicional;

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

    // Salva os adicionais pagos
    $successCount = 0;

    foreach ($listCats as $catId) {
        foreach ($itens as $item) {
            if ((int)$item->id_cat === (int)$catId) {
                $adicional = new WsAdicionalItem();
                $adicional->nome_adicional = $name;
                $adicional->categorias_adicional = $catId;
                $adicional->id_adicionais_cat = $item->id;
                $adicional->user_id = $userId;
                $adicional->valor_adicional = $valor;
                $adicional->medida_adicional = $medida;
                $adicional->status_adicional = 1;
                $adicional->save();
                $successCount++;
            }
        }
    }

    if ($successCount > 0) {
        return redirect()->route('add_adicionais_pagos.index')
            ->with('success', 'Condições vinculadas com sucesso.');
    }

    return back()->with('error', 'Nenhuma categoria foi vinculada.');
}


public function atualizarDisponibilidade(Request $request)
{
    try {
        $adicional = WsAdicionalItem::findOrFail($request->id_adicionais);

        $adicional->status_adicional = $adicional->status_adicional == 1 ? 0 : 1;
        $adicional->save();

        $statusMessage = $adicional->status_adicional == 1 ? 'ativado' : 'pausado';

        return back()->with('success', "Disponibilidade do adicional {$statusMessage} com sucesso!");
    } catch (\Exception $e) {
        return back()->with('error', 'Erro ao atualizar a disponibilidade do adicional: ' . $e->getMessage());
    }
}


      public function destroy($adicional)
    {

       $adicional = WsAdicionalItem::findOrFail($adicional);

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