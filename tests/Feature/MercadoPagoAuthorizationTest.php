<?php

namespace Tests\Feature;

use App\Http\Middleware\AutorizaMercadoPagoFinanceiro;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MercadoPagoAuthorizationTest extends TestCase
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

        Schema::create('usuarios', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('empresa_id');
            $table->boolean('super')->default(false);
            $table->text('permissao')->nullable();
            $table->timestamps();
        });

        DB::table('usuarios')->insert([
            'id' => 7,
            'empresa_id' => 1,
            'super' => 0,
            'permissao' => json_encode(['/produtos']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        session(['user_logged' => [
            'id' => 7,
            'empresa' => 1,
            'super' => 0,
        ]]);
    }

    public function test_usuario_sem_permissao_de_contas_a_receber_recebe_403(): void
    {
        $middleware = new AutorizaMercadoPagoFinanceiro();
        $response = $middleware->handle(
            Request::create('/conta_receber/mercadopago/pix/10', 'POST'),
            fn () => response()->json(['ok' => true])
        );

        $this->assertSame(403, $response->getStatusCode());
    }

    public function test_usuario_com_permissao_de_contas_a_receber_pode_operar(): void
    {
        DB::table('usuarios')->where('id', 7)->update([
            'permissao' => json_encode(['/conta-receber']),
        ]);

        $middleware = new AutorizaMercadoPagoFinanceiro();
        $response = $middleware->handle(
            Request::create('/conta_receber/mercadopago/pix/10', 'POST'),
            fn () => response()->json(['ok' => true])
        );

        $this->assertSame(200, $response->getStatusCode());
    }
}
