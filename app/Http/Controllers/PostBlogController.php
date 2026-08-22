<?php

namespace App\Http\Controllers;

use App\Models\AutorPostBlogEcommerce;
use App\Models\CategoriaPostBlogEcommerce;
use App\Models\PostBlogEcommerce;
use Illuminate\Http\Request;
use App\Utils\UploadUtil;
use Illuminate\Support\Facades\DB;

class PostBlogController extends Controller
{
    protected $util;

    public function __construct(UploadUtil $util)
    {
        $this->util = $util;
    }

    public function index(Request $request)
    {
        $data = PostBlogEcommerce::where('empresa_id', $request->empresa_id)
        ->paginate(env('PAGINACAO'));
        return view('blog_post.index', compact('data'));
    }

    public function create(Request $request)
    {
        $categoriaPosts = CategoriaPostBlogEcommerce::where('empresa_id', $request->empresa_id)->get();
        $autorPost = AutorPostBlogEcommerce::where('empresa_id', $request->empresa_id)->get();
        return view('blog_post.create', compact('categoriaPosts', 'autorPost'));
    }

    public function store(Request $request)
    {
        // dd($request);
        $this->_validate($request);
        try {
            $file_name = '';
            if ($request->hasFile('image')) {
                $file_name = $this->util->uploadImage($request, '/postBlog');
            }
            $request->merge([
                'img' => $file_name,
                'tags' => $request->tags ?? ''
            ]);

            DB::transaction(function () use ($request) {
                PostBlogEcommerce::create($request->all());
            });

            session()->flash('flash_sucesso', 'Cadastrado com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado:' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('postBlog.index');
    }

    public function edit(Request $request, $id)
    {
        $categoriaPosts = CategoriaPostBlogEcommerce::where('empresa_id', $request->empresa_id)->get();
        $autorPost = AutorPostBlogEcommerce::where('empresa_id', $request->empresa_id)->get();
        $item = PostBlogEcommerce::findOrFail($id);
        return view('blog_post.edit', compact('categoriaPosts', 'autorPost', 'item'));
    }

    public function update(Request $request, $id)
    {
        $item = PostBlogEcommerce::findOrFail($id);
        try {
            $file_name = '';
            if ($request->hasFile('image')) {
                $this->util->unlinkImage($item, '/postBlog');
                $file_name = $this->util->uploadImage($request, '/postBlog');
            }
            $request->merge([
                'img' => $file_name,
            ]);
            $item->fill($request->all())->save();
            session()->flash('flash_sucesso', 'Alterado com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado:' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('postBlog.index');
    }

    private function _validate(Request $request)
    {
        $rules = [
            'titulo' => 'required|max:50',
            'texto' => 'required',
            'image' => 'required'
        ];
        $messages = [
            'titulo.required' => 'Campo obrigatório.',
            'titulo.max' => '50 caracteres maximos permitidos.',
            'texto.required' => 'Campo obrigatório.',
            'image.required' => 'Campo obrigatório'
        ];
        $this->validate($request, $rules, $messages);
    }

    public function destroy($id)
    {
        $item = PostBlogEcommerce::findOrFail($id);
        try {
            $item->delete();
            session()->flash('flash_sucesso', 'Deletado com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado:' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('postBlog.index');
    }
}
