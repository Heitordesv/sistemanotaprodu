<?php

namespace App\Http\Controllers;

use App\Models\CategoriaProdutoEcommerce;
use App\Models\SubCategoriaEcommerce;
use Illuminate\Http\Request;

class SubCategoriaEcommerceController extends Controller
{
    public function index(Request $request, $id)
    {
        $data = SubCategoriaEcommerce::where('categoria_id', $id)
            ->orderBy('nome', 'asc')
            ->get();
        $categoria = CategoriaProdutoEcommerce::findOrFail($id);
        return view('sub_categorias_ecommerce.index', compact('data', 'categoria'));
    }

    public function create($id)
    {
        $categoria = CategoriaProdutoEcommerce::findOrFail($id);
        return view('sub_categorias_ecommerce.create', compact('categoria'));
    }

    public function edit($id)
    {
        $item = SubCategoriaEcommerce::findOrFail($id);
        $categoria = $item->categoria;
        return view('sub_categorias_ecommerce.edit', compact('categoria', 'item'));
    }

    public function store(Request $request, $id)
    {
        $item = CategoriaProdutoEcommerce::findOrFail($id);
        try {
            $request->merge([
                'categoria_id' => $id
            ]);
            SubCategoriaEcommerce::create($request->all());
            session()->flash("flash_sucesso", "Cadastrado com Sucesso!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('subCategoriaEcommerce.index', $item->id);
    }

    public function update(Request $request, $id)
    {
        $this->_validate($request);
        $item = SubCategoriaEcommerce::findOrFail($id);
        try {
            $item->fill($request->all())->save();
            session()->flash("flash_sucesso", "Cadastro com Sucesso!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado" . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('subCategoriaEcommerce.index', $item->categoria_id);
    }

    private function _validate(Request $request)
    {
        $rules = [
            'nome' => 'required|max:50',
        ];
        $messages = [
            'nome.required' => 'Nome é obrigatório',
            'nome.max' => 'No máximo 50 digitos'
        ];
        $this->validate($request, $rules, $messages);
    }

    public function destroy($id)
    {
        $item = SubCategoriaEcommerce::findOrFail($id);
        try {
            $item->delete();
            session()->flash("flash_sucesso", "Removido com sucesso!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('subCategoriaEcommerce.index', $item->categoria_id);
    }
}
