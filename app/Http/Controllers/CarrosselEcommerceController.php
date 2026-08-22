<?php

namespace App\Http\Controllers;

use App\Models\CarrosselEcommerce;
use Illuminate\Http\Request;
use App\Utils\UploadUtil;

use Illuminate\Http\Resources\MergeValue;
use Illuminate\Support\Facades\DB;

class CarrosselEcommerceController extends Controller
{
    protected $util;

    public function __construct(UploadUtil $util)
    {
        $this->util = $util;
    }

    public function index(Request $request)
    {
        $data = CarrosselEcommerce::where('empresa_id', $request->empresa_id)
        ->paginate(env("PAGINACAO"));
        return view('carrossel.index', compact('data'));
    }

    public function create()
    {
        return view('carrossel.create');
    }

    public function store(Request $request)
    {
        $this->_validate($request);
        try {
            $file_name = '';
            if ($request->hasFile('image')) {
                $file_name = $this->util->uploadImage($request, '/carrosselEcommerce');
            }
            $request->merge([
                'img' => $file_name,
                'link_acao' => $request->link_acao ?? '',
                'nome_botao' => $request->nome_botao ?? '',
            ]);
            DB::transaction(function () use ($request) {
                CarrosselEcommerce::create($request->all());
                session()->flash("flash_sucesso", "Cadastro com sucesso!");
            });
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado" . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('carrosselEcommerce.index');
    }

    public function edit($id)
    {
        $item = CarrosselEcommerce::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        return view('carrossel.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $this->_validate($request);
        $item = CarrosselEcommerce::findOrFail($id);
        try {
            $file_name = '';
            if ($request->hasFile('img')) {
                $this->util->unlinkImage($item, '/carrosselEcommerce', 'img');
                $file_name = $this->util->uploadImage($request, '/carrosselEcommerce', 'img');
            }
            $request->merge([
                'img' => $file_name,
                'link_acao' => $request->link_acao ?? '',
                'nome_botao' => $request->nome_botao ?? '',
            ]);
            DB::transaction(function () use ($request, $item) {
                $item->fill($request->all())->save();
                session()->flash("flash_sucesso", "Carrossel Atualizado!");
            });
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('carrosselEcommerce.index');
    }

    private function _validate(Request $request)
    {
        $rules = [
            'titulo' => 'required',
            'descricao' => 'required'
        ];
        $messages = [
            'titulo.required' => 'Campo Obrigatório ',
            'descricao.required' => 'Campo Obrigatório'
        ];
        $this->validate($request, $rules, $messages);
    }
}
