<?php

namespace App\Http\Controllers\Delivery;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PedidoDelivery;
use App\Models\ItemPedidoDelivery;
use App\Models\ItemPedidoComplementoDelivery;
use App\Models\ClienteDelivery;
use App\Models\DeliveryConfig;
use App\Models\ItemPizzaPedido;
use DB;

class PedidoController extends Controller
{
    public function save(Request $request){

        $result = DB::transaction(function () use ($request) {
            $cliente = ClienteDelivery::where('uid', $request->uid)->first();
            $itens = $request->itens;
            $dataPedido = [
                'cliente_id' => $cliente->id,
                'valor_total' => $request->pedido['total_produtos']+__convert_value_bd($request->valor_entrega),
                'forma_pagamento' => $request->forma_pagamento,
                'observacao' => $request->observacao ?? '',
                'telefone' => $cliente->celular,
                'estado' => 'novo',
                'endereco_id' => $request->endereco ? $request->endereco['id'] : null,
                'motivoEstado' => '',
                'troco_para' => $request->troco,
                'cupom_id' => $request->pedido['cupom'],
                'desconto' => $request->pedido['desconto'],
                'app' => 1,
                'empresa_id' => $request->empresa_id,
                'valor_entrega' => __convert_value_bd($request->valor_entrega),
                'qr_code_base64' => '',
                'qr_code' => '',
                'transacao_id' => '',
                'status_pagamento' => '',
                'pedido_lido' => 0,
                'horario_cricao' => date('H:i'),
                'horario_entrega' => '',
                'horario_leitura' => '',
            ];

            $pedido = PedidoDelivery::create($dataPedido);

            foreach($itens as $item){
                $dataItem = [
                    'pedido_id' => $pedido->id,
                    'produto_id' => $item['id'],
                    'status' => 0,
                    'quantidade' => __convert_value_bd($item['qtd']),
                    'observacao' => $item['observacao'] ?? '',
                    'tamanho_id' => $item['tamanho_id'] ?? null,
                    'valor' => __convert_value_bd($item['valor'])

                ];
                // return response()->json($dataItem, 200);

                $tItem = ItemPedidoDelivery::create($dataItem);

                if(isset($item['adicionais'])){
                    foreach($item['adicionais'] as $add){
                        ItemPedidoComplementoDelivery::create([
                            'item_pedido_id' => $tItem->id,
                            'complemento_id' => $add['id'],
                            'quantidade' => __convert_value_bd($item['qtd'])
                        ]);
                    }
                }

                if($item['sabores']){
                    foreach($item['sabores'] as $s){
                        ItemPizzaPedido::create([
                            'item_pedido' => $tItem->id,
                            'sabor_id' => $s
                        ]);
                    }
                }
            }

            if($request->forma_pagamento == 'Pix pelo App'){
                $pedido = $this->gerarQrcode($pedido, $request->cpf);
                $cpf = preg_replace('/[^0-9]/', '', $request->cpf);
                $cliente->cpf = $cpf;
                $cliente->save();
            }

            if($request->forma_pagamento == 'Cartão pelo App'){
                $payCard = $this->gerarPagamentoCartao($pedido, $request->tokenCard, $request->cpf, $request->paymentMethodId);
                
                return $payCard;
                
            }
            return $pedido;
        });

        return response()->json($result, 200);
    }

    public function gerarQrcode($pedido, $cpf){
        $config = DeliveryConfig::where('empresa_id', $pedido->empresa_id)->first();
        $cliente = $pedido->cliente;

        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        \MercadoPago\SDK::setAccessToken($config->mercadopago_access_token);

        $payment = new \MercadoPago\Payment();

        $payment->transaction_amount = (float) number_format($pedido->valor_total,2);
        $payment->description = "pagamento do pedido #" . $pedido->id;
        $payment->payment_method_id = "pix";

        $cep = str_replace("-", "", $config->cep);

        $payment->payer = array(
            "email" => $cliente->email,
            "first_name" => $cliente->nome,
            "last_name" => $cliente->sobre_nome,
            "identification" => array(
                "type" => 'CPF',
                "number" => $cpf
            ),
            "address"=>  array(
                "zip_code" => $cep,
                "street_name" => $config->rua,
                "street_number" => $config->numero,
                "neighborhood" => $config->bairro,
                "city" => $config->cidade->nome,
                "federal_unit" => $config->cidade->uf
            )
        );

        $payment->save();

        if($payment->transaction_details){
            $pedido->transacao_id = $payment->id;
            $pedido->status_pagamento = $payment->status;
            $pedido->qr_code_base64 = $payment->point_of_interaction->transaction_data->qr_code_base64;
            $pedido->qr_code = $payment->point_of_interaction->transaction_data->qr_code;

            $pedido->save();
            return $pedido;

        }else{
            return $payment->error;
        }

    }

    public function consultaPix(Request $request){
        try{

            $pedido = PedidoDelivery::findOrFail($request->pedido_id);

            $config = DeliveryConfig::where('empresa_id', $pedido->empresa_id)->first();
            \MercadoPago\SDK::setAccessToken($config->mercadopago_access_token);

            if($pedido){
                $payStatus = \MercadoPago\Payment::find_by_id($pedido->transacao_id);

                // $payStatus->status = "approved";
                if($payStatus->status == "approved"){

                    $pedido->status_pagamento = $payStatus->status;
                    $pedido->estado = 'aprovado';
                    $pedido->save();
                }
            }
            return response()->json($pedido->status_pagamento, 200);

        }catch(\Exception $e){
            return response()->json($e->getMessage(), 401);
        }
    }

    public function ultimoPedidoParaConfirmar(Request $request){
        $cliente = ClienteDelivery::where('uid', $request->uid)->first();

        $pedido = PedidoDelivery::
        where('cliente_id', $cliente->id)
        ->orderBy('id', 'desc')
        ->first();
        try{
            return response()->json($pedido, 200);

        }catch(\Exception $e){
            return response()->json($e->getMessage(), 401);
        }
    }

    public function consultaPedidoLido(Request $request){
        $pedido = PedidoDelivery::
        with('empresa')
        ->findOrFail($request->pedido_id);

        try{
            return response()->json($pedido, 200);

        }catch(\Exception $e){
            return response()->json($e->getMessage(), 401);
        }
    }

    public function gerarPagamentoCartao($pedido, $token, $cpf, $paymentMethodId){
        $config = DeliveryConfig::where('empresa_id', $pedido->empresa_id)->first();
        $cliente = $pedido->cliente;

        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        \MercadoPago\SDK::setAccessToken($config->mercadopago_access_token);

        $payment = new \MercadoPago\Payment();

        $payment->transaction_amount = (float) number_format($pedido->valor_total,2);
        $payment->description = "pagamento do pedido #" . $pedido->id;
        $payment->token = $token;
        $payment->installments = 1;
        $payment->payment_method_id = $paymentMethodId;

        $cep = str_replace("-", "", $config->cep);

        $payment->payer = array(
            "email" => $cliente->email,
            "first_name" => $cliente->nome,
            "last_name" => $cliente->sobre_nome,
            "identification" => array(
                "type" => 'CPF',
                "number" => $cpf
            ),
            "address"=>  array(
                "zip_code" => $cep,
                "street_name" => $config->rua,
                "street_number" => $config->numero,
                "neighborhood" => $config->bairro,
                "city" => $config->cidade->nome,
                "federal_unit" => $config->cidade->uf
            )
        );

        $payment->save();

        if($payment->error){

            return $payment->error;

        }else{
            $pedido->transacao_id = $payment->id;
            $pedido->status_pagamento = $payment->status;
            // $pedido->estado = 'aprovado';
            $pedido->save();
            return response()->json($pedido, 200);
        }
    }
    
}
