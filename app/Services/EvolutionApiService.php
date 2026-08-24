<?php

namespace App\Services;

use App\Models\EmpresaIntegracao;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class EvolutionApiService
{
    protected function client(): PendingRequest
    {
        $apiKey = (string) config('services.evolution.api_key');

        if ($apiKey === '') {
            throw new RuntimeException('EVOLUTION_API_KEY não configurada no servidor.');
        }

        return Http::timeout(30)
            ->acceptJson()
            ->asJson()
            ->withHeaders(['apikey' => $apiKey]);
    }

    protected function url(string $path): string
    {
        $baseUrl = rtrim((string) config('services.evolution.base_url'), '/');

        if ($baseUrl === '') {
            throw new RuntimeException('EVOLUTION_API_URL não configurada no servidor.');
        }

        return $baseUrl . '/' . ltrim($path, '/');
    }

    protected function ensureSuccess($response, string $action): array
    {
        if (!$response->successful()) {
            throw new RuntimeException($action . ' falhou: HTTP ' . $response->status() . ' - ' . $response->body());
        }

        return $response->json() ?? [];
    }

    public function createInstance(EmpresaIntegracao $config): array
    {
        $instance = $config->evolution_instance ?: ('nfenotas-' . $config->empresa_id);

        $response = $this->client()->post($this->url('/instance/create'), [
            'instanceName' => $instance,
            'integration' => 'WHATSAPP-BAILEYS',
            'qrcode' => true,
        ]);

        return $this->ensureSuccess($response, 'Criação da instância Evolution');
    }

    public function connect(EmpresaIntegracao $config): array
    {
        $response = $this->client()->get(
            $this->url('/instance/connect/' . rawurlencode($config->evolution_instance))
        );

        return $this->ensureSuccess($response, 'Conexão/QR Code da instância Evolution');
    }

    public function connectionState(EmpresaIntegracao $config): array
    {
        $response = $this->client()->get(
            $this->url('/instance/connectionState/' . rawurlencode($config->evolution_instance))
        );

        return $this->ensureSuccess($response, 'Consulta do estado da instância Evolution');
    }

    public function sendText(EmpresaIntegracao $config, string $number, string $text): array
    {
        $number = $this->normalizeWhatsappNumber($number);
        $endpoint = $this->url('/message/sendText/' . rawurlencode($config->evolution_instance));

        // Formato aceito por várias versões da Evolution API.
        $response = $this->client()->post($endpoint, [
            'number' => $number,
            'textMessage' => [
                'text' => $text,
            ],
            'delay' => 1200,
            // A Evolution tenta baixar todo link quando a previsualizacao esta
            // ativa. Links autenticados/temporarios (como os usados nas OS)
            // retornam 403 e algumas versoes abortam o envio da mensagem.
            'linkPreview' => false,
        ]);

        if ($response->successful()) {
            return $response->json() ?? [];
        }

        $firstStatus = $response->status();
        $firstBody = $response->body();

        // Algumas versões 2.x usam `text` diretamente no corpo em vez de `textMessage`.
        // Tenta automaticamente o segundo formato em erros de validação/implementação.
        if (in_array($firstStatus, [400, 422, 500], true)) {
            $response = $this->client()->post($endpoint, [
                'number' => $number,
                'text' => $text,
                'delay' => 1200,
                'linkPreview' => false,
            ]);

            if ($response->successful()) {
                return $response->json() ?? [];
            }

            throw new RuntimeException(
                'Envio de mensagem pela Evolution falhou nos dois formatos. '
                . 'Formato textMessage: HTTP ' . $firstStatus . ' - ' . $firstBody
                . ' | Formato text: HTTP ' . $response->status() . ' - ' . $response->body()
            );
        }

        return $this->ensureSuccess($response, 'Envio de mensagem pela Evolution');
    }

    private function normalizeWhatsappNumber(string $number): string
    {
        $number = preg_replace('/\D/', '', $number) ?: '';

        if ($number === '') {
            throw new RuntimeException('Número de WhatsApp vazio ou inválido.');
        }

        // Corrige o caso comum em que o controller acrescentou 55 a um número
        // que já estava salvo com o DDI 55 (ex.: 555511999999999).
        if (strlen($number) > 13 && str_starts_with($number, '5555')) {
            $number = substr($number, 2);
        }

        // Para números brasileiros salvos apenas com DDD + telefone.
        if (!str_starts_with($number, '55') && in_array(strlen($number), [10, 11], true)) {
            $number = '55' . $number;
        }

        return $number;
    }

    public function configureWebhook(EmpresaIntegracao $config, string $webhookUrl): array
    {
        if (!$config->evolution_webhook_secret) {
            throw new RuntimeException('Segredo do webhook Evolution não configurado.');
        }

        $endpoint = $this->url('/webhook/set/' . rawurlencode($config->evolution_instance));
        $events = [
            'MESSAGES_UPSERT',
            'MESSAGES_UPDATE',
            'CONNECTION_UPDATE',
        ];
        $headers = [
            'X-NFeNotas-Webhook-Secret' => $config->evolution_webhook_secret,
        ];

        // Formato usado pelas versões mais novas/documentação atual.
        $response = $this->client()->post($endpoint, [
            'enabled' => true,
            'url' => $webhookUrl,
            'events' => $events,
            'headers' => $headers,
            'base64' => false,
        ]);

        if ($response->successful()) {
            return $response->json() ?? [];
        }

        // Algumas versões 2.x exigem toda a configuração dentro de "webhook".
        // Ex.: HTTP 400 "instance requires property \"webhook\"".
        if (
            $response->status() === 400
            && str_contains(strtolower($response->body()), 'webhook')
            && str_contains(strtolower($response->body()), 'requires property')
        ) {
            $response = $this->client()->post($endpoint, [
                'webhook' => [
                    'enabled' => true,
                    'url' => $webhookUrl,
                    'byEvents' => false,
                    'base64' => false,
                    'events' => $events,
                    'headers' => $headers,
                ],
            ]);
        }

        return $this->ensureSuccess($response, 'Configuração de webhook da Evolution');
    }
}
