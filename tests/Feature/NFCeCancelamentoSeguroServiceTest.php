<?php

namespace Tests\Feature;

use App\Models\VendaCaixa;
use App\Services\NFCeCancelamentoSeguroService;
use App\Services\NFCeConsultaCancelamentoParser;
use App\Services\NFCeToolsFactory;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mockery;
use NFePHP\NFe\Tools;
use Tests\TestCase;

class NFCeCancelamentoSeguroServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => true,
            ],
        ]);

        DB::purge('sqlite');
        DB::setDefaultConnection('sqlite');
        DB::reconnect('sqlite');

        Schema::create('config_notas', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('empresa_id');
            $table->timestamps();
        });

        DB::table('config_notas')->insert([
            'id' => 1,
            'empresa_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_evento_cancelamento_ja_detectado_impede_nova_chamada_sefaz_cancela(): void
    {
        $chave = str_repeat('3', 44);
        $venda = new VendaCaixa();
        $venda->id = 10;
        $venda->empresa_id = 1;
        $venda->chave = $chave;

        $tools = Mockery::mock(Tools::class);
        $tools->shouldReceive('sefazConsultaChave')
            ->once()
            ->with($chave)
            ->andReturn('<?xml version="1.0" encoding="UTF-8"?><retConsSitNFe versao="4.00" xmlns="http://www.portalfiscal.inf.br/nfe"><tpAmb>2</tpAmb><verAplic>TESTE</verAplic><cStat>100</cStat><xMotivo>Autorizado</xMotivo><cUF>35</cUF><chNFe>' . $chave . '</chNFe></retConsSitNFe>');
        $tools->shouldNotReceive('sefazCancela');

        $factory = Mockery::mock(NFCeToolsFactory::class);
        $factory->shouldReceive('make')->once()->andReturn($tools);

        $parser = Mockery::mock(NFCeConsultaCancelamentoParser::class);
        $parser->shouldReceive('detectar')->once()->andReturn([
            'cstat' => '135',
            'mensagem' => 'Evento registrado e vinculado a NF-e',
            'protocolo' => '135260000000999',
            'chave' => $chave,
            'origem' => 'evento',
        ]);

        $service = new NFCeCancelamentoSeguroService($parser, $factory);
        $resultado = $service->cancelar($venda, 'Cancelamento solicitado pelo cliente.');

        $this->assertTrue($resultado['ok']);
        $this->assertTrue($resultado['ja_cancelada']);
        $this->assertSame('135', $resultado['cstat']);
        $this->assertSame('110111', $resultado['data']['retEvento']['infEvento']['tpEvento']);
    }
}
