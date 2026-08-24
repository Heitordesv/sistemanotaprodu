<?php

namespace Tests\Unit;

use App\Services\IbptService;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class IbptServiceTest extends TestCase
{
    public function test_it_sends_the_company_credentials_and_product_data_to_ibpt(): void
    {
        Http::fake(['apidoni.ibpt.org.br/*' => Http::response([
            'Codigo' => '09012100', 'UF' => 'MA', 'Descricao' => 'Cafe', 'Nacional' => 12.3,
            'Estadual' => 4.5, 'Importado' => 18.4, 'Municipal' => 0, 'VigenciaInicio' => '01/08/26',
            'VigenciaFim' => '31/08/26', 'Chave' => 'ABC', 'Versao' => '26.1.L', 'Fonte' => 'IBPT',
        ])]);
        $response = (new IbptService('token-da-empresa', '12.345.678/0001-90'))->consulta([
            'ncm' => '0901.21.00', 'uf' => 'ma', 'descricao' => 'Cafe', 'codigoInterno' => '55',
        ]);
        $this->assertSame('09012100', $response->Codigo);
        Http::assertSent(fn ($request) => $request['token'] === 'token-da-empresa'
            && $request['cnpj'] === '12345678000190' && $request['codigo'] === '09012100'
            && $request['uf'] === 'MA' && $request['codigoInterno'] === '55');
    }

    public function test_it_does_not_expose_the_token_when_api_is_unavailable(): void
    {
        Http::fake(['apidoni.ibpt.org.br/*' => Http::response([], 503)]);
        try {
            (new IbptService('token-secreto', '12345678000190'))->consulta(['ncm' => '09012100']);
            $this->fail('A consulta deveria falhar.');
        } catch (RuntimeException $e) {
            $this->assertStringNotContainsString('token-secreto', $e->getMessage());
        }
    }
}
