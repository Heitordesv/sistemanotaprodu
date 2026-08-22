<?php

namespace App\Services\Gemini;

use App\Models\Produto;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class ProductTaxAuditService
{
    public function audit(Produto $product): array
    {
        $apiKey = trim((string) config('services.gemini.api_key'));

        if ($apiKey === '') {
            throw new RuntimeException('CONFIG: A GEMINI_API_KEY não está configurada no servidor.');
        }

        $models = $this->models();
        $lastError = null;

        foreach ($models as $index => $model) {
            $httpResponse = $this->request($apiKey, $model, $product);

            if ($httpResponse->successful()) {
                return $this->parseSuccessfulResponse($httpResponse, $model);
            }

            $status = $httpResponse->status();
            $message = trim((string) data_get($httpResponse->json(), 'error.message'));
            $lastError = $this->formatHttpError($status, $message, $model);

            // 401/403 normalmente indicam chave inválida, restrição ou API sem permissão.
            // Trocar de modelo não resolveria e só faria outra chamada desnecessária.
            if (in_array($status, [400, 401, 403], true)) {
                throw new RuntimeException($lastError);
            }

            // 404 pode ser modelo indisponível; 429 e 5xx podem ser contornados
            // pelo modelo fallback configurado, quando existir.
            $hasFallback = isset($models[$index + 1]);

            if ($hasFallback && ($status === 404 || $status === 429 || $status >= 500)) {
                continue;
            }

            // Se a cota também acabou no fallback, devolvemos um resultado estruturado.
            // Assim a tela informa a causa real em vez de acusar incorretamente a API key.
            if ($status === 429) {
                return $this->quotaResult($message);
            }

            throw new RuntimeException($lastError);
        }

        throw new RuntimeException($lastError ?: 'UNAVAILABLE: Não foi possível concluir a auditoria com o Gemini.');
    }

    protected function request(string $apiKey, string $model, Produto $product): Response
    {
        return $this->client($apiKey)
            ->post('/models/' . rawurlencode($model) . ':generateContent', [
                'systemInstruction' => [
                    'parts' => [
                        ['text' => $this->instructions()],
                    ],
                ],
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [
                            [
                                'text' => json_encode(
                                    $this->productData($product),
                                    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                                ),
                            ],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'responseMimeType' => 'application/json',
                    'responseJsonSchema' => $this->schema(),
                ],
            ]);
    }

    protected function client(string $apiKey): PendingRequest
    {
        $baseUrl = rtrim((string) config(
            'services.gemini.base_url',
            'https://generativelanguage.googleapis.com/v1beta'
        ), '/');

        if ($baseUrl === '') {
            $baseUrl = 'https://generativelanguage.googleapis.com/v1beta';
        }

        return Http::baseUrl($baseUrl)
            ->withHeaders([
                'X-goog-api-key' => $apiKey,
            ])
            ->acceptJson()
            ->asJson()
            ->timeout(60)
            ->connectTimeout(15);
    }

    private function models(): array
    {
        $primary = trim((string) config('services.gemini.model', 'gemini-3.6-flash'));
        $fallback = trim((string) config('services.gemini.fallback_model', 'gemini-3.5-flash-lite'));

        $models = array_values(array_unique(array_filter([
            $primary,
            $fallback,
        ], fn ($model) => is_string($model) && trim($model) !== '')));

        if ($models === []) {
            return ['gemini-3.6-flash'];
        }

        return $models;
    }

    private function parseSuccessfulResponse(Response $httpResponse, string $model): array
    {
        $response = $httpResponse->json();

        if (!is_array($response)) {
            throw new RuntimeException('INVALID_RESPONSE: O Gemini retornou um corpo que não é JSON válido.');
        }

        $blockedReason = data_get($response, 'promptFeedback.blockReason');
        if ($blockedReason) {
            throw new RuntimeException('BLOCKED: O Gemini bloqueou a solicitação: ' . $blockedReason . '.');
        }

        $finishReason = data_get($response, 'candidates.0.finishReason');
        if ($finishReason && $finishReason !== 'STOP') {
            throw new RuntimeException('INCOMPLETE: O Gemini não concluiu a análise. Motivo: ' . $finishReason . '.');
        }

        $parts = data_get($response, 'candidates.0.content.parts', []);
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
            throw new RuntimeException("EMPTY_RESPONSE: O Gemini ({$model}) não retornou conteúdo para a auditoria tributária.");
        }

        $result = json_decode($text, true);

        if (!is_array($result)) {
            throw new RuntimeException('INVALID_JSON: O Gemini retornou JSON inválido: ' . json_last_error_msg() . '.');
        }

        return $result;
    }

    private function quotaResult(string $message = ''): array
    {
        $problema = 'O limite de uso da API Gemini foi atingido. A auditoria tributária deste produto não foi executada agora.';

        if ($message !== '' && preg_match('/retry[^0-9]*([0-9]+(?:\.[0-9]+)?)\s*s/i', $message, $matches)) {
            $segundos = (int) ceil((float) $matches[1]);
            $problema .= " Tente novamente em aproximadamente {$segundos} segundos.";
        }

        return [
            'status' => 'revisao',
            'confianca' => 0,
            'resumo' => 'Auditoria não executada por limite temporário da API Gemini.',
            'problemas' => [$problema],
            'sugestoes' => [
                'ncm' => null,
                'cest' => null,
                'cst_csosn' => null,
                'cfop_saida_interna' => null,
                'cfop_saida_interestadual' => null,
                'cfop_entrada_interna' => null,
                'cfop_entrada_interestadual' => null,
            ],
        ];
    }

    private function formatHttpError(int $status, string $message, string $model): string
    {
        $detail = $message !== '' ? ' Detalhe: ' . $message : '';

        return match (true) {
            $status === 401 || $status === 403 => "AUTH: O Gemini recusou a credencial ou a permissão da API (HTTP {$status}, modelo {$model}).{$detail}",
            $status === 404 => "MODEL: O modelo Gemini configurado não foi encontrado ou não está disponível (HTTP 404, modelo {$model}).{$detail}",
            $status === 429 => "QUOTA: O limite de uso/cota do Gemini foi atingido (HTTP 429, modelo {$model}).{$detail}",
            $status >= 500 => "UNAVAILABLE: O serviço Gemini está temporariamente indisponível (HTTP {$status}, modelo {$model}).{$detail}",
            default => "HTTP_ERROR: Erro Gemini HTTP {$status} no modelo {$model}.{$detail}",
        };
    }

    private function productData(Produto $product): array
    {
        return [
            'id' => $product->id,
            'descricao' => $product->nome,
            'codigo_barras' => $product->codBarras,
            'categoria' => optional($product->categoria)->nome,
            'ncm' => $product->NCM,
            'cest' => $product->CEST,
            'cst_csosn' => $product->CST_CSOSN,
            'cfop_saida_interna' => $product->CFOP_saida_estadual,
            'cfop_saida_interestadual' => $product->CFOP_saida_inter_estadual,
            'cfop_entrada_interna' => $product->CFOP_entrada_estadual,
            'cfop_entrada_interestadual' => $product->CFOP_entrada_inter_estadual,
        ];
    }

    private function instructions(): string
    {
        return 'Atue como revisor de cadastro fiscal brasileiro. Analise exclusivamente os campos recebidos no JSON: descrição, categoria, código de barras, NCM, CEST, CST/CSOSN e CFOP de entrada e saída. Não amplie a análise para outros tributos, campos ou modelos fiscais que não tenham sido enviados. Identifique inconsistências prováveis entre os dados recebidos. Considere que CEST não significa automaticamente substituição tributária. Legislação, regime tributário, UF, finalidade da operação e situação da mercadoria podem alterar a conclusão. Marque revisao quando houver dúvida ou dados insuficientes. Explique objetivamente e sugira valores apenas como indicação para conferência por contador. Nunca afirme que o resultado substitui validação fiscal profissional.';
    }

    private function schema(): array
    {
        return [
            'type' => 'object',
            'additionalProperties' => false,
            'properties' => [
                'status' => [
                    'type' => 'string',
                    'enum' => ['correto', 'revisao', 'incorreto'],
                ],
                'confianca' => [
                    'type' => 'integer',
                    'minimum' => 0,
                    'maximum' => 100,
                ],
                'resumo' => ['type' => 'string'],
                'problemas' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                ],
                'sugestoes' => [
                    'type' => 'object',
                    'additionalProperties' => false,
                    'properties' => [
                        'ncm' => ['type' => ['string', 'null']],
                        'cest' => ['type' => ['string', 'null']],
                        'cst_csosn' => ['type' => ['string', 'null']],
                        'cfop_saida_interna' => ['type' => ['string', 'null']],
                        'cfop_saida_interestadual' => ['type' => ['string', 'null']],
                        'cfop_entrada_interna' => ['type' => ['string', 'null']],
                        'cfop_entrada_interestadual' => ['type' => ['string', 'null']],
                    ],
                    'required' => [
                        'ncm',
                        'cest',
                        'cst_csosn',
                        'cfop_saida_interna',
                        'cfop_saida_interestadual',
                        'cfop_entrada_interna',
                        'cfop_entrada_interestadual',
                    ],
                ],
            ],
            'required' => [
                'status',
                'confianca',
                'resumo',
                'problemas',
                'sugestoes',
            ],
        ];
    }
}