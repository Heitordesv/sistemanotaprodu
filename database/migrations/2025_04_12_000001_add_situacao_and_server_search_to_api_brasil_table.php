<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSituacaoAndServerSearchToApiBrasilTable extends Migration
{
    public function up()
    {
        Schema::table('api_brasil', function (Blueprint $table) {
            $table->string('situacao', 45)->nullable()->after('tipo');
            $table->string('server_search', 45)->nullable()->after('situacao');
        });
    }

    public function down()
    {
        Schema::table('api_brasil', function (Blueprint $table) {
            $table->dropColumn('situacao');
            $table->dropColumn('server_search');
        });
    }
}
