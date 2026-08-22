<?php

namespace App\Http\Controllers;

use App\Models\EventoFuncionario;
use App\Models\EventoSalario;
use App\Models\Funcionario;
use App\Models\FuncionarioEvento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FuncionarioEventoController extends Controller
{
    public function index(Request $request)
    {
        $nome = $request->nome;
        $data = Funcionario::select('funcionarios.*')
        ->join('funcionario_eventos', 'funcionario_eventos.funcionario_id', '=', 'funcionarios.id')
        ->where('empresa_id', request()->empresa_id)
        ->when(!empty($nome), function ($query) use ($nome) {
            return $query->where('nome', 'like', "%$nome%");
        })
        ->groupBy('funcionarios.id')
        ->paginate(30);
        return view('funcionario_evento.index', compact('nome', 'data'));
    }

    public function create()
    {
        $funcionarios = Funcionario::select('funcionarios.*')
        ->doesntHave('eventos')
        ->orderBy('nome')
        ->where('empresa_id', request()->empresa_id)
        ->get();

        $eventos = EventoSalario::where('empresa_id', request()->empresa_id)
        ->get();
        return view('funcionario_evento.create', compact('funcionarios', 'eventos'));
    }

    public function store(Request $request)
    {
        try {
            DB::transaction(function () use ($request) {
                for ($i = 0; $i < sizeof($request->evento); $i++) {
                    $item = [
                        'evento_id' => $request->evento[$i],
                        'funcionario_id' => $request->funcionario_id,
                        'condicao' => $request->condicao[$i],
                        'metodo' => $request->metodo[$i],
                        'valor' => __convert_value_bd($request->valor[$i]),
                        'ativo' => $request->ativo[$i]
                    ];
                    FuncionarioEvento::create($item);
                }
            });
            session()->flash("flash_sucesso", "Eventos adicionados!");
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado: ' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('funcionarioEventos.index');
    }

    public function edit($id)
    {
        $item = Funcionario::findOrFail($id);
        $funcionarios = Funcionario::where('empresa_id', request()->empresa_id)
        ->get();
        $eventos = EventoSalario::where('empresa_id', request()->empresa_id)
        ->get();
        return view('funcionario_evento.edit', compact('eventos', 'funcionarios', 'item'));
    }

    public function update(Request $request, $id)
    {
        try {
            DB::transaction(function () use ($request, $id) {
                FuncionarioEvento::where('funcionario_id', $id)->delete();
                for ($i = 0; $i < sizeof($request->evento); $i++) {
                    $item = [
                        'evento_id' => $request->evento[$i],
                        'funcionario_id' => $id,
                        'condicao' => $request->condicao[$i],
                        'metodo' => $request->metodo[$i],
                        'valor' => __convert_value_bd($request->valor[$i]),
                        'ativo' => $request->ativo[$i]
                    ];
                    FuncionarioEvento::create($item);
                }
            });
            session()->flash("flash_sucesso", "Eventos atualizados!");
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado: ' . $e->getMessage());
        }
        return redirect()->route('funcionarioEventos.index');
    }

    public function destroy($id)
    {
        $item = FuncionarioEvento::findOrFail($id);
        try {
            $item->delete();
            session()->flash("flash_sucesso", "Eventos removido!");
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado: ' . $e->getMessage());
        }
        return redirect()->route('funcionarioEventos.index');
    }
}
