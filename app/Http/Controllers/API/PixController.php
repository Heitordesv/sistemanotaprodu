<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\VendaCaixa;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\MercadoPagoConfig;
use App\Models\ConfigEcommerce;

class PixController extends Controller
{
    public function status($id)
    {
        $venda = VendaCaixa::findOrFail($id);
        $config = ConfigEcommerce::where('empresa_id', $venda->empresa_id)->first();

        if (!$config || !$config->mp_access_token) {
            return response()->json(['error' => 'Token não configurado'], 400);
        }

        MercadoPagoConfig::setAccessToken($config->mp_access_token);
        $client = new PaymentClient();

        $payment = $client->get($venda->chave ?? $venda->id);

        return response()->json([
            'status' => $payment->status,
            'detail' => $payment->status_detail,
        ]);
    }
}
