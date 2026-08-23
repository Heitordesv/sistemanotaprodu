<?php

namespace App\Observers;

use App\Models\ContaReceber;
use Illuminate\Support\Facades\Log;

class ContaReceberMercadoPagoObserver
{
    public function updating(ContaReceber $conta): void
    {
        if (!$conta->isDirty('valor_recebido')) {
            return;
        }

        if (strtolower((string) $conta->mercadopago_status) !== 'approved') {
            return;
        }

        $valorIntegral = round((float) $conta->valor_integral, 2);
        $valorNovo = round((float) $conta->valor_recebido, 2);

        if ($valorIntegral < 0 || $valorNovo <= $valorIntegral + 0.009) {
            return;
        }

        $valorAnterior = round((float) ($conta->getOriginal('valor_recebido') ?? 0), 2);
        $excedente = round($valorNovo - $valorIntegral, 2);

        // O pagamento do provedor permanece integralmente registrado em
        // conta_receber_pagamentos para auditoria. Na conta, porém, aplicamos no
        // máximo o saldo devido para que valor_recebido nunca ultrapasse o título.
        $conta->valor_recebido = $valorIntegral;

        // Se a conta já estava integralmente quitada antes da aprovação tardia
        // do Mercado Pago, preserva a forma/data da baixa original. O metadata
        // mercadopago_* continua registrando a aprovação recebida do provedor.
        if ($valorAnterior >= $valorIntegral - 0.009) {
            $conta->tipo_pagamento = $conta->getOriginal('tipo_pagamento');
            $conta->data_recebimento = $conta->getOriginal('data_recebimento');
        }

        Log::warning('Pagamento Mercado Pago excedeu o saldo da conta a receber.', [
            'empresa_id' => (int) $conta->empresa_id,
            'conta_receber_id' => (int) $conta->id,
            'mercadopago_payment_id' => (string) ($conta->mercadopago_payment_id ?? ''),
            'valor_integral' => $valorIntegral,
            'valor_recebido_antes' => $valorAnterior,
            'valor_tentado' => $valorNovo,
            'valor_excedente_nao_aplicado' => $excedente,
        ]);
    }
}
