<?php

namespace App\Http\Controllers;

use App\Models\BairroDelivery;
use App\Models\BairroDeliveryLoja;
use App\Models\CategoriaProdutoDelivery;
use App\Models\ClienteDelivery;
use App\Models\ComplementoDelivery;
use App\Models\DeliveryConfig;
use App\Models\EnderecoDelivery;
use App\Models\ItemPedidoComplementoDelivery;
use App\Models\ItemPedidoDelivery;
use App\Models\ItemPizzaPedido;
use App\Models\PedidoDelivery;
use App\Models\Produto;
use App\Models\ProdutoDelivery;
use App\Models\TamanhoPizza;
use Illuminate\Http\Request;

class PedidoDeliveryController extends Controller
{
    public function today()
    {
        $pedidosNovo = $this->filtroPedidos(
            date("Y-m-d"),
            date('Y-m-d', strtotime('+1 day')),
            'novo'
        );
        $pedidosAprovado = $this->filtroPedidos(
            date("Y-m-d"),
            date('Y-m-d', strtotime('+1 day')),
            'aprovado'
        );
        $pedidosCancelado = $this->filtroPedidos(
            date("Y-m-d"),
            date('Y-m-d', strtotime('+1 day')),
            'cancelado'
        );
        $pedidosFinalizado = $this->filtroPedidos(
            date("Y-m-d"),
            date('Y-m-d', strtotime('+1 day')),
            'finalizado'
        );
        $carrinho = [];
        $somaNovos = $this->somaPedidos($pedidosNovo);
        $somaAprovados = $this->somaPedidos($pedidosAprovado);
        $somaCancelados = $this->somaPedidos($pedidosCancelado);
        $somaFinalizados = $this->somaPedidos($pedidosFinalizado);
        $item = PedidoDelivery::where('empresa_id', request()->empresa_id)->get();
        return view(
            'pedido_delivery.today',
            compact(
                'item',
                'pedidosNovo',
                'pedidosAprovado',
                'pedidosCancelado',
                'pedidosFinalizado',
                'somaNovos',
                'somaAprovados',
                'somaCancelados',
                'somaFinalizados'
            )
        );
    }

    public function frente(Request $request)
    {
        $config = DeliveryConfig::where('empresa_id', request()->empresa_id)
            ->first();
        if ($config == null) {
            session()->flash('flash_erro', 'Configurar o delivery primeiro');
            return redirect()->route('configDelivery.index');
        }
        $tamanhos = TamanhoPizza::orderBy('nome')
            ->where('empresa_id', request()->empresa_id)
            ->get();
        $data = PedidoDelivery::where('empresa_id', request()->empresa_id)->get();
        $clientes = ClienteDelivery::where('empresa_id', request()->empresa_id)->get();
        $categorias = CategoriaProdutoDelivery::where('empresa_id', request()->empresa_id)->get();
        $produtos = ProdutoDelivery::select('produto_deliveries.*')
            ->orderBy('produtos.nome')
            ->join('produtos', 'produtos.id', '=', 'produto_deliveries.produto_id')
            ->where('produtos.empresa_id', request()->empresa_id)
            ->with('pizza')
            ->get();
        $pizzas = [];
        foreach ($produtos as $p) {
            $p->pizza;
            $p->produto;
            foreach ($p->pizza as $pz) {
                $pz->tamanho;
            }
            if (sizeof($p->pizza) > 0) {
                array_push($pizzas, $p);
            }
        }
        $cliente = null;
        if ($request->cliente) {
            $cliente = ClienteDelivery::find($request->cliente);
            $pedidoAberto = PedidoDelivery::where('estado', 'novo')
                ->where('cliente_id', $request->cliente)
                ->first();
            if ($pedidoAberto == null) {
                $pedidoAberto = PedidoDelivery::create([
                    'cliente_id' => $request->cliente,
                    'valor_total' => 0,
                    'telefone' => '',
                    'observacao' => '',
                    'forma_pagamento' => '',
                    'estado' => 'novo',
                    'motivoEstado' => '',
                    'endereco_id' => NULL,
                    'troco_para' => 0,
                    'desconto' => 0,
                    'cupom_id' => NULL,
                    'app' => false,
                    'empresa_id' => $request->empresa_id
                ]);
            }
            return redirect()->route('pedidosDelivery.frenteComPedido', $pedidoAberto->id);
        }
        return view('pedido_delivery.frente', compact(
            'data',
            'clientes',
            'categorias',
            'produtos',
            'cliente',
            'tamanhos',
            'pizzas',
            'config'
        ));
    }

    private function somaPedidos($arr)
    {
        $v = 0;
        foreach ($arr as $r) {

            $v += $r->somaItens();
        }
        return $v;
    }

    private function filtroPedidos($dataInicial, $dataFinal, $estado, $sinal = '>')
    {
        $pedidos = PedidoDelivery::whereBetween('data_registro', [
            $dataInicial,
            $dataFinal
        ])
            ->where('estado', $estado)
            ->where('valor_total', $sinal, 0)
            ->get();
        return $pedidos;
    }

    public function frenteComPedido($id)
    {
        $pedido = PedidoDelivery::findOrFail($id);
        if (!__valida_objeto($pedido)) {
            abort(403);
        }
        if ($pedido->cliente_id != null) {
        }
        $bairros = BairroDeliveryLoja::where('empresa_id', request()->empresa_id)->first();
        if ($bairros == null) {
            session()->flash('flash_erro', 'Cadastre os bairros antes de continuar');
            return redirect()->route('bairrosDeliveryLoja.index');
        }
        $clientes = ClienteDelivery::orderBy('nome')
            ->where('empresa_id', request()->empresa_id)
            ->get();
        if ($pedido->estado == 'aprovado' || $pedido->valor_total > 0) {
            return redirect('/pedidosDelivery/verPedido/' . $pedido->id);
        }
        $config = DeliveryConfig::where('empresa_id', request()->empresa_id)
            ->first();
        $produtos = ProdutoDelivery::select('produto_deliveries.*')
            ->orderBy('produtos.nome')
            ->join('produtos', 'produtos.id', '=', 'produto_deliveries.produto_id')
            ->where('produtos.empresa_id', request()->empresa_id)
            ->limit(21)
            ->with('pizza')
            ->get();
        $categorias = CategoriaProdutoDelivery::where('empresa_id', request()->empresa_id)
            ->get();
        foreach ($produtos as $p) {
            $p->produto;
        }
        $tamanhos = TamanhoPizza::orderBy('nome')
            ->where('empresa_id', request()->empresa_id)
            ->get();
        $valorEntrega = 0;
        if ($pedido->endereco) {
            if ($config->usar_bairros) {
                $bairro = BairroDelivery::find($pedido->endereco->bairro_id);
                $valorEntrega = $bairro->valor_entrega;
            } else {
                $valorEntrega = $config->valor_entrega;
            }
        }
        $pizzas = [];
        foreach ($produtos as $p) {
            $p->pizza;
            $p->produto;
            foreach ($p->pizza as $pz) {
                $pz->tamanho;
            }
            if (sizeof($p->pizza) > 0) {
                array_push($pizzas, $p);
            }
        }
        return view('pedido_delivery.frente', compact(
            'pedido',
            'config',
            'produtos',
            'pizzas',
            'categorias',
            'bairros',
            'tamanhos',
            'clientes',
            'valorEntrega'
        ));
    }

    public function store(Request $request)
    {
        $pedido = PedidoDelivery::findOrFail($request->pedido_nr);
        try {
            if (isset($request->sabores)) {
                $prodId = $request->sabores[0];
            } else {
                $prodId = $request->prod_id;
            }
            $adicionais = $request->inp_adicionais != null ? json_decode($request->inp_adicionais) : [];
            $item = ItemPedidoDelivery::create([
                'pedido_id' => $pedido->id,
                'produto_id' => $prodId,
                'quantidade' => __convert_value_bd($request->quantidade),
                'status' => false,
                'tamanho_id' => $request->tamanho_pizza_id ?? NULL,
                'observacao' => $request->observacao ?? '',
                'valor' => __convert_value_bd($request->valor_com_add)
            ]);

            if (isset($request->sabores)) {
                for ($i = 0; $i < sizeof($request->sabores); $i++) {
                    ItemPizzaPedido::create([
                        'item_pedido' => $item->id,
                        'sabor_id' => $request->sabores[$i],
                    ]);
                }
            }

            if (sizeof($adicionais)) {
                foreach ($adicionais as $a) {
                    ItemPedidoComplementoDelivery::create([
                        'item_pedido_id' => $item->id,
                        'complemento_id' => $a->id,
                        'quantidade' => 1,
                    ]);
                }
            }
            session()->flash('flash_sucesso', 'Produto adicionado ao pedido');
        } catch (\Exception $e) {
            // echo $e->getMessage() . '<br>' . $e->getLine();
            // die;
            session()->flash('flash_erro', 'Algo deu errado: ' . $e->getMessage());
        }
        return redirect()->back();
    }

    public function deleteItem($id)
    {
        $item = ItemPedidoDelivery::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        try {
            $item->delete();
            session()->flash('flash_modal', '');
            session()->flash('flash_sucesso', 'Item removido com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado:' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->back();
    }

    public function frenteComEndereco(Request $request)
    {
        $pedido = PedidoDelivery::findOrFail($request->pedido_id);
        try {
            $pedido->endereco_id = $request->endereco_id;
            $pedido->save();
            session()->flash('flash_sucesso', 'Sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Nao deu boa.');
        }
        return redirect()->back();
    }

    public function frenteComPedidoFinalizar(Request $request)
    {
        $pedido = PedidoDelivery::find($request->pedido_id);
        $total = $pedido->somaItens();

        if ($pedido->endereco_id != null) {
            $total += $pedido->endereco->_bairro->valor_entrega;
        }
        $pedido->valor_total = $total;
        $pedido->forma_pagamento = $request->forma_pagamento;
        $pedido->estado = 'aprovado';
        $pedido->telefone = $pedido->cliente->telefone;
        $pedido->troco_para = __convert_value_bd($request->troco_para);
        $pedido->save();
        session()->flash('flash_sucesso', 'Pedido realizado!');
        return redirect()->route('pedidosDelivery.today');
    }
}
