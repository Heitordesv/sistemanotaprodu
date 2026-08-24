<?php

namespace Tests\Feature;

use App\Http\Controllers\API\NFCeController;
use App\Http\Controllers\API\NFeController;
use App\Http\Controllers\API\ProdutoController as ApiProdutoController;
use App\Http\Controllers\AppFiscal\ConfigEmitenteController as AppConfigEmitenteController;
use App\Http\Controllers\AppFiscal\NaturezaController as AppNaturezaController;
use App\Http\Controllers\AppFiscal\NfceAppController;
use App\Http\Controllers\AppFiscal\NotaFiscalAppController;
use App\Http\Controllers\AppFiscal\ProdutoController as AppProdutoController;
use App\Http\Controllers\AppFiscal\VendaCaixaController as AppVendaCaixaController;
use App\Http\Controllers\AppFiscal\VendaController as AppVendaController;
use App\Http\Controllers\ConfigNotaController;
use App\Http\Controllers\NaturezaController;
use App\Http\Controllers\ProductController;
use App\Http\Middleware\HashEmpresa;
use App\Http\Middleware\ResolveFiscalApiTenantContext;
use App\Http\Middleware\ResolveFiscalWebTenantContext;
use App\Models\ConfigNota;
use App\Models\NaturezaOperacao;
use App\Models\Produto;
use App\Models\Venda;
use App\Models\VendaCaixa;
use App\Services\FiscalTenantGuardService;
use Illuminate\Http\Request;
use Tests\TestCase;

class FiscalTenantGuardTest extends TestCase
{
    public function test_api_nfe_substitui_empresa_do_cliente_e_valida_venda_no_tenant(): void
    {
        $guard = new RecordingFiscalTenantGuard();
        $request = $this->requestWithAction(
            '/api/nfe/transmitir',
            'POST',
            NFeController::class . '@transmitir',
            ['id' => 91, 'empresa_id' => 999]
        );

        (new ResolveFiscalApiTenantContext($guard))->handle($request, fn ($req) => response()->json(['ok' => true]));

        $this->assertSame(10, (int) $request->empresa_id);
        $this->assertContains(['venda', 10, 91], $guard->calls);
    }

    public function test_api_nfce_substitui_empresa_do_cliente_e_valida_venda_caixa(): void
    {
        $guard = new RecordingFiscalTenantGuard();
        $request = $this->requestWithAction(
            '/api/nfce/transmitir',
            'POST',
            NFCeController::class . '@transmitir',
            ['id' => 92, 'empresa_id' => 999]
        );

        (new ResolveFiscalApiTenantContext($guard))->handle($request, fn ($req) => response()->json(['ok' => true]));

        $this->assertSame(10, (int) $request->empresa_id);
        $this->assertContains(['venda_caixa', 10, 92], $guard->calls);
    }

    public function test_api_produto_valida_product_id_antes_do_controller(): void
    {
        $guard = new RecordingFiscalTenantGuard();
        $request = $this->requestWithAction(
            '/api/produtos/linha',
            'POST',
            ApiProdutoController::class . '@linhaProdutoCompra',
            ['product_id' => 93, 'empresa_id' => 999]
        );

        (new ResolveFiscalApiTenantContext($guard))->handle($request, fn ($req) => response()->json(['ok' => true]));

        $this->assertSame(10, (int) $request->empresa_id);
        $this->assertContains(['produto', 10, 93], $guard->calls);
    }

    public function test_api_produto_utilitario_sem_dado_empresarial_nao_exige_tenant(): void
    {
        $guard = new RecordingFiscalTenantGuard();
        $request = $this->requestWithAction(
            '/api/produtos/getBarcode',
            'GET',
            ApiProdutoController::class . '@getBarcode',
            ['empresa_id' => 999]
        );

        (new ResolveFiscalApiTenantContext($guard))->handle($request, fn ($req) => response()->json(['ok' => true]));

        $this->assertSame(999, (int) $request->empresa_id);
        $this->assertSame([], $guard->calls);
    }

    public function test_appfiscal_produto_deriva_tenant_do_token_e_valida_recurso(): void
    {
        $guard = new RecordingFiscalTenantGuard();
        $request = $this->requestWithAction(
            '/api/appFiscal/produtos/salvar',
            'POST',
            AppProdutoController::class . '@salvar',
            ['id' => 94, 'empresa_id' => 999]
        );

        (new ResolveFiscalApiTenantContext($guard))->handle($request, fn ($req) => response()->json(['ok' => true]));

        $this->assertSame(20, (int) $request->empresa_id);
        $this->assertContains(['produto', 20, 94], $guard->calls);
    }

    public function test_appfiscal_configuracao_deriva_tenant_e_valida_natureza_padrao(): void
    {
        $guard = new RecordingFiscalTenantGuard();
        $request = $this->requestWithAction(
            '/api/appFiscal/configEmitente/salvar',
            'POST',
            AppConfigEmitenteController::class . '@salvar',
            ['empresa_id' => 999, 'nat_op_padrao' => 95]
        );

        (new ResolveFiscalApiTenantContext($guard))->handle($request, fn ($req) => response()->json(['ok' => true]));

        $this->assertSame(20, (int) $request->empresa_id);
        $this->assertContains(['app_tenant', 20], $guard->calls);
        $this->assertContains(['natureza', 20, 95], $guard->calls);
    }

    public function test_appfiscal_natureza_index_deriva_tenant_do_token(): void
    {
        $guard = new RecordingFiscalTenantGuard();
        $request = $this->requestWithAction(
            '/api/appFiscal/naturezas',
            'GET',
            AppNaturezaController::class . '@index',
            ['empresa_id' => 999]
        );

        (new ResolveFiscalApiTenantContext($guard))->handle($request, fn ($req) => response()->json(['ok' => true]));

        $this->assertSame(20, (int) $request->empresa_id);
        $this->assertContains(['app_tenant', 20], $guard->calls);
    }

    public function test_appfiscal_venda_find_valida_venda_antes_do_controller(): void
    {
        $guard = new RecordingFiscalTenantGuard();
        $request = $this->requestWithAction(
            '/api/appFiscal/vendas/find/101',
            'GET',
            AppVendaController::class . '@getVenda',
            ['empresa_id' => 999],
            ['id' => 101]
        );

        (new ResolveFiscalApiTenantContext($guard))->handle($request, fn ($req) => response()->json(['ok' => true]));

        $this->assertSame(20, (int) $request->empresa_id);
        $this->assertContains(['venda', 20, 101], $guard->calls);
    }

    public function test_appfiscal_venda_delete_valida_venda_antes_da_mutacao(): void
    {
        $guard = new RecordingFiscalTenantGuard();
        $request = $this->requestWithAction(
            '/api/appFiscal/vendas/delete',
            'POST',
            AppVendaController::class . '@delete',
            ['empresa_id' => 999, 'id' => 102]
        );

        (new ResolveFiscalApiTenantContext($guard))->handle($request, fn ($req) => response()->json(['ok' => true]));

        $this->assertSame(20, (int) $request->empresa_id);
        $this->assertContains(['venda', 20, 102], $guard->calls);
    }

    public function test_appfiscal_venda_salvar_valida_natureza_e_todos_os_produtos(): void
    {
        $guard = new RecordingFiscalTenantGuard();
        $request = $this->requestWithAction(
            '/api/appFiscal/vendas/salvar',
            'POST',
            AppVendaController::class . '@salvar',
            [
                'empresa_id' => 999,
                'natureza' => 103,
                'itens' => [
                    ['item_id' => 201],
                    ['item_id' => 202],
                ],
            ]
        );

        (new ResolveFiscalApiTenantContext($guard))->handle($request, fn ($req) => response()->json(['ok' => true]));

        $this->assertSame(20, (int) $request->empresa_id);
        $this->assertContains(['natureza', 20, 103], $guard->calls);
        $this->assertContains(['produtos', 20, [201, 202]], $guard->calls);
    }

    public function test_appfiscal_venda_caixa_find_valida_venda_caixa_antes_do_controller(): void
    {
        $guard = new RecordingFiscalTenantGuard();
        $request = $this->requestWithAction(
            '/api/appFiscal/vendasCaixa/find/104',
            'GET',
            AppVendaCaixaController::class . '@getVenda',
            ['empresa_id' => 999],
            ['id' => 104]
        );

        (new ResolveFiscalApiTenantContext($guard))->handle($request, fn ($req) => response()->json(['ok' => true]));

        $this->assertSame(20, (int) $request->empresa_id);
        $this->assertContains(['venda_caixa', 20, 104], $guard->calls);
    }

    public function test_appfiscal_venda_caixa_delete_valida_venda_caixa_antes_da_mutacao(): void
    {
        $guard = new RecordingFiscalTenantGuard();
        $request = $this->requestWithAction(
            '/api/appFiscal/vendasCaixa/delete',
            'POST',
            AppVendaCaixaController::class . '@delete',
            ['empresa_id' => 999, 'id' => 105]
        );

        (new ResolveFiscalApiTenantContext($guard))->handle($request, fn ($req) => response()->json(['ok' => true]));

        $this->assertSame(20, (int) $request->empresa_id);
        $this->assertContains(['venda_caixa', 20, 105], $guard->calls);
    }

    public function test_appfiscal_venda_caixa_salvar_valida_produtos_e_natureza_padrao_da_config(): void
    {
        $guard = new RecordingFiscalTenantGuard();
        $request = $this->requestWithAction(
            '/api/appFiscal/vendasCaixa/salvar',
            'POST',
            AppVendaCaixaController::class . '@salvar',
            [
                'empresa_id' => 999,
                'itens' => [
                    ['item_id' => 203],
                    ['item_id' => 204],
                ],
            ]
        );

        (new ResolveFiscalApiTenantContext($guard))->handle($request, fn ($req) => response()->json(['ok' => true]));

        $this->assertSame(20, (int) $request->empresa_id);
        $this->assertContains(['produtos', 20, [203, 204]], $guard->calls);
        $this->assertContains(['natureza_padrao_config', 20], $guard->calls);
    }

    public function test_appfiscal_nfe_transmitir_valida_venda_do_mesmo_tenant_antes_do_emitente(): void
    {
        $guard = new RecordingFiscalTenantGuard();
        $request = $this->requestWithAction(
            '/api/appFiscal/notaFiscal/transmitir',
            'POST',
            NotaFiscalAppController::class . '@transmitir',
            ['empresa_id' => 999, 'venda_id' => 106]
        );

        (new ResolveFiscalApiTenantContext($guard))->handle($request, fn ($req) => response()->json(['ok' => true]));

        $this->assertSame(20, (int) $request->empresa_id);
        $this->assertContains(['venda', 20, 106], $guard->calls);
    }

    public function test_appfiscal_nfce_transmitir_valida_venda_caixa_do_mesmo_tenant_antes_do_emitente(): void
    {
        $guard = new RecordingFiscalTenantGuard();
        $request = $this->requestWithAction(
            '/api/appFiscal/nfce/transmitir',
            'POST',
            NfceAppController::class . '@transmitir',
            ['empresa_id' => 999, 'venda_id' => 107]
        );

        (new ResolveFiscalApiTenantContext($guard))->handle($request, fn ($req) => response()->json(['ok' => true]));

        $this->assertSame(20, (int) $request->empresa_id);
        $this->assertContains(['venda_caixa', 20, 107], $guard->calls);
    }

    public function test_web_produto_usa_tenant_da_sessao_e_valida_id_da_rota(): void
    {
        $guard = new RecordingFiscalTenantGuard();
        $request = $this->requestWithAction(
            '/produtos/95',
            'PUT',
            ProductController::class . '@update',
            ['empresa_id' => 999],
            ['produto' => 95]
        );

        (new ResolveFiscalWebTenantContext($guard))->handle($request, fn ($req) => response()->json(['ok' => true]));

        $this->assertSame(30, (int) $request->empresa_id);
        $this->assertContains(['produto', 30, 95], $guard->calls);
    }

    public function test_web_natureza_usa_tenant_da_sessao_e_valida_id_da_rota(): void
    {
        $guard = new RecordingFiscalTenantGuard();
        $request = $this->requestWithAction(
            '/naturezas/96',
            'PUT',
            NaturezaController::class . '@update',
            ['empresa_id' => 999],
            ['natureza' => 96]
        );

        (new ResolveFiscalWebTenantContext($guard))->handle($request, fn ($req) => response()->json(['ok' => true]));

        $this->assertSame(30, (int) $request->empresa_id);
        $this->assertContains(['natureza', 30, 96], $guard->calls);
    }

    public function test_acoes_nfe_da_tela_web_usam_tenant_da_sessao_e_validam_venda(): void
    {
        $guard = new RecordingFiscalTenantGuard();
        $request = $this->requestWithAction(
            '/nfe/acoes/transmitir',
            'POST',
            NFeController::class . '@transmitir',
            ['id' => 98, 'empresa_id' => 999]
        );

        (new ResolveFiscalWebTenantContext($guard))->handle(
            $request,
            fn ($req) => response()->json(['ok' => true])
        );

        $this->assertSame(30, (int) $request->empresa_id);
        $this->assertContains(['web_tenant', 30], $guard->calls);
        $this->assertContains(['venda', 30, 98], $guard->calls);
    }

    public function test_consulta_status_nfe_web_usa_tenant_da_sessao_sem_exigir_venda(): void
    {
        $guard = new RecordingFiscalTenantGuard();
        $request = $this->requestWithAction(
            '/nfe/acoes/consulta-status-sefaz',
            'POST',
            NFeController::class . '@consultaStatusSefaz',
            ['empresa_id' => 999]
        );

        (new ResolveFiscalWebTenantContext($guard))->handle(
            $request,
            fn ($req) => response()->json(['ok' => true])
        );

        $this->assertSame(30, (int) $request->empresa_id);
        $this->assertContains(['web_tenant', 30], $guard->calls);
        $this->assertNotContains(['venda', 30, 0], $guard->calls);
    }

    public function test_web_remove_senha_valida_confignota_no_tenant(): void
    {
        $guard = new RecordingFiscalTenantGuard();
        $request = $this->requestWithAction(
            '/configNF/removeSenha/97',
            'GET',
            ConfigNotaController::class . '@removeSenha',
            ['empresa_id' => 999],
            ['id' => 97]
        );

        (new ResolveFiscalWebTenantContext($guard))->handle($request, fn ($req) => response()->json(['ok' => true]));

        $this->assertSame(30, (int) $request->empresa_id);
        $this->assertContains(['config_nota', 30, 97], $guard->calls);
    }

    public function test_hash_empresa_nao_sobrescreve_tenant_ja_verificado(): void
    {
        $request = Request::create('/api/nfe/transmitir', 'POST', [
            'empresa_id' => 999,
            'hash' => 'hash-de-outro-tenant',
        ]);
        $request->attributes->set(FiscalTenantGuardService::VERIFIED_TENANT_ATTRIBUTE, 44);

        $response = (new HashEmpresa())->handle(
            $request,
            fn ($req) => response()->json(['empresa_id' => (int) $req->empresa_id])
        );

        $this->assertSame(44, (int) $request->empresa_id);
        $this->assertSame(44, (int) $response->getData(true)['empresa_id']);
    }

    public function test_hash_empresa_rejeita_request_sem_identidade_de_tenant(): void
    {
        $request = Request::create('/api/nfe/transmitir', 'POST', [
            'empresa_id' => 999,
        ]);

        $response = (new HashEmpresa())->handle(
            $request,
            fn () => response()->json(['ok' => true])
        );

        $this->assertSame(403, $response->getStatusCode());
        $this->assertSame(999, (int) $request->empresa_id);
    }

    public function test_kernel_registra_guardas_de_tenant_fiscal(): void
    {
        $kernel = (string) file_get_contents(app_path('Http/Kernel.php'));

        $this->assertStringContainsString('ResolveFiscalWebTenantContext::class', $kernel);
        $this->assertStringContainsString('ResolveFiscalApiTenantContext::class', $kernel);
    }

    public function test_layout_envia_hash_da_empresa_em_ajax(): void
    {
        $scripts = (string) file_get_contents(resource_path('views/default/components/scripts.blade.php'));

        $this->assertStringContainsString('X-Empresa-Hash', $scripts);
        $this->assertStringContainsString("session('user_logged')['hash_empresa']", $scripts);

        $frontBox = (string) file_get_contents(resource_path('views/frontBox/index.blade.php'));
        $this->assertStringContainsString('X-Empresa-Hash', $frontBox);
        $this->assertStringContainsString("data_get(session('user_logged'), 'hash_empresa')", $frontBox);
        $this->assertStringContainsString('$.ajaxSetup', $frontBox);
    }

    public function test_guard_central_escopa_recurso_por_empresa_id_e_oculta_idor_com_404(): void
    {
        $guardSource = (string) file_get_contents(app_path('Services/FiscalTenantGuardService.php'));

        $this->assertStringContainsString("->where('empresa_id', \$empresaId)", $guardSource);
        $this->assertStringContainsString("->where('id', \$id)", $guardSource);
        $this->assertStringContainsString('->firstOrFail()', $guardSource);
    }

    public function test_config_emitente_appfiscal_cria_confignota_com_empresa_do_contexto(): void
    {
        $source = (string) file_get_contents(app_path('Http/Controllers/AppFiscal/ConfigEmitenteController.php'));

        $this->assertStringContainsString('$empresaId = (int) $request->empresa_id;', $source);
        $this->assertStringContainsString("'empresa_id' => \$empresaId", $source);
    }

    public function test_appfiscal_fiscal_controllers_estao_dentro_do_boundary_central(): void
    {
        $source = (string) file_get_contents(app_path('Http/Middleware/ResolveFiscalApiTenantContext.php'));

        $this->assertStringContainsString('AppNaturezaController::class', $source);
        $this->assertStringContainsString('AppVendaController::class', $source);
        $this->assertStringContainsString('AppVendaCaixaController::class', $source);
        $this->assertStringContainsString('NotaFiscalAppController::class', $source);
        $this->assertStringContainsString('NfceAppController::class', $source);
    }

    private function requestWithAction(
        string $uri,
        string $method,
        string $action,
        array $input = [],
        array $routeParameters = []
    ): Request {
        $request = Request::create($uri, $method, $input);
        $route = new FiscalTenantTestRoute($action, $routeParameters);
        $request->setRouteResolver(fn () => $route);

        $this->assertSame($action, $request->route()->getActionName());

        return $request;
    }
}

class FiscalTenantTestRoute
{
    public function __construct(
        private string $actionName,
        private array $routeParameters = []
    ) {
    }

    public function getActionName(): string
    {
        return $this->actionName;
    }

    public function parameters(): array
    {
        return $this->routeParameters;
    }
}

class RecordingFiscalTenantGuard extends FiscalTenantGuardService
{
    public array $calls = [];

    public function empresaIdPorHash(Request $request): int
    {
        $request->merge(['empresa_id' => 10]);
        $request->attributes->set(self::VERIFIED_TENANT_ATTRIBUTE, 10);
        $this->calls[] = ['hash_tenant', 10];
        return 10;
    }

    public function empresaIdPorTokenApp(Request $request): int
    {
        $request->merge(['empresa_id' => 20]);
        $request->attributes->set(self::VERIFIED_TENANT_ATTRIBUTE, 20);
        $this->calls[] = ['app_tenant', 20];
        return 20;
    }

    public function empresaIdDaSessao(Request $request): int
    {
        $request->merge(['empresa_id' => 30]);
        $request->attributes->set(self::VERIFIED_TENANT_ATTRIBUTE, 30);
        $this->calls[] = ['web_tenant', 30];
        return 30;
    }

    public function venda(int $empresaId, int $id): Venda
    {
        $this->calls[] = ['venda', $empresaId, $id];
        return new Venda();
    }

    public function vendaCaixa(int $empresaId, int $id): VendaCaixa
    {
        $this->calls[] = ['venda_caixa', $empresaId, $id];
        return new VendaCaixa();
    }

    public function natureza(int $empresaId, int $id): NaturezaOperacao
    {
        $this->calls[] = ['natureza', $empresaId, $id];
        return new NaturezaOperacao();
    }

    public function produto(int $empresaId, int $id): Produto
    {
        $this->calls[] = ['produto', $empresaId, $id];
        return new Produto();
    }

    public function produtos(int $empresaId, array $ids): void
    {
        $this->calls[] = ['produtos', $empresaId, array_values($ids)];
    }

    public function configNota(int $empresaId, int $id): ConfigNota
    {
        $this->calls[] = ['config_nota', $empresaId, $id];
        return new ConfigNota();
    }

    public function naturezaPadraoDaConfig(int $empresaId): ?NaturezaOperacao
    {
        $this->calls[] = ['natureza_padrao_config', $empresaId];
        return new NaturezaOperacao();
    }
}
