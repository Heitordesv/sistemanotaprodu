<?php

use App\Http\Controllers\CatalogoAtacadoController;
use Illuminate\Support\Facades\Route;

Route::get('/catalogo-atacado/{empresaId}', [CatalogoAtacadoController::class, 'index'])
    ->whereNumber('empresaId')
    ->name('catalogo.atacado');