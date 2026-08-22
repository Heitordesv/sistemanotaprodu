<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInfoDescargasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('info_descargas', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('mdfe_id')->unsigned();
            $table->foreign('mdfe_id')->references('id')
            ->on('mdves')->onDelete('cascade');

            $table->integer('cidade_id')->unsigned();
            $table->foreign('cidade_id')->references('id')
            ->on('cidades')->onDelete('cascade');

            $table->integer('tp_unid_transp');
            $table->string('id_unid_transp', 20);
            $table->decimal('quantidade_rateio', 5, 2);
            
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
        Schema::dropIfExists('info_descargas');
    }
}
