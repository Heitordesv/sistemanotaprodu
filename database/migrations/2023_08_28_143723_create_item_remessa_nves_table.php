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
        Schema::create('item_remessa_nves', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('remessa_id')->nullable()->constrained('remessa_nves');

            $table->integer('produto_id')->unsigned();
            $table->foreign('produto_id')->references('id')->on('produtos');

            $table->decimal('quantidade', 16,7);
            $table->decimal('valor_unitario', 16,7);
            $table->decimal('sub_total', 16,7);

            $table->string('cfop', 4);  
            $table->string('cst_csosn', 3);  
            $table->string('cst_pis', 3);   
            $table->string('cst_cofins', 3);   
            $table->string('cst_ipi', 3);   
            $table->decimal('perc_icms');   
            $table->decimal('perc_pis');   
            $table->decimal('perc_cofins');   
            $table->decimal('perc_ipi');  
            $table->decimal('pRedBC', 10, 4);

            $table->decimal('vbc_icms', 10, 4);
            $table->decimal('vbc_pis', 10, 4);
            $table->decimal('vbc_cofins', 10, 4);
            $table->decimal('vbc_ipi', 10, 4);

            $table->decimal('valor_icms', 10, 4);
            $table->decimal('valor_pis', 10, 4);
            $table->decimal('valor_cofins', 10, 4);
            $table->decimal('valor_ipi', 10, 4);

            $table->decimal('vBCSTRet', 8, 2)->default(0);
            $table->decimal('vFrete', 8, 2)->default(0);

            $table->decimal('modBCST', 8, 2);
            $table->decimal('vBCST', 8, 2);
            $table->decimal('pICMSST', 8, 2);
            $table->decimal('vICMSST', 8, 2);
            $table->decimal('pMVAST', 8, 2);

            $table->string('x_pedido', 30);
            $table->string('num_item_pedido', 30);
            $table->string('cest', 10);

            // alter table item_remessa_nves add column cfop varchar(4);

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
        Schema::dropIfExists('item_remessa_nves');
    }
};
