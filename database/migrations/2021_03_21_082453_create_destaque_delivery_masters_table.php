<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDestaqueDeliveryMastersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('destaque_delivery_masters', function (Blueprint $table) {
            $table->increments('id');
            $table->string('img', 80);
            $table->string('titulo', 50);
            $table->string('descricao', 250);
            $table->string('acao', 250)->default('');
            $table->boolean('status')->default(0);
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
        Schema::dropIfExists('destaque_delivery_masters');
    }
}
