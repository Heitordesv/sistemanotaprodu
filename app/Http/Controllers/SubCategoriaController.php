<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\SubCategoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubCategoriaController extends Controller
{
    public function index(Request $request, $id)
    {
        $data = SubCategoria::where('categoria_id', $id)
            ->orderBy('nome', 'asc')
            ->get();
        $categoria = Categoria::findOrFail($id);
        return view('sub_categorias.index', compact('data', 'categoria'));
    }

    public function create($id)
    {
        $categoria = Categoria::findOrFail($id);
        return view('sub_categorias.create', compact('categoria'));
    }

    public function edit($id)
    {
        $item = SubCategoria::findOrFail($id);
        $categoria = $item->categoria;
        return view('sub_categorias.edit', compact('categoria', 'item'));
    }

    public function store(Request $request, $id)
    {
        $item = Categoria::findOrFail($id);
        try {
            $request->merge([
                'categoria_id' => $id
            ]);
            SubCategoria::create($request->all());
            session()->flash("flash_sucesso", "Subcategoria cadastrada!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('subcategoria.index', $item->id);
    }

    public function update(Request $request, $id)
    {
        $this->_validate($request);
        $item = SubCategoria::findOrFail($id);
        try {
            DB::transaction(function () use ($request, $item) {
                $item->fill($request->all())->save();
            });
            session()->flash("flash_sucesso", "Cadastro com Sucesso!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado" . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('subcategoria.index', $item->categoria_id);
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
        $item = SubCategoria::findOrFail($id);
        try {
            $item->delete();
            session()->flash("flash_sucesso", "Removido com sucesso!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('subcategoria.index', $item->categoria_id);
    }
}
