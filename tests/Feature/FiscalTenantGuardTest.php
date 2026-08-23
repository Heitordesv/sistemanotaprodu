<?php

namespace Tests\Feature;

use App\Http\Controllers\API\NFCeController;
use App\Http\Controllers\API\NFeController;
use App\Http\Controllers\API\ProdutoController as ApiProdutoController;
use App\Http\Controllers\AppFiscal\ConfigEmitenteController as AppConfigEmitenteController;
use App\Http\Controllers\AppFiscal\ProdutoController as AppProdutoController;
use App\Http\Controllers\ConfigNotaController;
use App\Http\Controllers\NaturezaController;
use App\Http\Controllers\ProductController;
use App\Http\Middleware\ResolveFiscalApiTenantContext;
use App\Http\Middleware\ResolveFiscalWebTenantContext;
use App\Models\ConfigNota;
use App\Models\NaturezaOperacao;
use App\Models\Produto;
use App\Models\Venda;
use App\Models\VendaCaixa;
use App\Services\FiscalTenantGuardService;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
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

    public function test_appfiscal_produto_deriva_tenant_do_token_e_valida_recurso(): void
    {
        $guard = new RecordingFiscalTenantGuard();
        $request = $this->requestWithAction(
            '/api/app/produtos/94',
            'PUT',
            AppProdutoController::class . '@update',
            ['empresa_id' => 999],
            ['produto' => 94]
        );

        (new ResolveFiscalApiTenantContext($guard))->handle($request, fn ($req) => response()->json(['ok' => true]));

        $this->assertSame(20, (int) $request->empresa_id);
        $this->assertContains(['produto', 20, 94], $guard->calls);
    }

    public function test_appfiscal_configuracao_deriva_tenant_do_token(): void
    {
        $guard = new RecordingFiscalTenantGuard();
        $request = $this->requestWithAction(
            '/api/app/config/salvar',
            'POST',
            AppConfigEmitenteController::class . '@salvar',
            ['empresa_id' => 999]
        );

        (new ResolveFiscalApiTenantContext($guard))->handle($request, fn ($req) => response()->json(['ok' => true]));

        $this->assertSame(20, (int) $request->empresa_id);
        $this->assertContains(['app_tenant', 20], $guard->calls);
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
    }

    public function test_guard_central_escopa_recurso_por_empresa_id_e_oculta_idor_com_404(): void
    {
        $guardSource = (string) file_get_contents(app_path('Services/FiscalTenantGuardService.php'));

        $this->assertStringContainsString("->where('empresa_id', \$empresaId)", $guardSource);
        $this->assertStringContainsString("->where('id', \$id)", $guardSource);
        $this->assertStringContainsString('->firstOrFail()', $guardSource);
    }

    private function requestWithAction(
        string $uri,
        string $method,
        string $action,
        array $input = [],
        array $routeParameters = []
    ): Request {
        $request = Request::create($uri, $method, $input);
        $route = new Route([$method], $uri, ['uses' => $action]);

        foreach ($routeParameters as $key => $value) {
            $route->setParameter($key, $value);
        }

        $request->setRouteResolver(fn () => $route);

        return $request;
    }
}

class RecordingFiscalTenantGuard extends FiscalTenantGuardService
{
    public array $calls = [];

    public function empresaIdPorHash(Request $request): int
    {
        $request->merge(['empresa_id' => 10]);
        $this->calls[] = ['hash_tenant', 10];
        return 10;
    }

    public function empresaIdPorTokenApp(Request $request): int
    {
        $request->merge(['empresa_id' => 20]);
        $this->calls[] = ['app_tenant', 20];
        return 20;
    }

    public function empresaIdDaSessao(Request $request): int
    {
        $request->merge(['empresa_id' => 30]);
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

    public function configNota(int $empresaId, int $id): ConfigNota
    {
        $this->calls[] = ['config_nota', $empresaId, $id];
        return new ConfigNota();
    }
}
