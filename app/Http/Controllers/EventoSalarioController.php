<?php

namespace App\Http\Controllers;

use App\Models\EventoSalario;
use Illuminate\Http\Request;

class EventoSalarioController extends Controller
{
    public function index(Request $request)
    {
        $data = EventoSalario::when(!empty($request->nome), function ($q) use ($request) {
            return $q->where(function ($quer) use ($request) {
                return $quer->where('nome', 'LIKE', "%$request->nome%");
            });
        })
        ->paginate(env("PAGINACAO"));

        return view('evento_salario.index', compact('data'));
    }

    public function create()
    {
        return view('evento_salario.create');
    }

    public function store(Request $request)
    {
        $this->_validate($request);
        try {
            EventoSalario::create($request->all());
            session()->flash("flash_sucesso", "Evento cadastrado com sucesso!");
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Erro ao cadastrar evento!' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('eventoSalario.index');
    }

    public function edit($id)
    {
        $item = EventoSalario::findOrFail($id);
        return view('evento_salario.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = EventoSalario::findOrFail($id);

        try {
            $item->fill($request->all())->save();
            session()->flash("flash_sucesso", "Evento alterado com sucesso!");
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Erro ao alterar evento!' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('eventoSalario.index');
    }

    private function _validate(Request $request)
    {
        $rules = [
            'nome' => 'required|max:50',
            'tipo' => 'required',
            'metodo' => 'required',
            'condicao' => 'required',
            'ativo' => 'required',
        ];

        $messages = [
            'nome.required' => 'O campo Nome é obrigatório.',
            'nome.max' => '50 caracteres maximos permitidos.',
            'tipo.required' => 'O campo Tipo é obrigatório.',
            'metodo.required' => 'O campo Médoto é obrigatório.',
            'condicao.required' => 'O campo Condição é obrigatório.',
            'ativo.required' => 'O campo Ativo é obrigatório.',

        ];
        $this->validate($request, $rules, $messages);
    }

    public function destroy($id)
    {
        $item = EventoSalario::findOrFail($id);
        try{
            $item->delete();
            session()->flash("flash_sucesso", "Deletado com sucesso!");
        }catch(\Exception $e){
            session()->flash('flash_erro', 'Erro ao deletar evento!' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('eventoSalario.index');
    }
}
