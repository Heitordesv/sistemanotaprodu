<?php

namespace App\Events;

use App\Models\PedidoEcommerce;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EcommercePaymentApproved
{
    use Dispatchable, SerializesModels;

    public function __construct(public PedidoEcommerce $pedido)
    {
    }
}