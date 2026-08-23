<?php

use App\Http\Controllers\CaixaFechamentoController;
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
// bloqueada. O controller endurecido também valida produtos/cliente/filial
// contra a empresa autenticada antes de qualquer baixa de estoque.
Route::middleware(array_merge($financeMiddlewares, ['caixaMovimento:obrigatorio']))
    ->post('/frenteCaixa', [FrontBoxResumoController::class, 'store'])
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

// O resource legado registra PUT/PATCH /vendas/{venda}. Usar exatamente o
// mesmo padrão aqui faz esta rota, carregada depois de web.php, substituir a
// definição insegura em vez de criar uma segunda rota dinâmica concorrente.
// O vínculo abertura_caixa_id permanece imutável no controller seguro.
Route::middleware($financeMiddlewares)
    ->match(['put', 'patch'], '/vendas/{venda}', [VendaSeguraController::class, 'update'])
    ->name('vendas.update');
