<?php

use App\Http\Controllers\CaixaFechamentoController;
use Illuminate\Support\Facades\Route;

// Carregada depois de web.php para manter a URL/route name existente e substituir
// apenas o POST de fechamento legado por um fluxo transacional sem exigir vendas.
Route::middleware([
    'verificaEmpresa',
    'validaAcesso',
    'verificaContratoAssinado',
    'limiteArmazenamento',
])->post('/frenteCaixa/fechar', [CaixaFechamentoController::class, 'fechar'])
  ->name('frenteCaixa.fecharPost');
