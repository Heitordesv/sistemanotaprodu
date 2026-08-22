<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateManifestaDvesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('manifesta_dves', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('empresa_id')->unsigned();
            $table->foreign('empresa_id')->references('id')
            ->on('empresas')->onDelete('cascade');

            $table->string('chave', 44);
            $table->string('nome', 100);
            $table->string('documento', 20);
            $table->decimal('valor', 10, 2);
            $table->string('num_prot', 20);
            $table->string('data_emissao', 25);
            $table->integer('sequencia_evento');
            $table->boolean('fatura_salva');
            $table->boolean('devolucao');
            $table->integer('tipo');
            $table->integer('nsu');
            $table->integer('compra_id')->deafult(0);
            $table->integer('nNf')->default(0);

            $table->integer('filial_id')->unsigned()->nullable();
            $table->foreign('filial_id')->references('id')->on('filials')->onDelete('cascade');

            //1 => ciencia, 2 => confirmação, 3 => desconhecimento, 4 => operação não realizada

            // alter table manifesta_dves add column compra_id integer default 0;
            // alter table manifesta_dves add column nNf integer default 0;
            // alter table manifesta_dves add column devolucao boolean default 0;
            
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
        Schema::dropIfExists('manifesta_dves');
    }
}
