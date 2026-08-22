<?php

namespace App\Http\Controllers;

use App\Models\CategoriaProdutoDelivery;
use App\Models\TamanhoPizza;
use Illuminate\Http\Request;
use App\Utils\UploadUtil;
use Illuminate\Support\Facades\DB;


class CategoriaProdutoDeliveryController extends Controller
{
    protected $util;

    public function __construct(UploadUtil $util)
    {
        $this->util = $util;
    }

    public function index(Request $request)
    {
        $categorias = CategoriaProdutoDelivery::where('empresa_id', $request->empresa_id)
            ->get();
        $existeCategoriaPizza = $this->existeCategoriaPizza($categorias, $request->empresa_id);

        $data = CategoriaProdutoDelivery::where('empresa_id', $request->empresa_id)->get();
        return view('categoria_delivery_produto.index', compact('data', 'existeCategoriaPizza'));
    }

    private function existeCategoriaPizza($categorias, $empresa_id)
    {
        $tamanhoFirst = sizeof(TamanhoPizza::where('empresa_id', $empresa_id)
            ->get()) > 0 ? true : false;
        foreach ($categorias as $c) {
            if ($c->tipo_pizza) {
                if (!$tamanhoFirst) return true;
            }
        }
        return false;
    }

    public function create()
    {
        return view('categoria_delivery_produto.create');
    }

    public function store(Request $request)
    {
        $this->__validate($request);
        try {
            DB::transaction(function () use ($request) {
                $file_name = '';
                if ($request->hasFile('image')) {
                    $file_name = $this->util->uploadImage($request, '/categoriaDelivery');
                }
                $request->merge([
                    'path' => $file_name,
                    'descricao' => $request->descricao ?? '',
                ]);
                CategoriaProdutoDelivery::create($request->all());
            });
            session()->flash('flash_sucesso', 'Cadastro com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('categoriaDelivery.index');
    }

    public function edit($id)
    {
        $item = CategoriaProdutoDelivery::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        return view('categoria_delivery_produto.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $this->__validate($request);
        $item = CategoriaProdutoDelivery::findOrFail($id);
        try{
            DB::transaction(function () use ($request, $item) {
                $file_name = '';
                if ($request->hasFile('image')) {
                    $this->util->unlinkImage($item, '/categoriaDelivery');
                    $file_name = $this->util->uploadImage($request, '/categoriaDelivery');
                }
                $request->merge([
                    'path' => $file_name
                ]);
                $item->fill($request->all())->save();
            });
            session()->flash("flash_sucesso", "Categoria atualizada!");
        }catch(\Exception $e){
            session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('categoriaDelivery.index');
    }

    private function __validate(Request $request)
    {
        $rules = [
            'nome' => 'required',
            'image' => 'required'
        ];
        $messages = [
            'nome.required' => 'Campo obrigatório',
            'image.required' => 'Campo obrigatório'
        ];
        $this->validate($request, $rules, $messages);
    }

    public function destroy($id)
    {
        $item = CategoriaProdutoDelivery::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        try {
            $item->delete();
            session()->flash("flash_sucesso", "Categoria removida!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu Errado: Essa categoria esta sendo usada em algum cadastro! " . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('categoriaDelivery.index');
    }
}
