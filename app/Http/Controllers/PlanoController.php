<?php

namespace App\Http\Controllers;

use App\Models\PerfilAcesso;
use App\Models\Plano;
use App\Models\PlanoEmpresa;
use App\Utils\UploadUtil;
use Illuminate\Http\Request;

class PlanoController extends Controller
{
    protected $util;

    public function __construct(UploadUtil $util)
    {
        $this->util = $util;
    }

    public function index(Request $request)
    {
        $data = Plano::all();
        return view('planos.index', compact('data'));
    }

    public function create()
    {
        $perfil = PerfilAcesso::all();
        return view('planos.create', compact('perfil'));
    }

    public function store(Request $request)
    {
        $this->_validate($request);
        try {
            $file_name = '';
            if ($request->hasFile('image')) {
                $file_name = $this->util->uploadImage($request, '/planos');
            }
            $request->merge([
                'descricao' => $request->descricao ?? '',
                'valor' => __convert_value_bd($request->valor),
                'img' => $file_name,
                'armazenamento' => $request->armazenamento ?? ''
            ]);
            Plano::create($request->all());
            session()->flash("flash_sucesso", "Cadastro com Sucesso");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('planos.index');
    }

    private function _validate(Request $request)
    {
        $rules = [
            'nome' => 'required|max:50'
        ];
        $messages = [
            'nome.required' => 'O campo nome é obrigatório.',
            'nome.max' => '50 caracteres maximos permitidos.'
        ];
        $this->validate($request, $rules, $messages);
    }

    public function edit($id)
    {
        $item = Plano::findOrfail($id);
        $perfil = PerfilAcesso::all();
        return view('planos.edit', compact('item', 'perfil'));
    }

    public function update(Request $request, $id)
    {
        $this->_validate($request);
        $item = Plano::findOrfail($id);
        try {
            $file_name = $item->img;
            if ($request->hasFile('image')) {
                $this->util->unlinkImage($item, '/planos');
                $file_name = $this->util->uploadImage($request, '/planos');
            }
            $request->merge([
                'descricao' => $request->descricao ?? '',
                'valor' => __convert_value_bd($request->valor),
                'img' => $file_name,
                'armazenamento' => $request->armazenamento ?? ''
            ]);
            $item->fill($request->all())->save();
            session()->flash("flash_sucesso", "Cadastro Atualizado");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('planos.index');
    }

    public function destroy($id)
    {
        $item = Plano::findOrFail($id);
        try{
            $item->delete();
            session()->flash('flash_sucesso', 'Deletado com sucesso!');
        }catch(\Exception $e){
            session()->flash('flash_erro', 'Algo deu errado:' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('planos.index');
    }
}
