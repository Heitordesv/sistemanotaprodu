<?php

use App\Http\Controllers\ApiBrasilController;
use App\Http\Controllers\EvolutionController;
use App\Http\Controllers\EvolutionMessageController;
use App\Http\Controllers\SistemaAiController;
use App\Http\Controllers\WhatsAppAiMessageController;
use Illuminate\Support\Facades\Route;

// Compatibilidade: qualquer acesso antigo a /evolution retorna para o caminho oficial /dispositivos.
Route::prefix('evolution')->group(function () {
    Route::get('/', [EvolutionController::class, 'index'])->name('evolution.index');
    Route::post('/config', [EvolutionController::class, 'save'])->name('evolution.save');
    Route::post('/agent', [EvolutionController::class, 'saveAgent'])->name('evolution.agent.save');
    Route::post('/instance/create', [EvolutionController::class, 'createInstance'])->name('evolution.instance.create');
    Route::get('/instance/connect', [EvolutionController::class, 'connect'])->name('evolution.instance.connect');
    Route::get('/instance/status', [EvolutionController::class, 'status'])->name('evolution.instance.status');
    Route::post('/instance/webhook', [EvolutionController::class, 'configureWebhook'])->name('evolution.instance.webhook');
});

// Pesquisa IA interna: apenas orientação sobre módulos, funcionalidades e rotas do sistema.
Route::get('/pesquisa-ia', [SistemaAiController::class, 'index'])
    ->name('sistema-ia.index');
Route::post('/pesquisa-ia', [SistemaAiController::class, 'pesquisar'])
    ->name('sistema-ia.pesquisar');

// Configuração da Evolution no caminho já usado pelo sistema.
Route::get('/dispositivos/status', [ApiBrasilController::class, 'status'])
    ->name('dispositivos.status');
Route::post('/dispositivos/webhook', [ApiBrasilController::class, 'webhook'])
    ->name('dispositivos.webhook');
Route::get('/dispositivos/mensagens-ia', [WhatsAppAiMessageController::class, 'index'])
    ->name('dispositivos.mensagens-ia');
Route::get('/dispositivos/google/connect', [ApiBrasilController::class, 'googleConnect'])
    ->name('dispositivos.google.connect');
Route::get('/dispositivos/google/callback', [ApiBrasilController::class, 'googleCallback'])
    ->name('dispositivos.google.callback');
Route::post('/dispositivos/google/disconnect', [ApiBrasilController::class, 'googleDisconnect'])
    ->name('dispositivos.google.disconnect');

// Botões manuais de cobrança e Ordem de Serviço.
Route::post('/conta-receber/{id}/cobrar-whatsapp', [EvolutionMessageController::class, 'cobrar'])
    ->name('conta-receber.cobrar-whatsapp');
Route::get('/ordem-servico/{id}/whatsapp/preview', [EvolutionMessageController::class, 'previewOs'])
    ->name('ordem-servico.whatsapp.preview');
Route::post('/ordem-servico/{id}/whatsapp', [EvolutionMessageController::class, 'mensagemOs'])
    ->name('ordem-servico.whatsapp.enviar');

// Webhook público da Evolution: segredo enviado em header, não na URL.
Route::post('/api/evolution/webhook/{empresa}', [EvolutionController::class, 'webhook'])
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class])
    ->name('evolution.webhook');