<?php
require __DIR__.'/vendor/autoload.php';

use MercadoPago\SDK;
use MercadoPago\Payment;

SDK::setAccessToken('SEU_ACCESS_TOKEN_AQUI');

echo "SDK carregada com sucesso\n";
