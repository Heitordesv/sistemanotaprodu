<?php

use App\Http\Controllers\CaixaFechamentoController;
use App\Http\Controllers\FrontBoxController;
use App\Http\Controllers\SangriaCaixaController;
use App\Http\Controllers\SuprimentoCaixaController;
use App\Http\Controllers\VendaController;
use Illuminate\Support\Facades\Route;

// Este arquivo é carregado depois de web.php. Mantemos URLs e nomes legados,
// sobrescrevendo somente endpoints financeiros que precisam disputar o mesmo
// lock da AberturaCaixa usado pelo fechamento.
$financeMiddlewares = [
    'verificaEmpresa',
    'validaAcesso',
    'verificaContratoAssinado',
    'limiteArmazenamento',
];

Route::middleware($financeMiddlewares)
    ->post('/frenteCaixa/fechar', [CaixaFechamentoController::class, 'fechar'])
    ->name('frenteCaixa.fecharPost');

// PDV: venda só pode ser persistida enquanto a abertura atual permanece
// bloqueada. Se o fechamento vencer primeiro, a venda é rejeitada.
Route::middleware(array_merge($financeMiddlewares, ['caixaMovimento:obrigatorio']))
    ->post('/frenteCaixa', [FrontBoxController::class, 'store'])
    ->name('frenteCaixa.store');

Route::middleware(array_merge($financeMiddlewares, ['caixaMovimento:obrigatorio']))
    ->post('/sangriaCaixa', [SangriaCaixaController::class, 'store'])
    ->name('sangriaCaixa.store');

Route::middleware(array_merge($financeMiddlewares, ['caixaMovimento:obrigatorio']))
    ->post('/suprimentoCaixa', [SuprimentoCaixaController::class, 'store'])
    ->name('suprimentoCaixa.store');

// Venda/NFe compartilha endpoint com orçamento. Orçamento não movimenta caixa.
// NFe pode existir fora de uma sessão; quando há caixa aberto, ele é bloqueado
// até a gravação terminar para que o fechamento nunca consolide no meio dela.
Route::middleware(array_merge($financeMiddlewares, ['caixaMovimento:venda-opcional']))
    ->post('/vendas', [VendaController::class, 'store'])
    ->name('vendas.store');
