<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCTeDescargasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('c_te_descargas', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('info_id')->unsigned();
            $table->foreign('info_id')->references('id')
            ->on('info_descargas')->onDelete('cascade');

            $table->string('chave', 44);
            $table->string('seg_cod_barras', 44);
            
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
        Schema::dropIfExists('c_te_descargas');
    }
}
