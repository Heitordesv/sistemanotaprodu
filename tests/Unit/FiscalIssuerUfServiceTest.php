<?php

namespace Tests\Unit;

use App\Models\Cidade;
use App\Models\ConfigNota;
use App\Services\FiscalIssuerUfService;
use PHPUnit\Framework\TestCase;

class FiscalIssuerUfServiceTest extends TestCase
{
    public function test_prioriza_uf_da_cidade_do_emitente_para_consulta_ibpt(): void
    {
        $config = new ConfigNota();
        $config->setAttribute('cUF', '35');
        $config->setAttribute('UF', 'SP');

        $cidade = new Cidade();
        $cidade->setAttribute('uf', 'MA');
        $config->setRelation('cidade', $cidade);

        $this->assertSame('MA', (new FiscalIssuerUfService())->resolve($config));
    }

    public function test_usa_codigo_ibge_da_uf_quando_cidade_nao_esta_disponivel(): void
    {
        $config = new ConfigNota();
        $config->setAttribute('cUF', '21');
        $config->setRelation('cidade', null);

        $this->assertSame('MA', (new FiscalIssuerUfService())->resolve($config));
    }

    public function test_nao_inventa_uf_quando_configuracao_e_invalida(): void
    {
        $config = new ConfigNota();
        $config->setAttribute('cUF', '99');
        $config->setAttribute('UF', 'XX');
        $config->setRelation('cidade', null);

        $this->assertSame('', (new FiscalIssuerUfService())->resolve($config));
    }
}
