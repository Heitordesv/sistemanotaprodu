<?php

namespace Tests\Feature;

use App\Http\Controllers\ContaReceberMercadoPagoController;
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
            $table->timestamps();
        });

        DB::table('conta_recebers')->insert([
            'id' => 10,
            'empresa_id' => 1,
            'valor_integral' => 45,
            'valor_recebido' => 0,
            'status' => 0,
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
}
