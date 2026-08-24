<?php

namespace Tests\Feature;

use App\Http\Controllers\PagamentoPlanoController;
use App\Http\Controllers\UserController;
use App\Services\DFeService;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Tests\TestCase;

class TodayLogsRegressionTest extends TestCase
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
    }

    public function test_fiscal_access_denied_json_does_not_expose_stack_trace(): void
    {
        $request = Request::create('/api/nfe/consultar', 'POST');
        $request->headers->set('Accept', 'application/json');

        $response = app(ExceptionHandler::class)->render(
            $request,
            new AccessDeniedHttpException('Empresa não identificada.')
        );

        $this->assertSame(403, $response->getStatusCode());
        $this->assertSame(
            ['message' => 'Empresa não identificada ou acesso não autorizado.'],
            json_decode((string) $response->getContent(), true)
        );
        $this->assertStringNotContainsString('trace', (string) $response->getContent());
    }

    public function test_old_mercado_pago_webhook_is_acknowledged_when_plan_was_deleted(): void
    {
        Schema::create('plano_empresas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('plano_id');
            $table->timestamps();
        });

        $request = Request::create('/payment/notification/266', 'POST', [
            'action' => 'payment.updated',
            'data' => ['id' => '172515322451'],
        ]);

        $response = (new PagamentoPlanoController())->notification($request, 266);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(
            ['received' => true, 'ignored' => true],
            json_decode((string) $response->getContent(), true)
        );
    }

    public function test_login_uses_safe_fallback_when_configured_view_does_not_exist(): void
    {
        Schema::create('planos', function (Blueprint $table) {
            $table->id();
            $table->boolean('visivel')->default(true);
            $table->timestamps();
        });

        config(['app.login_page' => 'inexistente']);

        $view = (new UserController())->newAccess();

        $this->assertSame('login.login', $view->getName());
    }

    public function test_dfe_manifestation_converts_null_justification_to_string(): void
    {
        $tools = new class {
            public $justification;

            public function sefazManifesta($key, $event, string $justification, $sequence): string
            {
                $this->justification = $justification;

                return '<retEnvEvento><retEvento><infEvento><cStat>135</cStat></infEvento></retEvento></retEnvEvento>';
            }
        };

        $reflection = new \ReflectionClass(DFeService::class);
        $service = $reflection->newInstanceWithoutConstructor();
        $property = $reflection->getProperty('tools');
        $property->setAccessible(true);
        $property->setValue($service, $tools);

        $service->desconhecimento(str_repeat('1', 44), 1, null);

        $this->assertSame('', $tools->justification);
    }
}
