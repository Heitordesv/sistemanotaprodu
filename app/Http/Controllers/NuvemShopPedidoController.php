<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cliente;
use App\Models\NuvemShopPedido;
use App\Models\Produto;
use App\Models\ConfigNota;
use App\Models\Tributacao;
use App\Models\Categoria;
use App\Models\Transportadora;
use App\Models\Cidade;
use App\Models\Venda;
use App\Models\ItemVenda;
use App\Models\NaturezaOperacao;
use App\Models\NuvemShopItemPedido;
use Illuminate\Support\Str;
use Dompdf\Dompdf;
use App\Helpers\StockMove;
use Illuminate\Support\Facades\DB;

class NuvemShopPedidoController extends Controller
{
    public function index(Request $request)
    {
        $store_info = session('store_info');
        if (!$store_info) {
            return redirect()->route('nuvemshop-auth.authorize');
        }

        $page = $request->page ? $request->page : 1;
        $cliente = $request->cliente;
        $data_inicial = $request->data_inicial;
        $data_final = $request->data_final;

        $api = new \TiendaNube\API($store_info['store_id'], $store_info['access_token'], 'Awesome App (' . $store_info['email'] . ')');
        try {
            if ($cliente != "" || $data_inicial != "" || $data_final != "") {
                $sql = "orders?q=" . $cliente . "";
                if ($data_inicial) {
                    $sql .= "&created_at_min=" . $data_inicial . "";
                }
                if ($data_final) {
                    $sql .= "&created_at_max=" . $data_final . "";
                }
                $pedidos = (array)$api->get($sql . "&per_page=10");
            } else {
                $pedidos = (array)$api->get("orders?page=" . $page . "&per_page=12");
            }
            $pedidos = $pedidos['body'];
        } catch (\Exception $e) {
            echo $e->getMessage();
            die;
        }

        $this->salvaPedidos($pedidos);

        foreach ($pedidos as $p) {
            $p->numero_nfe = 0;
        }

        return view('nuvemshop_pedidos.index', compact('pedidos', 'page', 'cliente', 'data_inicial', 'data_final'));
    }

    private function salvaPedidos($pedidos)
    {
        foreach ($pedidos as $p) {

            // dd($p);
            $data = [
                'pedido_id' => $p->id,
                'rua' => $p->billing_address,
                'numero' => $p->billing_number ?? 0,
                'bairro' => $p->billing_locality ?? '',
                'cidade' => $p->billing_city,
                'cep' => $p->billing_zipcode,
                'total' => $p->total,
                'cliente_id' => $p->customer->id,
                'observacao' => $p->shipping_option,
                'nome' => $p->customer->name,
                'email' => $p->customer->email,
                'documento' => $p->customer->identification ? $p->customer->identification : '',
                'empresa_id' => request()->empresa_id,
                'subtotal' => $p->subtotal,
                'desconto' => $p->discount,
                'numero_nfe' => 0,
                'status_envio' => $p->shipping_status,
                'gateway' => $p->gateway,
                'status_pagamento' => $p->payment_status,
                'data' => $p->created_at
            ];

            $pedido = NuvemShopPedido::where('pedido_id', $p->id)->first();

            if ($pedido == null) {

                $this->salvaCliente($p);

                $pedido = NuvemShopPedido::create($data);

                foreach ($p->products as $prod) {

                    $produto = $this->validaProduto($prod);

                    $item = [
                        'pedido_id' => $pedido->id,
                        'produto_id' => $produto->id,
                        'quantidade' => $prod->quantity,
                        'valor' => $prod->price,
                        'nome' => $prod->name
                    ];

                    NuvemShopItemPedido::create($item);
                }
            } else {
                $this->atualizaCliente($p);
            }
        }
    }

    private function salvaCliente($pedido)
    {
        $customer = $pedido->customer;
        $address = $customer->default_address;

        if ($pedido->shipping_address) {
            $address = $pedido->shipping_address;
        }

        // echo "<pre>";
        // print_r($pedido);
        // echo "</pre>";

        // die;
        $cidade = null;
        if ($address) {
            $cidade = Cidade::where('nome', $address->city)
                ->first();
        }

        $telefone = $address ? ($address->phone ? $address->phone : $customer->billing_phone) : '';
        if (substr($telefone, 0, 3) == '+55') {
            $telefone = substr($telefone, 3, strlen($telefone));
        }

        $doc = $this->setMaskDoc($customer->identification);
        $cliente_id = '';
        if (isset($pedido->rua) && isset($pedido->nome)) {

            $doc = $this->setMaskDoc($pedido->identification);

            $cidade = Cidade::where('nome', $pedido->cidade)
                ->first();

            $data = [
                'razao_social' => $pedido->nome,
                'nome_fantasia' => $pedido->nome,
                'bairro' => $pedido->bairro,
                'numero' => $pedido->numero,
                'rua' => $pedido->rua,
                'cpf_cnpj' => $doc,
                'telefone' => '',
                'celular' => '',
                'email' => $pedido->email,
                'cep' => $pedido->cep,
                'ie_rg' => '',
                'consumidor_final' => 0,
                'limite_venda' => '',
                'cidade_id' => $cidade == null ? 1 : $cidade->id,
                'contribuinte' => 0,
                'rua_cobranca' => '',
                'numero_cobranca' => '',
                'bairro_cobranca' => '',
                'cep_cobranca' => '',
                'cidade_cobranca_id' => null,
                'empresa_id' => request()->empresa_id,
                'cod_pais' => 1058,
                'id_estrangeiro' => '',
                'grupo_id' => 0,
                'contador_nome' => '',
                'contador_telefone' => '',
                'funcionario_id' => 0,
                'observacao' => '',
                'contador_email' => '',
                'data_aniversario' => '',
                'complemento' => '',
                'nuvemshop_id' => $pedido->cliente_id
            ];
            $client_id = $pedido->cliente_id;
        } else {
            $data = [
                'razao_social' => $customer->name,
                'nome_fantasia' => $customer->name,
                'bairro' => $address->locality ? $address->locality : $customer->billing_locality,
                'numero' => $address->number ? $address->number : $customer->billing_number,
                'rua' => $address->address ? $address->address : $customer->billing_address,
                'cpf_cnpj' => $doc,
                'telefone' => $telefone,
                'celular' => '',
                'email' => $customer->email,
                'cep' => $address->zipcode,
                'ie_rg' => '',
                'consumidor_final' => 0,
                'limite_venda' => '',
                'cidade_id' => $cidade == null ? 1 : $cidade->id,
                'contribuinte' => 0,
                'rua_cobranca' => '',
                'numero_cobranca' => '',
                'bairro_cobranca' => '',
                'cep_cobranca' => '',
                'cidade_cobranca_id' => null,
                'empresa_id' => request()->empresa_id,
                'cod_pais' => 1058,
                'id_estrangeiro' => '',
                'grupo_id' => 0,
                'contador_nome' => '',
                'contador_telefone' => '',
                'funcionario_id' => 0,
                'observacao' => '',
                'contador_email' => '',
                'data_aniversario' => '',
                'complemento' => $address->floor ? $address->floor : $customer->billing_floor,
                'nuvemshop_id' => $customer->id
            ];
            $client_id = $customer->id;
        }

        // if($address){
        // $data = [
        //     'razao_social' => $customer->name,
        //     'nome_fantasia' => $customer->name,
        //     'bairro' => $address->locality ? $address->locality : $customer->billing_locality,
        //     'numero' => $address->number ? $address->number : $customer->billing_number,
        //     'rua' => $address->address ? $address->address : $customer->billing_address,
        //     'cpf_cnpj' => $doc,
        //     'telefone' => $telefone, 
        //     'celular' => '',
        //     'email' => $customer->email,
        //     'cep' => $address->zipcode,
        //     'ie_rg' => '',
        //     'consumidor_final' => 0,
        //     'limite_venda' => '',
        //     'cidade_id' => $cidade == null ? 1 : $cidade->id, 
        //     'contribuinte' => 0, 
        //     'rua_cobranca' => '',
        //     'numero_cobranca' => '',
        //     'bairro_cobranca' => '',
        //     'cep_cobranca' => '', 
        //     'cidade_cobranca_id' => null, 
        //     'empresa_id' => $this->empresa_id, 
        //     'cod_pais' => 1058,
        //     'id_estrangeiro' => '',
        //     'grupo_id' => 0,
        //     'contador_nome' => '',
        //     'contador_telefone' => '',
        //     'funcionario_id' => 0,
        //     'observacao' => '', 
        //     'contador_email' => '',
        //     'data_aniversario' => '',
        //     'complemento' => $address->floor ? $address->floor : $customer->billing_floor,
        //     'nuvemshop_id' => $customer->id
        // ];
        // }else{
        //     $data = [
        //         'razao_social' => $customer->name,
        //         'nome_fantasia' => $customer->name,
        //         'bairro' => '',
        //         'numero' => '',
        //         'rua' => '',
        //         'cpf_cnpj' => $doc,
        //         'telefone' => $telefone, 
        //         'celular' => '',
        //         'email' => $customer->email,
        //         'cep' => '',
        //         'ie_rg' => '',
        //         'consumidor_final' => 0,
        //         'limite_venda' => '',
        //         'cidade_id' => 1, 
        //         'contribuinte' => 0, 
        //         'rua_cobranca' => '',
        //         'numero_cobranca' => '',
        //         'bairro_cobranca' => '',
        //         'cep_cobranca' => '', 
        //         'cidade_cobranca_id' => null, 
        //         'empresa_id' => $this->empresa_id, 
        //         'cod_pais' => 1058,
        //         'id_estrangeiro' => '',
        //         'grupo_id' => 0,
        //         'contador_nome' => '',
        //         'contador_telefone' => '',
        //         'funcionario_id' => 0,
        //         'observacao' => '', 
        //         'contador_email' => '',
        //         'data_aniversario' => '',
        //         'complemento' => '',
        //         'nuvemshop_id' => $customer->id
        //     ];
        // }

        $cliente = Cliente::where('nuvemshop_id', $client_id)->first();
        if ($cliente == null) {
            Cliente::create($data);
        }
    }

    private function setMaskDoc($doc)
    {
        if (strlen($doc) == 14) {
            $str = substr($doc, 0, 2) . ".";
            $str .= substr($doc, 2, 3) . ".";
            $str .= substr($doc, 5, 3) . "/";
            $str .= substr($doc, 8, 4) . "-";
            $str .= substr($doc, 12, 2);

            return $str;
        } else {
            $str = substr($doc, 0, 3) . ".";
            $str .= substr($doc, 3, 3) . ".";
            $str .= substr($doc, 6, 3) . "-";
            $str .= substr($doc, 9, 2);
            return $str;
        }
    }

    private function validaProduto($prod)
    {

        $produto = Produto::where('nuvemshop_id', $prod->product_id)->first();

        if ($produto != null) return $produto;

        $config = ConfigNota::where('empresa_id', request()->empresa_id)->first();
        $natureza = Produto::firstNatureza(request()->empresa_id);
        $tributacao = Tributacao::where('empresa_id', request()->empresa_id)->first();
        $categoria = Categoria::where('empresa_id', request()->empresa_id)->first();
        if($categoria == null){
            session()->flash("flash_warning", "Cadastre uma categoria!");
            return redirect()->route('categorias.index');
        }
        $valorVenda = $prod->price;
        $valorCompra = $valorVenda - (($valorVenda * $config->percentual_lucro_padrao) / 100);

        $arr = [
            'nome' => $prod->name,
            'categoria_id' => $categoria->id,
            'cor' => '',
            'valor_venda' => $valorVenda,
            'NCM' => $tributacao->ncm_padrao,
            'CST_CSOSN' => $config->CST_CSOSN_padrao,
            'CST_PIS' => $config->CST_PIS_padrao,
            'CST_COFINS' => $config->CST_COFINS_padrao,
            'CST_IPI' => $config->CST_IPI_padrao,
            'unidade_compra' => 'UN',
            'unidade_venda' => 'UN',
            'composto' => 0,
            'codBarras' => 'SEM GTIN',
            'conversao_unitaria' => 1,
            'valor_livre' => 0,
            'perc_icms' => $tributacao->icms,
            'perc_pis' => $tributacao->pis,
            'perc_cofins' => $tributacao->cofins,
            'perc_ipi' => $tributacao->ipi,
            'CFOP_saida_estadual' => $natureza->CFOP_saida_estadual,
            'CFOP_saida_inter_estadual' => $natureza->CFOP_saida_inter_estadual,
            'codigo_anp' => '',
            'descricao_anp' => '',
            'perc_iss' => 0,
            'cListServ' => '',
            'imagem' => '',
            'alerta_vencimento' => 0,
            'valor_compra' => $valorCompra,
            'gerenciar_estoque' => 0,
            'estoque_minimo' => 0,
            'referencia' => '',
            'tela_id' => NULL,
            'largura' => $prod->width,
            'comprimento' => $prod->depth,
            'altura' => $prod->height,
            'peso_liquido' => $prod->weight,
            'peso_bruto' => $prod->weight,
            'empresa_id' => request()->empresa_id,
            'percentual_lucro' => $config->percentual_lucro_padrao,
            'referencia_grade' => Str::random(20),
            "nuvemshop_id" => $prod->product_id
        ];
        // print_r($arr);
        // die;

        $produto = Produto::create($arr);

        return $produto;
    }

    private function atualizaCliente($pedido)
    {

        $customer = $pedido->customer;

        if ($customer) {

            $cliente = Cliente::where('nuvemshop_id', $customer->id)->first();
            try {
                $cliente->razao_social = $customer->name;
                $cliente->nome_fantasia = $customer->name;
                $cliente->cpf_cnpj = $customer->identification;

                if (isset($pedido->shipping_address)) {
                    $address = $pedido->shipping_address;

                    $telefone = $address ? ($address->phone ? $address->phone : $customer->billing_phone) : '';

                    if (substr($telefone, 0, 3) == '+55') {
                        $telefone = substr($telefone, 3, strlen($telefone));
                    }

                    $cidade = Cidade::where('nome', $address->city)
                        ->first();

                    $cliente->telefone = $telefone;
                    $cliente->cep = $address->zipcode;
                    $cliente->bairro = $address->locality;
                    $cliente->numero = $address->number;
                    $cliente->rua = $address->address;
                    $cliente->cidade_id = $cidade == null ? 1 : $cidade->id;

                    $cliente->save();
                }
            } catch (\Exception $e) {
                // echo $e->getMessage(). "<br>";
                // echo $e->getLine(). "<br>";
                // die;
            }
        } else {
        }
    }

    public function show($id)
    {
        $store_info = session('store_info');
        $api = new \TiendaNube\API($store_info['store_id'], $store_info['access_token'], 'Awesome App (' . $store_info['email'] . ')');
        $pedido = NuvemShopPedido::where('pedido_id', $id)->first();
        $pTemp = (array)$api->get("orders/" . $pedido->pedido_id);
        $pTemp = $pTemp['body'];

        foreach ($pedido->itens as $i) {
            foreach ($pTemp->products as $iTemp) {
                if ($i->nome == $iTemp->name) {
                    $i->src = $iTemp->image->src;
                }
            }
        }
        $doc = $pedido->cliente->cpf_cnpj;

        $erros = [];

        if (strlen($doc) == 14 && str_contains($doc, ".")) {
            if (!$this->validaCPF($doc)) {
                array_push($erros, "CPF cliente inválido");
            }
        }

        if (strlen($doc) == 18) {
            if (!$this->validaCNPJ($doc)) {
                array_push($erros, "CNPJ cliente inválido");
            }
        }

        if ($pedido->cliente->cidade_id == 1) {
            array_push($erros, "Cidade cliente inválida");
        }

        return view('nuvemshop_pedidos/show', compact('pedido', 'erros'));
    }

    private function validaCPF($cpf)
    {

        $cpf = preg_replace('/[^0-9]/is', '', $cpf);
        // Verifica se foi informado todos os digitos corretamente
        if (strlen($cpf) != 11) {
            return false;
        }

        // Verifica se foi informada uma sequência de digitos repetidos. Ex: 111.111.111-11
        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }

        // Faz o calculo para validar o CPF
        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                return false;
            }
        }
        return true;
    }

    private function validaCNPJ($cnpj)
    {

        $cnpj = preg_replace('/[^0-9]/', '', (string) $cnpj);

        // Valida tamanho
        if (strlen($cnpj) != 14)
            return false;

        // Verifica se todos os digitos são iguais
        if (preg_match('/(\d)\1{13}/', $cnpj))
            return false;

        // Valida primeiro dígito verificador
        for ($i = 0, $j = 5, $soma = 0; $i < 12; $i++) {
            $soma += $cnpj[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }

        $resto = $soma % 11;

        if ($cnpj[12] != ($resto < 2 ? 0 : 11 - $resto))
            return false;

        // Valida segundo dígito verificador
        for ($i = 0, $j = 6, $soma = 0; $i < 13; $i++) {
            $soma += $cnpj[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }

        $resto = $soma % 11;

        return $cnpj[13] == ($resto < 2 ? 0 : 11 - $resto);
    }

    public function print($id)
    {
        $store_info = session('store_info');
        $api = new \TiendaNube\API($store_info['store_id'], $store_info['access_token'], 'Awesome App (' . $store_info['email'] . ')');
        $pedido = NuvemShopPedido::find($id);

        // $pTemp = (array)$api->get("orders/".$pedido->pedido_id);
        // $pTemp = $pTemp['body'];

        if (!__valida_objeto($pedido)) {
            abort(403);
        }
        $config = ConfigNota::where('empresa_id', request()->empresa_id)
            ->first();
        $p = view('nuvemshop_pedidos.print', compact('config', 'pedido'));
        // return $p;
        $domPdf = new Dompdf(["enable_remote" => true]);
        $domPdf->loadHtml($p);

        $pdf = ob_get_clean();

        $domPdf->setPaper("A4");
        $domPdf->render();
        $domPdf->stream("Pedido Nuvem Shop $pedido->pedido_id.pdf", array("Attachment" => false));
    }

    public function nfe($id)
    {
        $store_info = session('store_info');
        $api = new \TiendaNube\API($store_info['store_id'], $store_info['access_token'], 'Awesome App (' . $store_info['email'] . ')');
        $pedido = NuvemShopPedido::find($id);

        if (!__valida_objeto($pedido)) {
            abort(403);
        }

        $config = ConfigNota::where('empresa_id', request()->empresa_id)
            ->first();

        $naturezas = NaturezaOperacao::where('empresa_id', request()->empresa_id)
            ->get();

        $transportadoras = Transportadora::where('empresa_id', request()->empresa_id)
            ->get();

        $cidades = Cidade::all();

        $erros = [];

        $doc = $pedido->cliente->cpf_cnpj;

        if (strlen($doc) == 14) {
            if (!$this->validaCPF($doc)) {
                array_push($erros, "CPF cliente inválido");
            }
        }

        if (strlen($doc) == 18) {
            if (!$this->validaCNPJ($doc)) {
                array_push($erros, "CNPJ cliente inválido");
            }
        }

        if ($pedido->cliente->cidade_id == 1) {
            array_push($erros, "Cidade cliente inválida");
        }

        return view(
            'nuvemshop_pedidos.gerar_venda',
            compact('pedido', 'erros', 'cidades', 'naturezas', 'transportadoras')
        );
    }

    public function storeVenda(Request $request, $id)
    {
        $pedido = NuvemShopPedido::find($id);


        try {
            DB::transaction(function () use ($request, $pedido) {
                $transportadora = $request->transportadora ?? NULL;
                $natureza = $request->natureza;
                $tipoPagamento = $request->forma_pagamento;
                $dataVenda = [
                    'cliente_id' => $pedido->cliente->id,
                    'usuario_id' => get_id_user(),
                    'frete_id' => null,
                    'valor_total' => $pedido->total,
                    'forma_pagamento' => 'a_vista',
                    'NfNumero' => 0,
                    'natureza_id' => $natureza,
                    'chave' => '',
                    'path_xml' => '',
                    'estado' => 'novo',
                    'observacao' => '',
                    'desconto' => 0,
                    'transportadora_id' => $transportadora,
                    'sequencia_cce' => 0,
                    'tipo_pagamento' => $tipoPagamento,
                    'empresa_id' => $request->empresa_id,
                    'pedido_nuvemshop_id' => $pedido->id,
                ];

                $venda = Venda::create($dataVenda);

                $pedido->venda_id = $venda->id;
                $pedido->save();

                $stockMove = new StockMove();
                foreach ($pedido->itens as $i) {
                    $dataItem = [
                        'produto_id' => $i->produto->id,
                        'venda_id' => $venda->id,
                        'quantidade' => $i->quantidade,
                        'valor' => $i->valor,
                        'valor_custo' => $i->produto->valor_compra
                    ];

                    $stockMove->downStock($i->produto->id, $i->quantidade);

                    $item = ItemVenda::create($dataItem);
                }
            });

            session()->flash("flash_sucesso", "Venda de pedido gerada com sucesso!");
            return redirect()->route('vendas.index');
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
            return redirect()->back();
        }
    }
}
