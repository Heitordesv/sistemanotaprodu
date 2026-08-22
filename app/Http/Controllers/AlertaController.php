<?php

namespace App\Http\Controllers;

use App\Models\Alerta;
use Illuminate\Http\Request;

class AlertaController extends Controller
{
    public function index()
    {
        $data = Alerta::all();
        return view('alertas.index', compact('data'));
    }

    public function create()
    {
        return view('alertas.create');
    }

    public function store(Request $request)
    {
        $this->_validate($request);
        try {
            $request->merge([
                'status' => $request->status ?? 0,
            ]);
            Alerta::create($request->all());
            session()->flash('flash_sucesso', 'Cadastrado com sucesso');
        } catch (\Exception $e) {
            session()->flash('flash_erro' . $e->getMessage());

        }
        return redirect()->route('alertas.index');
    }

    public function edit($id)
    {
        $item = Alerta::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        return view('alertas.edit', compact('item'));
    }

    public function update(Request $request, $id){
        $this->_validate($request);
        $item = Alerta::findOrFail($id);
        try{
            $request->merge([
                'status' => $request->status ?? 0,
            ]);
            $item->fill($request->all())->save();
            session()->flash('flash_sucesso', 'Editado com sucesso');
        }catch(\Exception $e){
            session()->flash('flash_erro' . $e->getMessage());
        }
        return redirect()->route('alertas.index');
    }

    private function _validate(Request $request)
    {
        $rules = [
            'titulo' => 'required|max:50',
            'texto' => 'required',
        ];
        $messages = [
            'titulo.required' => 'Campo obrigatório.',
            'titulo.max' => '50 caracteres maximos permitidos.',
            'texto.required' => 'Campo obrigatório.'
        ];
        $this->validate($request, $rules, $messages);
    }
}
