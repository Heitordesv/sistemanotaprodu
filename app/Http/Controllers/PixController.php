<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\ConfigEcommerce;
use App\Models\Empresa;
use Carbon\Carbon;

class PixController extends Controller
{
    /**
     * Gera o QR Code PIX via Mercado Pago.
     */
    public function gerar(Request $request)
    {
        $valor = (float) $request->valor;

        if (!$valor || $valor <= 0) {
            return response()->json(['erro' => 'Valor inválido.'], 400);
        }

        // 🔹 Busca as configurações do Mercado Pago para a empresa
        $config = ConfigEcommerce::where('empresa_id', $request->empresa_id)->first();

        if (!$config || empty($config->mercadopago_access_token)) {
            return response()->json(['erro' => 'Token do Mercado Pago não configurado.'], 400);
        }

        // 🔹 Busca dados da empresa
        $empresa = Empresa::find($request->empresa_id);

        if (!$empresa) {
            return response()->json(['erro' => 'Empresa não encontrada.'], 404);
        }

        // 🔹 Monta os dados do pagamento PIX
        $dados = [
            "transaction_amount" => $valor,
            "description" => "Pagamento PIX - {$empresa->nome_fantasia}",
            "payment_method_id" => "pix",
            "payer" => [
                "email" => "sistema@gmail.com",
                "first_name" => "SISTEMA",
                "last_name" => "USUARIO",
                "identification" => [
                    "type" => "CPF",
                    "number" => "00000000191"
                ]
            ]
        ];

        $codigoKey = uniqid("pix_", true);

        // 🔹 Requisição ao Mercado Pago
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
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $resultado = json_decode($response);
        curl_close($curl);

        if (isset($resultado->point_of_interaction->transaction_data) && $httpCode === 201) {
            $pix = $resultado->point_of_interaction->transaction_data;

            return response()->json([
                'empresa' => [
                    'id' => $empresa->id,
                    'razao_social' => $empresa->razao_social ?? '',
                    'nome_fantasia' => $empresa->nome_fantasia ?? '',
                    'cnpj' => $empresa->cnpj ?? '',
                ],
                'valor' => number_format($valor, 2, ',', '.'),
                'descricao' => $dados['description'],
                'qr_code_base64' => $pix->qr_code_base64 ?? null,
                'chave_pix' => $pix->qr_code ?? null,
                'id_pagamento' => $resultado->id ?? null,
                'status' => 'gerado'
            ]);
        }

        return response()->json([
            'erro' => 'Falha ao gerar PIX.',
            'detalhes' => $response
        ], 500);
    }

public function statusPagamento($idPagamento, Request $request)
{
    try {
        $empresaId = $request->query('empresa_id');

        if (!$empresaId) {
            return response()->json(['erro' => 'empresa_id não informado'], 400);
        }

        $config = ConfigEcommerce::where('empresa_id', $empresaId)->first();

        if (!$config || !$config->mercadopago_access_token) {
            return response()->json(['erro' => 'Configuração do Mercado Pago não encontrada para esta empresa.'], 404);
        }

        // 🔹 Requisição para o Mercado Pago
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.mercadopago.com/v1/payments/{$idPagamento}",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $config->mercadopago_access_token,
                'Content-Type: application/json'
            ],
        ]);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        $resultado = json_decode($response);

        // 🔹 Se não houver resposta válida
        if ($httpCode !== 200 || !$resultado || !isset($resultado->status)) {
            return response()->json([
                'erro' => 'Erro ao consultar status do pagamento.',
                'codigo_http' => $httpCode,
                'retorno' => $resultado
            ], 500);
        }

        // 🔹 Extrai dados com segurança
        $status = $resultado->status ?? 'desconhecido';
        $id = $resultado->id ?? $idPagamento;
        $valor = $resultado->transaction_amount ?? 0;
        $data_pagamento = $resultado->date_approved ?? null;

        // 🔹 Retorno para status aprovado
        if ($status === 'approved') {
            return response()->json([
                'status' => 'pago',
                'id_pagamento' => $id,
                'valor' => $valor,
                'data_pagamento' => $data_pagamento
            ]);
        }

        // 🔹 Retorno para outros status
        return response()->json([
            'status' => $status,
            'id_pagamento' => $id,
            'valor' => $valor
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'erro' => 'Ocorreu um erro interno.',
            'mensagem' => $e->getMessage()
        ], 500);
    }
}

}


