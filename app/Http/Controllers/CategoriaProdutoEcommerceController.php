<?php

namespace App\Http\Controllers;

use App\Models\CategoriaProdutoEcommerce;
use Illuminate\Http\Request;
use App\Utils\UploadUtil;


class CategoriaProdutoEcommerceController extends Controller
{
    protected $util;

    public function __construct(UploadUtil $util)
    {
        $this->util = $util;
    }

    public function index(Request $request)
    {
        $data = CategoriaProdutoEcommerce::
        where('empresa_id', request()->empresa_id)
        ->when(!empty($request->nome), function ($q) use ($request) {
            return  $q->where(function ($quer) use ($request) {
                return $quer->where('nome', 'LIKE', "%$request->nome%");
            });
        })
        ->paginate(env("PAGINACAO"));
        return view('categorias_ecommerce.index', compact('data'));
    }

    public function create()
    {
        return view('categorias_ecommerce.create');
    }

    public function store(Request $request)
    {
        $this->_validate($request);
        try {
            $file_name = '';
            if ($request->hasFile('image')) {
                $file_name = $this->util->uploadImage($request, '/ecommerce');
            }
            $request->merge([
                'imagem' => $file_name
            ]);
            CategoriaProdutoEcommerce::create($request->all());
            session()->flash("flash_sucesso", "Cadastrada com Sucesso");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Cadastrada com Sucesso" . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('categoriaEcommerce.index');

    }

    public function edit($id)
    {
        $item = CategoriaProdutoEcommerce::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        return view('categorias_ecommerce.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $this->_validate($request);
        $item = CategoriaProdutoEcommerce::findOrFail($id);
        try {
            $file_name = '';
            if ($request->hasFile('image')) {
                $this->util->unlinkImage($item, '/ecommerce');
                $file_name = $this->util->uploadImage($request, '/ecommerce');
            }
            $request->merge([
                'imagem' => $file_name
            ]);
            $item->fill($request->all())->save();
            session()->flash("flash_sucesso", "Categoria Atualizada!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('categoriaEcommerce.index');
    }


    private function _validate(Request $request)
    {
        $rules = [
            'nome' => 'required'
        ];
        $messages = [
            'nome.required' => 'Campo Obrigatório'
        ];
        $this->validate($request, $rules, $messages);
    }

    public function destroy(Request $request, $id)
    {
        $item = CategoriaProdutoEcommerce::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        try {
            $item->delete();
            session()->flash("flash_sucesso", "Categoria removida!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('categoriaEcommerce.index');
    }
}
