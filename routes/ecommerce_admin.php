<?php

use App\Http\Controllers\PedidoEcommerceSecurityController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Administração da Loja Online
|--------------------------------------------------------------------------
| Rotas internas usadas pelo lojista para acompanhar e operar pedidos.
| Mantidas separadas das rotas públicas da vitrine/checkout.
*/

Route::middleware('web')->group(function () {
    Route::get('/pedidosEcommerce', [PedidoEcommerceSecurityController::class, 'index'])
        ->name('pedidosEcommerce.index');

    Route::get('/pedidosEcommerce/{id}', [PedidoEcommerceSecurityController::class, 'show'])
        ->whereNumber('id')
        ->name('pedidosEcommerce.show');

    Route::get('/pedidosEcommerce/alterarStatus/{id}/{status}/{tipo}', [PedidoEcommerceSecurityController::class, 'alterarStatus'])
        ->whereNumber('id')
        ->name('pedidosEcommerce.alterarStatus');

    Route::get('/pedidosEcommerce/danfe/{id}', [PedidoEcommerceSecurityController::class, 'danfeSimulada'])
        ->whereNumber('id')
        ->name('pedidosEcommerce.danfe');

    Route::get('/pedidosEcommerce/declaracao/{id}', [PedidoEcommerceSecurityController::class, 'declaracaoConteudo'])
        ->whereNumber('id')
        ->name('pedidosEcommerce.declaracao');

    Route::get('/pedidosEcommerce/etiqueta/{id}', [PedidoEcommerceSecurityController::class, 'etiqueta'])
        ->whereNumber('id')
        ->name('pedidosEcommerce.etiqueta');
});