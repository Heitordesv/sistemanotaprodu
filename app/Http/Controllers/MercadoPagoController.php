<?php

namespace App\Http\Controllers;

use App\Models\ConfigEcommerce;
use App\Models\ContaReceber;
use App\Models\Payment;
use App\Models\Plano;
use App\Models\PlanoEmpresa;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class MercadoPagoController extends Controller
{
    public function notification(Request $request)
    {
        $type = (string) (
            $request->input('type')
            ?: $request->input('topic')
            ?: $request->query('type')
            ?: $request->query('topic')
            ?: ''
        );

        $resourceId = (string) (
            data_get($request->all(), 'data.id')
            ?: $request->query('data.id')
            ?: $request->query('data_id')
            ?: $request->input('id')
            ?: ''
        );

        try {
            $normalizedType = match ($type) {
                'preapproval' => 'subscription_preapproval',
                'authorized_payment' => 'subscription_authorized_payment',
                default => $type,
            };

            if ($resourceId !== '' && $normalizedType === 'subscription_preapproval') {
                $this->syncSubscription($resourceId);
                return response()->json(['success' => true]);
            }

            if ($resourceId !== '' && $normalizedType === 'subscription_authorized_payment') {
                $this->processAuthorizedPayment($resourceId);
                return response()->json(['success' => true]);
            }

            if ($resourceId !== '' && $normalizedType === 'payment') {
                if ($this->processSubscriptionPayment($resourceId)) {
                    return response()->json(['success' => true]);
                }
            }

            $contaId = (int) $request->query('id', 0);
            if ($contaId > 0) {
                $this->processLegacyContaReceber($contaId, $resourceId ?: null);
            }

            return response()->json(['success' => true]);
        } catch (Throwable $e) {
            Log::error('Erro webhook Mercado Pago.', [
                'type' => $type,
                'resource_id' => $resourceId,
                'erro' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'webhook_processing_failed'], 500);
        }
    }

    private function syncSubscription(string $preapprovalId): void
    {
        $config = $this->adminConfig();
        $response = $this->client($config)
            ->get('https://api.mercadopago.com/preapproval/' . urlencode($preapprovalId));

        if (!$response->successful()) {
            throw new RuntimeException('N«ªo foi poss«¿vel consultar a assinatura no Mercado Pago.');
        }

        $data = $response->json();
        $payment = Payment::where('forma_pagamento', 'cartao')
            ->where('transacao_id', $preapprovalId)
            ->first();

        if (!$payment && preg_match('/saas_card_payment_(\d+)/', (string) ($data['external_reference'] ?? ''), $matches)) {
            $payment = Payment::find((int) $matches[1]);
        }

        if (!$payment) {
            return;
        }

        $payment->update([
            'transacao_id' => $preapprovalId,
            'status' => $data['status'] ?? $payment->status,
        ]);

        $planoEmpresa = PlanoEmpresa::find($payment->plano_id);
        if ($planoEmpresa && in_array(($data['status'] ?? null), ['canceled', 'cancelled', 'paused'], true)) {
            $planoEmpresa->update([
                'mensagem_alerta' => 'Assinatura ' . ($data['status'] ?? 'atualizada') . '. O acesso j«¡ pago permanece at«± o vencimento atual.',
            ]);
        }
    }

    private function processAuthorizedPayment(string $authorizedPaymentId): void
    {
        $config = $this->adminConfig();
        $response = $this->client($config)
            ->get('https://api.mercadopago.com/authorized_payments/' . urlencode($authorizedPaymentId));

        if (!$response->successful()) {
            throw new RuntimeException('N«ªo foi poss«¿vel consultar a fatura recorrente no Mercado Pago.');
        }

        $this->applyInvoice($response->json(), $config);
    }

    private function processSubscriptionPayment(string $mpPaymentId): bool
    {
        $config = $this->adminConfig();
        $response = $this->client($config)
            ->get('https://api.mercadopago.com/authorized_payments/search', [
                'payment_id' => $mpPaymentId,
                'limit' => 20,
            ]);

        if (!$response->successful()) {
            return false;
        }

        $results = $response->json('results', []);
        if (!$results) {
            return false;
        }

        foreach ($results as $invoice) {
            $this->applyInvoice($invoice, $config);
        }

        return true;
    }

    private function applyInvoice(array $invoice, ConfigEcommerce $config): void
    {
        $preapprovalId = (string) ($invoice['preapproval_id'] ?? '');
        $mpPaymentId = (string) data_get($invoice, 'payment.id', '');
        $status = (string) data_get($invoice, 'payment.status', 'pending');

        if ($preapprovalId === '' || $mpPaymentId === '') {
            return;
        }

        $subscription = Payment::where('forma_pagamento', 'cartao')
            ->where('transacao_id', $preapprovalId)
            ->first();

        if (!$subscription) {
            return;
        }

        $targetPlanId = $this->targetPlanId($subscription);
        if (!$targetPlanId) {
            return;
        }

        $recurring = Payment::where('empresa_id', $subscription->empresa_id)
            ->where('plano_id', $subscription->plano_id)
            ->where('forma_pagamento', 'rec_card')
            ->where('transacao_id', $mpPaymentId)
            ->first();

        if (!$recurring) {
            $recurring = Payment::create([
                'empresa_id' => $subscription->empresa_id,
                'plano_id' => $subscription->plano_id,
                'valor' => (float) ($invoice['transaction_amount'] ?? $subscription->valor),
                'transacao_id' => $mpPaymentId,
                'forma_pagamento' => 'rec_card',
                'status' => $status,
                'status_detalhe' => $this->detail($targetPlanId, false, data_get($invoice, 'payment.status_detail', 'recorrente')),
                'descricao' => Str::limit('SAAS recorrente | assinatura:' . $preapprovalId, 200, ''),
                'link_boleto' => '',
                'qr_code_base64' => '',
                'qr_code' => '',
            ]);
        }

        if ($status !== 'approved' || $this->wasApplied($recurring)) {
            $recurring->update([
                'status' => $status,
                'status_detalhe' => $this->detail($targetPlanId, $this->wasApplied($recurring), data_get($invoice, 'payment.status_detail', $status)),
            ]);
            return;
        }

        $paymentResponse = $this->client($config)
            ->get('https://api.mercadopago.com/v1/payments/' . urlencode($mpPaymentId));

        $mpPayment = $paymentResponse->successful()
            ? $paymentResponse->json()
            : [
                'id' => $mpPaymentId,
                'status' => 'approved',
                'status_detail' => data_get($invoice, 'payment.status_detail', 'approved'),
                'transaction_amount' => $invoice['transaction_amount'] ?? $recurring->valor,
                'date_approved' => $invoice['debit_date'] ?? $invoice['last_modified'] ?? now()->toIso8601String(),
            ];

        $this->activatePlan($recurring, $mpPayment);
    }

    private function activatePlan(Payment $payment, array $mpPayment): void
    {
        DB::transaction(function () use ($payment, $mpPayment) {
            $payment = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();

            if ($this->wasApplied($payment)) {
                return;
            }

            $targetPlanId = $this->targetPlanId($payment);
            $plano = Plano::findOrFail($targetPlanId);
            $planoEmpresa = PlanoEmpresa::whereKey($payment->plano_id)->lockForUpdate()->firstOrFail();
            $approvedAt = Carbon::parse($mpPayment['date_approved'] ?? $mpPayment['date_created'] ?? now());
            $novaExpiracao = $approvedAt->copy()->addDays((int) ($plano->intervalo_dias ?: 30));

            if ($planoEmpresa->expiracao) {
                $current = Carbon::parse($planoEmpresa->expiracao);
                if ($current->gt($novaExpiracao)) {
                    $novaExpiracao = $current;
                }
            }

            $planoEmpresa->update([
                'plano_id' => $plano->id,
                'valor' => $plano->valor,
                'expiracao' => $novaExpiracao->toDateString(),
                'mensagem_alerta' => 'Pagamento recorrente aprovado - plano ativo at«± ' . $novaExpiracao->format('d/m/Y'),
                'primeiro_envio_realizado' => true,
            ]);

            $payment->update([
                'status' => 'approved',
                'status_detalhe' => $this->detail($plano->id, true, $mpPayment['status_detail'] ?? 'approved'),
                'valor' => (float) ($mpPayment['transaction_amount'] ?? $payment->valor),
            ]);
        });
    }

    private function processLegacyContaReceber(int $contaId, ?string $notifiedPaymentId): void
    {
        $conta = ContaReceber::find($contaId);
        if (!$conta || (int) $conta->status === 1) {
            return;
        }

        $configEmpresaId = $conta->empresa_id_emp ? 1 : (int) $conta->empresa_id;
        $config = ConfigEcommerce::where('empresa_id', $configEmpresaId)->first();

        if (!$config || empty($config->mercadopago_access_token)) {
            return;
        }

        $paymentId = $notifiedPaymentId ?: $conta->mp_payment_id;
        if (!$paymentId) {
            return;
        }

        $response = $this->client($config)
            ->get('https://api.mercadopago.com/v1/payments/' . urlencode((string) $paymentId));

        if (!$response->successful()) {
            return;
        }

        $payment = $response->json();
        if (($payment['status'] ?? null) !== 'approved') {
            return;
        }

        $conta->update([
            'status' => 1,
            'valor_recebido' => $payment['transaction_amount'] ?? $conta->valor_integral,
            'data_recebimento' => $payment['date_approved'] ?? now(),
            'observacao' => 'Pagamento aprovado via Mercado Pago. ID: ' . ($payment['id'] ?? 'N/A'),
            'mp_payment_id' => $payment['id'] ?? $conta->mp_payment_id,
        ]);
    }

    private function targetPlanId(Payment $payment): ?int
    {
        if (preg_match('/plano:(\d+)/', (string) $payment->status_detalhe, $matches)) {
            return (int) $matches[1];
        }
        if (preg_match('/plano:(\d+)/', (string) $payment->descricao, $matches)) {
            return (int) $matches[1];
        }
        return null;
    }

    private function wasApplied(Payment $payment): bool
    {
        return Str::contains((string) $payment->status_detalhe, 'applied:1');
    }

    private function detail(?int $planoId, bool $applied, string $detail = ''): string
    {
        return Str::limit(
            'plano:' . ((int) $planoId) . '|applied:' . ($applied ? '1' : '0') . '|' . preg_replace('/[^a-zA-Z0-9_.-]/', '_', $detail),
            100,
            ''
        );
    }

    private function adminConfig(): ConfigEcommerce
    {
        $config = ConfigEcommerce::where('empresa_id', 1)->first();
        if (!$config || empty($config->mercadopago_access_token)) {
            throw new RuntimeException('Mercado Pago n«ªo configurado na ConfigEcommerce da empresa_id = 1.');
        }
        return $config;
    }

    private function client(ConfigEcommerce $config)
    {
        return Http::acceptJson()
            ->asJson()
            ->withToken(trim($config->mercadopago_access_token))
            ->connectTimeout(8)
            ->timeout(20);
    }
}