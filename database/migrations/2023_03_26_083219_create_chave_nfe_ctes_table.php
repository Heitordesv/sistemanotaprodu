<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chave_nfe_ctes', function (Blueprint $table) {
            $table->id();

            $table->integer('cte_id')->unsigned();
            $table->foreign('cte_id')->references('id')->on('ctes');

            $table->string('chave', 44);
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
        Schema::dropIfExists('chave_nfe_ctes');
    }
};
