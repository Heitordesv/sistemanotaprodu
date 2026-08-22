<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use App\Models\Receita;
use App\Models\ItemReceita;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;
use PhpParser\Node\Expr\FuncCall;

class ProductCompController extends Controller
{
    public function create($id)
    {
        $item = Produto::findOrFail($id);
        return view('produtos.produtos_composto.create', compact('item'));
    }

    public function createItem($id)
    {
        $item = Receita::findOrFail($id);
        return view('produtos.produtos_composto.create_item', compact('item'));
    }

    public function store(Request $request, $id)
    {
        $this->_validate($request);
        try {
            $request->merge([
                'produto_id' => $id,
                'valor_custo' => 0,
                'pizza' => 0,
            ]);
            $receita = DB::transaction(function () use ($request) {
                $receita = Receita::create($request->all());
                return $receita;
            });
            session()->flash("flash_sucesso", "Cadastro com sucesso");
            return redirect()->route('produtosComposto.create_item', [$receita->id]);
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado" . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
            return redirect()->back();
        }
    }

    public function storeItem(Request $request, $id)
    {
        try {
            $request->merge([
                'quantidade' => __convert_value_bd($request->quantidade),
                'receita_id' => $id
            ]);
            DB::transaction(function () use ($request) {
                $receita = ItemReceita::create($request->all());
            });
            session()->flash("flash_sucesso", "Cadastro com sucesso");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado" . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->back();
    }

    private function _validate(Request $request)
    {
        $rules = [];
        $messages = [];
        $this->validate($request, $rules, $messages);
    }
}
