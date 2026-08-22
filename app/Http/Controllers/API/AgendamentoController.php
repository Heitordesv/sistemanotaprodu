<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Agendamento;

class AgendamentoController extends Controller
{

    private function menos10Dias(){
        return date('Y-m-d', strtotime("-10 days",strtotime(str_replace("/", "-", 
            date('Y-m-d')))));
    }

    private function mais20Dias(){
        return date('Y-m-d', strtotime("+20 days",strtotime(str_replace("/", "-", 
            date('Y-m-d')))));
    }
    public function all(Request $request){
        try{
            $mais20 = $this->mais20Dias();
            $menos10 = $this->menos10Dias();

            $agendamentos = Agendamento::
            whereBetween('data', [$menos10, 
                $mais20])
            ->where('empresa_id', $request->empresa_id)
            ->get();
            $temp = [];

            foreach($agendamentos as $a){
                $titulo = $a->cliente->razao_social . " - ";
                foreach($a->itens as $key => $i){
                    $titulo .= $i->servico->nome . ($key < sizeof($a->itens)-1 ? "|" : "");
                }

                $arr = [
                    'title' => $titulo,
                    'start' => $a->data.'T'.$a->inicio,
                    'end' => $a->data.'T'.$a->termino,
                    'url' => route('agendamentos.show', [$a->id]),
                    'backgroundColor' => $a->status ? '#4db6ac' : '#ef5350'
                ];

                array_push($temp, $arr);
            }
            return response()->json($temp, 200);
        }catch(\Exception $e){
            return response()->json($e->getMessage(), 401);
        }

    }
}
