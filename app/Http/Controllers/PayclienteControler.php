<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Empresa;
use App\Models\Plano;
use App\Models\ConfigNota;
use App\Models\Payment;
use App\Models\PlanoEmpresa;
use App\Models\RepresentanteEmpresa;
use App\Models\FinanceiroRepresentante;
use App\Models\Acessor;
use App\Models\ContaReceber;
use App\Models\CategoriaConta;
use App\Models\Cidade;
use App\Models\Cliente;
use App\Models\Funcionario;
use App\Models\GrupoCliente;
use App\Models\Pais;
use App\Models\ConfigEcommerce;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use MercadoPago\SDK;
use MercadoPago\Payment as MPayment;
use App\Jobs\EnviarConfirmaWhatsAppJobs;
use Carbon\Carbon;

class PayclienteControler extends Controller
{
    public function index()
    {
        $empresa = Empresa::find(request()->empresa_id);
        $planos = Plano::where('visivel', true)->get();

        $config = ConfigNota::where('empresa_id', request()->empresa_id)->first();
        if (!$config) {
            session()->flash("flash_erro", "Informe o emitente primeiramente");
            return redirect()->route('configNF.index');
        }

        $plano = $empresa->planoEmpresa;
        if (!$plano) {
            session()->flash("mensagem_erro", "Defina um plano!!");
            return redirect()->route('empresas.index');
        }

        return redirect()->route('payment.finish');
    }

    public function finish()
    {
        $empresa = Empresa::find(request()->empresa_id);
        $plano = $empresa->planoEmpresa;

        return view('payment.finish', compact('empresa', 'plano'));
    }

    // =====================
    // GERAR PIX
    // =====================
public function gerarPixMercadoPago($id)
{
    // 1. CARREGA O REGISTRO DO BANCO
    $conta = ContaReceber::findOrFail($id);

    // --------------------------------------------------------
    // *** REGRA DE VERIFICAÇÃO DE STATUS PAGO (RETORNO 200 OK) ***
    // --------------------------------------------------------
    if ($conta->status == 1) {
        // Se o status for 1 (Aprovado/Pago), retorna 200 OK com uma flag especial.
        return response()->json([
            'status_pago' => true, // Flag para o JS entender que está pago
            'message' => 'Pagamento já aprovado (Status: Aprovado). Não é necessário gerar um novo PIX.'
        ], 200); // Retorna 200 OK
    }
    // --------------------------------------------------------

    // 2. BUSCA CLIENTE E CONFIGURAÇÕES
    $cliente = Cliente::find($conta->cliente_id) ?? Empresa::find($conta->empresa_id_emp);

    if (!$cliente) {
        return response()->json(['erro' => 'Cliente ou empresa não encontrado.'], 400);
    }

$config = ConfigEcommerce::where('empresa_id', $conta->empresa_id)->first();    if (!$config || empty($config->mercadopago_access_token)) {
        return response()->json(['erro' => 'Token do Mercado Pago não configurado.'], 400);
    }

    // 3. PREPARAÇÃO DOS DADOS (Primeiro Nome e Sobrenome, CPF/CNPJ)
    $nome = trim($cliente->razao_social ?? $cliente->nome_fantasia ?? "Cliente");
    $primeiroNome = explode(' ', $nome)[0];
    $sobrenome = trim(str_replace($primeiroNome, '', $nome)) ?: " ";

    $cpfCnpj = preg_replace('/\D/', '', $cliente->cpf_cnpj ?? '00000000191');
    $identificacao = strlen($cpfCnpj) === 11
        ? ['type' => 'CPF', 'number' => $cpfCnpj]
        : ['type' => 'CNPJ', 'number' => $cpfCnpj];

    // 4. PREPARAÇÃO DO PAYLOAD PARA MP
    $dados = [
        "transaction_amount" => (float) $conta->valor_integral,
        "description" => "Pagamento referente ao título: {$conta->referencia}",
        "external_reference" => (string) $conta->id,
        "payment_method_id" => "pix",
        "notification_url" => url("/mercadopago/notification?id={$conta->id}"),
        "payer" => [
            "email" => $cliente->email ?? "cliente@exemplo.com",
            "first_name" => $primeiroNome,
            "last_name" => $sobrenome,
            "identification" => $identificacao
        ]
    ];

    $codigoKey = uniqid("pix_{$conta->id}_", true);

    // 5. CHAMADA CURL PARA O MERCADO PAGO
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => 'https://api.mercadopago.com/v1/payments',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode($dados),
        CURLOPT_HTTPHEADER => [
            'accept: application/json',
            'content-type: application/json',
            'X-Idempotency-Key: ' . $codigoKey,
            'Authorization: Bearer ' . $config->mercadopago_access_token
        ],
    ]);

    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE); // Captura o código HTTP
    $resultado = json_decode($response);
    curl_close($curl);

    // 6. TRATAMENTO DA RESPOSTA E RETORNO

    // Tratamento de Sucesso (Status 201 Created)
    if (isset($resultado->point_of_interaction->transaction_data) && $httpCode === 201) {
        $pix = $resultado->point_of_interaction->transaction_data;
        $qr_code_base64 = $pix->qr_code_base64 ?? null;
        $qr_code_text = $pix->qr_code ?? null; // Código Copia e Cola

        // Salva status, observação e a chave PIX no banco
        $conta->update([
            'status' => 0, // 0 = Pendente/Aguardando Pagamento
            'observacao' => "PIX Mercado Pago gerado.",
            'chave_pix' => $qr_code_text
        ]);

        return response()->json([
            'qr_code_base64' => $qr_code_base64,
            'qr_code_text' => $qr_code_text
        ]);
    }
    
    // 7. TRATAMENTO DE ERRO EXPLÍCITO DA API DO MP (Incluindo falha de "Dados Incompletos")
    
    // Loga o erro completo para debug no backend (somente você vê)
    \Log::error("Erro na API do Mercado Pago ao gerar PIX para conta ID {$id}. HTTP Code: {$httpCode}", (array) $resultado);

    $mensagemErro = 'Erro ao gerar PIX. ';

    if (isset($resultado->message)) {
        // Mensagem de erro do MP
        $mensagemErro = "Falha na geração do PIX: {$resultado->message}";
    } elseif ($httpCode !== 201) {
        // Erro HTTP genérico
        $mensagemErro = "Falha na comunicação com o Mercado Pago. Status HTTP: {$httpCode}.";
    }

    // Retorna o erro detalhado para o front-end
    return response()->json(['erro' => $mensagemErro], 400);
}
  public function linkPublico($id)
    {
        $conta = ContaReceber::find($id);
        if (!$conta) {
            abort(404, 'Título de pagamento não encontrado.');
        }

        return view('payment.public', ['id' => $id]);
    }

    // Rota: /payment/json/{id} (endpoint para JS)
    public function linkPublicoJson($id)
    {
        $conta = ContaReceber::find($id);
        if (!$conta) {
            return response()->json([
                'erro' => 'Conta não encontrada.',
                'conta' => null,
                'cliente' => null,
                'config' => null,
                'pix_chave' => null,
                'pix_qr_base64' => null
            ], 404);
        }

        $cliente = Cliente::find($conta->cliente_id);

        if (!$cliente) {
            return response()->json([
                'erro' => 'Cliente não encontrado.',
                'conta' => $conta,
                'cliente' => null,
                'config' => null,
                'pix_chave' => $conta->chave_pix ?? null,
                'pix_qr_base64' => $conta->pix_qr_base64 ?? null
            ], 404);
        }

        $config = ConfigEcommerce::where('empresa_id', $conta->empresa_id)->first();

        return response()->json([
            'erro' => null,
            'conta' => $conta,
            'cliente' => $cliente,
            'config' => $config,
            'pix_chave' => $conta->chave_pix ?? null,
            'pix_qr_base64' => $conta->pix_qr_base64 ?? null
        ]);
    }

 public function linkPublicoEmpresa($id)
{
    // Busca a conta
    $conta = ContaReceber::find($id);
    if (!$conta) {
        abort(404, 'Título de pagamento não encontrado.');
    }

    // Busca a empresa com base em empresa_id_emp da conta
    $empresa = Empresa::find($conta->empresa_id_emp);
    if (!$empresa) {
        abort(404, 'Empresa não encontrada para esta conta.');
    }

    // Retorna a view com os dados
    return view('payment.empresa', [
        'id' => $id,
        'empresa' => $empresa,
        'conta' => $conta
    ]);
}

public function linkPublicoEmpresaJson($id)
{
    $conta = ContaReceber::find($id);
    if (!$conta) {
        return response()->json([
            'erro' => 'Conta não encontrada.',
            'conta' => null,
            'empresa' => null,
        ], 404);
    }

    $empresa = Empresa::find($conta->empresa_id_emp);
    if (!$empresa) {
        return response()->json([
            'erro' => 'Empresa não encontrada.',
            'conta' => $conta,
            'empresa' => null
        ], 404);
    }

    return response()->json([
        'erro' => null,
        'conta' => $conta,
        'empresa' => $empresa
    ]);
}


   public function verificarStatus($id)
{
    $conta = ContaReceber::find($id);

    if (!$conta) {
        return response()->json(['erro' => 'Conta não encontrada'], 404);
    }

$config = ConfigEcommerce::where('empresa_id', $conta->empresa_id)->first();    if (!$config || empty($config->mercadopago_access_token)) {
        return response()->json(['erro' => 'Configuração Mercado Pago não encontrada'], 404);
    }

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => 'https://api.mercadopago.com/v1/payments/search?external_reference=' . $conta->id,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $config->mercadopago_access_token,
            'Content-Type: application/json'
        ],
    ]);

    $response = curl_exec($curl);
    curl_close($curl);

    $resultado = json_decode($response);

    if (!isset($resultado->results) || count($resultado->results) === 0) {
        return response()->json(['status' => 0]);
    }

    $pagamento = collect($resultado->results)->sortByDesc('date_created')->first();

    if ($pagamento->status === 'approved') {

        // Atualiza a conta se ainda não estiver como recebida
        if ($conta->status != 1) {
            $conta->update([
                'status' => 1,
                'valor_recebido' => $pagamento->transaction_amount,
                'data_recebimento' => now(),
                'observacao' => 'Pagamento aprovado via Mercado Pago'
            ]);
        }

        // Atualiza o plano da empresa
        $planoEmpresa = PlanoEmpresa::where('empresa_id', $conta->empresa_id_emp)->first();
        if ($planoEmpresa) {
            $novaExpiracao = Carbon::now()->addDays(30);
            $planoEmpresa->update([
                'expiracao' => $novaExpiracao,
                'mensagem_alerta' => 'Plano renovado automaticamente em ' . now()->format('d/m/Y H:i'),
            ]);
        }

        return response()->json([
            'status' => 1,
            'data_recebimento' => Carbon::parse($conta->data_recebimento)->format('d/m/Y H:i'),
            'nova_expiracao' => isset($novaExpiracao) ? $novaExpiracao->format('d/m/Y H:i') : null
        ]);
    }

    return response()->json(['status' => 0]);
    
    
}


    // =====================
    // GERAR BOLETO
    // =====================
    public function gerarBoletoEmpresaMercadoPago($id)
    {
        $conta = ContaReceber::findOrFail($id);
        $empresa = ConfigNota::where('empresa_id', $conta->empresa_id_emp)->first();

        if (!$empresa) {
            return response()->json(['erro' => 'Empresa não encontrada.'], 400);
        }

$config = ConfigEcommerce::where('empresa_id', $conta->empresa_id)->first();        if (!$config || empty($config->mercadopago_access_token)) {
            return response()->json(['erro' => 'Token do Mercado Pago não configurado.'], 400);
        }

        $nome = trim($empresa->nome_fantasia ?? $empresa->razao_social ?? "Empresa");
        $primeiroNome = explode(' ', $nome)[0];
        $sobrenome = trim(str_replace($primeiroNome, '', $nome)) ?: " ";
        $cnpj = preg_replace('/\D/', '', $empresa->cnpj ?? '00000000191');
        $cidade = $empresa->cidade_id ? Cidade::find($empresa->cidade_id) : null;

        $zip_code = preg_replace('/\D/', '', $empresa->cep ?? '00000000');
        $street_name = $empresa->logradouro ?? 'Endereço não informado';
        $street_number = $empresa->numero ?? 'S/N';
        $neighborhood = $empresa->bairro ?? 'Bairro';
        $city_name = $cidade->nome ?? 'Cidade';
        $federal_unit = $cidade->uf ?? 'SP';

        $dados = [
            "transaction_amount" => (float) $conta->valor_integral,
            "description" => "Pagamento referente ao título: {$conta->referencia}",
            "external_reference" => (string) $conta->id,
            "payment_method_id" => "bolbradesco",
            "notification_url" => url("/mercadopago/notification?id={$conta->id}"),
            "payer" => [
                "email" => $empresa->email ?? "empresa@exemplo.com",
                "first_name" => $primeiroNome,
                "last_name" => $sobrenome,
                "identification" => ["type" => "CNPJ", "number" => $cnpj],
                "address" => [
                    "zip_code" => $zip_code,
                    "street_name" => $street_name,
                    "street_number" => $street_number,
                    "neighborhood" => $neighborhood,
                    "city" => $city_name,
                    "federal_unit" => $federal_unit,
                ]
            ]
        ];

        $codigoKey = uniqid("boleto_{$conta->id}_", true);

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://api.mercadopago.com/v1/payments',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($dados),
            CURLOPT_HTTPHEADER => [
                'accept: application/json',
                'content-type: application/json',
                'X-Idempotency-Key: ' . $codigoKey,
                'Authorization: Bearer ' . $config->mercadopago_access_token
            ],
        ]);

        $response = curl_exec($curl);
        $resultado = json_decode($response);
        curl_close($curl);

        if (isset($resultado->transaction_details->external_resource_url)) {
            $conta->update([
                'status' => 'aguardando_pagamento',
                'boleto_link' => $resultado->transaction_details->external_resource_url,
                'observacao' => 'Boleto Mercado Pago gerado para empresa.'
            ]);

            return response()->json(['boleto_link' => $resultado->transaction_details->external_resource_url]);
        }

        return response()->json(['erro' => $resultado->message ?? 'Erro ao gerar boleto.'], 400);
    }

    // =====================
    // VERIFICAR PAGAMENTO BOLETO
    // =====================
    public function verificarPagamentoMercadoPago($id)
    {
        $conta = ContaReceber::findOrFail($id);

        if (empty($conta->boleto_link)) {
            return response()->json(['erro' => 'Boleto não gerado.'], 400);
        }

$config = ConfigEcommerce::where('empresa_id', $conta->empresa_id)->first();        if (!$config || empty($config->mercadopago_access_token)) {
            return response()->json(['erro' => 'Token do Mercado Pago não configurado.'], 400);
        }

        $payment_id = $conta->external_reference ?? null;
        if (!$payment_id) {
            return response()->json(['erro' => 'Payment ID não encontrado.'], 400);
        }

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://api.mercadopago.com/v1/payments/' . $payment_id,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => [
                'accept: application/json',
                'content-type: application/json',
                'Authorization: Bearer ' . $config->mercadopago_access_token
            ],
        ]);

        $response = curl_exec($curl);
        $resultado = json_decode($response);
        curl_close($curl);

        if (isset($resultado->status) && $resultado->status === 'approved') {
            if ($conta->status != 1) {
                $conta->update([
                    'status' => 1,
                    'valor_recebido' => $resultado->transaction_amount,
                    'data_recebimento' => now(),
                    'observacao' => 'Pagamento aprovado via Mercado Pago'
                ]);
            }

            return response()->json([
                'status' => 1,
                'data_recebimento' => $conta->data_recebimento->format('d/m/Y H:i')
            ]);
        }

        return response()->json(['status' => $resultado->status ?? 'pending']);
    }
}
