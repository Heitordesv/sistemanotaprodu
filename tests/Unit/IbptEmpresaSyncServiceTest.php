<?php

namespace Tests\Unit;

use App\Models\ConfigNota;
use App\Models\Produto;
use App\Services\FiscalIssuerUfService;
use App\Services\IbptEmpresaSyncService;
use RuntimeException;
use Tests\TestCase;

class IbptEmpresaSyncServiceTest extends TestCase
{
    public function test_it_rejects_a_product_from_another_company_before_calling_ibpt(): void
    {
        $config = new ConfigNota(['empresa_id' => 10]);
        $produto = new Produto(['empresa_id' => 20, 'NCM' => '09012100']);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('não pertence à empresa ativa');
        (new IbptEmpresaSyncService(app(FiscalIssuerUfService::class)))->sync($config, $produto);
    }
}
