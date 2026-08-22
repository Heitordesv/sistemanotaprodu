<?php

namespace App\Listeners;

use App\Events\EcommercePaymentApproved;
use App\Models\CupomDescontoEcommerce;
use App\Models\CupomEcommerceUtilizado;

class ConsumeEcommerceCouponAfterPayment
{
    public function handle(EcommercePaymentApproved $event): void
    {
        $pedido = $event->pedido;
        $codigo = trim((string) $pedido->cupom_desconto);

        if ($codigo === '' || !$pedido->cliente_id) {
            return;
        }

        $cupom = CupomDescontoEcommerce::where('empresa_id', $pedido->empresa_id)
            ->where('codigo', $codigo)
            ->first();

        if (!$cupom) {
            return;
        }

        CupomEcommerceUtilizado::firstOrCreate(
            ['pedido_id' => $pedido->id],
            [
                'empresa_id' => $pedido->empresa_id,
                'cupom_id' => $cupom->id,
                'cliente_id' => $pedido->cliente_id,
            ]
        );
    }
}