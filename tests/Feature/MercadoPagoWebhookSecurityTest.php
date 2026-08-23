<?php

namespace Tests\Feature;

use App\Http\Controllers\ContaReceberMercadoPagoController;
use App\Services\ContaReceberMercadoPagoDirectChargeService;
use App\Services\ContaReceberMercadoPagoService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MercadoPagoWebhookSecurityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => true,
            ],
        ]);

        DB::purge('sqlite');
        DB::setDefaultConnection('sqlite');
        DB::reconnect('sqlite');

        Schema::create('config_ecommerces', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('empresa_id');
            $table->text('mercadopago_access_token')->nullable();
            $table->string('mercadopago_webhook_secret')->nullable();
            $table->timestamps();
        });

        DB::table('config_ecommerces')->insert([
            'id' => 30,
            'empresa_id' => 1,
            'mercadopago_access_token' => 'TEST-TOKEN',
            'mercadopago_webhook_secret' => 'secret-test-key',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_webhook_com_assinatura_valida_processa_pagamento(): void
    {
        $service = $this->getMockBuilder(ContaReceberMercadoPagoService::class)
            ->onlyMethods(['processarWebhook'])
            ->getMock();

        $service->expects($this->once())
            ->method('processarWebhook')
            ->with(30, '999999');

        $controller = $this->controller($service);
        $response = $controller->webhook($this->requestAssinado('999999', true), 30);

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_webhook_com_assinatura_invalida_retorna_401_e_nao_concilia(): void
    {
        $service = $this->getMockBuilder(ContaReceberMercadoPagoService::class)
            ->onlyMethods(['processarWebhook'])
            ->getMock();

        $service->expects($this->never())->method('processarWebhook');

        $controller = $this->controller($service);
        $response = $controller->webhook($this->requestAssinado('999999', false), 30);

        $this->assertSame(401, $response->getStatusCode());
    }

    public function test_falha_transitoria_na_conciliacao_retorna_503_para_permitir_reentrega(): void
    {
        $service = $this->getMockBuilder(ContaReceberMercadoPagoService::class)
            ->onlyMethods(['processarWebhook'])
            ->getMock();

        $service->expects($this->once())
            ->method('processarWebhook')
            ->willThrowException(new \RuntimeException('database temporarily unavailable'));

        $controller = $this->controller($service);
        $response = $controller->webhook($this->requestAssinado('999999', true), 30);

        $this->assertSame(503, $response->getStatusCode());
    }

    private function controller(ContaReceberMercadoPagoService $service): ContaReceberMercadoPagoController
    {
        $directChargeService = $this->createMock(ContaReceberMercadoPagoDirectChargeService::class);
        return new ContaReceberMercadoPagoController($service, $directChargeService);
    }

    private function requestAssinado(string $paymentId, bool $valido): Request
    {
        $timestamp = '1704908010';
        $requestId = 'request-test-123';
        $manifest = 'id:' . strtolower($paymentId)
            . ';request-id:' . $requestId
            . ';ts:' . $timestamp . ';';

        $hash = hash_hmac('sha256', $manifest, 'secret-test-key');
        if (!$valido) {
            $hash = str_repeat('0', 64);
        }

        return Request::create(
            '/webhooks/mercadopago/contas-receber/30',
            'POST',
            [
                'type' => 'payment',
                'data' => ['id' => $paymentId],
            ],
            [],
            [],
            [
                'HTTP_X_SIGNATURE' => 'ts=' . $timestamp . ',v1=' . $hash,
                'HTTP_X_REQUEST_ID' => $requestId,
            ]
        );
    }
}
