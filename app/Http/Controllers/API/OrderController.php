<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Funcionario;
use App\Models\OrdemServico;
use App\Models\Servico;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function linhaServico(Request $request){
        try{
            $qtd = $request->qtd;
            $valor = __convert_value_bd($request->valor);
            $servico_id = $request->servico_id;
            $status = $request->status;
            $nome = $request->nome;

            $servico = OrdemServico::findOrFail($servico_id);
            return view('ordem_servico.partials.row_servico', compact('servico', 'qtd', 'valor', 'status', 'nome'));
        }catch(\Exception $e){
            return response()->json($e->getMessage(), 401);
        }
    }

    public function linhaFuncionario(Request $request){
        try{
            $celular = $request->celular;
            $funcionario_id = $request->funcionario_id;
            $funcao = $request->funcao;

            $funcionario = Funcionario::findOrFail($funcionario_id);
            return view('ordem_servico.partials.row_funcionario', compact('celular', 'funcionario', 'funcao'));
        }catch(\Exception $e){
            return response()->json($e->getMessage(), 401);
        }
    }

    public function find($id){
        $item = Servico::with('categoria')->where('id', $id)
        ->first();
        return response()->json($item, 200);
    }

    public function findFuncionario($id){
        $item = Funcionario::where('id', $id)
        ->first();
        return response()->json($item, 200);
    }
}
