<?php

namespace App\Http\Controllers\Delivery;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DeliveryConfig;
use App\Models\CodigoDesconto;
use App\Models\BairroDeliveryLoja;
use App\Models\FuncionamentoDelivery;

class ConfigController extends Controller
{
   public function index(){
      $item = DeliveryConfig::
      where('empresa_id', request()->empresa_id)
      ->with('galeria')
      ->with('cidade')
      ->first();

      $dia = date('w');
      $hora = date('H:i');
      $dia = FuncionamentoDelivery::getDia($dia);
      // return response()->json($dia, 401);
      
      $funcionamento = $item->getFuncionamento($dia, request()->empresa_id);
      $item->fim_expediente = $funcionamento['fim_expediente'];
      $item->inicio_expediente = $funcionamento['inicio_expediente'];
      $item->aberto = $funcionamento['aberto'];

      $tiposPagamento = [];
      if($item != null){
         $tipos_pagamento = $item->tipos_pagamento ? json_decode($item->tipos_pagamento) : [];
         foreach($tipos_pagamento as $tp){
            array_push($tiposPagamento, DeliveryConfig::tiposPagamento()[$tp]);
         }
         $item->tipos_pagamento = $tiposPagamento;
      }

      return response()->json($item, 200);
   }

   public function cupom(Request $request){
      $item = CodigoDesconto::where('codigo', $request->cupom)
      ->where('empresa_id', $request->empresa_id)
      ->where('ativo', 1)
      ->first();
      if($item == null){
         return response()->json("cupom não encontrado", 404);
      }
      $total = $request->total;

      if($total < $item->valor_minimo_pedido){
         return response()->json("valor minímo para este cupom R$ " . moeda($item->valor_minimo_pedido), 401);
      }

      $desconto = 0;
      if($item->tipo == 'valor'){
         $desconto = $item->valor;
      }else{
         $desconto = $total * $item->valor;
      }
      return response()->json(number_format($desconto, 2), 200);

   }

   public function bairros(Request $request){
      $data = BairroDeliveryLoja::
      where('empresa_id', $request->empresa_id)
      ->get();

      return response()->json($data, 200);

   }
}
