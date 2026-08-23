<?php

namespace Tests\Feature;

use App\Models\Certificado;
use App\Models\ConfigNota;
use App\Services\FiscalCertificateService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class FiscalSecretsSecurityTest extends TestCase
{
    private string $certificadosTable;

    protected function setUp(): void
    {
        parent::setUp();

        $this->certificadosTable = (new Certificado())->getTable();
        Schema::dropIfExists($this->certificadosTable);

        Schema::create($this->certificadosTable, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('empresa_id');
            $table->text('senha');
            $table->longText('arquivo');
            $table->timestamps();
        });

        DB::table($this->certificadosTable)->insert([
            'empresa_id' => 20,
            'senha' => 'senha-empresa-b',
            'arquivo' => 'certificado-empresa-b',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists($this->certificadosTable);
        parent::tearDown();
    }

    public function test_substituir_certificado_da_empresa_a_nao_altera_empresa_b(): void
    {
        $service = new FiscalCertificateService();

        $service->replaceForEmpresa(10, 'certificado-a-v1', 'senha-a-v1');
        $service->replaceForEmpresa(10, 'certificado-a-v2', 'senha-a-v2');

        $this->assertSame(1, Certificado::where('empresa_id', 10)->count());
        $this->assertSame(1, Certificado::where('empresa_id', 20)->count());

        $certificadoA = Certificado::where('empresa_id', 10)->firstOrFail();
        $certificadoB = Certificado::where('empresa_id', 20)->firstOrFail();

        $this->assertSame('senha-a-v2', $certificadoA->senha);
        $this->assertSame('certificado-a-v2', $certificadoA->arquivo);
        $this->assertSame('senha-empresa-b', $certificadoB->senha);
        $this->assertSame('certificado-empresa-b', $certificadoB->arquivo);
    }

    public function test_excluir_certificado_da_empresa_a_preserva_empresa_b(): void
    {
        $service = new FiscalCertificateService();
        $service->replaceForEmpresa(10, 'certificado-a', 'senha-a');

        $service->deleteForEmpresa(10);

        $this->assertSame(0, Certificado::where('empresa_id', 10)->count());
        $this->assertSame(1, Certificado::where('empresa_id', 20)->count());
    }

    public function test_certificado_nao_serializa_senha_nem_arquivo(): void
    {
        $certificado = new Certificado([
            'empresa_id' => 10,
            'senha' => 'segredo-certificado',
            'arquivo' => 'bytes-pfx',
        ]);

        $data = $certificado->toArray();

        $this->assertArrayNotHasKey('senha', $data);
        $this->assertArrayNotHasKey('arquivo', $data);
        $this->assertSame(10, $data['empresa_id']);
    }

    public function test_config_nota_nao_serializa_segredos_fiscais(): void
    {
        $config = new ConfigNota([
            'empresa_id' => 10,
            'cnpj' => '12345678000199',
            'csc' => 'CSC-SECRETO',
            'senha' => 'SENHA-PFX',
            'arquivo' => 'PFX-BYTES',
            'token_ibpt' => 'TOKEN-IBPT',
            'token_nfse' => 'TOKEN-NFSE',
            'DeviceToken' => 'DEVICE-TOKEN',
            'Bearer' => 'BEARER-TOKEN',
            'senha_remover' => 'HASH-REMOVER',
        ]);

        $data = $config->toArray();

        foreach (['csc', 'senha', 'arquivo', 'token_ibpt', 'token_nfse', 'DeviceToken', 'Bearer', 'senha_remover'] as $secret) {
            $this->assertArrayNotHasKey($secret, $data, $secret . ' não pode ser serializado');
        }

        $this->assertSame(10, $data['empresa_id']);
        $this->assertSame('12345678000199', $data['cnpj']);
    }

    public function test_appfiscal_nao_usa_truncate_nem_certificado_global(): void
    {
        $source = (string) file_get_contents(app_path('Http/Controllers/AppFiscal/ConfigEmitenteController.php'));

        $this->assertStringNotContainsString('truncate()', $source);
        $this->assertStringNotContainsString('Certificado::first', $source);
        $this->assertStringContainsString('FiscalCertificateService', $source);
        $this->assertStringContainsString('(int) $request->empresa_id', $source);
    }

    public function test_pdv_nao_retorna_csc_ou_senha_do_certificado(): void
    {
        $source = (string) file_get_contents(app_path('Http/Controllers/Pdv/ConfigController.php'));

        $this->assertStringNotContainsString("'csc' =>", $source);
        $this->assertStringNotContainsString('senhaCertificado', $source);
        $this->assertStringContainsString('csc_configurado', $source);
        $this->assertStringContainsString('certificado_configurado', $source);
    }

    public function test_web_nao_grava_novo_certificado_em_diretorio_publico(): void
    {
        $source = (string) file_get_contents(app_path('Http/Controllers/ConfigNotaController.php'));

        $this->assertStringNotContainsString("public_path('certificados')", $source);
        $this->assertStringContainsString("storage_path('app/private/certificados/'", $source);
        $this->assertStringContainsString("->where('empresa_id', request()->empresa_id)", $source);
    }
}
