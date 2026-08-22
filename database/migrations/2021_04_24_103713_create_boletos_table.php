<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBoletosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('boletos', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('banco_id')->unsigned();
            $table->foreign('banco_id')->references('id')->on('conta_bancarias')->onDelete('cascade');

            $table->integer('conta_id')->unsigned();
            $table->foreign('conta_id')->references('id')->on('conta_recebers')->onDelete('cascade');

            $table->string('numero', 10);
            $table->string('numero_documento', 10);
            $table->string('carteira', 10);
            $table->string('convenio', 20);

            $table->string('linha_digitavel', 50);
            $table->string('nome_arquivo', 40);

            $table->decimal('juros', 10, 2);
            $table->decimal('multa', 10, 2);
            $table->integer('juros_apos');

            $table->string('instrucoes', 100);
            $table->string('tipo', 7); // Cnab400 ou Cnab240
            $table->boolean('logo')->default(0);

            $table->string('posto', 10)->default('');
            $table->string('codigo_cliente', 10)->default('');

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
        Schema::dropIfExists('boletos');
    }
}
