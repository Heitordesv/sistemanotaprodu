<?php

namespace Tests\Unit;

use App\Models\EmpresaIntegracao;
use App\Services\EvolutionApiService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class EvolutionApiServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.evolution.api_key' => 'test-api-key',
            'services.evolution.base_url' => 'https://evolution.test',
        ]);
    }

    public function test_envio_de_texto_desativa_previa_para_nao_baixar_link_protegido(): void
    {
        Http::fake([
            'evolution.test/*' => Http::response([
                'key' => ['id' => 'message-id'],
            ]),
        ]);

        $config = new EmpresaIntegracao([
            'evolution_instance' => 'empresa-10',
        ]);

        (new EvolutionApiService())->sendText(
            $config,
            '11999999999',
            'Acompanhe sua OS: https://sistema.test/ordem-servico/10'
        );

        Http::assertSent(function ($request) {
            return $request->url() === 'https://evolution.test/message/sendText/empresa-10'
                && $request['number'] === '5511999999999'
                && $request['linkPreview'] === false
                && $request['text'] === 'Acompanhe sua OS: https://sistema.test/ordem-servico/10';
        });
    }

    public function test_formato_alternativo_tambem_desativa_previa_de_link(): void
    {
        Http::fakeSequence()
            ->push(['message' => 'payload invalido'], 422)
            ->push(['key' => ['id' => 'message-id']], 200);

        $config = new EmpresaIntegracao([
            'evolution_instance' => 'empresa-10',
        ]);

        (new EvolutionApiService())->sendText(
            $config,
            '5511999999999',
            'Mensagem da OS com https://sistema.test/arquivo-protegido'
        );

        Http::assertSentCount(2);
        Http::assertSent(function ($request) {
            return data_get($request->data(), 'textMessage.text') === 'Mensagem da OS com https://sistema.test/arquivo-protegido'
                && $request['linkPreview'] === false;
        });
    }

    public function test_conexao_fechada_retorna_orientacao_sem_tentar_outro_payload(): void
    {
        Http::fake([
            'evolution.test/*' => Http::response([
                'status' => 400,
                'error' => 'Bad Request',
                'response' => ['message' => ['Error: Connection Closed']],
            ], 400),
        ]);

        $config = new EmpresaIntegracao([
            'evolution_instance' => 'empresa-10',
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Reconecte o aparelho');

        try {
            (new EvolutionApiService())->sendText($config, '5511999999999', 'Mensagem da OS');
        } finally {
            Http::assertSentCount(1);
        }
    }
}
