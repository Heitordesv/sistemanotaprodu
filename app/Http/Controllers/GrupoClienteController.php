<?php

namespace App\Http\Controllers;

use App\Models\GrupoCliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GrupoClienteController extends Controller
{

    public function index(Request $request)
    {
        $data = GrupoCliente::where('empresa_id', request()->empresa_id)
            ->when(!empty($request->nome), function ($q) use ($request) {
                return  $q->where(function ($quer) use ($request) {
                    return $quer->where('nome', 'LIKE', "%$request->nome%");
                });
            })
            ->paginate(env("PAGINACAO"));
        return view('grupo_clientes.index', compact('data'));
    }


    public function create()
    {
        return view('grupo_clientes.create');
    }

    public function store(Request $request)
    {
        $this->_validate($request);
        try {
            DB::transaction(function () use ($request) {
                GrupoCliente::create($request->all());
            });
            session()->flash("flash_sucesso", "Grupo cadastrado!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('gruposCliente.index');
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
        $item = GrupoCliente::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        return view('grupo_clientes.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $this->_validate($request);
        $item = GrupoCliente::findOrFail($id);
        try {
            DB::transaction(function () use ($request, $item) {
                $item->fill($request->all())->save();
            });
            session()->flash("flash_sucesso", "Grupo atualizada!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Deu Errado!");
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('gruposCliente.index');
    }

    public function destroy(Request $request, $id)
    {
        $item = GrupoCliente::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        try {
            $item->delete();
            session()->flash("flash_sucesso", "Deletado com Sucesso");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu Errado");
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('gruposCliente.index');
    }
}
