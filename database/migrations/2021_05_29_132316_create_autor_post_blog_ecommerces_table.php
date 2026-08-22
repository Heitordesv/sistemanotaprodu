<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAutorPostBlogEcommercesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('autor_post_blog_ecommerces', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nome', 50);
            $table->string('tipo', 50);
            $table->string('img', 40);

            $table->integer('empresa_id')->unsigned();
            $table->foreign('empresa_id')->references('id')
            ->on('empresas')->onDelete('cascade');
            
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
        Schema::dropIfExists('autor_post_blog_ecommerces');
    }
}
