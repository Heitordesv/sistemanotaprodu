<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class IbptService
{
    private const ENDPOINT = 'https://apidoni.ibpt.org.br/api/v1/produtos';

    public function __construct(protected string $token, protected string $cnpj)
    {
    }

    public function consulta(array $data): object
    {
        try {
            $response = Http::acceptJson()->connectTimeout(3)->timeout(10)->get(self::ENDPOINT, [
                'token' => $this->token,
                'cnpj' => preg_replace('/\D/', '', $this->cnpj),
                'codigo' => preg_replace('/\D/', '', (string) ($data['ncm'] ?? '')),
                'uf' => strtoupper((string) ($data['uf'] ?? '')),
                'ex' => (int) ($data['extarif'] ?? 0),
                'descricao' => (string) ($data['descricao'] ?? ''),
                'unidadeMedida' => (string) ($data['unidadeMedida'] ?? 'UN'),
                'valor' => (float) ($data['valor'] ?? 0),
                'gtin' => (string) ($data['gtin'] ?? 'SEM GTIN'),
                'codigoInterno' => (string) ($data['codigoInterno'] ?? ''),
            ]);
        } catch (ConnectionException $e) {
            throw new RuntimeException('A API do IBPT não está disponível no momento.', 0, $e);
        }

        if (!$response->successful()) {
            throw new RuntimeException('A API do IBPT recusou a consulta (HTTP '.$response->status().').');
        }

        $payload = $response->json();
        if (isset($payload[0]) && is_array($payload[0])) {
            $payload = $payload[0];
        }
        if (!is_array($payload) || !isset($payload['Codigo'])) {
            throw new RuntimeException('A API do IBPT retornou uma resposta inválida.');
        }

        return (object) $payload;
    }
}
