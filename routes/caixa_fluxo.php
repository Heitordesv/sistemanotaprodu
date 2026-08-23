<?php

use App\Http\Controllers\CaixaFechamentoController;
use App\Http\Controllers\FrontBoxController;
use App\Http\Controllers\FrontBoxResumoController;
use App\Http\Controllers\SangriaCaixaController;
use App\Http\Controllers\SuprimentoCaixaController;
use App\Http\Controllers\VendaSeguraController;
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

// A tela principal do PDV usa a mesma consolidação por abertura do fechamento.
Route::middleware($financeMiddlewares)
    ->get('/frenteCaixa', [FrontBoxResumoController::class, 'index'])
    ->name('frenteCaixa.index');

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
// VendaSeguraController valida todas as referências multi-tenant antes de
// delegar ao controller legado.
Route::middleware(array_merge($financeMiddlewares, ['caixaMovimento:venda-opcional']))
    ->post('/vendas', [VendaSeguraController::class, 'store'])
    ->name('vendas.store');

// Edição não pode trocar a abertura histórica da venda. O controller seguro
// remove abertura_caixa_id do PUT/PATCH e valida venda/natureza/produtos e
// demais referências contra a empresa autenticada.
Route::middleware($financeMiddlewares)
    ->match(['put', 'patch'], '/vendas/{id}', [VendaSeguraController::class, 'update'])
    ->name('vendas.update');
