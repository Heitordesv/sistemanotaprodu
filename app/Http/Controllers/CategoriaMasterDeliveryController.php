<?php

namespace App\Http\Controllers;

use App\Models\CategoriaMasterDelivery;
use Illuminate\Http\Request;
use App\Utils\UploadUtil;
use Illuminate\Support\Facades\DB;


class CategoriaMasterDeliveryController extends Controller
{
    protected $util;

    public function __construct(UploadUtil $util)
    {
        $this->util = $util;
    }

    public function index()
    {
        $data = CategoriaMasterDelivery::all();
        return view('categoria_master_delivery.index', compact('data'));
    }

    public function create()
    {
        return view('categoria_master_delivery.create');
    }

    public function store(Request $request)
    {
        $this->__validate($request);
        try {
            DB::transaction(function () use ($request) {
                $file_name = '';
                if ($request->hasFile('image')) {
                    $file_name = $this->util->uploadImage($request, '/categoriaMasterDelivery');
                }
                $request->merge([
                    'img' => $file_name
                ]);
                CategoriaMasterDelivery::create($request->all());
            });
            session()->flash('flash_sucesso', 'Cadastro com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('categoriaMasterDelivery.index');
    }

    public function edit($id)
    {
        $item = CategoriaMasterDelivery::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        return view('categoria_master_delivery.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $this->__validate($request);
        $item = CategoriaMasterDelivery::findOrFail($id);
        try{
            DB::transaction(function () use ($request, $item) {
                $file_name = '';
                if ($request->hasFile('image')) {
                    $this->util->unlinkImage($item, '/categoriaMasterDelivery');
                    $file_name = $this->util->uploadImage($request, '/categoriaMasterDelivery');
                }
                $request->merge([
                    'img' => $file_name
                ]);
                $item->fill($request->all())->save();
            });
            session()->flash("flash_sucesso", "Categoria atualizada!");
        }catch(\Exception $e){
            session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('categoriaMasterDelivery.index');
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
        $item = CategoriaMasterDelivery::findOrFail($id);
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
        return redirect()->route('categoriaMasterDelivery.index');
    }
}
