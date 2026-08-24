<?php

namespace Tests\Feature;

use App\Models\Produto;
use App\Services\PdvListaPrecoService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PdvListaPrecoTest extends TestCase
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

        Schema::create('categorias', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->string('nome');
            $table->boolean('desconto_ativo')->default(false);
            $table->decimal('desconto', 10, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('produtos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('categoria_id')->nullable();
            $table->string('nome');
            $table->decimal('valor_venda', 10, 2);
            $table->timestamps();
        });

        Schema::create('lista_precos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->string('nome');
            $table->timestamps();
        });

        Schema::create('produto_lista_precos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lista_id');
            $table->unsignedBigInteger('produto_id');
            $table->decimal('valor', 10, 2);
            $table->decimal('percentual_lucro', 10, 2)->default(0);
            $table->timestamps();
        });

        DB::table('categorias')->insert([
            'id' => 10,
            'empresa_id' => 1,
            'nome' => 'Geral',
            'desconto_ativo' => 0,
            'desconto' => 0,
        ]);

        DB::table('produtos')->insert([
            'id' => 100,
            'empresa_id' => 1,
            'categoria_id' => 10,
            'nome' => 'Produto A',
            'valor_venda' => 50,
        ]);

        DB::table('lista_precos')->insert([
            ['id' => 20, 'empresa_id' => 1, 'nome' => 'Atacado'],
            ['id' => 30, 'empresa_id' => 2, 'nome' => 'Outra empresa'],
        ]);

        DB::table('produto_lista_precos')->insert([
            'lista_id' => 20,
            'produto_id' => 100,
            'valor' => 42,
            'percentual_lucro' => 0,
        ]);
    }

    public function test_aplica_preco_da_lista_da_empresa(): void
    {
        $produto = Produto::with('categoria')->findOrFail(100);

        $valor = app(PdvListaPrecoService::class)
            ->precoPdv($produto, 20, 1);

        $this->assertSame(42.0, $valor);
    }

    public function test_lista_sem_produto_usa_preco_padrao(): void
    {
        DB::table('produto_lista_precos')->delete();
        $produto = Produto::with('categoria')->findOrFail(100);

        $valor = app(PdvListaPrecoService::class)
            ->precoPdv($produto, 20, 1);

        $this->assertSame(50.0, $valor);
    }

    public function test_desconto_da_categoria_incide_sobre_preco_da_lista(): void
    {
        DB::table('categorias')->where('id', 10)->update([
            'desconto_ativo' => 1,
            'desconto' => 10,
        ]);
        $produto = Produto::with('categoria')->findOrFail(100);

        $valor = app(PdvListaPrecoService::class)
            ->precoPdv($produto, 20, 1);

        $this->assertSame(37.8, $valor);
    }

    public function test_rejeita_lista_de_outra_empresa(): void
    {
        $produto = Produto::with('categoria')->findOrFail(100);

        $this->expectException(ValidationException::class);

        app(PdvListaPrecoService::class)
            ->precoPdv($produto, 30, 1);
    }
}
