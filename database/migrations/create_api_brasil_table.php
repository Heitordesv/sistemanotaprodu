<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateApiBrasilTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('api_brasil', function (Blueprint $table) {
            $table->increments('id');
            $table->string('user_id', 220);
            $table->string('DeviceToken', 1000);
            $table->string('Bearer', 1000);
            $table->integer('tipo');
            $table->timestamps(); // Usar isso é melhor do que definir created_at e updated_at manualmente
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_brasil');
    }
}
