<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WsAdicionaisCat;
use App\Models\Cat_ws;
use App\Models\Item;
use App\Models\ConfigNota;

class AdicionalcondicaoController extends Controller
{
  public function index(Request $request)
{
    $config = ConfigNota::where('empresa_id', $request->empresa_id)->first();

    if (!$config) {
        return response()->json([
            'data' => [],
            'message' => 'Configuração não encontrada.'
        ]);
    }

    $coplementoAdd = WsAdicionaisCat::where('user_id', $config->user_id)
        ->orderBy('id', 'desc') // ordena por ID do mais novo para o mais antigo
        ->paginate(30);        // paginação de 30 por página

    $categorias = Cat_ws::where('user_id', $config->user_id)
        ->orderBy('nome_cat')
        ->get();

    return view('adicionar_condicao_adicional.index', compact('categorias', 'coplementoAdd'));
}

    public function store(Request $request)
    {
        $request->validate([
            'name_adicionais_cat' => 'required|string|max:255',
            'amount' => 'required|integer',
            'id_cat' => 'required|string',
            'id_itens' => 'required|string',
            'pay' => 'nullable|boolean',
        ]);

        $name = trim(strip_tags($request->name_adicionais_cat));
        $amount = $request->amount;
        $pay = $request->pay ?? 0;

        $listCats = array_filter(explode(',', $request->id_cat));
        $listItens = array_filter(explode(',', $request->id_itens));
        

        $config = ConfigNota::where('empresa_id', $request->empresa_id)->first();
        if (!$config) {
            return back()->with('error', 'Configuração não encontrada.');
        }

        $userId = $config->user_id;

        $itens = Item::whereIn('id', $listItens)
            ->where('user_id', $userId)
            ->get();

        if ($itens->isEmpty()) {
            return back()->with('error', 'Nenhum item encontrado.');
        }

        $successCount = 0;

        foreach ($listCats as $catId) {
            foreach ($itens as $item) {
                if ((int)$item->id_cat === (int)$catId) {
                    $newCat = new WsAdicionaisCat();
                    $newCat->name_adicionais_cat = $name;
                    $newCat->amount = $amount;
                    $newCat->pay = $pay;
                    $newCat->id_cat = $catId;
                    $newCat->id_itens = $item->id;
                    $newCat->user_id = $userId;
                    $newCat->save();

                    $successCount++;
                }
            }
        }

        if ($successCount > 0) {
            return redirect()->route('adicionar_condicao_adicional.index')->with('success', 'Condição vinculadas com sucesso.');
        }

        return back()->with('error', 'Nenhuma categoria foi vinculada.');
    }

    public function getItensPorCategoria(Request $request)
    {
        $categorias = $request->input('categories', []);

        if (empty($categorias)) {
            return response()->json(['items' => []]);
        }

        $config = ConfigNota::where('empresa_id', $request->empresa_id)->first();
        $itens = Item::where('user_id', $config->user_id)->whereIn('id_cat', $categorias)->get();

        return response()->json([
            'items' => $itens
        ]);
    }

    public function listItens($user_id, $cat_id, $type = null)
    {
        $categoryQuery = Item::where('user_id', $user_id)
            ->whereIn('id_cat', (array)$cat_id)
            ->orderBy('id_cat', 'asc')
            ->get();

        if ($categoryQuery->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Nenhum item encontrado.',
                'data' => []
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $categoryQuery
        ], 200);
    }

    public function listAdicionaisCat($user, $cat_id)
    {
        $adicionais = WsAdicionaisCat::where('user_id', $user)
            ->where('pay', 1)
            ->whereIn('id_cat', (array)$cat_id)
            ->orderBy('name_adicionais_cat', 'asc')
            ->get();

        $itensResponse = $this->listItens($user, $cat_id, 1);
        $itens = $itensResponse->getData()->data ?? [];

        return response()->json([
            'addt_cats' => $adicionais,
            'list_itens' => $itens
        ]);
    }

    public function listAdicionaisCatP($user, $cat_id)
    {
        $adicionais = WsAdicionaisCat::where('user_id', $user)
            ->where('pay', 1)
            ->whereIn('id_cat', (array)$cat_id)
            ->orderBy('name_adicionais_cat', 'asc')
            ->get();

        $itensResponse = $this->listItens($user, $cat_id, 1);
        $itens = $itensResponse->getData()->data ?? [];

        return response()->json([
            'addt_cats' => $adicionais,
            'list_itens' => $itens
        ]);
    }
    
       public function destroy($adicional)
    {

       $adicional = WsAdicionaisCat::findOrFail($adicional);

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
