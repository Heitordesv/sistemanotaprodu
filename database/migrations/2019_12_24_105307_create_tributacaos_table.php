<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTributacaosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tributacaos', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('empresa_id')->unsigned();
            $table->foreign('empresa_id')->references('id')
            ->on('empresas')->onDelete('cascade');

            $table->decimal('icms', 4, 2);
            $table->decimal('pis', 4, 2);
            $table->decimal('cofins', 4, 2);
            $table->decimal('ipi', 4, 2);
            $table->decimal('perc_ap_cred', 5, 2);
            $table->string('ncm_padrao', 10)->default('0000.00.00');
            $table->string('link_nfse', 200)->default('');

            // alter table tributacaos add column link_nfse varchar(200) default '';
            // alter table tributacaos add column perc_ap_cred decimal(5,2) default 0;
            
            $table->string('regime');
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
        Schema::dropIfExists('tributacaos');
    }
}
