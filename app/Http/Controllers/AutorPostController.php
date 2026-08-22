<?php

namespace App\Http\Controllers;

use App\Models\AutorPostBlogEcommerce;
use Illuminate\Http\Request;
use App\Utils\UploadUtil;


class AutorPostController extends Controller
{
    protected $util;

    public function __construct(UploadUtil $util)
    {
        $this->util = $util;
    }

    public function index(Request $request)
    {
        $data = AutorPostBlogEcommerce::where('empresa_id', $request->empresa_id)
        ->paginate(env('PAGINACAO'));
        return view('autor_post.index', compact('data'));
    }

    public function create()
    {
        return view('autor_post.create');
    }

    public function store(Request $request)
    {
        try {
            $file_name = '';
            if ($request->hasFile('image')) {
                $file_name = $this->util->uploadImage($request, '/autorPost');
            }
            $request->merge([
                'img' => $file_name,
            ]);
            AutorPostBlogEcommerce::create($request->all());
            session()->flash('flash_sucesso', 'Cadastro com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado:' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('autorPost.index');
    }

    public function edit($id)
    {
        $item = AutorPostBlogEcommerce::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        return view('autor_post.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = AutorPostBlogEcommerce::findOrFail($id);
        try {
            $file_name = '';
            if ($request->hasFile('image')) {
                $this->util->unlinkImage($item, '/autorPost');
                $file_name = $this->util->uploadImage($request, '/autorPost');

                $request->merge([
                    'img' => $file_name,
                ]);
            }
            $item->fill($request->all())->save();
            session()->flash("flash_sucesso", "Autor editado!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('autorPost.index');
    }

    public function destroy($id)
    {
        $item = AutorPostBlogEcommerce::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        try {
            $item->delete();
            session()->flash('flash_sucesso', 'Deletado com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('autorPost.index');
    }
}
