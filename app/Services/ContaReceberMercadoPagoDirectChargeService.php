<?php

namespace App\Services;

use App\Models\ConfigEcommerce;
use App\Models\ContaReceber;
use App\Models\Empresa;
use Carbon\Carbon;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class ContaReceberMercadoPagoDirectChargeService
{
    private const API = 'https://api.mercadopago.com';

    private const ACTIVE_STATUSES = [
        'pending',
        'in_process',
        'authorized',
        'action_required',
    ];

    private const FINAL_STATUSES = [
        'cancelled',
        'rejected',
        'refunded',
        'charged_back',
    ];

    public function __construct(private ContaReceberMercadoPagoService $syncService)
    {
    }

    public function gerarPix(ContaReceber $conta): array
    {
        return $this->gerar($conta, 'pix');
    }

    public function gerarBoleto(ContaReceber $conta): array
    {
        return $this->gerar($conta, 'boleto');
    }

    private function gerar(ContaReceber $conta, string $tipo, int $depth = 0): array
    {
        if ($depth > 2) {
            throw new RuntimeException('Não foi possível estabilizar a cobrança anterior do Mercado Pago. Consulte o status antes de tentar novamente.');
        }

        if (!in_array($tipo, ['pix', 'boleto'], true)) {
            throw new RuntimeException('Forma de cobrança direta inválida.');
        }

        $config = $this->config($conta);
        $preparo = $this->prepararTentativa($conta, $tipo);
        $conta = $preparo['conta'];

        if (!empty($preparo['payment_id'])) {
            $payment = $this->buscarPagamento($config, (string) $preparo['payment_id']);
            return $this->resolverPagamentoEncontrado($conta, $config, $payment, $tipo, $depth);
        }

        $externalReference = (string) $preparo['external_reference'];
        $idempotencyKey = (string) $preparo['idempotency_key'];
        $valorPreparado = (float) $preparo['valor'];
        $tipoPreparado = (string) $preparo['tipo_preparado'];

        // Primeiro tenta recuperar uma cobrança criada pelo provedor em uma
        // tentativa anterior cuja resposta tenha se perdido por timeout/rede.
        $existente = $this->buscarPorReferencia($config, $externalReference);
        if ($existente) {
            return $this->resolverPagamentoEncontrado($conta, $config, $existente, $tipo, $depth);
        }

        if ($tipoPreparado !== $tipo) {
            $this->limparTentativaSemPagamento($conta, $externalReference, 'payment_method_changed');
            return $this->gerar($conta->fresh(), $tipo, $depth + 1);
        }

        $saldoAtual = $this->saldoRestante($conta->fresh());
        if (abs($saldoAtual - $valorPreparado) > 0.009) {
            // Não cria uma nova cobrança com valor diferente enquanto existe uma
            // tentativa anterior sem resultado confirmado. Isso evita cobrar um
            // saldo antigo caso o Mercado Pago tenha processado a primeira chamada.
            throw new RuntimeException('Existe uma tentativa anterior do Mercado Pago sem confirmação e o saldo da conta mudou. Consulte o status antes de gerar outra cobrança.');
        }

        $payer = $this->payer($conta, $tipo === 'boleto');
        $payload = [
            'transaction_amount' => $valorPreparado,
            'description' => $this->descricao($conta),
            'external_reference' => $externalReference,
            'payment_method_id' => $tipo === 'pix' ? 'pix' : 'bolbradesco',
            'notification_url' => route('conta-receber.mp.webhook', ['configId' => $config->id]),
            'payer' => $payer,
        ];

        if ($tipo === 'boleto') {
            $payload['date_of_expiration'] = $this->vencimentoBoleto($conta)->toIso8601String();
        }

        try {
            $response = $this->request($config)
                ->withHeaders(['X-Idempotency-Key' => $idempotencyKey])
                ->post(self::API . '/v1/payments', $payload);
        } catch (\Throwable $e) {
            $this->marcarFalhaDaTentativa($conta, $externalReference, $idempotencyKey, 'connection_error');
            throw new RuntimeException('Não foi possível confirmar com o Mercado Pago se a cobrança foi criada. Tente consultar o status antes de gerar novamente.', 0, $e);
        }

        if (!$response->successful()) {
            $this->marcarFalhaDaTentativa($conta, $externalReference, $idempotencyKey, 'provider_error');
            $this->validarResposta($response, 'gerar ' . strtoupper($tipo));
        }

        $payment = (array) $response->json();
        $paymentId = (string) ($payment['id'] ?? '');

        if ($paymentId === '') {
            $this->marcarFalhaDaTentativa($conta, $externalReference, $idempotencyKey, 'missing_payment_id');
            throw new RuntimeException('O Mercado Pago não retornou o identificador da cobrança. Consulte o status antes de tentar novamente.');
        }

        if ((string) ($payment['external_reference'] ?? '') !== $externalReference) {
            throw new RuntimeException('O Mercado Pago retornou uma referência diferente da tentativa registrada.');
        }

        $this->persistirIdentidadePagamento($conta, $externalReference, $idempotencyKey, $payment);

        // A sincronização oficial continua centralizada no serviço já existente,
        // que valida empresa/conta, atualiza QR code/boleto e registra a baixa.
        return $this->syncService->consultar($conta->fresh());
    }

    private function prepararTentativa(ContaReceber $conta, string $tipo): array
    {
        return DB::transaction(function () use ($conta, $tipo) {
            $locked = ContaReceber::whereKey($conta->id)
                ->where('empresa_id', $conta->empresa_id)
                ->lockForUpdate()
                ->firstOrFail();

            $this->validarConta($locked);

            if (
                $locked->mercadopago_preference_id
                && in_array((string) $locked->mercadopago_payment_method, ['cartao', 'checkout'], true)
                && !in_array((string) $locked->mercadopago_status, array_merge(self::FINAL_STATUSES, ['approved']), true)
            ) {
                throw new RuntimeException('Existe um checkout do Mercado Pago ainda ativo para esta conta. Finalize essa cobrança antes de gerar PIX ou boleto.');
            }

            if ($locked->mercadopago_payment_id) {
                return [
                    'conta' => $locked,
                    'payment_id' => (string) $locked->mercadopago_payment_id,
                ];
            }

            if (
                $locked->mercadopago_idempotency_key
                && $locked->mercadopago_external_reference
                && in_array((string) $locked->mercadopago_status, ['creating', 'request_failed'], true)
            ) {
                $valor = $this->valorDaReferencia((string) $locked->mercadopago_external_reference);

                return [
                    'conta' => $locked,
                    'payment_id' => null,
                    'idempotency_key' => (string) $locked->mercadopago_idempotency_key,
                    'external_reference' => (string) $locked->mercadopago_external_reference,
                    'valor' => $valor ?? $this->saldoRestante($locked),
                    'tipo_preparado' => (string) $locked->mercadopago_payment_method,
                ];
            }

            $valor = $this->saldoRestante($locked);
            $idempotencyKey = (string) Str::uuid();
            $externalReference = $this->externalReference($locked, $tipo, $valor, $idempotencyKey);

            $locked->mercadopago_external_reference = $externalReference;
            $locked->mercadopago_idempotency_key = $idempotencyKey;
            $locked->mercadopago_payment_method = $tipo;
            $locked->mercadopago_payment_id = null;
            $locked->mercadopago_preference_id = null;
            $locked->mercadopago_checkout_url = null;
            $locked->mercadopago_status = 'creating';
            $locked->mercadopago_status_detail = 'request_pending';
            $locked->mercadopago_last_sync_at = now();
            $this->garantirTokenPublico($locked);
            $locked->save();

            return [
                'conta' => $locked,
                'payment_id' => null,
                'idempotency_key' => $idempotencyKey,
                'external_reference' => $externalReference,
                'valor' => $valor,
                'tipo_preparado' => $tipo,
            ];
        });
    }

    private function resolverPagamentoEncontrado(
        ContaReceber $conta,
        ConfigEcommerce $config,
        array $payment,
        string $tipoDesejado,
        int $depth
    ): array {
        $externalReference = (string) ($payment['external_reference'] ?? '');
        if (!$this->referenciaPertenceAConta($externalReference, $conta)) {
            throw new RuntimeException('Pagamento encontrado não pertence à conta a receber informada.');
        }

        $status = (string) ($payment['status'] ?? '');
        $paymentId = (string) ($payment['id'] ?? '');
        $valorPagamento = round((float) ($payment['transaction_amount'] ?? 0), 2);
        $saldoAtual = $this->saldoRestante($conta->fresh());
        $tipoPagamento = $this->tipoDoPagamento($payment);

        if ($status === 'approved') {
            $this->persistirIdentidadePagamento(
                $conta,
                $externalReference,
                (string) ($conta->mercadopago_idempotency_key ?? ''),
                $payment
            );
            return $this->syncService->consultar($conta->fresh());
        }

        if (in_array($status, self::ACTIVE_STATUSES, true)) {
            if ($tipoPagamento === $tipoDesejado && abs($valorPagamento - $saldoAtual) <= 0.009) {
                $this->persistirIdentidadePagamento(
                    $conta,
                    $externalReference,
                    (string) ($conta->mercadopago_idempotency_key ?? ''),
                    $payment
                );
                return $this->syncService->consultar($conta->fresh());
            }

            if (!in_array($status, ['pending', 'in_process', 'authorized'], true) || $paymentId === '') {
                throw new RuntimeException('Existe uma cobrança ativa do Mercado Pago que não pode ser substituída automaticamente. Consulte o status antes de gerar outra.');
            }

            $this->cancelarPagamento($config, $paymentId);
            $this->limparCobrancaAtual($conta, $externalReference, 'replaced_after_balance_or_method_change');
            return $this->gerar($conta->fresh(), $tipoDesejado, $depth + 1);
        }

        if (in_array($status, self::FINAL_STATUSES, true)) {
            $this->limparCobrancaAtual($conta, $externalReference, 'previous_payment_finalized_without_approval');
            return $this->gerar($conta->fresh(), $tipoDesejado, $depth + 1);
        }

        throw new RuntimeException('O Mercado Pago retornou um status de cobrança que exige conferência antes de uma nova tentativa.');
    }

    private function persistirIdentidadePagamento(
        ContaReceber $conta,
        string $externalReference,
        string $idempotencyKey,
        array $payment
    ): void {
        DB::transaction(function () use ($conta, $externalReference, $idempotencyKey, $payment) {
            $locked = ContaReceber::whereKey($conta->id)
                ->where('empresa_id', $conta->empresa_id)
                ->lockForUpdate()
                ->firstOrFail();

            $paymentId = (string) ($payment['id'] ?? '');
            if ($paymentId === '') {
                throw new RuntimeException('Pagamento do Mercado Pago sem identificador.');
            }

            if (
                $locked->mercadopago_external_reference
                && (string) $locked->mercadopago_external_reference !== $externalReference
            ) {
                throw new RuntimeException('A tentativa de cobrança foi alterada por outra requisição.');
            }

            $locked->mercadopago_payment_id = $paymentId;
            $locked->mercadopago_external_reference = $externalReference;
            if ($idempotencyKey !== '') {
                $locked->mercadopago_idempotency_key = $idempotencyKey;
            }
            $locked->mercadopago_status = (string) ($payment['status'] ?? $locked->mercadopago_status);
            $locked->mercadopago_status_detail = (string) ($payment['status_detail'] ?? '');
            $locked->mercadopago_last_sync_at = now();
            $locked->save();
        });
    }

    private function marcarFalhaDaTentativa(
        ContaReceber $conta,
        string $externalReference,
        string $idempotencyKey,
        string $detail
    ): void {
        DB::transaction(function () use ($conta, $externalReference, $idempotencyKey, $detail) {
            $locked = ContaReceber::whereKey($conta->id)
                ->where('empresa_id', $conta->empresa_id)
                ->lockForUpdate()
                ->first();

            if (!$locked) {
                return;
            }

            if (
                (string) $locked->mercadopago_external_reference !== $externalReference
                || (string) $locked->mercadopago_idempotency_key !== $idempotencyKey
                || $locked->mercadopago_payment_id
            ) {
                return;
            }

            $locked->mercadopago_status = 'request_failed';
            $locked->mercadopago_status_detail = $detail;
            $locked->mercadopago_last_sync_at = now();
            $locked->save();
        });
    }

    private function limparTentativaSemPagamento(ContaReceber $conta, string $externalReference, string $detail): void
    {
        DB::transaction(function () use ($conta, $externalReference, $detail) {
            $locked = ContaReceber::whereKey($conta->id)
                ->where('empresa_id', $conta->empresa_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->mercadopago_payment_id) {
                return;
            }

            if ((string) $locked->mercadopago_external_reference !== $externalReference) {
                return;
            }

            $this->zerarCamposCobrancaDireta($locked, $detail);
            $locked->save();
        });
    }

    private function limparCobrancaAtual(ContaReceber $conta, string $externalReference, string $detail): void
    {
        DB::transaction(function () use ($conta, $externalReference, $detail) {
            $locked = ContaReceber::whereKey($conta->id)
                ->where('empresa_id', $conta->empresa_id)
                ->lockForUpdate()
                ->firstOrFail();

            if (
                $locked->mercadopago_external_reference
                && (string) $locked->mercadopago_external_reference !== $externalReference
            ) {
                return;
            }

            $this->zerarCamposCobrancaDireta($locked, $detail);
            $locked->save();
        });
    }

    private function zerarCamposCobrancaDireta(ContaReceber $conta, string $detail): void
    {
        $conta->mercadopago_payment_id = null;
        $conta->mercadopago_external_reference = null;
        $conta->mercadopago_idempotency_key = null;
        $conta->mercadopago_status = 'cancelled';
        $conta->mercadopago_status_detail = $detail;
        $conta->mercadopago_ticket_url = null;
        $conta->mercadopago_digitable_line = null;
        $conta->mercadopago_qr_code = null;
        $conta->mercadopago_qr_code_base64 = null;
        $conta->boleto_link = null;
        $conta->chave_pix = null;
        $conta->mercadopago_last_sync_at = now();
    }

    private function buscarPagamento(ConfigEcommerce $config, string $paymentId): array
    {
        $response = $this->request($config)
            ->get(self::API . '/v1/payments/' . urlencode($paymentId));

        $this->validarResposta($response, 'consultar pagamento existente');
        return (array) $response->json();
    }

    private function buscarPorReferencia(ConfigEcommerce $config, string $externalReference): ?array
    {
        try {
            $response = $this->request($config)
                ->get(self::API . '/v1/payments/search', [
                    'external_reference' => $externalReference,
                    'sort' => 'date_created',
                    'criteria' => 'desc',
                    'limit' => 1,
                ]);
        } catch (\Throwable $e) {
            return null;
        }

        if ($response->failed()) {
            return null;
        }

        $results = $response->json('results') ?: [];
        return isset($results[0]) ? (array) $results[0] : null;
    }

    private function cancelarPagamento(ConfigEcommerce $config, string $paymentId): void
    {
        $response = $this->request($config)
            ->put(self::API . '/v1/payments/' . urlencode($paymentId), ['status' => 'cancelled']);

        $this->validarResposta($response, 'cancelar cobrança anterior');
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

        Log::warning('Falha Mercado Pago ao processar cobrança direta.', [
            'acao' => $acao,
            'http_status' => $response->status(),
            'message' => Str::limit($message, 250, ''),
        ]);

        throw new RuntimeException('Mercado Pago: ' . $message);
    }

    private function validarConta(ContaReceber $conta): void
    {
        if ((int) $conta->status === 1 || $this->saldoRestante($conta) <= 0.009) {
            throw new RuntimeException('Esta conta já está recebida.');
        }

        if ((float) $conta->valor_integral <= 0) {
            throw new RuntimeException('O valor da conta deve ser maior que zero.');
        }
    }

    private function saldoRestante(ContaReceber $conta): float
    {
        return round(max(0, (float) $conta->valor_integral - (float) $conta->valor_recebido), 2);
    }

    private function externalReference(ContaReceber $conta, string $tipo, float $valor, string $idempotencyKey): string
    {
        $centavos = (int) round($valor * 100);
        $tentativa = str_replace('-', '', $idempotencyKey);

        return sprintf(
            'CR:%d:%d:%s:A%d:%s',
            (int) $conta->empresa_id,
            (int) $conta->id,
            $tipo,
            $centavos,
            substr($tentativa, 0, 16)
        );
    }

    private function valorDaReferencia(string $reference): ?float
    {
        $parts = explode(':', $reference);
        if (count($parts) < 6 || !str_starts_with((string) $parts[4], 'A')) {
            return null;
        }

        $centavos = substr((string) $parts[4], 1);
        if ($centavos === '' || !ctype_digit($centavos)) {
            return null;
        }

        return round(((int) $centavos) / 100, 2);
    }

    private function referenciaPertenceAConta(string $reference, ContaReceber $conta): bool
    {
        $parts = explode(':', $reference);

        return count($parts) >= 4
            && $parts[0] === 'CR'
            && (int) $parts[1] === (int) $conta->empresa_id
            && (int) $parts[2] === (int) $conta->id;
    }

    private function tipoDoPagamento(array $payment): string
    {
        $methodId = (string) ($payment['payment_method_id'] ?? '');
        $paymentType = (string) ($payment['payment_type_id'] ?? '');

        if ($methodId === 'pix' || $paymentType === 'bank_transfer') {
            return 'pix';
        }

        if ($methodId === 'bolbradesco' || str_contains($methodId, 'boleto') || $paymentType === 'ticket') {
            return 'boleto';
        }

        return 'outro';
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

    private function garantirTokenPublico(ContaReceber $conta): void
    {
        if (!$conta->mercadopago_public_token) {
            $conta->mercadopago_public_token = Str::random(48);
        }
    }
}
