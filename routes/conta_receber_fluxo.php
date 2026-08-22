<?php

use App\Http\Controllers\ContaReceberPagamentoController;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->prefix('contasReceber')->group(function () {
    Route::get('/{id}/pay', [ContaReceberPagamentoController::class, 'pay'])
        ->whereNumber('id')
        ->name('conta-receber.pay');

    Route::put('/{id}/payPut', [ContaReceberPagamentoController::class, 'payPut'])
        ->whereNumber('id')
        ->name('conta-receber.payPut');
});