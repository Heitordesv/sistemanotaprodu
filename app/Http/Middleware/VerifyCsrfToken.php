<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * URIs que recebem notifica«®«Øes diretamente de servi«®os externos.
     * Esses endpoints n«ªo possuem token CSRF da sess«ªo Laravel e devem
     * validar a origem da notifica«®«ªo no respectivo controller.
     *
     * @var array<int, string>
     */
    protected $except = [
        'webhooks/mercadopago/ecommerce/*',
        'mercadopago/notification',
        'mercadopago/notification/plano/*',
    ];
}