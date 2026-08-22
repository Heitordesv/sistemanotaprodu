<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ConfigEcommerce;
use App\Models\PostBlogEcommerce;
use App\Models\PedidoEcommerce;
use App\Models\CategoriaProdutoEcommerce;
use App\Helpers\PedidoEcommerceHelper;
use Mail;
use Illuminate\Support\Str;
use App\Helpers\StockMove;
use App\Models\Orcamento;
use App\Models\ItemOrcamento;
use App\Models\Cliente;
use App\Models\Produto;
use App\Models\Cidade;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Payment\PaymentClient;

class EcommercePayController extends Controller
{
    
    
public function paymentCartao(Request $request)
{
    // Busca o pedido
    $pedido = PedidoEcommerce::find($request->carrinho_id);

    // Busca a configuração do e-commerce
    $config = ConfigEcommerce::where('empresa_id', $pedido->empresa_id)->first();

    // Define o token do Mercado Pago
    \MercadoPago\SDK::setAccessToken($config->mercadopago_access_token);

    // Cria o pagamento
    $payment = new \MercadoPago\Payment();
    $payment->transaction_amount = $request->transactionAmount;
    $payment->description = $request->description;
    $payment->token = $request->token;
    $payment->installments = (int) $request->installments;
    $payment->payment_method_id = $request->paymentMethodId;

    // Sanitiza o número do documento
    $docNumber = preg_replace('/[^0-9]/', '', $request->docNumber);

    // Define o pagador
    $payer = new \MercadoPago\Payer();
    $payer->email = $request->email;
    $payer->identification = [
        "type" => $request->docType,
        "number" => $docNumber
    ];
    $payment->payer = $payer;

    // Salva o pagamento
    $payment->save();

    // Verifica se houve erro
    if ($payment->error) {
        session()->flash("mensagem_erro", $payment->error);

        // Redireciona de volta à rota de endereço
        $link = $config->link;
        $default = $this->getDadosDefault($link);
        return redirect($default['rota'] . '/endereco');
    } else {
        // Sucesso no pagamento
        $pedido->transacao_id = $payment->id;
        $pedido->status_pagamento = $payment->status;
        $pedido->forma_pagamento = 'CARTÃO';
        $pedido->status_detalhe = $payment->status_detail;
        $pedido->hash = Str::random(20);
        $pedido->status = 1;
        $pedido->valor_total = $request->total_pag;

        // Tenta enviar o e-mail
        try {
            $this->sendMail($pedido);
        } catch (\Exception $e) {
            // Log de erro se necessário
        }

        $pedido->save();

        // Redireciona para a página de finalização
        return redirect('/ecommercePay/finalizado/' . $pedido->hash);
    }
}

public function paymentBoleto(Request $request)
{
    $pedido = PedidoEcommerce::findOrFail($request->carrinho_id);
    $config = ConfigEcommerce::where('empresa_id', $pedido->empresa_id)->firstOrFail();

    // Configura o Access Token
    MercadoPagoConfig::setAccessToken($config->mercadopago_access_token);

    try {
        // 🔹 Cria uma instância do PaymentClient
        $client = new PaymentClient();

        // 🔹 Cria o pagamento usando a instância
        $payment = $client->create([
            "transaction_amount" => (float) $request->transactionAmount,
            "description" => "Pedido Ecommerce #" . $pedido->id,
            "payment_method_id" => "bolbradesco", // boleto Bradesco
            "payer" => [
                "email" => $request->payerEmail,
                "first_name" => $request->payerFirstName,
                "last_name" => $request->payerLastName,
                "identification" => [
                    "type" => $request->docType,
                    "number" => preg_replace('/[^0-9]/', '', $request->docNumber)
                ],
                "address" => [
                    "zip_code" => preg_replace('/[^0-9]/', '', $config->cep),
                    "street_name" => $config->rua,
                    "street_number" => $config->numero,
                    "neighborhood" => $config->bairro,
                    "city" => is_object($config->cidade) ? $config->cidade->nome : $config->cidade,
                    "federal_unit" => is_object($config->cidade) ? $config->cidade->uf : $config->uf
                ]
            ]
        ]);

        // Atualiza o pedido
        $pedido->update([
            'transacao_id' => $payment->id,
            'status_pagamento' => $payment->status,
            'forma_pagamento' => 'Boleto',
            'status_detalhe' => $payment->status_detail,
            'valor_total' => $request->total_pag,
            'hash' => $pedido->hash ?? Str::random(20),
            'status' => 1,
            'link_boleto' => $payment->point_of_interaction->transaction_data->external_resource_url ?? null,
        ]);

        return redirect()->route('ecommerce.showBoleto', [
            'link' => $config->link,
            'pedidoId' => $pedido->id
        ]);

    } catch (\Exception $e) {
        \Log::error("Erro Boleto MercadoPago: " . $e->getMessage());
        return back()->with("mensagem_erro", "Erro ao gerar boleto.");
    }
}
// 🔹 Cria pagamento PIX e redireciona para a view específica
public function paymentPix(Request $request)
{
    $pedido = PedidoEcommerce::findOrFail($request->carrinho_id);

    $config = ConfigEcommerce::where('empresa_id', $pedido->empresa_id)->first();
    if (!$config) {
        return back()->with("mensagem_erro", "Configuração do e-commerce não encontrada.");
    }

    MercadoPagoConfig::setAccessToken($config->mercadopago_access_token);
    $client = new PaymentClient();

    try {
        $payment = $client->create([
            "transaction_amount" => (float) $request->transactionAmount,
            "description" => "Pedido Ecommerce #" . $pedido->id,
            "payment_method_id" => "pix",
            "payer" => [
                "email" => $request->payerEmail,
                "first_name" => $request->payerFirstName,
                "last_name" => $request->payerLastName,
                "identification" => [
                    "type" => $request->docType,
                    "number" => preg_replace('/[^0-9]/', '', $request->docNumber)
                ]
            ]
        ]);

        // Atualiza dados do pedido
        $pedido->update([
            'transacao_id' => $payment->id,
            'status_pagamento' => $payment->status,
            'forma_pagamento' => 'Pix',
            'status_detalhe' => $payment->status_detail,
            'valor_total' => $request->total_pag,
            'hash' => $pedido->hash ?? \Str::random(20),
            'status' => 1,
            'qr_code' => $payment->point_of_interaction->transaction_data->qr_code ?? null,
            'qr_code_base64' => $payment->point_of_interaction->transaction_data->qr_code_base64 ?? null
        ]);

        // 🔹 Redireciona para a view específica pix.blade.php
        return redirect()->route('ecommerce.showPix', [
            'link' => $config->link,
            'pedidoId' => $pedido->id
        ]);

    } catch (\Exception $e) {
        \Log::error("Erro PIX MercadoPago: " . $e->getMessage());
        return back()->with("mensagem_erro", "Erro ao gerar PIX.");
    }
}

// 🔹 Mostra o PIX do pedido com dados de Cliente e Empresa
public function showPix($link, $pedidoId)
{
    // Carregamos o pedido com a relação 'cliente' (certifique-se que essa relação existe no Model PedidoEcommerce)
    // Se a relação não existir, você pode buscar manualmente como feito abaixo
    $pedido = PedidoEcommerce::with('cliente')->findOrFail($pedidoId);
//dd($pedido);
    if ($pedido->forma_pagamento !== 'Pix') {
        return redirect()->back()->with('mensagem_erro', 'Pedido não é PIX.');
    }

    $default = $this->getDadosDefault($link);

    // Buscamos os dados da empresa/configuração para exibir na tela
    $config = ConfigEcommerce::where('empresa_id', $pedido->empresa_id)->first();
   // dd($config);
    // Se você não tiver a relação 'cliente' no model, pode buscar assim:
    // $cliente = Cliente::find($pedido->cliente_id);

    return view($default['template'].'/pix')
        ->with('pedido_pix', $pedido)
        ->with('pedido', $pedido) // Dados gerais do pedido
        ->with('cliente', $pedido->cliente) // Dados do cliente (via relação)
        ->with('empresa', $config) // Dados da empresa (nome, cnpj, etc)
        ->with('default', $default)
        ->with('title', 'Pagamento PIX - Pedido #' . $pedido->id)
        ->with('link', $link)
        ->with('rota', url("/loja/{$link}/carrinho"));
}

// 🔹 Consulta status do pagamento via AJAX
public function consultaPagamento($link, $transacao_id)
{
    $pedido = PedidoEcommerce::where('transacao_id', $transacao_id)->firstOrFail();
    $config = ConfigEcommerce::where('empresa_id', $pedido->empresa_id)->firstOrFail();

    MercadoPagoConfig::setAccessToken($config->mercadopago_access_token);

    try {
        $client = new PaymentClient();
        $payStatus = $client->get($transacao_id);

        if ($payStatus->status != $pedido->status_pagamento) {
            $pedido->update([
                'status_pagamento' => $payStatus->status,
                'status' => ($payStatus->status == "approved") ? 2 : 1
            ]);
        }

        return response()->json(['status' => $payStatus->status]);
    } catch (\Exception $e) {
        \Log::error("Erro consulta PIX: " . $e->getMessage());
        return response()->json(['status' => 'erro'], 500);
    }
}
	private function getDadosDefault($link){

		$config = $this->getConfig($link);

		$categorias = CategoriaProdutoEcommerce::
		where('empresa_id', $config->empresa_id)
		->get();

		$produtoEcommerceHelper = new PedidoEcommerceHelper();
		$carrinho = $produtoEcommerceHelper->getCarrinho();
		$curtidas = $produtoEcommerceHelper->getProdutosCurtidos();

		$postBlogExists = PostBlogEcommerce::
		where('empresa_id', $config->empresa_id)
		->exists();
		$active = $this->getActive();
		return [
			'config' => $config,
			'template' => $config->tema_ecommerce,
			'categorias' => $categorias,
			'curtidas' => $curtidas,
			'carrinho' => $carrinho,
			'active' => $active,
			'postBlogExists' => $postBlogExists,
			'rota' => '/loja/' . strtolower($config->link)
		];
	}

	private function getConfig($link){
		$config = ConfigEcommerce::
		where('link', $link)
		->first();
		return $config;
	}


	private function getActive(){
		$uri = $_SERVER['REQUEST_URI'];
		$uri = explode("/", $uri);

		$active = "";
		if(isset($uri[3])){
			if($uri[3] == 'categorias') $active = 'categorias';
			elseif($uri[3] == '1') $active = 'categorias';
			elseif($uri[3] == '2') $active = 'categorias';
			// elseif($uri[3] == 'carrinho') $active = 'categorias';
			elseif($uri[3] == 'contato') $active = 'contato';
			elseif($uri[3] == 'blog') $active = 'blog';

			// echo $uri[3];
		}else{
			$active = "home";
		}

		return $active;
	}

private function sendMail($pedido)
{
    $config = ConfigEcommerce::where('empresa_id', $pedido->empresa_id)->first();

    if (env('MAIL_USERNAME') != "") {
        Mail::send('mail.pedido_finalizado', [
            'pedido' => $pedido,
            'config' => $config
        ], function ($m) use ($pedido, $config) {
            $nomeEmail = $config->nome;
            $m->from(env('MAIL_USERNAME'), $nomeEmail);
            $m->subject('Pedido realizado');
            $m->to($pedido->cliente->email);
        });
    }
}


	public function finalizaOrcamento(Request $request){
		$pedido = PedidoEcommerce::find($request->carrinho_id);
		$config = ConfigEcommerce::
		where('empresa_id', $pedido->empresa_id)
		->first();

		$pedido->observacao = $request->observacao ?? '';
		$pedido->hash = Str::random(20);
		$pedido->modelo_orcamento = 1;
		$pedido->save();

		$this->criaOrcamento($pedido);

		return redirect('/ecommercePay/finalizado/'.$pedido->hash);
	}


	private function criaOrcamento($pedido){

		$cliente = $this->salvarCliente($pedido);
		$natureza = Produto::firstNatureza($pedido->empresa_id);
		$total = $this->calcTotal($pedido);

		$dt = date("Y-m-d");
		$dataOrcamento = [
			'cliente_id' => $cliente->id,
			'usuario_id' => get_id_user(),
			'frete_id' => null,
			'valor_total' => $total,
			'forma_pagamento' => 'personalizado',
			'email_enviado' => 0,
			'natureza_id' => $natureza->id, 
			'estado' => 'NOVO',
			'observacao' => request()->observacao ?? "",
			'desconto' => 0,
			'transportadora_id' => null,
			'tipo_pagamento' => '99',
			'validade' => date( "Y-m-d", strtotime( "$dt +7 day" )),
			'venda_id' => 0, 
			'empresa_id' => $pedido->empresa_id,
			'acrescimo' => 0,
			'filial_id' => null,
			'vendedor_id' => null,
			'ecommerce' => 1
		];
		$orc = Orcamento::create($dataOrcamento);

		foreach($pedido->itens as $item){
			ItemOrcamento::create([
				'produto_id' => $item->produto->produto->id,
				'orcamento_id' => $orc->id,
				'quantidade' => $item->quantidade,
				'valor' => $item->produto->valor,
				'altura' => 0,
				'largura' => 0,
				'profundidade' => 0,
				'acrescimo_perca' => 0,
				'esquerda' => 0,
				'direita' => 0,
				'inferior' => 0,
				'superior' => 0

			]);

		}

	}

	private function calcTotal($pedido){
		$soma = 0;
		foreach($pedido->itens as $item){
			$quantidade = $item->quantidade;
			$valor = $item->produto->valor;
			$soma += $item->quantidade*$valor;
		}
		return $soma;
	}

	private function salvarCliente($pedido){

		$cliente = $pedido->cliente;
		$endereco = $pedido->endereco;

		$clienteExist = Cliente::
		where('cpf_cnpj', $cliente->cpf)
		->first();

		$cidade = Cidade::
		where('nome', $endereco->cidade)
		->first();

		if($clienteExist == null){
            //criar novo

			$dataCliente = [
				'razao_social' => "$cliente->nome $cliente->sobre_nome",
				'nome_fantasia' => "$cliente->nome $cliente->sobre_nome",
				'bairro' => $endereco->bairro,
				'numero' => $endereco->numero,
				'rua' => $endereco->rua,
				'cpf_cnpj' => $cliente->cpf,
				'telefone' => $cliente->telefone,
				'celular' => $cliente->telefone,
				'email' => $cliente->email,
				'cep' => $endereco->cep,
				'ie_rg' => $cliente->ie,
				'consumidor_final' => 1,
				'limite_venda' => 0,
				'cidade_id' => $cidade != null ? $cidade->id : 1, 
				'contribuinte' => 1,
				'rua_cobranca' => '',
				'numero_cobranca' => '',
				'bairro_cobranca' => '',
				'cep_cobranca' => '',
				'cidade_cobranca_id' => NULL,
				'empresa_id' => $pedido->empresa_id,
				'cod_pais' => 1058,
				'id_estrangeiro' => '',
				'grupo_id' => 0
			];

            // print_r($dataCliente);

			return Cliente::create($dataCliente);

		}else{
            //atualiza endereﾃｧo

			$clienteExist->rua = $endereco->rua;
			$clienteExist->numero = $endereco->numero;
			$clienteExist->bairro = $endereco->bairro;
			$clienteExist->cep = $endereco->cep;
			$clienteExist->cidade_id = $cidade != null ? $cidade->id : 1;

			$clienteExist->save();
			return $clienteExist;
		}

	}
}
