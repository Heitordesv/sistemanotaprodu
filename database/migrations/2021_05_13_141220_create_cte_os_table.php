<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCteOsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cte_os', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('empresa_id')->unsigned();
            $table->foreign('empresa_id')->references('id')
            ->on('empresas')->onDelete('cascade');

            $table->integer('emitente_id')->unsigned();
            $table->foreign('emitente_id')->references('id')
            ->on('clientes');

            $table->integer('tomador_id')->unsigned();
            $table->foreign('tomador_id')->references('id')
            ->on('clientes');

            $table->integer('municipio_envio')->unsigned();
            $table->foreign('municipio_envio')->references('id')
            ->on('cidades');

            $table->integer('municipio_inicio')->unsigned();
            $table->foreign('municipio_inicio')->references('id')
            ->on('cidades');

            $table->integer('municipio_fim')->unsigned();
            $table->foreign('municipio_fim')->references('id')
            ->on('cidades');

            $table->integer('veiculo_id')->unsigned();
            $table->foreign('veiculo_id')->references('id')
            ->on('veiculos');

            $table->integer('usuario_id')->unsigned();
            $table->foreign('usuario_id')->references('id')->on('usuarios');

            $table->string('modal', 2);
            $table->string('cst', 3)->default('00');
            $table->decimal('perc_icms', 5, 2)->default(0);
            $table->decimal('valor_transporte', 10, 2);
            $table->decimal('valor_receber', 10, 2);

            $table->string('descricao_servico', 100)->default('');
            $table->decimal('quantidade_carga', 12, 4);

            $table->integer('natureza_id')->unsigned();
            $table->foreign('natureza_id')->references('id')->on('natureza_operacaos');

            $table->integer('tomador');
            // Indica o "papel" do tomador: 0-Remetente; 1-Expedidor; 2-Recebedor; 3-Destinatário

            $table->integer('sequencia_cce');
            $table->string('observacao', 200);
            $table->integer('numero_emissao')->default(0);
            $table->string('chave', 48);
            $table->enum('estado_emissao', ['novo', 'aprovado', 'cancelado', 'rejeitado']);
            $table->timestamp('data_emissao')->nullable();

            $table->string('data_viagem', 10);
            $table->string('horario_viagem', 5);

            // alter table cte_os add column data_viagem varchar(10) default '';
            // alter table cte_os add column horario_viagem varchar(5) default '';
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
        Schema::dropIfExists('cte_os');
    }
}
