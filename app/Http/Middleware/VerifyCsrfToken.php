<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * URIs que recebem notificações diretamente de serviços externos.
     * Esses endpoints não possuem token CSRF da sessão Laravel e devem
     * validar a origem da notificação no respectivo controller.
     *
     * @var array<int, string>
     */
    protected $except = [
        'webhooks/mercadopago/ecommerce/*',
        'webhooks/mercadopago/contas-receber/*',
        'mercadopago/notification',
        'mercadopago/notification/plano/*',
    ];
}
