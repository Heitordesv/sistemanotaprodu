<?php

namespace Tests\Feature;

use App\Models\ContaReceber;
use App\Services\ContaReceberMercadoPagoDirectChargeService;
use App\Services\ContaReceberMercadoPagoService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MercadoPagoGeracaoIdempotenteTest extends TestCase
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

        Schema::create('clientes', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('razao_social')->nullable();
            $table->string('nome_fantasia')->nullable();
            $table->string('cpf_cnpj')->nullable();
            $table->string('email')->nullable();
            $table->string('celular')->nullable();
            $table->string('telefone')->nullable();
            $table->timestamps();
        });

        Schema::create('config_ecommerces', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('empresa_id');
            $table->text('mercadopago_access_token')->nullable();
            $table->string('mercadopago_webhook_secret')->nullable();
            $table->timestamps();
        });

        Schema::create('conta_recebers', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('cliente_id')->nullable();
            $table->unsignedBigInteger('venda_id')->nullable();
            $table->unsignedBigInteger('empresa_id_emp')->nullable();
            $table->date('data_vencimento')->nullable();
            $table->string('referencia')->nullable();
            $table->decimal('valor_integral', 15, 2)->default(0);
            $table->decimal('valor_recebido', 15, 2)->default(0);
            $table->boolean('status')->default(false);
            $table->string('tipo_pagamento', 10)->nullable();
            $table->timestamp('data_recebimento')->nullable();
            $table->string('mercadopago_payment_id')->nullable();
            $table->string('mercadopago_preference_id')->nullable();
            $table->string('mercadopago_external_reference')->nullable();
            $table->string('mercadopago_payment_method')->nullable();
            $table->string('mercadopago_status')->nullable();
            $table->string('mercadopago_status_detail')->nullable();
            $table->text('mercadopago_ticket_url')->nullable();
            $table->text('mercadopago_digitable_line')->nullable();
            $table->text('mercadopago_qr_code')->nullable();
            $table->longText('mercadopago_qr_code_base64')->nullable();
            $table->text('mercadopago_checkout_url')->nullable();
            $table->string('mercadopago_idempotency_key')->nullable();
            $table->string('mercadopago_public_token')->nullable();
            $table->timestamp('mercadopago_last_sync_at')->nullable();
            $table->text('boleto_link')->nullable();
            $table->text('chave_pix')->nullable();
            $table->unsignedBigInteger('received_by_user_id')->nullable();
            $table->unsignedBigInteger('abertura_caixa_id')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamps();
        });

        DB::table('clientes')->insert([
            'id' => 20,
            'razao_social' => 'Cliente Teste',
            'cpf_cnpj' => '12345678909',
            'email' => 'cliente@example.com',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('config_ecommerces')->insert([
            'id' => 30,
            'empresa_id' => 1,
            'mercadopago_access_token' => 'TEST-TOKEN',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('conta_recebers')->insert([
            'id' => 10,
            'empresa_id' => 1,
            'cliente_id' => 20,
            'referencia' => 'TITULO-10',
            'valor_integral' => 45,
            'valor_recebido' => 0,
            'status' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        session()->forget('user_logged');
    }

    public function test_retry_reutiliza_mesma_chave_e_referencia_apos_falha_do_provedor(): void
    {
        $sync = $this->getMockBuilder(ContaReceberMercadoPagoService::class)
            ->onlyMethods(['consultar'])
            ->getMock();

        $sync->expects($this->once())
            ->method('consultar')
            ->willReturn(['payment_id' => 'mp-safe-1', 'status' => 'pending']);

        $service = new ContaReceberMercadoPagoDirectChargeService($sync);
        $provedorDisponivel = false;

        Http::fake(function ($request) use (&$provedorDisponivel) {
            if (str_contains($request->url(), '/v1/payments/search')) {
                return Http::response(['results' => []], 200);
            }

            if ($request->method() === 'POST' && str_ends_with($request->url(), '/v1/payments')) {
                if (!$provedorDisponivel) {
                    return Http::response(['message' => 'temporary failure'], 500);
                }

                return Http::response([
                    'id' => 'mp-safe-1',
                    'status' => 'pending',
                    'status_detail' => 'pending_waiting_transfer',
                    'transaction_amount' => 45,
                    'payment_method_id' => 'pix',
                    'payment_type_id' => 'bank_transfer',
                    'external_reference' => (string) $request['external_reference'],
                ], 201);
            }

            return Http::response([], 404);
        });

        try {
            $service->gerarPix(ContaReceber::findOrFail(10));
            $this->fail('A primeira tentativa deveria falhar.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('Mercado Pago', $e->getMessage());
        }

        $aposFalha = ContaReceber::findOrFail(10);
        $chaveOriginal = (string) $aposFalha->mercadopago_idempotency_key;
        $referenciaOriginal = (string) $aposFalha->mercadopago_external_reference;

        $this->assertNotSame('', $chaveOriginal);
        $this->assertNotSame('', $referenciaOriginal);
        $this->assertSame('request_failed', (string) $aposFalha->mercadopago_status);

        $provedorDisponivel = true;

        $resultado = $service->gerarPix($aposFalha->fresh());
        $this->assertSame('mp-safe-1', $resultado['payment_id']);

        $aposRetry = ContaReceber::findOrFail(10);
        $this->assertSame($chaveOriginal, (string) $aposRetry->mercadopago_idempotency_key);
        $this->assertSame($referenciaOriginal, (string) $aposRetry->mercadopago_external_reference);
        $this->assertSame('mp-safe-1', (string) $aposRetry->mercadopago_payment_id);

        $posts = collect(Http::recorded())
            ->filter(fn ($pair) => $pair[0]->method() === 'POST' && str_ends_with($pair[0]->url(), '/v1/payments'));

        $this->assertGreaterThanOrEqual(2, $posts->count());
        $ultimoPost = $posts->last()[0];
        $this->assertSame($chaveOriginal, $ultimoPost->header('X-Idempotency-Key')[0] ?? null);
        $this->assertSame($referenciaOriginal, (string) $ultimoPost['external_reference']);
    }
}
