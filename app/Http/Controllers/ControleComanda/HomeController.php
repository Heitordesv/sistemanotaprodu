<?php

namespace App\Http\Controllers\ControleComanda;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pedido;
use App\Models\Mesa;
use App\Models\ItemPedido;
use App\Models\TamanhoPizza;
use App\Models\ComplementoDelivery;
use App\Models\ItemPedidoComplementoLocal;
use App\Models\ItemPizzaPedidoLocal;
use App\Models\Produto;
use App\Models\ImpressaoPedido;
use App\Models\Impressora;

class HomeController extends Controller
{
    public function index(Request $request){
        try{
            $data = Pedido::
            where('desativado', false)
            ->where('empresa_id', $request->empresa_id)
            ->with('itens')
            ->with('mesa')
            ->get();

            foreach($data as $d){
                $d->total = $d->somaItems();
            }

            return response()->json($data, 200);
        }catch(\Exception $e){
            return response()->json($e->getMessage(), 401);
        }
    }

    public function mesas(Request $request){
        try{
            $data = Mesa::
            where('empresa_id', $request->empresa_id)
            ->get();

            return response()->json($data, 200);
        }catch(\Exception $e){
            return response()->json($e->getMessage(), 401);
        }
    }

    public function tamanhosPizza(Request $request){
        try{
            $data = TamanhoPizza::
            where('empresa_id', $request->empresa_id)
            ->get();

            return response()->json($data, 200);
        }catch(\Exception $e){
            return response()->json($e->getMessage(), 401);
        }
    }

    public function deleteComanda(Request $request){
        try{
            $item = Pedido::find($request->id);
            $item = $item->delete();
            return response()->json($item, 200);
        }catch(\Exception $e){
            return response()->json($e->getMessage(), 401);
        }
    }

    public function deleteItem(Request $request){
        try{
            $item = ItemPedido::find($request->id);
            $item = $item->delete();
            return response()->json($item, 200);
        }catch(\Exception $e){
            return response()->json($e->getMessage(), 401);
        }
    }

    public function entregue(Request $request){
        try{
            $item = ItemPedido::find($request->id);
            $item->status = 1;
            $item->save();
            return response()->json($item, 200);
        }catch(\Exception $e){
            return response()->json($e->getMessage(), 401);
        }
    }

    public function abrirComanda(Request $request){
        try{

            $item = Pedido::create([
                'comanda' => $request->codigo,
                'observacao' => $request->observacao ?? '',
                'status' => false,
                'nome' => '',
                'rua' => '',
                'numero' => '',
                'bairro_id' => null,
                'referencia' => '',
                'telefone' => '',
                'desativado' => false,
                'mesa_id' => $request->mesa,
                'cliente_id' => null,
                'empresa_id' => $request->empresa_id
            ]);
            return response()->json($item, 200);

        }catch(\Exception $e){
            return response()->json($e->getMessage(), 401);
        }
    }

    public function salvarItem(Request $request){
        try{

            $pedido = Pedido::findOrFail($request->pedido_id);

            $result = ItemPedido::create([
                'pedido_id' => $pedido->id,
                'produto_id' => $request->produto,
                'quantidade' => str_replace(",", ".", $request->qtd),
                'status' => $request->entregue,
                'tamanho_pizza_id' => $request->tamanho_pizza_id ?? NULL,
                'observacao' => $request->observacao ?? '',
                'valor' => str_replace(",", ".", $request->valor),
                'impresso' => false
            ]);

            if(!$request->entregue){
                // $impressoraPadrao = Impressora::where('empresa_id', $pedido->empresa_id)
                // ->where('padrao', 1)->first();
                // $impressora_id = 0;
                // if($impressoraPadrao != null){
                //     $impressora_id = $impressoraPadrao->id;
                // }
                // ImpressaoPedido::create([
                //     'empresa_id' => $pedido->empresa_id,
                //     'impressora_id' => $impressora_id,
                //     'produto_id' => $request->produto,
                //     'pedido_id' => $pedido->id,
                //     'quantidade_item' => str_replace(",", ".", $request->qtd),
                //     'valor_total' => str_replace(",", ".", $request->valor),
                //     'tabela' => 'pedidos',
                //     'status' => 0
                // ]);
            }

            if($request->tamanho_pizza_id && $request->sabores){

                foreach($request->sabores as $sab){

                    $prod = Produto::find($sab['id']);
                    $item = ItemPizzaPedidoLocal::create([
                        'item_pedido' => $result->id,
                        'sabor_id' => $prod->delivery->id,
                    ]);

                }
            }

            if($request->adicionais){

                foreach($request->adicionais as $a){

                    $adicional = ComplementoDelivery::find($a['id']);

                    $item = ItemPedidoComplementoLocal::create([
                      'item_pedido' => $result->id,
                      'complemento_id' => $adicional->id,
                      'quantidade' => str_replace(",", ".", $request->qtd),
                  ]);
                }
            }
            return response()->json($pedido, 200);
        }catch(\Exception $e){
            return response()->json($e->getMessage(), 401);
        }
    }

    public function pedido($id){
        try{
            $item = Pedido::with('itens')
            ->findOrFail($id);
            return response()->json($item, 200);
        }catch(\Exception $e){
            return response()->json($e->getMessage(), 401);
        }
    }
}
