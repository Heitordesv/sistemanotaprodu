<?php

use App\Http\Controllers\ContaReceberMercadoPagoController;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::post('/conta_receber/mercadopago/pix/{id}', [ContaReceberMercadoPagoController::class, 'pix'])
        ->whereNumber('id')
        ->name('conta-receber.mp.pix');

    Route::post('/conta_receber/mercadopago/boleto/{id}', [ContaReceberMercadoPagoController::class, 'boleto'])
        ->whereNumber('id')
        ->name('conta-receber.mp.boleto');

    Route::post('/conta_receber/mercadopago/cartao/{id}', [ContaReceberMercadoPagoController::class, 'cartao'])
        ->whereNumber('id')
        ->name('conta-receber.mp.cartao');

    Route::post('/conta_receber/mercadopago/checkout/{id}', [ContaReceberMercadoPagoController::class, 'checkout'])
        ->whereNumber('id')
        ->name('conta-receber.mp.checkout');

    Route::get('/conta_receber/mercadopago/status/{id}', [ContaReceberMercadoPagoController::class, 'status'])
        ->whereNumber('id')
        ->name('conta-receber.mp.status');

    // Compatibilidade com URLs legadas da tela de Contas a Receber.
    Route::post('/conta_receber/gerar-pix/{id}', [ContaReceberMercadoPagoController::class, 'pix'])->whereNumber('id');
    Route::post('/conta_receber/gerar-boleto-cliente/{id}', [ContaReceberMercadoPagoController::class, 'boleto'])->whereNumber('id');
    Route::post('/conta_receber/gerar-boleto-empresa/{id}', [ContaReceberMercadoPagoController::class, 'boleto'])->whereNumber('id');
    Route::get('/conta_receber/status/{id}', [ContaReceberMercadoPagoController::class, 'status'])->whereNumber('id');
});

Route::get('/pagamento/conta-receber/{id}/{token}', [ContaReceberMercadoPagoController::class, 'retorno'])
    ->whereNumber('id')
    ->name('conta-receber.mp.retorno');

Route::post('/webhooks/mercadopago/contas-receber/{configId}', [ContaReceberMercadoPagoController::class, 'webhook'])
    ->whereNumber('configId')
    ->name('conta-receber.mp.webhook');