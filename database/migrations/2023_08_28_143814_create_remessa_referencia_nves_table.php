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
        Schema::create('remessa_referencia_nves', function (Blueprint $table) {
            $table->id();

            $table->foreignId('remessa_id')->nullable()->constrained('remessa_nves');
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
        Schema::dropIfExists('remessa_referencia_nves');
    }
};
