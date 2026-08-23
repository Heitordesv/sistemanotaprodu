<?php

use App\Http\Controllers\ContaReceberPagamentoController;
use Illuminate\Support\Facades\Route;

$middlewaresFinanceiros = [
    'verificaEmpresa',
    'validaAcesso',
    'verificaContratoAssinado',
    'limiteArmazenamento',
];

Route::middleware($middlewaresFinanceiros)
    ->prefix('contasReceber')
    ->group(function () {
        Route::get('/{id}/pay', [ContaReceberPagamentoController::class, 'pay'])
            ->whereNumber('id')
            ->name('conta-receber.pay');

        Route::put('/{id}/payPut', [ContaReceberPagamentoController::class, 'payPut'])
            ->whereNumber('id')
            ->name('conta-receber.payPut');
    });

// Sobrescreve a rota legada carregada em web.php. A URL é preservada para
// não quebrar a tela atual, mas o processamento passa pelo serviço transacional
// e recebe a mesma proteção aplicada ao fechamento financeiro do caixa.
Route::middleware($middlewaresFinanceiros)
    ->post('/receber-massa', [ContaReceberPagamentoController::class, 'receberMassa'])
    ->name('conta-receber.receber.massa');
