<?php

use Illuminate\Support\Facades\Route;

$middlewares = [
    'verificaEmpresa',
    'validaAcesso',
    'verificaContratoAssinado',
    'limiteArmazenamento',
];

// A sincronização altera segredo/configuração e não pode ser acionada por GET.
Route::get('/configNF/certificados', static function () {
    return response()->json(['message' => 'Método não permitido.'], 405);
});

Route::middleware($middlewares)
    ->post('/configNF/certificados', 'ConfigNotaController@certificadosFresh')
    ->name('configNF.certificados.post');

// Os GETs legados de remoção continuam abrindo uma confirmação, sem alterar estado.
// A mutação efetiva ocorre somente via POST sob o middleware web/CSRF.
Route::middleware($middlewares)
    ->post('/configNF/deleteCertificado', 'ConfigNotaController@deleteCertificado')
    ->name('configNF.deleteCertificado.post');

Route::middleware($middlewares)
    ->post('/configNF/removeSenha/{id}', 'ConfigNotaController@removeSenha')
    ->name('configNF.removeSenha.post');
