<?php

use App\Http\Controllers\PdvDevolucaoController;
use Illuminate\Support\Facades\Route;

// Carregado por último e dentro do grupo web: mantém as URLs legadas, mas
// substitui exclusivamente os endpoints de devolução/cancelamento por versões
// com sessão, CSRF, validação de tenant, contrato e armazenamento.
$middlewares = [
    'verificaEmpresa',
    'validaAcesso',
    'verificaContratoAssinado',
    'limiteArmazenamento',
    'throttle:20,1',
];

// Usa exatamente o mesmo nome de parâmetro criado por Route::resource(). Isso
// substitui a assinatura dinâmica legada no RouteCollection; usar {id} criaria
// outra rota compatível com a mesma URL e deixaria FrontBoxController@destroy
// vencer a resolução.
Route::middleware($middlewares)
    ->delete('/frenteCaixa/{frenteCaixa}', [PdvDevolucaoController::class, 'devolver'])
    ->name('frenteCaixa.destroy');

// A URL permanece /api/nfce/cancelar para não quebrar o JavaScript atual,
// porém esta definição roda no grupo WEB e portanto possui sessão + CSRF.
Route::middleware($middlewares)
    ->post('/api/nfce/cancelar', [PdvDevolucaoController::class, 'cancelarNfce'])
    ->name('nfce.cancelar.seguro');
