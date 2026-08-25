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

// Senha de autorização não pode trafegar na query string. O GET legado é neutralizado
// e a validação passa a aceitar somente POST + CSRF, com limitação de tentativas.
Route::get('/configNF/verificaSenha', static function () {
    return response()->json(['message' => 'Método não permitido.'], 405);
});

Route::middleware(array_merge($middlewares, ['throttle:10,1']))
    ->post('/configNF/verificaSenha', 'ConfigNotaController@verificaSenha')
    ->name('configNF.verificaSenha.post');

// Certificado A1 contém chave privada e não deve ser distribuído pelo painel do contador.
// O endpoint legado permanece bloqueado mesmo que arquivos antigos ainda existam no servidor.
Route::get('/contador/download-certificado/{id}', static function () {
    abort(403, 'Download de certificado digital não permitido.');
});
