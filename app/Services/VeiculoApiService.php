<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class VeiculoApiService
{
    protected $baseUrl;
    protected $token;

    public function __construct()
    {
        $this->baseUrl = "https://gateway.apibrasil.io/api/v2/vehicles/base/001/consulta";
        $this->token = env('APIBRASIL_TOKEN'); // Coloque seu token no .env
    }

    /**
     * Consulta veículo
     */
    public function consultar(array $data)
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->token
        ])->post($this->baseUrl, $data);

        if ($response->failed()) {
            return [
                'success' => false,
                'message' => 'Erro ao consultar API Brasil',
                'data' => null
            ];
        }

        return [
            'success' => true,
            'data' => $response->json()
        ];
    }
}
