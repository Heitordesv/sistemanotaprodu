<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReceitaCtesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('receita_ctes', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('cte_id')->unsigned();
            $table->foreign('cte_id')->references('id')->on('ctes')
            ->onDelete('cascade');

            $table->decimal('valor', 10, 2);

            $table->string('descricao', 50);
            $table->timestamp('data_registro')->useCurrent();

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
        Schema::dropIfExists('receita_ctes');
    }
}
