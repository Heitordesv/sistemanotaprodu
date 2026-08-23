<?php

namespace Tests\Feature;

use App\Http\Controllers\ContaReceberMercadoPagoController;
use App\Models\ContaReceber;
use App\Services\ContaReceberMercadoPagoService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MercadoPagoConciliacaoDeterministicaTest extends TestCase
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

        Schema::create('conta_recebers', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('empresa_id');
            $table->decimal('valor_integral', 15, 2)->default(0);
            $table->decimal('valor_recebido', 15, 2)->default(0);
            $table->boolean('status')->default(false);
            $table->string('tipo_pagamento', 10)->nullable();
            $table->timestamp('data_recebimento')->nullable();
            $table->string('mercadopago_status')->nullable();
            $table->string('mercadopago_payment_id')->nullable();
            $table->unsignedBigInteger('received_by_user_id')->nullable();
            $table->unsignedBigInteger('abertura_caixa_id')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamps();
        });

        DB::table('conta_recebers')->insert([
            'id' => 10,
            'empresa_id' => 1,
            'valor_integral' => 45,
            'valor_recebido' => 0,
            'status' => 0,
            'tipo_pagamento' => null,
            'data_recebimento' => null,
            'mercadopago_status' => null,
            'mercadopago_payment_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        session(['user_logged' => [
            'id' => 7,
            'empresa' => 1,
            'adm' => 1,
            'super' => 0,
        ]]);
    }

    public function test_consulta_admin_remove_contexto_de_caixa_durante_conciliacao_e_restaura_sessao(): void
    {
        $service = $this->getMockBuilder(ContaReceberMercadoPagoService::class)
            ->onlyMethods(['consultar'])
            ->getMock();

        $service->expects($this->once())
            ->method('consultar')
            ->willReturnCallback(function () {
                return [
                    'user_logged_durante_conciliacao' => session()->has('user_logged'),
                ];
            });

        $controller = new ContaReceberMercadoPagoController($service);
        $response = $controller->status(10);
        $payload = json_decode((string) $response->getContent(), true);

        $this->assertFalse((bool) $payload['user_logged_durante_conciliacao']);
        $this->assertTrue(session()->has('user_logged'));
        $this->assertSame(7, (int) session('user_logged.id'));
    }

    public function test_erro_do_provedor_nao_e_exposto_na_resposta_admin(): void
    {
        $service = $this->getMockBuilder(ContaReceberMercadoPagoService::class)
            ->onlyMethods(['consultar'])
            ->getMock();

        $service->expects($this->once())
            ->method('consultar')
            ->willThrowException(new \RuntimeException('Access token secreto e SQLSTATE interno'));

        $controller = new ContaReceberMercadoPagoController($service);
        $response = $controller->status(10);
        $conteudo = (string) $response->getContent();
        $payload = json_decode($conteudo, true);

        $this->assertSame(422, $response->getStatusCode());
        $this->assertStringNotContainsString('Access token', $conteudo);
        $this->assertStringNotContainsString('SQLSTATE', $conteudo);
        $this->assertSame(
            'Não foi possível processar a operação do Mercado Pago.',
            $payload['message'] ?? null
        );
        $this->assertSame(
            'Não foi possível processar a operação do Mercado Pago.',
            $payload['erro'] ?? null
        );
        $this->assertTrue(session()->has('user_logged'));
    }

    public function test_aprovacao_tardia_nao_deixa_valor_recebido_ultrapassar_valor_integral(): void
    {
        // Sem sessão durante conciliação automática: evita resolução de caixa e
        // reproduz o comportamento do webhook/retorno público.
        session()->forget('user_logged');

        $conta = ContaReceber::findOrFail(10);
        $conta->mercadopago_status = 'approved';
        $conta->mercadopago_payment_id = 'mp-over-1';
        $conta->tipo_pagamento = '17';
        $conta->data_recebimento = now();
        $conta->valor_recebido = 60;
        $conta->save();

        $conta->refresh();
        $this->assertSame(45.0, (float) $conta->valor_recebido);
        $this->assertSame('17', (string) $conta->tipo_pagamento);
    }

    public function test_aprovacao_tardia_em_conta_ja_quitada_preserva_forma_e_data_da_baixa_original(): void
    {
        $dataOriginal = now()->subDay()->startOfSecond();

        DB::table('conta_recebers')->where('id', 10)->update([
            'valor_recebido' => 45,
            'status' => 1,
            'tipo_pagamento' => '01',
            'data_recebimento' => $dataOriginal,
        ]);

        session()->forget('user_logged');

        $conta = ContaReceber::findOrFail(10);
        $conta->mercadopago_status = 'approved';
        $conta->mercadopago_payment_id = 'mp-over-2';
        $conta->tipo_pagamento = '17';
        $conta->data_recebimento = now();
        $conta->valor_recebido = 90;
        $conta->save();

        $conta->refresh();
        $this->assertSame(45.0, (float) $conta->valor_recebido);
        $this->assertSame('01', (string) $conta->tipo_pagamento);
        $this->assertSame(
            $dataOriginal->format('Y-m-d H:i:s'),
            optional($conta->data_recebimento)->format('Y-m-d H:i:s')
        );
    }
}
