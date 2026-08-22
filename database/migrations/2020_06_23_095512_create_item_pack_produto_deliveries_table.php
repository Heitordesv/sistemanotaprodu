<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateItemPackProdutoDeliveriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('item_pack_produto_deliveries', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('produto_delivery_id')->nullable()->unsigned();
            $table->foreign('produto_delivery_id')->references('id')
            ->on('produto_deliveries')->onDelete('cascade');

            $table->integer('pack_id')->nullable()->unsigned();
            $table->foreign('pack_id')->references('id')
            ->on('pack_produto_deliveries')->onDelete('cascade');

            $table->decimal('quantidade', 5, 2);


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
        Schema::dropIfExists('item_pack_produto_deliveries');
    }
}
