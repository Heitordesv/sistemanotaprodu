<?php

namespace Tests\Feature;

use App\Http\Controllers\DfeController;
use App\Http\Controllers\API\ProdutoController;
use App\Services\DFeService;
use Illuminate\Http\Request;
use Tests\TestCase;

class DfeEntradaMercadoriaRegressionTest extends TestCase
{
    public function test_consulta_e_cadastro_rapido_usam_rotas_web(): void
    {
        $consulta = app('router')->getRoutes()->match(
            Request::create('/dfe/getDocumentosNovos', 'GET')
        );
        $produto = app('router')->getRoutes()->match(
            Request::create('/dfe/produtos/store', 'POST')
        );

        $this->assertSame(DfeController::class . '@getDocumentosNovos', $consulta->getActionName());
        $this->assertSame(ProdutoController::class . '@store', $produto->getActionName());
        $this->assertContains('web', $consulta->gatherMiddleware());
        $this->assertContains('web', $produto->gatherMiddleware());
    }

    public function test_javascript_do_dfe_nao_chama_endpoints_api_sem_sessao(): void
    {
        $consulta = (string) file_get_contents(public_path('js/dfe.js'));
        $manifesto = (string) file_get_contents(public_path('js/manifestoDfe.js'));

        $this->assertStringContainsString('window.dfeEndpoints', $consulta);
        $this->assertStringContainsString('window.dfeEndpoints', $manifesto);
        $this->assertStringNotContainsString('api/dfe/', $consulta);
        $this->assertStringNotContainsString('api/produtos/store', $manifesto);
        $this->assertStringNotContainsString('api/categorias/buscarSubCategoria', $manifesto);
        $this->assertStringNotContainsString('api/conta-pagar/faturaManifesto', $manifesto);
    }

    public function test_controller_isola_recursos_e_grava_compra_em_transacao(): void
    {
        $controller = (string) file_get_contents(app_path('Http/Controllers/DfeController.php'));
        $middleware = (string) file_get_contents(
            app_path('Http/Middleware/ResolveFiscalWebTenantContext.php')
        );

        $this->assertStringContainsString('DfeController::class', $middleware);
        $this->assertStringContainsString("where('empresa_id', \$empresaId)", $controller);
        $this->assertStringContainsString('DB::transaction(function () use ($request)', $controller);
        $this->assertStringContainsString('Cache::lock', $controller);
        $this->assertStringContainsString('configuracaoDoManifesto', $controller);
        $this->assertStringContainsString('$manifestoDocumento->sequencia_evento = $numEvento', $controller);
        $this->assertStringNotContainsString('ManifestaDfe::findOrFail', $controller);
        $this->assertStringNotContainsString('Fornecedor::findOrFail', $controller);
        $this->assertStringNotContainsString('Produto::findOrFail', $controller);
    }

    public function test_documento_distribuido_e_convertido_para_valores_escalares(): void
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'
            . '<resNFe xmlns="http://www.portalfiscal.inf.br/nfe" versao="1.01">'
            . '<chNFe>35260812345678000190550010000001231000001234</chNFe>'
            . '<CNPJ>12345678000190</CNPJ><xNome>Fornecedor Teste</xNome>'
            . '<dhEmi>2026-08-24T10:00:00-03:00</dhEmi><vNF>125.90</vNF><nProt>123</nProt>'
            . '</resNFe>';

        $documento = DFeService::normalizaDocumentoDistribuido($xml, 'resNFe_v1.01.xsd', '000000000000321', 7);

        $this->assertSame('Fornecedor Teste', $documento['nome']);
        $this->assertSame('12345678000190', $documento['documento']);
        $this->assertSame('125.90', $documento['valor']);
        $this->assertSame(321, $documento['nsu']);
        foreach (['nome', 'documento', 'valor', 'chave'] as $campo) {
            $this->assertIsString($documento[$campo]);
        }
    }

    public function test_javascript_rejeita_objetos_e_valores_nao_numericos(): void
    {
        $javascript = (string) file_get_contents(public_path('js/dfe.js'));

        $this->assertStringContainsString("typeof valor === 'string'", $javascript);
        $this->assertStringContainsString('Number.isFinite', $javascript);
        $this->assertStringContainsString('escapaHtml', $javascript);
        $this->assertStringNotContainsString('parseFloat(v.valor)', $javascript);
    }
}
