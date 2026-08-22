<?php

namespace App\Services;

use App\Models\EmpresaIntegracao;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GeminiAgentService
{
    public function generate(EmpresaIntegracao $integracao, string $systemInstruction, string $userMessage): string
    {
        $apiKey = trim((string) config('services.gemini.api_key'));
        if ($apiKey === '') {
            throw new RuntimeException('GEMINI_API_KEY não está configurada.');
        }

        $primaryModel = trim((string) config('services.gemini.model', 'gemini-flash-latest'));
        if ($primaryModel === '') {
            $primaryModel = 'gemini-flash-latest';
        }

        $fallbackModel = trim((string) config('services.gemini.fallback_model', 'gemini-flash-lite-latest'));
        $models = array_values(array_unique(array_filter([$primaryModel, $fallbackModel])));

        $lastQuotaError = null;

        foreach ($models as $model) {
            try {
                return $this->generateWithModel($apiKey, $model, $systemInstruction, $userMessage);
            } catch (GeminiQuotaException $e) {
                $lastQuotaError = $e;
                continue;
            }
        }

        if ($lastQuotaError) {
            $retryAfter = $lastQuotaError->retryAfterSeconds();
            $suffix = $retryAfter !== null
                ? " Aguarde cerca de {$retryAfter} segundos e tente novamente."
                : ' Tente novamente em alguns instantes.';

            throw new RuntimeException(
                'A cota da IA foi atingida temporariamente.' . $suffix .
                ' Se isso acontecer com frequência, habilite faturamento no projeto Gemini ou aumente os limites da API.'
            );
        }

        throw new RuntimeException('Não foi possível obter resposta da IA.');
    }

    private function generateWithModel(string $apiKey, string $model, string $systemInstruction, string $userMessage): string
    {
        $baseUrl = rtrim((string) config(
            'services.gemini.base_url',
            'https://generativelanguage.googleapis.com/v1beta'
        ), '/');

        $maxOutputTokens = max(1024, (int) config('services.gemini.max_output_tokens', 8192));
        $timeout = max(45, (int) config('services.gemini.timeout', 90));

        try {
            $response = Http::baseUrl($baseUrl)
                ->withHeaders(['X-goog-api-key' => $apiKey])
                ->acceptJson()
                ->asJson()
                ->connectTimeout(15)
                ->timeout($timeout)
                ->retry(1, 350, function ($exception) {
                    return $exception instanceof ConnectionException;
                }, false)
                ->post('/models/' . rawurlencode($model) . ':generateContent', [
                    'systemInstruction' => [
                        'parts' => [
                            ['text' => $systemInstruction],
                        ],
                    ],
                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => [
                                ['text' => $userMessage],
                            ],
                        ],
                    ],
                    'generationConfig' => [
                        'maxOutputTokens' => $maxOutputTokens,
                    ],
                ]);
        } catch (ConnectionException $e) {
            throw new RuntimeException('Não foi possível conectar ao Gemini agora. Tente novamente em instantes.');
        }

        if ($response->status() === 429) {
            $message = (string) data_get($response->json(), 'error.message', '');
            throw new GeminiQuotaException($message);
        }

        if ($response->failed()) {
            $message = data_get($response->json(), 'error.message');
            $status = $response->status();

            if (is_string($message) && trim($message) !== '') {
                throw new RuntimeException("Erro Gemini HTTP {$status}: {$message}");
            }

            throw new RuntimeException("Erro Gemini HTTP {$status}. Verifique GEMINI_API_KEY e GEMINI_MODEL.");
        }

        $blockedReason = data_get($response->json(), 'promptFeedback.blockReason');
        if ($blockedReason) {
            throw new RuntimeException('Gemini bloqueou a mensagem: ' . $blockedReason . '.');
        }

        $parts = data_get($response->json(), 'candidates.0.content.parts', []);
        $text = '';

        if (is_array($parts)) {
            foreach ($parts as $part) {
                $partText = is_array($part) ? ($part['text'] ?? null) : null;
                if (is_string($partText)) {
                    $text .= $partText;
                }
            }
        }

        if (trim($text) === '') {
            throw new RuntimeException('Gemini não retornou uma resposta de texto válida.');
        }

        $finishReason = strtoupper((string) data_get($response->json(), 'candidates.0.finishReason', ''));

        if ($finishReason === 'MAX_TOKENS') {
            return rtrim($text) . "\n\n[Resposta interrompida pelo limite do modelo. Refine a consulta ou peça a continuação da análise.]";
        }

        return trim($text);
    }

    public function humanizeOutgoingMessage(EmpresaIntegracao $integracao, string $message, string $type): string
    {
        if (!in_array($type, ['cobranca', 'cobranca_manual', 'ordem_servico', 'ordem_servico_manual'], true)) {
            return $message;
        }

        if ($type === 'cobranca' && !$integracao->agente_cobranca) {
            return $message;
        }

        if ($type === 'ordem_servico' && !$integracao->agente_ordem_servico) {
            return $message;
        }

        $extraInstructions = trim((string) $integracao->agente_instrucoes);

        $system = <<<PROMPT
Você revisa mensagens de WhatsApp para clientes.
Reescreva a mensagem com palavras e estrutura diferentes, deixando-a mais humana, profissional, cordial, curta e natural.
Preserve exatamente valores, datas, vencimentos, links, nomes, referências, números e status.
Nunca invente informações, descontos, pagamentos, prazos ou promessas.
Em cobranças, use tom de lembrete cordial.
Em Ordem de Serviço, apenas melhore a comunicação.
Responda somente com a mensagem final pronta para WhatsApp.
Use português do Brasil e emojis com moderação.

Instruções adicionais:
{$extraInstructions}
PROMPT;

        $input = "TIPO: {$type}\n\nTEXTO ORIGINAL:\n{$message}";
        $rewritten = $this->generate($integracao, $system, $input);

        if ($this->sameMessage($rewritten, $message)) {
            $rewritten = $this->generate(
                $integracao,
                $system,
                $input . "\n\nReescreva novamente com redação visivelmente diferente, sem alterar nenhum dado factual."
            );
        }

        return trim($rewritten) !== '' ? trim($rewritten) : $message;
    }

    private function sameMessage(string $first, string $second): bool
    {
        $normalize = static function (string $value): string {
            $value = preg_replace('/\s+/u', ' ', trim($value));
            return mb_strtolower((string) $value);
        };

        return $normalize($first) === $normalize($second);
    }
}

class GeminiQuotaException extends RuntimeException
{
    public function retryAfterSeconds(): ?int
    {
        if (preg_match('/retry in\s+([0-9]+(?:\.[0-9]+)?)s/i', $this->getMessage(), $matches)) {
            return max(1, (int) ceil((float) $matches[1]));
        }

        return null;
    }
}