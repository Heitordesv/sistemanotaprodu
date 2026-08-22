<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class StoneService
{
    protected $client;
    protected $baseUrl;
    protected $token;
    protected $accountId;

    public function __construct()
    {
        $this->baseUrl = config('services.stone.base_url');
        $this->token = config('services.stone.token');
        $this->accountId = config('services.stone.account_id');

        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout'  => 15,
        ]);
    }

    /**
     * Lista boletos da Stone
     */
    public function listarBoletos($params = [])
    {
        try {
            $query = array_merge(['account_id' => $this->accountId], $params);

            $response = $this->client->get('/barcode_payment_invoices', [
                'headers' => [
                    'Authorization' => "Bearer {$this->token}",
                    'Accept'        => 'application/json',
                ],
                'query' => $query,
            ]);

            return json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            Log::error('Erro ao listar boletos Stone: ' . $e->getMessage());
            return null;
        }
    }
}
