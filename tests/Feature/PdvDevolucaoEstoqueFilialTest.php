<?php

namespace Tests\Feature;

use App\Models\Usuario;
use App\Models\VendaCaixa;
use App\Services\DevolucaoEstoqueService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PdvDevolucaoEstoqueFilialTest extends TestCase
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

        $this->criarSchema();
    }

    public function test_devolucao_retorna_quantidade_para_estoque_filial_historico_sem_alterar_matriz(): void
    {
        DB::table('produtos')->insert([
            'id' => 50,
            'empresa_id' => 1,
            'nome' => 'Produto teste',
            'valor_compra' => 10,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('estoques')->insert([
            [
                'produto_id' => 50,
                'empresa_id' => 1,
                'filial_id' => null,
                'quantidade' => 50,
                'valor_compra' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'produto_id' => 50,
                'empresa_id' => 1,
                'filial_id' => 9,
                'quantidade' => 5,
                'valor_compra' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $venda = VendaCaixa::create([
            'empresa_id' => 1,
            'usuario_id' => 7,
            'valor_total' => 20,
            'estado_emissao' => 'novo',
            'retorno_estoque' => 0,
            'filial_id' => 9,
            'estoque_filial_id' => 9,
        ]);

        DB::table('item_venda_caixas')->insert([
            'produto_id' => 50,
            'venda_caixa_id' => $venda->id,
            'quantidade' => 2,
            'valor' => 10,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $operador = new Usuario();
        $operador->id = 7;
        $operador->nome = 'Operador';
        $operador->empresa_id = 1;

        $admin = new Usuario();
        $admin->id = 8;
        $admin->nome = 'Administrador';
        $admin->empresa_id = 1;

        (new DevolucaoEstoqueService())->devolver($venda, $operador, $admin);

        $matriz = DB::table('estoques')
            ->where('produto_id', 50)
            ->whereNull('filial_id')
            ->value('quantidade');

        $filial = DB::table('estoques')
            ->where('produto_id', 50)
            ->where('filial_id', 9)
            ->value('quantidade');

        $this->assertSame(50.0, (float) $matriz);
        $this->assertSame(7.0, (float) $filial);

        $auditoria = DB::table('alteracao_estoques')->where('produto_id', 50)->first();
        $this->assertNotNull($auditoria);
        $this->assertSame('devolucao', $auditoria->tipo);
        $this->assertSame(9, (int) $auditoria->filial_id);
        $this->assertStringContainsString('venda #' . $venda->id, strtolower((string) $auditoria->observacao));
    }

    public function test_venda_legada_sem_estoque_filial_retorna_para_matriz_mesmo_se_venda_tiver_filial(): void
    {
        DB::table('produtos')->insert([
            'id' => 51,
            'empresa_id' => 1,
            'nome' => 'Produto legado',
            'valor_compra' => 10,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('estoques')->insert([
            [
                'produto_id' => 51,
                'empresa_id' => 1,
                'filial_id' => null,
                'quantidade' => 10,
                'valor_compra' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'produto_id' => 51,
                'empresa_id' => 1,
                'filial_id' => 9,
                'quantidade' => 30,
                'valor_compra' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $venda = VendaCaixa::create([
            'empresa_id' => 1,
            'usuario_id' => 7,
            'valor_total' => 10,
            'estado_emissao' => 'novo',
            'retorno_estoque' => 0,
            'filial_id' => 9,
            'estoque_filial_id' => null,
        ]);

        DB::table('item_venda_caixas')->insert([
            'produto_id' => 51,
            'venda_caixa_id' => $venda->id,
            'quantidade' => 1,
            'valor' => 10,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $operador = new Usuario();
        $operador->id = 7;
        $operador->nome = 'Operador';
        $admin = new Usuario();
        $admin->id = 8;
        $admin->nome = 'Administrador';

        (new DevolucaoEstoqueService())->devolver($venda, $operador, $admin);

        $this->assertSame(11.0, (float) DB::table('estoques')->where('produto_id', 51)->whereNull('filial_id')->value('quantidade'));
        $this->assertSame(30.0, (float) DB::table('estoques')->where('produto_id', 51)->where('filial_id', 9)->value('quantidade'));
    }

    private function criarSchema(): void
    {
        Schema::create('produtos', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('empresa_id');
            $table->string('nome');
            $table->decimal('valor_compra', 16, 7)->default(0);
            $table->timestamps();
        });

        Schema::create('estoques', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('produto_id');
            $table->unsignedInteger('empresa_id');
            $table->unsignedInteger('filial_id')->nullable();
            $table->decimal('quantidade', 16, 7)->default(0);
            $table->decimal('valor_compra', 16, 7)->default(0);
            $table->timestamps();
        });

        Schema::create('venda_caixas', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('empresa_id');
            $table->unsignedInteger('usuario_id');
            $table->decimal('valor_total', 16, 7)->default(0);
            $table->string('estado_emissao')->nullable();
            $table->boolean('retorno_estoque')->default(false);
            $table->unsignedInteger('filial_id')->nullable();
            $table->unsignedInteger('estoque_filial_id')->nullable();
            $table->timestamps();
        });

        Schema::create('item_venda_caixas', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('produto_id');
            $table->unsignedInteger('venda_caixa_id');
            $table->decimal('quantidade', 16, 7);
            $table->decimal('valor', 16, 7)->default(0);
            $table->timestamps();
        });

        Schema::create('alteracao_estoques', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('produto_id');
            $table->unsignedInteger('usuario_id');
            $table->decimal('quantidade', 16, 7);
            $table->string('tipo');
            $table->string('observacao')->nullable();
            $table->unsignedInteger('empresa_id');
            $table->unsignedInteger('filial_id')->nullable();
            $table->timestamps();
        });
    }
}
