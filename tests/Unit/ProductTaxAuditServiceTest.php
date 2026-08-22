<?php

namespace Tests\Unit;

use App\Models\Produto;
use App\Services\OpenAi\ProductTaxAuditService;
use App\Exceptions\ProductTaxAuditException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ProductTaxAuditServiceTest extends TestCase
{
    public function test_it_requests_a_structured_tax_audit(): void
    {
        config()->set('services.openai.api_key', 'test-key');
        config()->set('services.openai.base_url', 'https://api.openai.test/v1');
        config()->set('services.openai.model', 'test-model');
        $analysis = ['status' => 'revisao', 'confianca' => 70, 'resumo' => 'Conferir', 'problemas' => [], 'sugestoes' => [
            'ncm' => null, 'cest' => null, 'cst_csosn' => null, 'cfop_saida_interna' => null,
            'cfop_saida_interestadual' => null, 'cfop_entrada_interna' => null, 'cfop_entrada_interestadual' => null,
        ]];
        Http::fake(['api.openai.test/*' => Http::response(['output_text' => json_encode($analysis)])]);

        $product = new Produto(['nome' => 'Café', 'NCM' => '09012100', 'CST_CSOSN' => '102']);
        $product->id = 10;

        $this->assertSame($analysis, app(ProductTaxAuditService::class)->audit($product));
        Http::assertSent(fn ($request) => $request->url() === 'https://api.openai.test/v1/responses'
            && $request['model'] === 'test-model'
            && data_get($request, 'text.format.type') === 'json_schema');
    }

    public function test_it_reads_text_from_the_responses_api_output_items(): void
    {
        config()->set('services.openai.api_key', 'test-key');
        config()->set('services.openai.base_url', 'https://api.openai.test/v1');
        $analysis = ['status' => 'correto', 'confianca' => 90, 'resumo' => 'Cadastro coerente', 'problemas' => [], 'sugestoes' => []];
        Http::fake(['api.openai.test/*' => Http::response([
            'output' => [
                ['type' => 'reasoning'],
                ['type' => 'message', 'content' => [['type' => 'output_text', 'text' => json_encode($analysis)]]],
            ],
        ])]);

        $this->assertSame($analysis, app(ProductTaxAuditService::class)->audit(new Produto(['nome' => 'Cadeira de plástico'])));
    }

    public function test_it_explains_an_invalid_api_key(): void
    {
        config()->set('services.openai.api_key', 'invalid-key');
        config()->set('services.openai.base_url', 'https://api.openai.test/v1');
        Http::fake(['api.openai.test/*' => Http::response(['error' => ['message' => 'Incorrect API key']], 401)]);

        $this->expectException(ProductTaxAuditException::class);
        $this->expectExceptionMessage('Confira OPENAI_API_KEY');

        app(ProductTaxAuditService::class)->audit(new Produto(['nome' => 'Cadeira de plástico']));
    }
}