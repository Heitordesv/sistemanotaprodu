<?php

use App\Http\Controllers\OperacionalSeguroController;
use Illuminate\Support\Facades\Route;

// Neutraliza as rotas GET legadas carregadas anteriormente em web.php.
// Operações que alteram estado nunca devem ser disparadas por GET, pois GET não
// recebe proteção CSRF e pode ser acionado por terceiros no navegador do admin.
Route::get('/limpar-cache', static function () {
    return response()->json([
        'message' => 'Método não permitido. Use o endpoint administrativo autenticado.',
    ], 405);
});

Route::get('/enviar-cobranca', static function () {
    return response()->json([
        'message' => 'Método não permitido. Use o endpoint administrativo autenticado.',
    ], 405);
});

$middlewares = [
    'verificaEmpresa',
    'validaAcesso',
    'verificaContratoAssinado',
    'limiteArmazenamento',
];

// POST passa pelo grupo web (CSRF) e o controller exige adm/super da sessão.
Route::middleware($middlewares)
    ->post('/limpar-cache', [OperacionalSeguroController::class, 'limparCache'])
    ->name('operacional.limpar-cache');

Route::middleware($middlewares)
    ->post('/enviar-cobranca', [OperacionalSeguroController::class, 'enviarCobranca'])
    ->name('operacional.enviar-cobranca');
