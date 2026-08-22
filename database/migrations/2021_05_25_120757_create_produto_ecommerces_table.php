<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProdutoEcommercesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('produto_ecommerces', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('empresa_id')->unsigned();
            $table->foreign('empresa_id')->references('id')->on('empresas')
            ->onDelete('cascade');

            $table->integer('produto_id')->unsigned();
            $table->foreign('produto_id')->references('id')->on('produtos')
            ->onDelete('cascade');

            $table->integer('categoria_id')->unsigned();
            $table->foreign('categoria_id')->references('id')
            ->on('categoria_produto_ecommerces')->onDelete('cascade');

            $table->integer('sub_categoria_id')->default(0);

            $table->text('descricao');
            $table->boolean('controlar_estoque');
            $table->boolean('status');
            $table->boolean('destaque');

            $table->string('cep', 9)->default('');

            $table->decimal('valor', 10, 2);
            $table->integer('percentual_desconto_view')->default(0);
            $table->timestamps();

            // alter table produto_ecommerces add column sub_categoria_id integer default 0;
            // alter table produto_ecommerces add column cep varchar(9) default '';
            // alter table produto_ecommerces add column percentual_desconto_view integer default 0;
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('produto_ecommerces');
    }
}
