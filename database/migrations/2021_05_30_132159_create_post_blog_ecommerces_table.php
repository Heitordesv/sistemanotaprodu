<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePostBlogEcommercesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('post_blog_ecommerces', function (Blueprint $table) {
            $table->increments('id');
            $table->string('titulo', 50);
            $table->string('img', 50);
            $table->string('tags', 100);
            $table->text('texto');

            $table->integer('categoria_id')->unsigned();
            $table->foreign('categoria_id')->references('id')
            ->on('categoria_post_blog_ecommerces')
            ->onDelete('cascade');

            $table->integer('autor_id')->unsigned();
            $table->foreign('autor_id')->references('id')
            ->on('autor_post_blog_ecommerces')
            ->onDelete('cascade');

            $table->integer('empresa_id')->unsigned();
            $table->foreign('empresa_id')->references('id')
            ->on('empresas')
            ->onDelete('cascade');

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
        Schema::dropIfExists('post_blog_ecommerces');
    }
}
