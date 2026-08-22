<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateComponenteCtesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('componente_ctes', function (Blueprint $table) {
            $table->increments('id');

            $table->string('nome', 30);
            $table->decimal('valor', 10, 4);

            $table->integer('cte_id')->unsigned();
            $table->foreign('cte_id')->references('id')
            ->on('ctes')->onDelete('cascade');
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
        Schema::dropIfExists('componente_ctes');
    }
}
