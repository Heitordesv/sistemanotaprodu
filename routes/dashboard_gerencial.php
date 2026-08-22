<?php

use App\Http\Controllers\DashboardGerencialController;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard-gerencial/resumo', [DashboardGerencialController::class, 'resumo'])
    ->name('dashboard-gerencial.resumo');
