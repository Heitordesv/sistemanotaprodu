<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCidadeDeliveriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cidade_deliveries', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nome', 50);
            $table->string('uf', 2);
            $table->string('cep', 9);

            // alter table cidade_deliveries add column uf varchar(2);
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
        Schema::dropIfExists('cidade_deliveries');
    }
}
