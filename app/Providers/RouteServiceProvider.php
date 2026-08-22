<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Rota padrão após autenticação.
     */
    public const HOME = '/home';

    /**
     * Namespace padrão dos controllers.
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * Inicializa as rotas da aplicação.
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {

            /*
            |--------------------------------------------------------------------------
            | API
            |--------------------------------------------------------------------------
            */
            Route::middleware('api')
                ->prefix('api')
                ->namespace($this->namespace)
                ->group(base_path('routes/api.php'));


            /*
            |--------------------------------------------------------------------------
            | Catálogo Atacado
            |--------------------------------------------------------------------------
            */
            $this->loadWebRoutes('routes/catalogo_atacado.php');


            /*
            |--------------------------------------------------------------------------
            | Rotas principais
            |--------------------------------------------------------------------------
            */
            $this->loadWebRoutes('routes/web.php');


            /*
            |--------------------------------------------------------------------------
            | Evolution API / WhatsApp
            |--------------------------------------------------------------------------
            */
            $this->loadWebRoutes('routes/evolution.php');


            /*
            |--------------------------------------------------------------------------
            | Mercado Pago - Contas a Receber
            |--------------------------------------------------------------------------
            |
            | Carregado após web.php para permitir substituir endpoints
            | financeiros antigos sem quebrar URLs existentes.
            |
            */
            $this->loadWebRoutes('routes/conta_receber_mercadopago.php');


            /*
            |--------------------------------------------------------------------------
            | Fluxo Financeiro - Contas a Receber
            |--------------------------------------------------------------------------
            */
            $this->loadWebRoutes('routes/conta_receber_fluxo.php');


            /*
            |--------------------------------------------------------------------------
            | Fluxo Financeiro - Fechamento de Caixa
            |--------------------------------------------------------------------------
            |
            | Carregado após web.php para manter a URL legada e substituir apenas
            | o fechamento por um fluxo transacional que aceita caixa sem vendas.
            |
            */
            $this->loadWebRoutes('routes/caixa_fluxo.php');


            /*
            |--------------------------------------------------------------------------
            | Segurança E-commerce
            |--------------------------------------------------------------------------
            */
            $this->loadWebRoutes('routes/ecommerce_security.php');
        });
    }

    /**
     * Carrega um arquivo de rotas WEB somente se ele existir.
     *
     * Isso evita derrubar todo o Laravel caso um arquivo de rota
     * seja removido, renomeado ou ainda não tenha sido publicado
     * no servidor.
     */
    protected function loadWebRoutes(string $routeFile): void
    {
        $path = base_path($routeFile);

        if (!file_exists($path)) {
            return;
        }

        Route::middleware('web')
            ->namespace($this->namespace)
            ->group($path);
    }

    /**
     * Rate limiting da API.
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)
                ->by(
                    $request->user()?->id
                    ?: $request->ip()
                );
        });
    }
}