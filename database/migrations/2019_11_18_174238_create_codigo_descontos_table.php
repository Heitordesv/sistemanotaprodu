<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCodigoDescontosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('codigo_descontos', function (Blueprint $table) {
            $table->increments('id');
            
            $table->integer('empresa_id')->unsigned();
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');

            $table->integer('codigo');
            $table->string('descricao', 50);
            
            $table->integer('cliente_id')->nullable()->unsigned();
            $table->foreign('cliente_id')->references('id')->on('cliente_deliveries')->onDelete('cascade');
            $table->string('tipo');
            $table->decimal('valor', 10, 4);
            $table->decimal('valor_minimo_pedido', 12, 4);
            $table->boolean('ativo');
            $table->boolean('push');
            $table->boolean('sms');

            $table->date('expiracao')->default(null);
            $table->timestamps();

            // alter table codigo_descontos add column valor_minimo_pedido decimal(12,4) default 0;
            // alter table codigo_descontos add column descricao varchar(50) default '';
            // alter table codigo_descontos add column expiracao date default null;
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('codigo_descontos');
    }
}
