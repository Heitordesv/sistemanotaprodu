<?php

namespace Tests\Feature;

use App\Exceptions\CaixaMovimentacaoException;
use App\Models\Venda;
use App\Services\VendaCaixaEdicaoService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class VendaCaixaImutabilidadeTest extends TestCase
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

        $this->criarSchemaMinimo();
    }

    public function test_venda_vinculada_a_caixa_fechado_nao_pode_ser_editada(): void
    {
        $this->criarAbertura(100, 1, 0, 10);
        $this->criarVenda(11, 100, 45.00);

        try {
            app(VendaCaixaEdicaoService::class)->executar(11, 1, function (Venda $venda) {
                $venda->valor_total = 99;
                $venda->save();
            });
            $this->fail('Venda de caixa fechado não poderia ter sido alterada.');
        } catch (CaixaMovimentacaoException $e) {
            $this->assertStringContainsString('caixa já fechado', $e->getMessage());
        }

        $this->assertSame(45.0, (float) DB::table('vendas')->where('id', 11)->value('valor_total'));
    }

    public function test_venda_legada_que_entra_na_faixa_de_caixa_fechado_tambem_fica_imutavel(): void
    {
        $this->criarAbertura(101, 1, 10, 20);
        $this->criarVenda(15, null, 30.00);

        try {
            app(VendaCaixaEdicaoService::class)->executar(15, 1, function (Venda $venda) {
                $venda->valor_total = 70;
                $venda->save();
            });
            $this->fail('Venda legada consolidada em caixa fechado não poderia ser alterada.');
        } catch (CaixaMovimentacaoException $e) {
            $this->assertStringContainsString('caixa já fechado', $e->getMessage());
        }

        $this->assertSame(30.0, (float) DB::table('vendas')->where('id', 15)->value('valor_total'));
    }

    public function test_venda_de_caixa_aberto_pode_ser_editada_sob_o_lock_da_abertura(): void
    {
        $this->criarAbertura(102, 0, 20, 20);
        $this->criarVenda(21, 102, 10.00);

        app(VendaCaixaEdicaoService::class)->executar(21, 1, function (Venda $venda) {
            $venda->valor_total = 25;
            $venda->save();
        });

        $this->assertSame(25.0, (float) DB::table('vendas')->where('id', 21)->value('valor_total'));
    }

    public function test_venda_realmente_fora_de_caixa_continua_editavel(): void
    {
        $this->criarVenda(30, null, 12.00);

        app(VendaCaixaEdicaoService::class)->executar(30, 1, function (Venda $venda, $abertura) {
            $this->assertNull($abertura);
            $venda->valor_total = 18;
            $venda->save();
        });

        $this->assertSame(18.0, (float) DB::table('vendas')->where('id', 30)->value('valor_total'));
    }

    private function criarAbertura(
        int $id,
        int $status,
        int $primeiraVendaNfe,
        int $ultimaVendaNfe
    ): void {
        DB::table('abertura_caixas')->insert([
            'id' => $id,
            'usuario_id' => 7,
            'empresa_id' => 1,
            'status' => $status,
            'valor' => 0,
            'primeira_venda_nfe' => $primeiraVendaNfe,
            'ultima_venda_nfe' => $ultimaVendaNfe,
            'created_at' => now()->subHour(),
            'updated_at' => now(),
        ]);
    }

    private function criarVenda(int $id, ?int $aberturaId, float $valor): void
    {
        DB::table('vendas')->insert([
            'id' => $id,
            'empresa_id' => 1,
            'usuario_id' => 7,
            'abertura_caixa_id' => $aberturaId,
            'valor_total' => $valor,
            'created_at' => now()->subMinutes(10),
            'updated_at' => now()->subMinutes(10),
        ]);
    }

    private function criarSchemaMinimo(): void
    {
        Schema::create('abertura_caixas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('usuario_id');
            $table->unsignedBigInteger('empresa_id');
            $table->boolean('status')->default(false);
            $table->decimal('valor', 15, 2)->default(0);
            $table->unsignedBigInteger('primeira_venda_nfe')->default(0);
            $table->unsignedBigInteger('ultima_venda_nfe')->default(0);
            $table->timestamps();
        });

        Schema::create('vendas', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('usuario_id');
            $table->unsignedBigInteger('abertura_caixa_id')->nullable();
            $table->decimal('valor_total', 15, 2)->default(0);
            $table->timestamps();
        });
    }
}
