<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBannerMaisVendidosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('banner_mais_vendidos', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('empresa_id')->unsigned();
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');

            $table->string('path', 100);
            $table->string('texto_primario', 20);
            $table->string('texto_secundario', 30);

            $table->integer('produto_delivery_id')->nullable()->unsigned();
            $table->foreign('produto_delivery_id')->references('id')
            ->on('produto_deliveries')->onDelete('cascade');

            $table->integer('pack_id')->nullable()->unsigned();
            $table->foreign('pack_id')->references('id')
            ->on('pack_produto_deliveries')->onDelete('cascade');

            $table->boolean('ativo');
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
        Schema::dropIfExists('banner_mais_vendidos');
    }
}
