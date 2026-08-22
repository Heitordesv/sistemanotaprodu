<?php

namespace App\Http\Controllers;

use App\Models\CategoriaPostBlogEcommerce;
use Illuminate\Http\Request;

class CategoriaPostsController extends Controller
{
    public function index(Request $request)
    {
        $data = CategoriaPostBlogEcommerce::where('empresa_id', $request->empresa_id)
            ->paginate(env('PAGINACAO'));
        return view('categoria_posts.index', compact('data'));
    }

    public function create()
    {
        return view('categoria_posts.create');
    }

    public function store(Request $request)
    {
        try {
            CategoriaPostBlogEcommerce::create($request->all());
            session()->flash('flash_sucesso', 'Cadastrado com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado:' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('categoriaPosts.index');
    }

    public function edit($id)
    {
        $item = CategoriaPostBlogEcommerce::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        return view('categoria_posts.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = CategoriaPostBlogEcommerce::findOrFail($id);
        try {
            $item->fill($request->all())->save();
            session()->flash('flash_sucesso', 'Alterado com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado:', $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('categoriaPosts.index');
    }

    public function destroy($id)
    {
        $item = CategoriaPostBlogEcommerce::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        try {
            $item->delete();
            session()->flash('flash_sucesso', 'Deletado com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado:' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('categoriaPosts.index');
    }
}
