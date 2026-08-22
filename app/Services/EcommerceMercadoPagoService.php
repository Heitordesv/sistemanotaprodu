<?php

namespace App\Services;

use App\Models\ConfigEcommerce;
use App\Models\PedidoEcommerce;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class EcommerceMercadoPagoService
{
    public function __construct(private EcommerceCheckoutService $checkoutService)
    {
    }

    public function criarPix(PedidoEcommerce $pedido, ConfigEcommerce $config): array
    {
        return $this->processarComLock($pedido, 'pix', function (PedidoEcommerce $pedido) use ($config) {
            $valor = $this->valorSeguro($pedido, $config, 'pix');
            $payload = [
                'transaction_amount' => $valor,
                'description' => $this->descricao($pedido),
                'payment_method_id' => 'pix',
                'external_reference' => $this->externalReference($pedido),
                'notification_url' => $this->notificationUrl($config),
                'payer' => $this->payer($pedido, false),
            ];

            return $this->criarPagamento($pedido, $config, 'Pix', $payload, 'pix');
        });
    }

    public function criarBoleto(PedidoEcommerce $pedido, ConfigEcommerce $config): array
    {
        return $this->processarComLock($pedido, 'boleto', function (PedidoEcommerce $pedido) use ($config) {
            $valor = $this->valorSeguro($pedido, $config, 'boleto');
            $payload = [
                'transaction_amount' => $valor,
                'description' => $this->descricao($pedido),
                'payment_method_id' => 'bolbradesco',
                'external_reference' => $this->externalReference($pedido),
                'notification_url' => $this->notificationUrl($config),
                'payer' => $this->payer($pedido, true),
            ];

            return $this->criarPagamento($pedido, $config, 'Boleto', $payload, 'boleto');
        });
    }

    public function criarCartao(PedidoEcommerce $pedido, ConfigEcommerce $config, array $dados): array
    {
        return $this->processarComLock($pedido, 'cartao', function (PedidoEcommerce $pedido) use ($config, $dados) {
            $valor = $this->valorSeguro($pedido, $config, 'cartao');

            $token = trim((string) ($dados['token'] ?? ''));
            $paymentMethodId = trim((string) ($dados['payment_method_id'] ?? ''));
            $installments = max(1, min(24, (int) ($dados['installments'] ?? 1)));

            if ($token === '' || $paymentMethodId === '') {
                throw new RuntimeException('Não foi possível tokenizar o cartão. Revise os dados e tente novamente.');
            }

            $payer = $this->payer($pedido, false);
            if (!empty($dados['payer']['email'])) {
                $payer['email'] = filter_var($dados['payer']['email'], FILTER_VALIDATE_EMAIL)
                    ? $dados['payer']['email']
                    : $payer['email'];
            }
            if (!empty($dados['payer']['identification']['type'])) {
                $payer['identification']['type'] = strtoupper((string) $dados['payer']['identification']['type']);
            }
            if (!empty($dados['payer']['identification']['number'])) {
                $payer['identification']['number'] = preg_replace('/\D/', '', (string) $dados['payer']['identification']['number']);
            }

            $payload = [
                'transaction_amount' => $valor,
                'token' => $token,
                'description' => $this->descricao($pedido),
                'installments' => $installments,
                'payment_method_id' => $paymentMethodId,
                'external_reference' => $this->externalReference($pedido),
                'notification_url' => $this->notificationUrl($config),
                'payer' => $payer,
            ];

            if (!empty($dados['issuer_id'])) {
                $payload['issuer_id'] = (string) $dados['issuer_id'];
            }

            return $this->criarPagamento($pedido, $config, 'CARTÃO', $payload, 'cartao');
        });
    }

    public function consultarPagamento(PedidoEcommerce $pedido, ConfigEcommerce $config): array
    {
        if (!$pedido->transacao_id) {
            throw new RuntimeException('Pedido ainda não possui transação no Mercado Pago.');
        }

        $response = $this->request($config)
            ->get('https://api.mercadopago.com/v1/payments/' . urlencode((string) $pedido->transacao_id));

        if ($response->failed()) {
            throw new RuntimeException('Não foi possível consultar o pagamento no Mercado Pago.');
        }

        $payment = $response->json();
        $this->sincronizarPedido($pedido, $payment);

        return $payment;
    }

    public function sincronizarPorWebhook(ConfigEcommerce $config, string $paymentId): ?PedidoEcommerce
    {
        $response = $this->request($config)
            ->get('https://api.mercadopago.com/v1/payments/' . urlencode($paymentId));

        if ($response->failed()) {
            throw new RuntimeException('Falha ao consultar o pagamento notificado pelo Mercado Pago.');
        }

        $payment = $response->json();
        $externalReference = (string) ($payment['external_reference'] ?? '');
        $pedidoId = $this->pedidoIdFromExternalReference($externalReference);

        if (!$pedidoId) {
            return null;
        }

        $pedido = PedidoEcommerce::where('id', $pedidoId)
            ->where('empresa_id', $config->empresa_id)
            ->first();

        if (!$pedido) {
            return null;
        }

        $this->sincronizarPedido($pedido, $payment);
        return $pedido->fresh();
    }

    public function validarAssinaturaWebhook(
        ConfigEcommerce $config,
        ?string $xSignature,
        ?string $xRequestId,
        ?string $dataId
    ): bool {
        $secret = trim((string) ($config->mercadopago_webhook_secret ?? ''));
        if ($secret === '' || !$xSignature || !$xRequestId || !$dataId) {
            return false;
        }

        $parts = [];
        foreach (explode(',', $xSignature) as $part) {
            [$key, $value] = array_pad(explode('=', trim($part), 2), 2, null);
            if ($key && $value) {
                $parts[trim($key)] = trim($value);
            }
        }

        $ts = $parts['ts'] ?? null;
        $hash = $parts['v1'] ?? null;
        if (!$ts || !$hash) {
            return false;
        }

        $manifest = 'id:' . strtolower((string) $dataId)
            . ';request-id:' . $xRequestId
            . ';ts:' . $ts . ';';

        $generated = hash_hmac('sha256', $manifest, $secret);
        return hash_equals($generated, $hash);
    }

    private function criarPagamento(
        PedidoEcommerce $pedido,
        ConfigEcommerce $config,
        string $formaPagamento,
        array $payload,
        string $tipo
    ): array {
        $this->validarCredenciais($config);

        $pedido->refresh();
        if ($this->temTransacaoReutilizavel($pedido, $formaPagamento)) {
            return $this->consultarPagamento($pedido, $config);
        }

        if ($pedido->transacao_id && in_array($pedido->status_pagamento, ['rejected', 'cancelled'], true)) {
            $pedido->token = Str::random(40);
            $pedido->transacao_id = null;
            $pedido->status_detalhe = null;
            $pedido->save();
        }

        $idempotencyKey = $this->idempotencyKey($pedido, $tipo);

        $response = $this->request($config)
            ->withHeaders(['X-Idempotency-Key' => $idempotencyKey])
            ->post('https://api.mercadopago.com/v1/payments', $payload);

        if ($response->failed()) {
            throw new RuntimeException($this->mensagemErroMercadoPago($response));
        }

        $payment = $response->json();
        if (empty($payment['id'])) {
            throw new RuntimeException('O Mercado Pago não retornou o identificador do pagamento.');
        }

        $pedido->forma_pagamento = $formaPagamento;
        $this->sincronizarPedido($pedido, $payment);

        return $payment;
    }

    private function processarComLock(PedidoEcommerce $pedido, string $tipo, callable $callback): array
    {
        $lock = Cache::lock('ecommerce-payment:' . $pedido->id, 20);

        try {
            return $lock->block(5, function () use ($pedido, $callback) {
                return $callback($pedido->fresh());
            });
        } catch (\Illuminate\Contracts\Cache\LockTimeoutException $e) {
            throw new RuntimeException('Já existe um pagamento sendo processado para este pedido. Tente novamente em instantes.');
        } finally {
            optional($lock)->release();
        }
    }

    private function valorSeguro(PedidoEcommerce $pedido, ConfigEcommerce $config, string $forma): float
    {
        if (!$pedido->endereco) {
            throw new RuntimeException('O pedido não possui endereço selecionado.');
        }

        $resumo = $this->checkoutService->resumo(
            $pedido,
            $config,
            $pedido->endereco,
            (string) $pedido->tipo_frete,
            $pedido->cupom_desconto ?: null
        );

        $this->checkoutService->salvarResumo($pedido, $pedido->endereco, $resumo);
        $totais = $this->checkoutService->totaisPorFormaPagamento($resumo['total'], $config);

        $valor = match ($forma) {
            'pix' => (float) $totais->total_pix,
            'boleto' => (float) $totais->total_boleto,
            'cartao' => (float) $totais->total_cartao,
            default => throw new RuntimeException('Forma de pagamento inválida.'),
        };

        if ($valor <= 0) {
            throw new RuntimeException('O valor final do pedido é inválido.');
        }

        return round($valor, 2);
    }

    private function payer(PedidoEcommerce $pedido, bool $comEndereco): array
    {
        $cliente = $pedido->cliente;
        if (!$cliente) {
            throw new RuntimeException('Cliente do pedido não encontrado.');
        }

        $documento = preg_replace('/\D/', '', (string) $cliente->cpf);
        $payer = [
            'email' => $cliente->email,
            'first_name' => $cliente->nome,
            'last_name' => $cliente->sobre_nome,
            'identification' => [
                'type' => strlen($documento) > 11 ? 'CNPJ' : 'CPF',
                'number' => $documento,
            ],
        ];

        if ($comEndereco) {
            $endereco = $pedido->endereco;
            if (!$endereco) {
                throw new RuntimeException('Endereço do cliente não encontrado para gerar o boleto.');
            }

            $payer['address'] = [
                'zip_code' => preg_replace('/\D/', '', (string) $endereco->cep),
                'street_name' => $endereco->rua,
                'street_number' => (string) $endereco->numero,
                'neighborhood' => $endereco->bairro,
                'city' => $endereco->cidade,
                'federal_unit' => strtoupper((string) $endereco->uf),
            ];
        }

        return $payer;
    }

    private function sincronizarPedido(PedidoEcommerce $pedido, array $payment): void
    {
        $statusAnterior = (string) $pedido->status_pagamento;
        $status = (string) ($payment['status'] ?? $statusAnterior ?: 'pending');

        $pedido->transacao_id = (string) ($payment['id'] ?? $pedido->transacao_id);
        $pedido->status_pagamento = $status;
        $pedido->status_detalhe = (string) ($payment['status_detail'] ?? '');
        $pedido->status = $status === 'approved' ? 2 : 1;
        $pedido->hash = $pedido->hash ?: Str::random(32);

        $transactionData = $payment['point_of_interaction']['transaction_data'] ?? [];
        if (!empty($transactionData['qr_code'])) {
            $pedido->qr_code = $transactionData['qr_code'];
        }
        if (!empty($transactionData['qr_code_base64'])) {
            $pedido->qr_code_base64 = $transactionData['qr_code_base64'];
        }

        $boletoUrl = $transactionData['external_resource_url']
            ?? $payment['transaction_details']['external_resource_url']
            ?? null;
        if ($boletoUrl) {
            $pedido->link_boleto = $boletoUrl;
        }

        $pedido->save();

        if ($status === 'approved' && $statusAnterior !== 'approved') {
            event(new \App\Events\EcommercePaymentApproved($pedido->fresh()));
        }
    }

    private function request(ConfigEcommerce $config): \Illuminate\Http\Client\PendingRequest
    {
        $this->validarCredenciais($config);

        return Http::acceptJson()
            ->asJson()
            ->withToken($config->mercadopago_access_token)
            ->timeout(20)
            ->retry(2, 300, throw: false);
    }

    private function validarCredenciais(ConfigEcommerce $config): void
    {
        if (empty($config->mercadopago_access_token)) {
            throw new RuntimeException('Access Token do Mercado Pago não configurado nesta loja.');
        }
    }

    private function idempotencyKey(PedidoEcommerce $pedido, string $tipo): string
    {
        $token = (string) ($pedido->token ?: $pedido->id);
        return substr(hash('sha256', 'ecommerce|' . $pedido->empresa_id . '|' . $pedido->id . '|' . $tipo . '|' . $token), 0, 64);
    }

    private function externalReference(PedidoEcommerce $pedido): string
    {
        return 'ecommerce:' . $pedido->empresa_id . ':' . $pedido->id;
    }

    private function pedidoIdFromExternalReference(string $reference): ?int
    {
        if (!preg_match('/^ecommerce:(\d+):(\d+)$/', $reference, $m)) {
            return null;
        }

        return (int) $m[2];
    }

    private function notificationUrl(ConfigEcommerce $config): string
    {
        return url('/webhooks/mercadopago/ecommerce/' . $config->id);
    }

    private function descricao(PedidoEcommerce $pedido): string
    {
        return 'Pedido Ecommerce #' . $pedido->id;
    }

    private function temTransacaoReutilizavel(PedidoEcommerce $pedido, string $formaPagamento): bool
    {
        if (!$pedido->transacao_id || strcasecmp((string) $pedido->forma_pagamento, $formaPagamento) !== 0) {
            return false;
        }

        return in_array((string) $pedido->status_pagamento, ['pending', 'in_process', 'approved'], true);
    }

    private function mensagemErroMercadoPago(Response $response): string
    {
        $json = $response->json();
        $message = $json['message'] ?? $json['error'] ?? null;

        if (is_string($message) && trim($message) !== '') {
            return 'Mercado Pago: ' . $message;
        }

        $cause = $json['cause'][0]['description'] ?? null;
        if ($cause) {
            return 'Mercado Pago: ' . $cause;
        }

        return 'Não foi possível processar o pagamento no Mercado Pago.';
    }
}