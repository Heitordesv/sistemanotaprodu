<?php

namespace App\Http\Controllers;

use App\Models\CategoriaDestaqueMasterDelivery;
use App\Models\CategoriaMasterDelivery;
use App\Models\ProdutoDestaqueMasterDelivery;
use Illuminate\Http\Request;

class DestaqueDeliveryMasterController extends Controller
{

    public function index()
    {
        $data = ProdutoDestaqueMasterDelivery::all();
        return view('produtos_destaque.index', compact('data'));
    }

    public function indexCategoria()
    {
        $data = CategoriaDestaqueMasterDelivery::all();
        return view('produtos_destaque.indexCategoria', compact('data'));
    }

    public function createCategoria()
    {
        return view('produtos_destaque.createCategoria');
    }

    public function storeCategoria(Request $request)
    {
        try {
            CategoriaDestaqueMasterDelivery::create($request->all());
            session()->flash('flash_sucesso', 'Cadastrado com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro' . 'Algo deu errado:' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('categoriasParaDestaque.indexCategoria');
    }

    public function editCategoria($id)
    {
        $item = CategoriaDestaqueMasterDelivery::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        return view('produtos_destaque.editCategoria', compact('item'));
    }

    public function updateCategoria(Request $request, $id)
    {
        $item = CategoriaDestaqueMasterDelivery::findOrFail($id);
        try {
            $item->fill($request->all())->save();
            session()->flash('flash_sucesso', 'Atualizado com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro' . 'Algo deu errado:' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('categoriasParaDestaque.indexCategoria');
    }

    public function destroyCategoria($id)
    {
        $item = CategoriaDestaqueMasterDelivery::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        try {
            $item->delete();
            session()->flash('flash_sucesso', 'Deletado com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro' . 'Algo deu errado:' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('categoriasParaDestaque.indexCategoria');
    }

    public function create()
    {
        $categoria = CategoriaDestaqueMasterDelivery::all();
        return view('produtos_destaque.create', compact('categoria'));
    }

    public function store(Request $request)
    {
        try {
            ProdutoDestaqueMasterDelivery::create($request->all());
            session()->flash('flash_sucesso', 'Cadastrado com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado:' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('produtosDestaque.index');
    }

    public function edit($id)
    {
        $item = ProdutoDestaqueMasterDelivery::findOrFail($id);
        $categoria = CategoriaDestaqueMasterDelivery::all();

        return view('produtos_destaque.edit', compact('item', 'categoria'));
    }

    public function update(Request $request, $id)
    {
        $item = ProdutoDestaqueMasterDelivery::findOrFail($id);
        try {
            $item->fill($request->all())->save();
            session()->flash('flash_sucesso', 'Atualizado com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado:' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('produtosDestaque.index');
    }

    public function destroy($id)
    {
        $item = ProdutoDestaqueMasterDelivery::findOrFail($id);
        try {
            $item->delete();
            session()->flash('flash_sucesso', 'Deletado com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado:' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('produtosDestaque.index');
    }
}
