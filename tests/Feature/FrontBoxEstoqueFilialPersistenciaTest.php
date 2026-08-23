<?php

namespace Tests\Feature;

use App\Models\VendaCaixa;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class FrontBoxEstoqueFilialPersistenciaTest extends TestCase
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

        Schema::create('venda_caixas', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('empresa_id');
            $table->unsignedInteger('usuario_id');
            $table->decimal('valor_total', 16, 7)->default(0);
            $table->unsignedInteger('filial_id')->nullable();
            $table->unsignedInteger('estoque_filial_id')->nullable();
            $table->timestamps();
        });
    }

    public function test_estoque_filial_id_e_persistido_no_mesmo_create_da_venda(): void
    {
        $venda = VendaCaixa::create([
            'empresa_id' => 1,
            'usuario_id' => 7,
            'valor_total' => 10,
            'filial_id' => 9,
            'estoque_filial_id' => 9,
        ]);

        $this->assertSame(9, (int) $venda->fresh()->estoque_filial_id);
        $this->assertSame(9, (int) DB::table('venda_caixas')->where('id', $venda->id)->value('estoque_filial_id'));
    }
}
