<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEtiquetasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('etiquetas', function (Blueprint $table) {
            $table->increments('id');

            $table->string('nome', 40);
            $table->string('observacao', 255);

            $table->integer('empresa_id')->unsigned()->nullable();
            $table->foreign('empresa_id')->references('id')
            ->on('empresas')->onDelete('cascade');

            $table->string('altura', 10);
            $table->string('largura', 10);
            $table->integer('etiquestas_por_linha');
            $table->string('distancia_etiquetas_lateral', 10);
            $table->string('distancia_etiquetas_topo', 10);
            $table->integer('quantidade_etiquetas');

            $table->string('tamanho_fonte', 10);
            $table->string('tamanho_codigo_barras', 10);

            $table->boolean('nome_empresa');
            $table->boolean('nome_produto');
            $table->boolean('valor_produto');
            $table->boolean('codigo_produto');
            $table->boolean('codigo_barras_numerico');

            // alter table etiquetas add column codigo_barras_numerico boolean;

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('etiquetas');
    }
}
