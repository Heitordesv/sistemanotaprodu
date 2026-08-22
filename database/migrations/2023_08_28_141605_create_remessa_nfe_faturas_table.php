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
        Schema::create('remessa_nfe_faturas', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('remessa_id')->nullable()->constrained('remessa_nves');

            $table->string('tipo_pagamento', 30);
            $table->decimal('valor', 16,7);
            $table->date('data_vencimento');

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
        Schema::dropIfExists('remessa_nfe_faturas');
    }
};
