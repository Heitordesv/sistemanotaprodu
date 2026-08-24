<?php

namespace App\Services;

use App\Models\ConfigNota;
use App\Models\Produto;
use App\Models\ProdutoIbpt;
use RuntimeException;

class IbptEmpresaSyncService
{
    public function __construct(private FiscalIssuerUfService $issuerUf)
    {
    }

    public function sync(ConfigNota $config, Produto $produto): ProdutoIbpt
    {
        if ((int) $config->empresa_id !== (int) $produto->empresa_id) {
            throw new RuntimeException('O produto não pertence à empresa ativa.');
        }
        $token = trim((string) $config->getRawOriginal('token_ibpt'));
        if ($token === '') {
            throw new RuntimeException('Cadastre o Token IBPT na configuração do emitente.');
        }
        $ncm = preg_replace('/\D/', '', (string) $produto->NCM);
        if (strlen($ncm) !== 8) {
            throw new RuntimeException('Produto sem NCM válido.');
        }
        $uf = $this->issuerUf->resolve($config);
        if ($uf === '') {
            throw new RuntimeException('A UF do emitente não está configurada.');
        }

        $response = (new IbptService($token, (string) $config->cnpj))->consulta([
            'ncm' => $ncm, 'uf' => $uf, 'extarif' => 0,
            'descricao' => $produto->nome,
            'unidadeMedida' => $produto->unidade_venda ?: 'UN',
            'valor' => (float) $produto->valor_venda,
            'gtin' => $produto->codBarras ?: 'SEM GTIN',
            'codigoInterno' => (string) $produto->id,
        ]);

        return ProdutoIbpt::updateOrCreate(['produto_id' => $produto->id], [
            'codigo' => (string) $response->Codigo,
            'uf' => strtoupper((string) $response->UF),
            'descricao' => mb_substr((string) $response->Descricao, 0, 100),
            'nacional' => (float) $response->Nacional,
            'estadual' => (float) $response->Estadual,
            'importado' => (float) $response->Importado,
            'municipal' => (float) $response->Municipal,
            'vigencia_inicio' => (string) $response->VigenciaInicio,
            'vigencia_fim' => (string) $response->VigenciaFim,
            'chave' => mb_substr((string) $response->Chave, 0, 10),
            'versao' => mb_substr((string) $response->Versao, 0, 10),
            'fonte' => mb_substr((string) $response->Fonte, 0, 40),
        ]);
    }
}
