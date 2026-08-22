<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCiotsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ciots', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('mdfe_id')->unsigned();
            $table->foreign('mdfe_id')->references('id')
            ->on('mdves')->onDelete('cascade');

            $table->string('cpf_cnpj', 18);
            $table->string('codigo', 20);
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
        Schema::dropIfExists('ciots');
    }
}
