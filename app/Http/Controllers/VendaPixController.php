<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\VendaCaixa;
use App\Models\ConfigEcommerce;
use App\Jobs\EnviarMensagemWhatsAppVenda;

class VendaPixController extends Controller
{
    /**
     * Gera o QR Code do PIX para uma venda existente
     */
    public function gerarPix(Request $request, $venda_id)
    {
        $vendaCaixa = VendaCaixa::findOrFail($venda_id);

        // Só gera PIX se for tipo_pagamento = 17
        if ($vendaCaixa->tipo_pagamento != '17') {
            return response()->json(['erro' => 'Pagamento não é PIX'], 400);
        }

        try {
            $config = ConfigEcommerce::where('empresa_id', $vendaCaixa->empresa_id)->first();
            if (!$config || empty($config->mercadopago_access_token)) {
                return response()->json(['erro' => 'Token Mercado Pago não configurado'], 400);
            }

            $cliente = $vendaCaixa->cliente ?? $vendaCaixa->empresa;
            $nome = trim($cliente->razao_social ?? $cliente->nome_fantasia ?? "Cliente");
            $primeiroNome = explode(' ', $nome)[0];
            $sobrenome = trim(str_replace($primeiroNome, '', $nome)) ?: " ";
            $cpfCnpj = preg_replace('/\D/', '', $cliente->cpf_cnpj ?? '00000000191');
            $identificacao = strlen($cpfCnpj) === 11
                ? ['type' => 'CPF', 'number' => $cpfCnpj]
                : ['type' => 'CNPJ', 'number' => $cpfCnpj];

            $dados = [
                "transaction_amount" => (float)$vendaCaixa->valor_total,
                "description" => "Venda código: {$vendaCaixa->id}",
                "external_reference" => (string)$vendaCaixa->id,
                "payment_method_id" => "pix",
                "notification_url" => url("/mercadopago/notification?id={$vendaCaixa->id}"),
                "payer" => [
                    "email" => $cliente->email ?? "cliente@exemplo.com",
                    "first_name" => $primeiroNome,
                    "last_name" => $sobrenome,
                    "identification" => $identificacao
                ]
            ];

            $codigoKey = uniqid("pix_{$vendaCaixa->id}_", true);

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

            if (!isset($resultado->point_of_interaction->transaction_data)) {
                return response()->json(['erro' => 'Erro ao gerar PIX', 'detalhes' => $resultado], 500);
            }

            $pix = $resultado->point_of_interaction->transaction_data;

            // Salva na venda
            $vendaCaixa->qr_code_base64 = $pix->qr_code_base64 ?? null;
            $vendaCaixa->qr_code = $pix->qr_code ?? null;
            $vendaCaixa->status_pix = 'pendente'; // inicializa como pendente
            $vendaCaixa->save();

            // Consultar status do PIX imediatamente ou via job/cron
            $this->verificarStatusPix($vendaCaixa->id, $config->mercadopago_access_token);

            return response()->json([
                'qr_code_base64' => $vendaCaixa->qr_code_base64,
                'pix_copia_cola' => $vendaCaixa->qr_code,
                'status_pix' => $vendaCaixa->status_pix
            ], 200);

        } catch (\Exception $e) {
            __saveLogError($e, $vendaCaixa->empresa_id);
            return response()->json(['erro' => $e->getMessage()], 500);
        }
    }

    /**
     * Verifica o status do PIX via Mercado Pago
     */
    private function verificarStatusPix($vendaId, $accessToken)
    {
        $venda = VendaCaixa::find($vendaId);
        if (!$venda) return;

        $externalReference = $venda->id;

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.mercadopago.com/v1/payments/search?external_reference={$externalReference}&payment_type=pix",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $accessToken,
                'Content-Type: application/json',
            ],
        ]);

        $response = curl_exec($curl);
        $resultado = json_decode($response, true);
        curl_close($curl);

        if (isset($resultado['results']) && count($resultado['results']) > 0) {
            $pagamento = $resultado['results'][0];
            if ($pagamento['status'] == 'approved') {
                $venda->status_pix = 'pago';
                $venda->save();

                // Opcional: enviar WhatsApp de confirmação
                $empresa = $venda->empresa;
                EnviarMensagemWhatsAppVenda::dispatch($venda, $empresa);
            }
        }
    }
}
