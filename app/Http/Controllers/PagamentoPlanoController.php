<?php

namespace App\Http\Controllers;

use App\Models\Cidade;
use App\Models\ConfigEcommerce;
use App\Models\Empresa;
use App\Models\Payment;
use App\Models\Plano;
use App\Models\PlanoEmpresa;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class PagamentoPlanoController extends Controller
{
    public function escolherPlanoPorEmpresa($empresaId)
    {
        $this->assertCanManageEmpresa((int) $empresaId);
        $empresa = Empresa::findOrFail($empresaId);
        $planos = Plano::where('visivel', true)->get();

        return view('planos.pagamento', [
            'planos' => $planos,
            'empresa' => $empresa,
            'title' => 'Escolha do Plano',
        ]);
    }

    public function gerarPixPlano($empresaId, $planoId): JsonResponse
    {
        try {
            $this->assertCanManageEmpresa((int) $empresaId);

            $empresa = Empresa::findOrFail($empresaId);
            $planoEmpresa = PlanoEmpresa::where('empresa_id', $empresa->id)->firstOrFail();
            $plano = Plano::where('visivel', true)->findOrFail($planoId);
            $config = $this->adminMercadoPagoConfig();

            $payment = $this->novaCobranca($planoEmpresa, $plano, 'pix');
            $externalReference = 'saas_payment_' . $payment->id;

            $response = $this->mpClient($config)
                ->withHeaders(['X-Idempotency-Key' => (string) Str::uuid()])
                ->post('https://api.mercadopago.com/v1/payments', [
                    'transaction_amount' => (float) $plano->valor,
                    'description' => "NF-e Notas - {$plano->nome}",
                    'external_reference' => $externalReference,
                    'payment_method_id' => 'pix',
                    'notification_url' => url("/mercadopago/notification/plano/{$planoEmpresa->id}"),
                    'payer' => $this->payer($empresa),
                ]);

            $data = $response->json();
            $pix = data_get($data, 'point_of_interaction.transaction_data');

            if (!$response->successful() || !$pix || empty($data['id'])) {
                $payment->update([
                    'status' => 'error',
                    'status_detalhe' => $this->detail($plano->id, false, $data['status_detail'] ?? 'erro_api'),
                ]);

                Log::error('Erro Mercado Pago PIX do plano.', [
                    'empresa_id' => $empresa->id,
                    'plano_id' => $plano->id,
                    'http_status' => $response->status(),
                    'response' => $data ?: $response->body(),
                ]);

                return response()->json([
                    'erro' => $data['message'] ?? 'Falha ao gerar PIX no Mercado Pago.',
                ], 422);
            }

            $payment->update([
                'transacao_id' => (string) $data['id'],
                'status' => $data['status'] ?? 'pending',
                'status_detalhe' => $this->detail($plano->id, false, $data['status_detail'] ?? 'pending'),
                'qr_code_base64' => $pix['qr_code_base64'] ?? '',
                'qr_code' => $pix['qr_code'] ?? '',
            ]);

            $planoEmpresa->update([
                'mensagem_alerta' => "PIX do plano {$plano->nome} gerado. Aguardando aprovação do pagamento.",
            ]);

            return response()->json([
                'status' => 'success',
                'payment_id' => $payment->id,
                'mp_payment_id' => $data['id'],
                'qr_code_base64' => $pix['qr_code_base64'] ?? null,
                'qr_code_text' => $pix['qr_code'] ?? null,
                'external_reference' => $planoEmpresa->id,
                'plano_id' => $plano->id,
            ]);
        } catch (Throwable $e) {
            Log::error('Falha ao gerar PIX para plano do SaaS.', [
                'empresa_id' => $empresaId,
                'plano_id' => $planoId,
                'erro' => $e->getMessage(),
            ]);

            return response()->json(['erro' => $e->getMessage()], 422);
        }
    }

    public function verificarStatus(Request $request, $planoEmpresaId): JsonResponse
    {
        try {
            $planoEmpresa = PlanoEmpresa::findOrFail($planoEmpresaId);
            $this->assertCanManageEmpresa((int) $planoEmpresa->empresa_id);

            $query = Payment::where('plano_id', $planoEmpresa->id)
                ->where('empresa_id', $planoEmpresa->empresa_id);

            if ($request->filled('payment_id')) {
                $query->where('id', (int) $request->query('payment_id'));
            }

            $payment = $query->latest('id')->first();

            if (!$payment) {
                return response()->json([
                    'status' => 'pendente',
                    'mensagem' => 'Nenhuma cobrança de plano encontrada.',
                ]);
            }

            $config = $this->adminMercadoPagoConfig();
            $novaExpiracao = null;

            if ($payment->forma_pagamento === 'cartao') {
                $sync = $this->syncCardSubscription($payment, $config);
                $payment = $sync['payment'];
                $novaExpiracao = $sync['nova_expiracao'];
            } elseif ($payment->forma_pagamento === 'rec_card') {
                if ($payment->status === 'approved' && !$this->wasApplied($payment)) {
                    $novaExpiracao = $this->ativarPlanoPorPagamento($payment, [
                        'id' => $payment->transacao_id,
                        'status' => 'approved',
                        'date_approved' => $payment->updated_at?->toIso8601String(),
                    ]);
                }
            } elseif (!Str::startsWith($payment->transacao_id, 'pending_')) {
                $response = $this->mpClient($config)
                    ->get('https://api.mercadopago.com/v1/payments/' . urlencode($payment->transacao_id));

                if ($response->successful()) {
                    $mpPayment = $response->json();
                    $payment = $this->sincronizarCobrancaAvulsa($payment, $mpPayment);

                    if (($mpPayment['status'] ?? null) === 'approved') {
                        $novaExpiracao = $this->ativarPlanoPorPagamento($payment, $mpPayment);
                        $payment = $payment->fresh();
                    }
                }
            }

            return response()->json([
                'status' => $payment->status,
                'mensagem' => $payment->status === 'approved'
                    ? 'Pagamento aprovado e plano ativado.'
                    : 'Pagamento/assinatura ainda aguardando confirmação.',
                'payment_id' => $payment->id,
                'forma_pagamento' => $payment->forma_pagamento,
                'nova_expiracao' => $novaExpiracao?->format('d/m/Y'),
                'expiracao' => $planoEmpresa->fresh()->expiracao,
                'subscription' => $payment->forma_pagamento === 'cartao'
                    ? $this->subscriptionPayload($payment)
                    : null,
            ]);
        } catch (Throwable $e) {
            Log::warning('Erro ao verificar pagamento de plano Mercado Pago.', [
                'plano_empresa_id' => $planoEmpresaId,
                'erro' => $e->getMessage(),
            ]);

            return response()->json(['erro' => $e->getMessage()], 422);
        }
    }

    public function notification(Request $request, $planoEmpresaId): JsonResponse
    {
        try {
            $metodo = (string) $request->input('metodo', '');
            $planoEmpresa = PlanoEmpresa::with('empresa')->find($planoEmpresaId);

            if (!$planoEmpresa) {
                if ($metodo !== '') {
                    return response()->json(['erro' => 'Plano da empresa não encontrado.'], 404);
                }

                Log::info('Webhook antigo do Mercado Pago ignorado: plano não existe mais.', [
                    'plano_empresa_id' => $planoEmpresaId,
                    'mp_payment_id' => data_get($request->all(), 'data.id'),
                ]);

                return response()->json(['received' => true, 'ignored' => true]);
            }

            if ($metodo !== '') {
                $this->validateSessionRequest($request, (int) $planoEmpresa->empresa_id);

                if ($metodo === 'boleto') {
                    return $this->gerarBoletoPlano($request, $planoEmpresa);
                }

                if ($metodo === 'cartao_assinatura') {
                    return $this->criarOuAtualizarAssinaturaCartao($request, $planoEmpresa);
                }

                if ($metodo === 'cancelar_assinatura') {
                    return $this->cancelarAssinaturaCartao($planoEmpresa);
                }

                return response()->json(['erro' => 'Método de pagamento não suportado.'], 422);
            }

            $paymentId = data_get($request->all(), 'data.id')
                ?: $request->query('data.id')
                ?: $request->input('id');

            if (!$paymentId) {
                return response()->json(['received' => true]);
            }

            $config = $this->adminMercadoPagoConfig();
            $response = $this->mpClient($config)
                ->get('https://api.mercadopago.com/v1/payments/' . urlencode((string) $paymentId));

            if (!$response->successful()) {
                throw new RuntimeException('Não foi possível consultar o pagamento notificado no Mercado Pago.');
            }

            $mpPayment = $response->json();
            $payment = Payment::where('transacao_id', (string) $paymentId)
                ->where('plano_id', $planoEmpresa->id)
                ->first();

            if (!$payment) {
                $payment = $this->paymentFromExternalReference($mpPayment['external_reference'] ?? null);
            }

            if (!$payment || (int) $payment->plano_id !== (int) $planoEmpresa->id) {
                Log::warning('Webhook de plano sem cobrança local correspondente.', [
                    'plano_empresa_id' => $planoEmpresa->id,
                    'mp_payment_id' => $paymentId,
                    'external_reference' => $mpPayment['external_reference'] ?? null,
                ]);

                return response()->json(['received' => true, 'ignored' => true]);
            }

            $payment = $this->sincronizarCobrancaAvulsa($payment, $mpPayment);

            if (($mpPayment['status'] ?? null) === 'approved') {
                $this->ativarPlanoPorPagamento($payment, $mpPayment);
            }

            return response()->json(['received' => true]);
        } catch (Throwable $e) {
            Log::error('Erro no pagamento de plano Mercado Pago.', [
                'plano_empresa_id' => $planoEmpresaId,
                'erro' => $e->getMessage(),
                'payload' => $request->except(['card_token']),
            ]);

            return response()->json([
                'error' => 'payment_processing_failed',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    private function gerarBoletoPlano(Request $request, PlanoEmpresa $planoEmpresa): JsonResponse
    {
        $validated = $request->validate([
            'plano_id' => ['required', 'integer'],
            'payer_email' => ['nullable', 'email:rfc', 'max:190'],
        ]);

        $plano = Plano::where('visivel', true)->findOrFail((int) $validated['plano_id']);
        $empresa = Empresa::findOrFail($planoEmpresa->empresa_id);
        $config = $this->adminMercadoPagoConfig();
        $payment = $this->novaCobranca($planoEmpresa, $plano, 'boleto');
        $payer = $this->payer($empresa, $validated['payer_email'] ?? null, true);

        $response = $this->mpClient($config)
            ->withHeaders(['X-Idempotency-Key' => (string) Str::uuid()])
            ->post('https://api.mercadopago.com/v1/payments', [
                'transaction_amount' => (float) $plano->valor,
                'description' => "NF-e Notas - {$plano->nome}",
                'external_reference' => 'saas_payment_' . $payment->id,
                'payment_method_id' => 'bolbradesco',
                'notification_url' => url("/mercadopago/notification/plano/{$planoEmpresa->id}"),
                'payer' => $payer,
            ]);

        $data = $response->json();
        $boletoLink = data_get($data, 'transaction_details.external_resource_url');

        if (!$response->successful() || !$boletoLink || empty($data['id'])) {
            $payment->update([
                'status' => 'error',
                'status_detalhe' => $this->detail($plano->id, false, $data['status_detail'] ?? 'erro_api'),
            ]);

            return response()->json([
                'erro' => $data['message'] ?? 'Não foi possível gerar o boleto.',
            ], 422);
        }

        $payment->update([
            'transacao_id' => (string) $data['id'],
            'status' => $data['status'] ?? 'pending',
            'status_detalhe' => $this->detail($plano->id, false, $data['status_detail'] ?? 'pending'),
            'link_boleto' => $boletoLink,
        ]);

        $planoEmpresa->update([
            'mensagem_alerta' => "Boleto do plano {$plano->nome} gerado. Aguardando aprovação do pagamento.",
        ]);

        return response()->json([
            'status' => 'success',
            'payment_id' => $payment->id,
            'mp_payment_id' => $data['id'],
            'boleto_link' => $boletoLink,
            'plano_id' => $plano->id,
        ]);
    }

    private function criarOuAtualizarAssinaturaCartao(Request $request, PlanoEmpresa $planoEmpresa): JsonResponse
    {
        $validated = $request->validate([
            'plano_id' => ['required', 'integer'],
            'card_token' => ['required', 'string', 'max:255'],
            'payer_email' => ['required', 'email:rfc', 'max:190'],
        ]);

        $plano = Plano::where('visivel', true)->findOrFail((int) $validated['plano_id']);
        $config = $this->adminMercadoPagoConfig();

        $existing = Payment::where('plano_id', $planoEmpresa->id)
            ->where('empresa_id', $planoEmpresa->empresa_id)
            ->where('forma_pagamento', 'cartao')
            ->whereIn('status', ['authorized', 'pending', 'paused'])
            ->latest('id')
            ->first();

        if ($existing && !Str::startsWith($existing->transacao_id, 'pending_')) {
            $existingPlanId = $this->targetPlanId($existing);

            if ($existingPlanId === $plano->id) {
                $response = $this->mpClient($config)->put(
                    'https://api.mercadopago.com/preapproval/' . urlencode($existing->transacao_id),
                    [
                        'card_token_id' => $validated['card_token'],
                        'status' => 'authorized',
                    ]
                );

                $data = $response->json();
                if (!$response->successful()) {
                    return response()->json([
                        'erro' => $data['message'] ?? 'Não foi possível atualizar o cartão da assinatura.',
                    ], 422);
                }

                $existing->update([
                    'status' => $data['status'] ?? 'authorized',
                    'status_detalhe' => $this->detail($plano->id, false, 'cartao_atualizado'),
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Cartão atualizado na assinatura.',
                    'payment_id' => $existing->id,
                    'subscription' => $this->subscriptionPayload($existing->fresh(), $data),
                ]);
            }

            $this->mpClient($config)->put(
                'https://api.mercadopago.com/preapproval/' . urlencode($existing->transacao_id),
                ['status' => 'canceled']
            );

            $existing->update([
                'status' => 'canceled',
                'status_detalhe' => $this->detail($existingPlanId, $this->wasApplied($existing), 'troca_plano'),
            ]);
        }

        $usuario = Usuario::find((int) session('user_logged.id'));
        if (!$usuario) {
            throw new RuntimeException('Usuário logado não encontrado.');
        }

        $payment = $this->novaCobranca($planoEmpresa, $plano, 'cartao');
        $externalReference = sprintf(
            'saas_card_payment_%d_user_%d_empresa_%d',
            $payment->id,
            $usuario->id,
            $planoEmpresa->empresa_id
        );

        [$frequency, $frequencyType] = $this->recurrence((int) ($plano->intervalo_dias ?: 30));

        $planResponse = $this->mpClient($config)
            ->withHeaders(['X-Idempotency-Key' => (string) Str::uuid()])
            ->post('https://api.mercadopago.com/preapproval_plan', [
                'reason' => 'NF-e Notas - ' . $plano->nome,
                'external_reference' => 'saas_plano_' . $plano->id,
                'auto_recurring' => [
                    'frequency' => $frequency,
                    'frequency_type' => $frequencyType,
                    'transaction_amount' => (float) $plano->valor,
                    'currency_id' => 'BRL',
                ],
                'back_url' => url('/payment/finish?empresa_id=' . $planoEmpresa->empresa_id . '&plano_id=' . $plano->id),
            ]);

        $mpPlan = $planResponse->json();
        if (!$planResponse->successful() || empty($mpPlan['id'])) {
            $payment->update(['status' => 'error']);

            return response()->json([
                'erro' => $mpPlan['message'] ?? 'Não foi possível criar o plano recorrente no Mercado Pago.',
            ], 422);
        }

        $subscriptionResponse = $this->mpClient($config)
            ->withHeaders(['X-Idempotency-Key' => (string) Str::uuid()])
            ->post('https://api.mercadopago.com/preapproval', [
                'preapproval_plan_id' => $mpPlan['id'],
                'reason' => 'NF-e Notas - ' . $plano->nome,
                'external_reference' => $externalReference,
                'payer_email' => $validated['payer_email'],
                'card_token_id' => $validated['card_token'],
                'back_url' => url('/payment/finish?empresa_id=' . $planoEmpresa->empresa_id . '&plano_id=' . $plano->id),
                'status' => 'authorized',
            ]);

        $subscription = $subscriptionResponse->json();

        if (!$subscriptionResponse->successful() || empty($subscription['id'])) {
            $payment->update([
                'status' => 'error',
                'status_detalhe' => $this->detail($plano->id, false, 'erro_assinatura'),
            ]);

            return response()->json([
                'erro' => $subscription['message'] ?? 'Não foi possível criar a assinatura no Mercado Pago.',
            ], 422);
        }

        $payment->update([
            'transacao_id' => (string) $subscription['id'],
            'status' => $subscription['status'] ?? 'pending',
            'status_detalhe' => $this->detail($plano->id, false, 'assinatura'),
            'descricao' => Str::limit("SAAS assinatura | plano:{$plano->id} | {$plano->nome} | ref:{$externalReference}", 200, ''),
        ]);

        $planoEmpresa->update([
            'mensagem_alerta' => 'Assinatura no cartão criada. O plano só será alterado quando uma cobrança for aprovada.',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Assinatura configurada. O acesso será atualizado somente após a cobrança aprovada.',
            'payment_id' => $payment->id,
            'subscription' => $this->subscriptionPayload($payment->fresh(), $subscription),
        ], 201);
    }

    private function cancelarAssinaturaCartao(PlanoEmpresa $planoEmpresa): JsonResponse
    {
        $payment = Payment::where('plano_id', $planoEmpresa->id)
            ->where('empresa_id', $planoEmpresa->empresa_id)
            ->where('forma_pagamento', 'cartao')
            ->whereNotIn('status', ['canceled', 'cancelled'])
            ->latest('id')
            ->first();

        if (!$payment || Str::startsWith($payment->transacao_id, 'pending_')) {
            return response()->json(['erro' => 'Nenhuma assinatura ativa encontrada.'], 404);
        }

        $config = $this->adminMercadoPagoConfig();
        $response = $this->mpClient($config)->put(
            'https://api.mercadopago.com/preapproval/' . urlencode($payment->transacao_id),
            ['status' => 'canceled']
        );

        $data = $response->json();
        if (!$response->successful()) {
            return response()->json([
                'erro' => $data['message'] ?? 'Não foi possível cancelar a assinatura.',
            ], 422);
        }

        $payment->update([
            'status' => $data['status'] ?? 'canceled',
            'status_detalhe' => $this->detail($this->targetPlanId($payment), $this->wasApplied($payment), 'cancelada'),
        ]);

        $planoEmpresa->update([
            'mensagem_alerta' => 'Renovação automática cancelada. O acesso já pago permanece até o vencimento atual.',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Renovação automática cancelada. O período já pago continua válido.',
            'subscription' => $this->subscriptionPayload($payment->fresh(), $data),
        ]);
    }

    private function syncCardSubscription(Payment $payment, ConfigEcommerce $config): array
    {
        $novaExpiracao = null;

        if (Str::startsWith($payment->transacao_id, 'pending_')) {
            return compact('payment', 'novaExpiracao');
        }

        $response = $this->mpClient($config)
            ->get('https://api.mercadopago.com/preapproval/' . urlencode($payment->transacao_id));

        if ($response->successful()) {
            $subscription = $response->json();
            $payment->update([
                'status' => $subscription['status'] ?? $payment->status,
                'status_detalhe' => $this->detail(
                    $this->targetPlanId($payment),
                    $this->wasApplied($payment),
                    'assinatura'
                ),
            ]);
        }

        $invoiceResponse = $this->mpClient($config)
            ->get('https://api.mercadopago.com/authorized_payments/search', [
                'preapproval_id' => $payment->transacao_id,
                'limit' => 20,
            ]);

        if ($invoiceResponse->successful()) {
            foreach ($invoiceResponse->json('results', []) as $invoice) {
                if (data_get($invoice, 'payment.status') !== 'approved') {
                    continue;
                }

                $novaExpiracao = $this->processarFaturaRecorrente($payment, $invoice, $config)
                    ?: $novaExpiracao;
            }
        }

        return [
            'payment' => $payment->fresh(),
            'nova_expiracao' => $novaExpiracao,
        ];
    }

    private function processarFaturaRecorrente(Payment $subscriptionPayment, array $invoice, ConfigEcommerce $config): ?Carbon
    {
        $mpPaymentId = (string) data_get($invoice, 'payment.id', '');
        if ($mpPaymentId === '') {
            return null;
        }

        $recurring = Payment::where('empresa_id', $subscriptionPayment->empresa_id)
            ->where('plano_id', $subscriptionPayment->plano_id)
            ->where('forma_pagamento', 'rec_card')
            ->where('transacao_id', $mpPaymentId)
            ->first();

        if (!$recurring) {
            $targetPlanId = $this->targetPlanId($subscriptionPayment);
            $recurring = Payment::create([
                'empresa_id' => $subscriptionPayment->empresa_id,
                'plano_id' => $subscriptionPayment->plano_id,
                'valor' => (float) ($invoice['transaction_amount'] ?? $subscriptionPayment->valor),
                'transacao_id' => $mpPaymentId,
                'forma_pagamento' => 'rec_card',
                'status' => data_get($invoice, 'payment.status', 'pending'),
                'status_detalhe' => $this->detail($targetPlanId, false, data_get($invoice, 'payment.status_detail', 'recorrente')),
                'descricao' => Str::limit('SAAS recorrente | assinatura:' . $subscriptionPayment->transacao_id, 200, ''),
                'link_boleto' => '',
                'qr_code_base64' => '',
                'qr_code' => '',
            ]);
        }

        if ($this->wasApplied($recurring)) {
            return null;
        }

        $paymentResponse = $this->mpClient($config)
            ->get('https://api.mercadopago.com/v1/payments/' . urlencode($mpPaymentId));

        $mpPayment = $paymentResponse->successful()
            ? $paymentResponse->json()
            : [
                'id' => $mpPaymentId,
                'status' => data_get($invoice, 'payment.status'),
                'status_detail' => data_get($invoice, 'payment.status_detail'),
                'transaction_amount' => $invoice['transaction_amount'] ?? null,
                'date_approved' => $invoice['debit_date'] ?? $invoice['last_modified'] ?? null,
            ];

        $recurring = $this->sincronizarCobrancaAvulsa($recurring, $mpPayment);

        if (($mpPayment['status'] ?? null) !== 'approved') {
            return null;
        }

        return $this->ativarPlanoPorPagamento($recurring, $mpPayment);
    }

    private function ativarPlanoPorPagamento(Payment $payment, array $mpPayment): Carbon
    {
        return DB::transaction(function () use ($payment, $mpPayment) {
            $payment = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();

            if ($this->wasApplied($payment)) {
                $planoEmpresa = PlanoEmpresa::findOrFail($payment->plano_id);
                return Carbon::parse($planoEmpresa->expiracao ?: now());
            }

            if (($mpPayment['status'] ?? null) !== 'approved') {
                throw new RuntimeException('Tentativa de ativar plano sem pagamento aprovado.');
            }

            $targetPlanId = $this->targetPlanId($payment);
            if (!$targetPlanId) {
                throw new RuntimeException('Plano contratado não identificado na cobrança.');
            }

            $plano = Plano::findOrFail($targetPlanId);
            $planoEmpresa = PlanoEmpresa::whereKey($payment->plano_id)->lockForUpdate()->firstOrFail();
            $approvedAt = Carbon::parse($mpPayment['date_approved'] ?? $mpPayment['date_created'] ?? now());
            $days = (int) ($plano->intervalo_dias ?: 30);
            $novaExpiracao = $approvedAt->copy()->addDays($days);

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
                'mensagem_alerta' => 'Pagamento aprovado - plano ativo até ' . $novaExpiracao->format('d/m/Y'),
                'primeiro_envio_realizado' => true,
            ]);

            $payment->update([
                'status' => 'approved',
                'status_detalhe' => $this->detail($plano->id, true, $mpPayment['status_detail'] ?? 'approved'),
                'valor' => (float) ($mpPayment['transaction_amount'] ?? $payment->valor),
            ]);

            return $novaExpiracao;
        });
    }

    private function sincronizarCobrancaAvulsa(Payment $payment, array $mpPayment): Payment
    {
        $targetPlanId = $this->targetPlanId($payment);
        $status = $mpPayment['status'] ?? $payment->status;

        $payment->update([
            'status' => $status,
            'status_detalhe' => $this->detail($targetPlanId, $this->wasApplied($payment), $mpPayment['status_detail'] ?? $status),
            'valor' => (float) ($mpPayment['transaction_amount'] ?? $payment->valor),
            'link_boleto' => data_get($mpPayment, 'transaction_details.external_resource_url', $payment->link_boleto ?: ''),
            'qr_code_base64' => data_get($mpPayment, 'point_of_interaction.transaction_data.qr_code_base64', $payment->qr_code_base64 ?: ''),
            'qr_code' => data_get($mpPayment, 'point_of_interaction.transaction_data.qr_code', $payment->qr_code ?: ''),
        ]);

        return $payment->fresh();
    }

    private function novaCobranca(PlanoEmpresa $planoEmpresa, Plano $plano, string $forma): Payment
    {
        return Payment::create([
            'empresa_id' => $planoEmpresa->empresa_id,
            'plano_id' => $planoEmpresa->id,
            'valor' => (float) $plano->valor,
            'transacao_id' => 'pending_' . Str::uuid(),
            'forma_pagamento' => $forma,
            'status' => 'pending',
            'status_detalhe' => $this->detail($plano->id, false, 'created'),
            'descricao' => Str::limit("SAAS plano:{$plano->id} | {$plano->nome}", 200, ''),
            'link_boleto' => '',
            'qr_code_base64' => '',
            'qr_code' => '',
        ]);
    }

    private function payer(Empresa $empresa, ?string $email = null, bool $withAddress = false): array
    {
        $nome = trim($empresa->nome_fantasia ?? $empresa->razao_social ?? 'Cliente');
        $partes = preg_split('/\s+/', $nome, 2);
        $document = preg_replace('/\D/', '', $empresa->cpf_cnpj ?? '');

        $payer = [
            'email' => $email ?: ($empresa->email ?: 'cliente@nfenotas.com.br'),
            'first_name' => $partes[0] ?: 'Cliente',
            'last_name' => $partes[1] ?? 'NF-e Notas',
        ];

        if (in_array(strlen($document), [11, 14], true)) {
            $payer['identification'] = [
                'type' => strlen($document) === 11 ? 'CPF' : 'CNPJ',
                'number' => $document,
            ];
        }

        if ($withAddress) {
            $cidade = $empresa->cidade_id ? Cidade::find($empresa->cidade_id) : null;
            $payer['address'] = [
                'zip_code' => preg_replace('/\D/', '', $empresa->cep ?? ''),
                'street_name' => $empresa->rua ?: 'Endereço não informado',
                'street_number' => $empresa->numero ?: 'S/N',
                'neighborhood' => $empresa->bairro ?: 'Bairro',
                'city' => $cidade->nome ?? 'Cidade',
                'federal_unit' => $cidade->uf ?? ($empresa->uf ?? 'SP'),
            ];
        }

        return $payer;
    }

    private function paymentFromExternalReference(?string $reference): ?Payment
    {
        if (!$reference || !preg_match('/saas_payment_(\d+)/', $reference, $matches)) {
            return null;
        }

        return Payment::find((int) $matches[1]);
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

    private function recurrence(int $intervalDays): array
    {
        if ($intervalDays >= 28 && $intervalDays <= 31) {
            return [1, 'months'];
        }

        if ($intervalDays >= 360 && $intervalDays <= 370) {
            return [12, 'months'];
        }

        return [max(1, $intervalDays), 'days'];
    }

    private function subscriptionPayload(Payment $payment, array $mpData = []): array
    {
        return [
            'id' => $payment->id,
            'mp_subscription_id' => $payment->transacao_id,
            'status' => $mpData['status'] ?? $payment->status,
            'plano_id' => $this->targetPlanId($payment),
            'next_payment_date' => $mpData['next_payment_date'] ?? null,
        ];
    }

    private function adminMercadoPagoConfig(): ConfigEcommerce
    {
        $config = ConfigEcommerce::where('empresa_id', 1)->first();

        if (!$config || empty($config->mercadopago_access_token)) {
            throw new RuntimeException('Token do Mercado Pago não configurado na ConfigEcommerce da empresa_id = 1.');
        }

        return $config;
    }

    private function mpClient(ConfigEcommerce $config)
    {
        return Http::acceptJson()
            ->asJson()
            ->withToken(trim($config->mercadopago_access_token))
            ->connectTimeout(8)
            ->timeout(20);
    }

    private function assertCanManageEmpresa(int $empresaId): void
    {
        $session = session('user_logged');
        abort_unless($session && isset($session['empresa']), 401, 'Usuário não autenticado.');

        $isSuper = isSuper($session['login'] ?? $session['super'] ?? '');
        abort_unless($isSuper || (int) $session['empresa'] === $empresaId, 403, 'Empresa não autorizada.');
    }

    private function validateSessionRequest(Request $request, int $empresaId): void
    {
        $this->assertCanManageEmpresa($empresaId);
        $headerToken = (string) $request->header('X-CSRF-TOKEN', '');
        $sessionToken = (string) $request->session()->token();

        abort_unless(
            $headerToken !== '' && $sessionToken !== '' && hash_equals($sessionToken, $headerToken),
            419,
            'Sessão expirada. Atualize a página e tente novamente.'
        );
    }
}
