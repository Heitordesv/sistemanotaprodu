<?php

namespace App\Services;

use App\Models\ConfigEcommerce;
use App\Models\ContaReceber;
use App\Models\ContaReceberPagamento;
use App\Models\Empresa;
use Carbon\Carbon;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class ContaReceberMercadoPagoService
{
    private const API = 'https://api.mercadopago.com';

    public function gerarPix(ContaReceber $conta): array
    {
        return $this->gerarPagamentoDireto($conta, 'pix');
    }

    public function gerarBoleto(ContaReceber $conta): array
    {
        return $this->gerarPagamentoDireto($conta, 'boleto');
    }

    public function gerarCartao(ContaReceber $conta): array
    {
        return $this->gerarCheckoutPro($conta, true);
    }

    public function gerarCheckout(ContaReceber $conta): array
    {
        return $this->gerarCheckoutPro($conta, false);
    }

    public function consultar(ContaReceber $conta): array
    {
        $config = $this->config($conta);

        if ($conta->mercadopago_payment_id) {
            $payment = $this->buscarPagamento($config, (string) $conta->mercadopago_payment_id);
            return $this->sincronizarPagamento($conta, $payment);
        }

        if ($conta->mercadopago_external_reference) {
            $payment = $this->buscarPorReferencia($config, (string) $conta->mercadopago_external_reference);
            if ($payment) {
                return $this->sincronizarPagamento($conta, $payment);
            }
        }

        return $this->respostaAtual($conta);
    }

    public function processarWebhook(int $configId, string $paymentId): void
    {
        $config = ConfigEcommerce::findOrFail($configId);
        $payment = $this->buscarPagamento($config, $paymentId);
        $externalReference = (string) ($payment['external_reference'] ?? '');
        $dados = $this->parseExternalReference($externalReference);

        if (!$dados || (int) $dados['empresa_id'] !== (int) $config->empresa_id) {
            throw new RuntimeException('Pagamento não pertence à empresa informada.');
        }

        $conta = ContaReceber::where('id', $dados['conta_id'])
            ->where('empresa_id', $config->empresa_id)
            ->firstOrFail();

        $this->sincronizarPagamento($conta, $payment);
    }

    public function retornoPublico(ContaReceber $conta, ?string $paymentId = null): array
    {
        if ($paymentId) {
            try {
                $config = $this->config($conta);
                $payment = $this->buscarPagamento($config, $paymentId);
                return $this->sincronizarPagamento($conta, $payment);
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return $this->respostaAtual($conta->fresh());
    }

    private function gerarPagamentoDireto(ContaReceber $conta, string $tipo): array
    {
        $this->validarConta($conta);
        $config = $this->config($conta);
        $payer = $this->payer($conta, $tipo === 'boleto');
        $valorCobranca = $this->saldoRestante($conta);

        if (
            $conta->mercadopago_payment_id
            && $conta->mercadopago_payment_method === $tipo
            && in_array((string) $conta->mercadopago_status, ['pending', 'in_process', 'action_required'], true)
        ) {
            $existente = $this->buscarPagamento($config, (string) $conta->mercadopago_payment_id);
            $statusExistente = (string) ($existente['status'] ?? '');

            if ($statusExistente === 'approved') {
                return $this->sincronizarPagamento($conta, $existente);
            }

            $valorExistente = round((float) ($existente['transaction_amount'] ?? 0), 2);

            if (abs($valorExistente - $valorCobranca) <= 0.009) {
                return $this->sincronizarPagamento($conta, $existente);
            }

            $this->cancelarPagamentoPendente($config, (string) $conta->mercadopago_payment_id, $statusExistente);
            $this->limparCobrancaDiretaAtual($conta);
        }

        $externalReference = $this->externalReference($conta, $tipo);
        $idempotencyKey = (string) Str::uuid();

        $payload = [
            'transaction_amount' => $valorCobranca,
            'description' => $this->descricao($conta),
            'external_reference' => $externalReference,
            'payment_method_id' => $tipo === 'pix' ? 'pix' : 'bolbradesco',
            'notification_url' => route('conta-receber.mp.webhook', ['configId' => $config->id]),
            'payer' => $payer,
        ];

        if ($tipo === 'boleto') {
            $payload['date_of_expiration'] = $this->vencimentoBoleto($conta)->toIso8601String();
        }

        $response = $this->request($config)
            ->withHeaders(['X-Idempotency-Key' => $idempotencyKey])
            ->post(self::API . '/v1/payments', $payload);

        $this->validarResposta($response, 'gerar ' . strtoupper($tipo));
        $payment = (array) $response->json();

        $conta->mercadopago_external_reference = $externalReference;
        $conta->mercadopago_idempotency_key = $idempotencyKey;
        $conta->mercadopago_payment_method = $tipo;
        $conta->mercadopago_preference_id = null;
        $conta->mercadopago_checkout_url = null;
        $this->garantirTokenPublico($conta);
        $conta->save();

        return $this->sincronizarPagamento($conta, $payment);
    }

    private function gerarCheckoutPro(ContaReceber $conta, bool $somenteCartao): array
    {
        $this->validarConta($conta);
        $config = $this->config($conta);
        $method = $somenteCartao ? 'cartao' : 'checkout';
        $valorCobranca = $this->saldoRestante($conta);

        // Só reutiliza um checkout se ainda não houve recebimento parcial.
        // Se o saldo mudou, cria uma preferência nova com o saldo atualizado.
        if (
            (float) $conta->valor_recebido <= 0
            && $conta->mercadopago_preference_id
            && $conta->mercadopago_checkout_url
            && $conta->mercadopago_payment_method === $method
            && !in_array((string) $conta->mercadopago_status, ['approved', 'cancelled', 'rejected', 'refunded', 'charged_back'], true)
        ) {
            return $this->respostaAtual($conta);
        }

        $this->garantirTokenPublico($conta);
        $externalReference = $this->externalReference($conta, $method);
        $payer = $this->payer($conta, false);
        $retorno = route('conta-receber.mp.retorno', [
            'id' => $conta->id,
            'token' => $conta->mercadopago_public_token,
        ]);

        $payload = [
            'items' => [[
                'id' => 'conta-receber-' . $conta->id,
                'title' => $this->descricao($conta),
                'quantity' => 1,
                'currency_id' => 'BRL',
                'unit_price' => $valorCobranca,
            ]],
            'payer' => [
                'name' => $payer['first_name'] ?? null,
                'surname' => $payer['last_name'] ?? null,
                'email' => $payer['email'] ?? null,
                'identification' => $payer['identification'] ?? null,
            ],
            'external_reference' => $externalReference,
            'notification_url' => route('conta-receber.mp.webhook', ['configId' => $config->id]),
            'back_urls' => [
                'success' => $retorno,
                'pending' => $retorno,
                'failure' => $retorno,
            ],
            'auto_return' => 'approved',
            'expires' => true,
            'expiration_date_to' => $this->vencimentoCheckout($conta)->toIso8601String(),
        ];

        if ($somenteCartao) {
            $payload['payment_methods'] = [
                'excluded_payment_types' => [
                    ['id' => 'ticket'],
                    ['id' => 'bank_transfer'],
                    ['id' => 'atm'],
                ],
                'installments' => 12,
            ];
        }

        $response = $this->request($config)
            ->post(self::API . '/checkout/preferences', $payload);

        $this->validarResposta($response, 'criar checkout');
        $data = (array) $response->json();
        $url = (string) ($data['init_point'] ?? '');

        if ($url === '') {
            throw new RuntimeException('O Mercado Pago não retornou o link do checkout.');
        }

        $conta->mercadopago_preference_id = (string) ($data['id'] ?? '');
        $conta->mercadopago_external_reference = $externalReference;
        $conta->mercadopago_payment_method = $method;
        $conta->mercadopago_status = 'checkout_created';
        $conta->mercadopago_status_detail = 'waiting_customer';
        $conta->mercadopago_checkout_url = $url;
        $conta->mercadopago_payment_id = null;
        $conta->mercadopago_idempotency_key = null;
        $conta->mercadopago_last_sync_at = now();
        $conta->save();

        return $this->respostaAtual($conta);
    }

    private function buscarPagamento(ConfigEcommerce $config, string $paymentId): array
    {
        $response = $this->request($config)
            ->get(self::API . '/v1/payments/' . urlencode($paymentId));

        $this->validarResposta($response, 'consultar pagamento');
        return (array) $response->json();
    }

    private function buscarPorReferencia(ConfigEcommerce $config, string $externalReference): ?array
    {
        $response = $this->request($config)
            ->get(self::API . '/v1/payments/search', [
                'external_reference' => $externalReference,
                'sort' => 'date_created',
                'criteria' => 'desc',
                'limit' => 1,
            ]);

        if ($response->failed()) {
            return null;
        }

        $results = $response->json('results') ?: [];
        return isset($results[0]) ? (array) $results[0] : null;
    }

    private function sincronizarPagamento(ContaReceber $conta, array $payment): array
    {
        $externalReference = (string) ($payment['external_reference'] ?? '');
        $dados = $this->parseExternalReference($externalReference);

        if (
            !$dados
            || (int) $dados['empresa_id'] !== (int) $conta->empresa_id
            || (int) $dados['conta_id'] !== (int) $conta->id
        ) {
            throw new RuntimeException('Referência do pagamento não corresponde à conta a receber.');
        }

        $transactionData = data_get($payment, 'point_of_interaction.transaction_data', []);
        $transactionDetails = data_get($payment, 'transaction_details', []);
        $status = (string) ($payment['status'] ?? '');
        $methodId = (string) ($payment['payment_method_id'] ?? '');
        $paymentTypeId = (string) ($payment['payment_type_id'] ?? '');
        $paymentId = (string) ($payment['id'] ?? '');

        $ticketUrl = (string) (
            data_get($transactionData, 'ticket_url')
            ?: data_get($transactionDetails, 'external_resource_url')
            ?: data_get($payment, 'transaction_details.external_resource_url')
            ?: ''
        );

        $conta->mercadopago_payment_id = $paymentId ?: $conta->mercadopago_payment_id;
        $conta->mercadopago_external_reference = $externalReference;
        $conta->mercadopago_status = $status ?: $conta->mercadopago_status;
        $conta->mercadopago_status_detail = (string) ($payment['status_detail'] ?? '');
        $conta->mercadopago_ticket_url = $ticketUrl ?: $conta->mercadopago_ticket_url;
        $conta->mercadopago_digitable_line = (string) (
            data_get($transactionData, 'digitable_line')
            ?: data_get($payment, 'barcode.content')
            ?: $conta->mercadopago_digitable_line
            ?: ''
        );
        $conta->mercadopago_qr_code = (string) (data_get($transactionData, 'qr_code') ?: $conta->mercadopago_qr_code ?: '');
        $conta->mercadopago_qr_code_base64 = (string) (data_get($transactionData, 'qr_code_base64') ?: $conta->mercadopago_qr_code_base64 ?: '');
        $conta->mercadopago_last_sync_at = now();

        if ($methodId === 'pix') {
            $conta->mercadopago_payment_method = 'pix';
        } elseif ($methodId === 'bolbradesco' || str_contains($methodId, 'boleto')) {
            $conta->mercadopago_payment_method = 'boleto';
        } elseif (in_array($paymentTypeId, ['credit_card', 'debit_card'], true)) {
            $conta->mercadopago_payment_method = $paymentTypeId === 'debit_card' ? 'cartao_debito' : 'cartao';
        }

        if ($conta->mercadopago_ticket_url) {
            $conta->boleto_link = $conta->mercadopago_ticket_url;
        }
        if ($conta->mercadopago_qr_code) {
            $conta->chave_pix = $conta->mercadopago_qr_code;
        }

        if ($status === 'approved' && $paymentId !== '') {
            $this->registrarAprovacaoNoFinanceiro($conta, $payment, $paymentId, $paymentTypeId, $methodId);
            return $this->respostaAtual($conta->fresh());
        }

        $conta->save();
        return $this->respostaAtual($conta->fresh());
    }

    private function registrarAprovacaoNoFinanceiro(
        ContaReceber $conta,
        array $payment,
        string $paymentId,
        string $paymentTypeId,
        string $methodId
    ): void {
        DB::transaction(function () use ($conta, $payment, $paymentId, $paymentTypeId, $methodId) {
            $conta = ContaReceber::whereKey($conta->id)->lockForUpdate()->firstOrFail();

            $jaRegistrado = ContaReceberPagamento::where('provedor', 'mercadopago')
                ->where('external_id', $paymentId)
                ->exists();

            if (!$jaRegistrado) {
                $valorPagamento = round((float) ($payment['transaction_amount'] ?? 0), 2);

                if ($valorPagamento <= 0) {
                    throw new RuntimeException('Mercado Pago aprovou o pagamento sem valor válido para conciliação.');
                }

                ContaReceberPagamento::create([
                    'conta_receber_id' => $conta->id,
                    'empresa_id' => $conta->empresa_id,
                    'valor' => $valorPagamento,
                    'forma_pagamento' => $this->formaPagamentoFinanceira($paymentTypeId, $methodId),
                    'data_pagamento' => $payment['date_approved'] ?? now(),
                    'origem' => 'automatico',
                    'provedor' => 'mercadopago',
                    'external_id' => $paymentId,
                    'lote_uuid' => null,
                    'status' => 'confirmado',
                    'observacao' => 'Pagamento conciliado automaticamente pelo Mercado Pago.',
                ]);

                $conta->valor_recebido = round((float) $conta->valor_recebido + $valorPagamento, 2);
            }

            $conta->status = (float) $conta->valor_recebido >= ((float) $conta->valor_integral - 0.009) ? 1 : 0;
            $conta->data_recebimento = $payment['date_approved'] ?? now();
            $conta->tipo_pagamento = $this->formaPagamentoFinanceira($paymentTypeId, $methodId);
            $conta->mercadopago_payment_id = $paymentId;
            $conta->mercadopago_status = (string) ($payment['status'] ?? 'approved');
            $conta->mercadopago_status_detail = (string) ($payment['status_detail'] ?? '');
            $conta->mercadopago_last_sync_at = now();
            $conta->save();
        });
    }

    private function formaPagamentoFinanceira(string $paymentTypeId, string $methodId): string
    {
        if ($methodId === 'pix' || $paymentTypeId === 'bank_transfer') {
            return '17';
        }
        if ($methodId === 'bolbradesco' || str_contains($methodId, 'boleto') || $paymentTypeId === 'ticket') {
            return '15';
        }
        if ($paymentTypeId === 'debit_card') {
            return '04';
        }
        if ($paymentTypeId === 'credit_card') {
            return '03';
        }

        return '99';
    }

    private function respostaAtual(ContaReceber $conta): array
    {
        return [
            'id' => $conta->id,
            'pago' => $this->contaPaga($conta),
            'status' => $conta->mercadopago_status,
            'status_detail' => $conta->mercadopago_status_detail,
            'payment_id' => $conta->mercadopago_payment_id,
            'preference_id' => $conta->mercadopago_preference_id,
            'payment_method' => $conta->mercadopago_payment_method,
            'qr_code' => $conta->mercadopago_qr_code,
            'qr_code_base64' => $conta->mercadopago_qr_code_base64,
            'pix_copia_cola' => $conta->mercadopago_qr_code,
            'boleto_link' => $conta->mercadopago_ticket_url ?: $conta->boleto_link,
            'linha_digitavel' => $conta->mercadopago_digitable_line,
            'checkout_url' => $conta->mercadopago_checkout_url,
            'valor_integral' => round((float) $conta->valor_integral, 2),
            'valor_recebido' => round((float) $conta->valor_recebido, 2),
            'saldo_restante' => $this->saldoRestante($conta),
            'data_recebimento' => $conta->data_recebimento,
            'last_sync_at' => $conta->mercadopago_last_sync_at,
        ];
    }

    private function config(ContaReceber $conta): ConfigEcommerce
    {
        $config = ConfigEcommerce::where('empresa_id', $conta->empresa_id)->first();

        if (!$config || !trim((string) $config->mercadopago_access_token)) {
            throw new RuntimeException('Mercado Pago não configurado para esta empresa. Informe o Access Token nas configurações da Loja Online.');
        }

        return $config;
    }

    private function request(ConfigEcommerce $config)
    {
        return Http::acceptJson()
            ->asJson()
            ->withToken(trim((string) $config->mercadopago_access_token))
            ->timeout(20)
            ->retry(2, 300, throw: false);
    }

    private function validarResposta(Response $response, string $acao): void
    {
        if ($response->successful()) {
            return;
        }

        $message = (string) (
            $response->json('message')
            ?: $response->json('error')
            ?: data_get($response->json(), 'cause.0.description')
            ?: 'Erro não informado pelo Mercado Pago.'
        );

        Log::warning('Falha Mercado Pago em Conta a Receber.', [
            'acao' => $acao,
            'http_status' => $response->status(),
            'message' => $message,
            'response' => $response->json(),
        ]);

        throw new RuntimeException('Mercado Pago: ' . $message);
    }

    private function cancelarPagamentoPendente(ConfigEcommerce $config, string $paymentId, string $status): void
    {
        if (!in_array($status, ['pending', 'in_process', 'authorized'], true)) {
            return;
        }

        $response = $this->request($config)
            ->put(self::API . '/v1/payments/' . urlencode($paymentId), ['status' => 'cancelled']);

        $this->validarResposta($response, 'cancelar cobrança antiga com saldo desatualizado');
    }

    private function limparCobrancaDiretaAtual(ContaReceber $conta): void
    {
        $conta->mercadopago_payment_id = null;
        $conta->mercadopago_status = 'cancelled';
        $conta->mercadopago_status_detail = 'balance_changed';
        $conta->mercadopago_ticket_url = null;
        $conta->mercadopago_digitable_line = null;
        $conta->mercadopago_qr_code = null;
        $conta->mercadopago_qr_code_base64 = null;
        $conta->boleto_link = null;
        $conta->chave_pix = null;
        $conta->save();
    }

    private function validarConta(ContaReceber $conta): void
    {
        if ($this->contaPaga($conta)) {
            throw new RuntimeException('Esta conta já está recebida.');
        }

        if ((float) $conta->valor_integral <= 0) {
            throw new RuntimeException('O valor da conta deve ser maior que zero.');
        }

        if ($this->saldoRestante($conta) <= 0) {
            throw new RuntimeException('Esta conta não possui saldo restante para cobrança.');
        }
    }

    private function saldoRestante(ContaReceber $conta): float
    {
        return round(max(0, (float) $conta->valor_integral - (float) $conta->valor_recebido), 2);
    }

    private function contaPaga(ContaReceber $conta): bool
    {
        return (int) $conta->status === 1 || $this->saldoRestante($conta) <= 0.009;
    }

    private function payer(ContaReceber $conta, bool $exigirEndereco): array
    {
        $pessoa = $conta->getCliente();

        if (!$pessoa && $conta->empresa_id_emp) {
            $pessoa = Empresa::with('cidade')->find($conta->empresa_id_emp);
        }

        if (!$pessoa) {
            throw new RuntimeException('A conta não possui cliente/empresa pagadora vinculada.');
        }

        $nomeCompleto = trim((string) ($pessoa->razao_social ?: $pessoa->nome_fantasia ?: 'Cliente'));
        [$firstName, $lastName] = $this->separarNome($nomeCompleto);
        $documento = preg_replace('/\D/', '', (string) ($pessoa->cpf_cnpj ?? ''));
        $email = trim((string) ($pessoa->email ?? ''));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('O cliente precisa ter um e-mail válido para gerar a cobrança no Mercado Pago.');
        }

        if (!in_array(strlen($documento), [11, 14], true)) {
            throw new RuntimeException('O CPF/CNPJ do cliente está inválido.');
        }

        $payload = [
            'email' => $email,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'identification' => [
                'type' => strlen($documento) === 14 ? 'CNPJ' : 'CPF',
                'number' => $documento,
            ],
        ];

        $telefone = preg_replace('/\D/', '', (string) ($pessoa->celular ?? $pessoa->telefone ?? ''));
        if ($telefone) {
            if (str_starts_with($telefone, '55') && strlen($telefone) > 11) {
                $telefone = substr($telefone, 2);
            }
            if (strlen($telefone) >= 10) {
                $payload['phone'] = [
                    'area_code' => substr($telefone, 0, 2),
                    'number' => substr($telefone, 2),
                ];
            }
        }

        if ($exigirEndereco) {
            $cidade = optional($pessoa->cidade)->nome;
            $uf = $pessoa->uf ?? optional($pessoa->cidade)->uf;
            $cep = preg_replace('/\D/', '', (string) ($pessoa->cep ?? ''));
            $rua = trim((string) ($pessoa->rua ?? ''));
            $numero = trim((string) ($pessoa->numero ?? ''));
            $bairro = trim((string) ($pessoa->bairro ?? ''));

            if (strlen($cep) !== 8 || !$rua || !$numero || !$bairro || !$cidade || !$uf) {
                throw new RuntimeException('Para gerar boleto, complete CEP, rua, número, bairro, cidade e UF do cliente.');
            }

            $payload['address'] = [
                'zip_code' => $cep,
                'street_name' => $rua,
                'street_number' => $numero,
                'neighborhood' => $bairro,
                'city' => $cidade,
                'federal_unit' => strtoupper((string) $uf),
            ];
        }

        return $payload;
    }

    private function descricao(ContaReceber $conta): string
    {
        $ref = trim((string) ($conta->referencia ?? ''));
        return Str::limit($ref !== '' ? 'Título ' . $ref : 'Conta a receber #' . $conta->id, 120, '');
    }

    private function externalReference(ContaReceber $conta, string $method): string
    {
        return 'CR:' . $conta->empresa_id . ':' . $conta->id . ':' . $method;
    }

    private function parseExternalReference(string $reference): ?array
    {
        $parts = explode(':', $reference);
        if (count($parts) < 4 || $parts[0] !== 'CR') {
            return null;
        }

        return [
            'empresa_id' => (int) $parts[1],
            'conta_id' => (int) $parts[2],
            'method' => (string) $parts[3],
        ];
    }

    private function separarNome(string $nome): array
    {
        $parts = preg_split('/\s+/', trim($nome)) ?: [];
        $first = array_shift($parts) ?: 'Cliente';
        $last = implode(' ', $parts) ?: 'Cliente';
        return [Str::limit($first, 50, ''), Str::limit($last, 50, '')];
    }

    private function vencimentoBoleto(ContaReceber $conta): Carbon
    {
        $limite = now()->addDays(29)->endOfDay();
        $vencimento = $conta->data_vencimento
            ? Carbon::parse($conta->data_vencimento)->endOfDay()
            : now()->addDays(3)->endOfDay();

        if ($vencimento->isPast()) {
            $vencimento = now()->addDay()->endOfDay();
        }

        return $vencimento->gt($limite) ? $limite : $vencimento;
    }

    private function vencimentoCheckout(ContaReceber $conta): Carbon
    {
        $vencimento = $conta->data_vencimento
            ? Carbon::parse($conta->data_vencimento)->endOfDay()
            : now()->addDays(7)->endOfDay();

        if ($vencimento->lte(now())) {
            $vencimento = now()->addDays(2)->endOfDay();
        }

        return $vencimento;
    }

    private function garantirTokenPublico(ContaReceber $conta): void
    {
        if (!$conta->mercadopago_public_token) {
            $conta->mercadopago_public_token = Str::random(48);
        }
    }
}