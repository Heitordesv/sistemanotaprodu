<?php

namespace App\Listeners;

use App\Events\EcommercePaymentApproved;
use App\Services\EcommerceStockService;

class ConsumeEcommerceStockAfterPayment
{
    public function __construct(private EcommerceStockService $stockService)
    {
    }

    public function handle(EcommercePaymentApproved $event): void
    {
        $this->stockService->consumirPedido($event->pedido);
    }
}