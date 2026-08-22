<?php

namespace App\Services\OpenAi;

use App\Models\Produto;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class ProductTaxAuditService
{
    public function audit(Produto $product): array
    {
        $apiKey = trim((string) config('services.gemini.api_key'));

        if ($apiKey === '') {
            throw new RuntimeException('A chave GEMINI_API_KEY não está configurada.');
        }

        $model = trim((string) config('services.gemini.model', 'gemini-flash-latest'));
        if ($model === '') {
            $model = 'gemini-flash-latest';
        }

        $httpResponse = $this->client($apiKey)
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

        if ($httpResponse->failed()) {
            $message = data_get($httpResponse->json(), 'error.message');
            $status = $httpResponse->status();

            if (is_string($message) && trim($message) !== '') {
                throw new RuntimeException("Erro Gemini HTTP {$status}: {$message}");
            }

            throw new RuntimeException("Erro Gemini HTTP {$status}. Verifique a GEMINI_API_KEY, o modelo e a liberação da Gemini API.");
        }

        $response = $httpResponse->json();

        if (!is_array($response)) {
            throw new RuntimeException('O Gemini retornou uma resposta HTTP válida, mas o corpo não é um JSON válido.');
        }

        $blockedReason = data_get($response, 'promptFeedback.blockReason');
        if ($blockedReason) {
            throw new RuntimeException('O Gemini bloqueou a solicitação: ' . $blockedReason . '.');
        }

        $finishReason = data_get($response, 'candidates.0.finishReason');
        if ($finishReason && $finishReason !== 'STOP') {
            throw new RuntimeException('O Gemini não concluiu a análise. Motivo: ' . $finishReason . '.');
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
            throw new RuntimeException('O Gemini não retornou conteúdo para a auditoria tributária.');
        }

        $result = json_decode($text, true);

        if (!is_array($result)) {
            throw new RuntimeException('O Gemini retornou JSON inválido: ' . json_last_error_msg() . '.');
        }

        return $result;
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
            ->connectTimeout(15)
            ->retry(2, 500, null, false);
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
        return 'Atue como revisor de cadastro fiscal brasileiro. Identifique inconsistências prováveis entre descrição, NCM, CEST, CST/CSOSN e CFOP. Considere que CEST não significa automaticamente substituição tributária. Legislação, regime tributário, UF, finalidade da operação e situação da mercadoria podem alterar a conclusão. Marque revisao quando houver dúvida ou dados insuficientes. Explique objetivamente e sugira valores apenas como indicação para conferência por contador. Nunca afirme que o resultado substitui validação fiscal profissional.';
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