<?php

namespace App\Http\Controllers;

use App\Models\Acessor;
use App\Models\Agendamento;
use App\Models\CategoriaServico;
use App\Models\Funcionario;
use App\Models\GrupoCliente;
use App\Models\ItemAgendamento;
use App\Models\Pais;
use App\Models\Servico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpParser\Node\Expr\FuncCall;

use function PHPUnit\Framework\returnSelf;

class AgendamentoController extends Controller
{
    public function index(Request $request)
    {
        $funcionarios = Funcionario::where('empresa_id', $request->empresa_id)->get();

        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');
        $type_search = $request->get('estado');
        $cliente_id = $request->get('cliente_id');

        $data = Agendamento::where('empresa_id', request()->empresa_id)
            ->when(!empty($start_date), function ($query) use ($start_date) {
                return $query->whereDate('created_at', '>=', $start_date);
            })
            ->when(!empty($end_date), function ($query) use ($end_date, $type_search) {
                return $query->whereDate('created_at', '<=', $end_date);
            })
            ->when(!empty($cliente_id), function ($query) use ($cliente_id) {
                return $query->where('cliente_id', $cliente_id);
            })
            ->paginate(env("PAGINACAO"));

        return view('agendamentos.index', compact('data', 'funcionarios'));
    }

    public function create(Request $request)
    {
        $servicos = Servico::where('empresa_id', $request->empresa_id)->get();
        $categorias = CategoriaServico::where('empresa_id', $request->empresa_id)->get();
        $paises = Pais::all();
        $grupos = GrupoCliente::where('empresa_id', $request->empresa_id)->get();
        $acessores = Acessor::where('empresa_id', $request->empresa_id)->get();
        $funcionarios = Funcionario::where('empresa_id', $request->empresa_id)->get();
        return view(
            'agendamentos.create',
            compact(
                'categorias',
                'paises',
                'grupos',
                'acessores',
                'funcionarios',
                'servicos'
            )
        );
    }

    public function store(Request $request)
    {
        $this->_validate($request);
        try {
            $request->merge([
                'total' => __convert_value_bd($request->total),
                'acrescimo' => $request->acrescimo ? __convert_value_bd($request->acrescimo) : 0,
                'desconto' => $request->desconto ? __convert_value_bd($request->desconto) : 0,
                'observacao' => $request->observacao ?? ''
            ]);
            $agendamento = Agendamento::create($request->all());
            for ($i = 0; $i < sizeof($request->servico_id); $i++) {
                ItemAgendamento::create([
                    'agendamento_id' => $agendamento->id,
                    'servico_id' => (int)$request->servico_id[$i],
                    'quantidade' => 1
                ]);
            }
            session()->flash("flash_sucesso", "Agendamento Cadastrado!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado" . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('agendamentos.index');
    }

    private function _validate(Request $request)
    {
        $rules = [
            'servico_id' => 'required',
            'cliente_id' => 'required',
            'funcionario_id' => 'required',
            'data' => 'required',
            'inicio' =>  'required',
            'termino' => 'required',
        ];
        $messages = [
            'servico_id.required' => 'Campo Obrigatório',
            'cliente_id.required' => 'Campo Obrigatório',
            'funcionario_id.required' => 'Campo Obrigatório',
            'data.required' => 'Campo Obrigatório',
            'inicio.required' => 'Campo Obrigatório',
            'termino.required' => 'Campo Obrigatório'
        ];
        $this->validate($request, $rules, $messages);
    }

    public function comissao()
    {
        $funcionarios = Funcionario::where('empresa_id', request()->empresa_id)->get();
        return view('agendamentos.comissao', compact('funcionarios'));
    }

    public function servicos(Request $request)
    {
        $funcionarios = Funcionario::where('empresa_id', $request->empresa_id)->get();
        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');
        $type_search = $request->get('estado');
        $data = Agendamento::where('empresa_id', request()->empresa_id)
            ->when(!empty($start_date), function ($query) use ($start_date) {
                return $query->whereDate('created_at', '>=', $start_date);
            })
            ->when(!empty($end_date), function ($query) use ($end_date, $type_search) {
                return $query->whereDate('created_at', '<=', $end_date);
            })
            ->paginate(env("PAGINACAO"));
        return view('agendamentos.agendamentos', compact('data', 'funcionarios'));
    }

    public function show($id)
    {
        $item = Agendamento::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        return view('agendamentos.show', compact('item'));
    }

    public function destroy($id)
    {
        $item = Agendamento::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        try {
            $item->itens()->delete();
            $item->delete();
            session()->flash('flash_sucesso', "Removido com sucesso!");
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado:' . $e->getMessage());
        }
    }

    public function alterarStatus($id)
    {
        $item = Agendamento::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        try {
            $item->status = 1;
            $valorComissao = $this->calculaComissao($item);
            $item->valor_comissao = $valorComissao;
            $item->save();
            session()->flash("flash_sucesso", "Agendamento alterado para finalizado!");
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado:' . $e->getMessage());
        }
        return redirect()->route('agendamentos.index');
    }

    private function calculaComissao($item)
    {
        $soma = 0;
        $somaDesconto = 0;
        $total = $item->total + $item->acrescimo - $item->desconto;
        foreach ($item->itens as $key => $i) {
            $tempDesc = 0;
            $valorServico = $i->servico->valor;
            if ($key < sizeof($item->itens) - 1) {
                $media = (((($valorServico - $total) / $total)) * 100);
                $media = 100 - ($media * -1);
                $tempDesc = ($item->desconto * $media) / 100;
                $somaDesconto += $tempDesc;
            } else {
                $tempDesc = $item->desconto - $somaDesconto;
            }
            $comissao = $i->servico->comissao;
            $valorComissao = ($valorServico - $tempDesc) * ($comissao / 100);
            $soma += $valorComissao;
        }
        return number_format($soma, 2);
    }
}
