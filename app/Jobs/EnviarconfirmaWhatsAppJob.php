<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Models\ConfigNota;
use App\Models\ApiBrasil;
use App\Models\TelaPedidoDeli; // Importe o modelo TelaPedidoDeli

class EnviarconfirmaWhatsAppJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $number;
    protected $text;
    protected $empresaId;

    // Propriedades para retentativas (tries) e backoff (tempo de espera entre retentativas)
    public $tries = 3; // Tentará executar o Job até 3 vezes em caso de falha
    public $backoff = 60; // Esperará 60 segundos antes de cada retentativa

    /**
     * Cria uma nova instância do Job.
     *
     * @param string $number O número de telefone do destinatário.
     * @param string $text A mensagem a ser enviada.
     * @param int $empresaId O ID da empresa para buscar configurações.
     * @return void
     */
    public function __construct(string $number, string $text, int $empresaId)
    {
        $this->number = $number;
        $this->text = $text;
        $this->empresaId = $empresaId;
    }

    /**
     * Executa o Job.
     * Este método contém a lógica para enviar a mensagem via API do WhatsApp.
     * @return void
     * @throws \Exception Se ocorrer um erro crítico que impeça o envio ou retentativa.
     */
    public function handle()
    {
        // 1. Buscar configurações da empresa
        $config = ConfigNota::where('empresa_id', $this->empresaId)->first();

        if (!$config) {
            $errorMessage = "Job WhatsApp (empresa_id: {$this->empresaId}): Nenhuma configuração encontrada para envio.";
            Log::error($errorMessage);
            throw new \Exception($errorMessage); // Lança exceção para retentativa ou falha
        }

        if (!$config->user_id) {
            $errorMessage = "Job WhatsApp (empresa_id: {$this->empresaId}): user_id não configurado na ConfigNota.";
            Log::error($errorMessage);
            throw new \Exception($errorMessage);
        }

        // 2. Buscar configurações da API Brasil usando o user_id da ConfigNota
        $apiBrasilConfig = ApiBrasil::where('user_id', $config->user_id)->first();

        if (!$apiBrasilConfig) {
            $errorMessage = "Job WhatsApp (user_id: {$config->user_id}): Configuração da API Brasil não encontrada.";
            Log::error($errorMessage);
            throw new \Exception($errorMessage);
        }

        $DeviceToken = $apiBrasilConfig->DeviceToken;
        $Bearer = $apiBrasilConfig->Bearer;

        if (!$DeviceToken || !$Bearer) {
            $errorMessage = "Job WhatsApp (user_id: {$config->user_id}): Token DeviceToken ou Bearer não configurados.";
            Log::error($errorMessage);
            throw new \Exception($errorMessage);
        }

        // A mensagem já vem formatada do controller
        $payload = json_encode([
            'number' => $this->number,
            'text' => $this->text,
        ]);

        // 3. Inicializa e executa a requisição cURL
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://gateway.apibrasil.io/api/v2/whatsapp/sendText",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "DeviceToken: $DeviceToken",
                "Authorization: Bearer $Bearer"
            ],
            CURLOPT_VERBOSE => false, // Defina como true para ver detalhes da conexão no log de erros do PHP
            CURLOPT_SSL_VERIFYPEER => true, // SEMPRE TRUE em produção
            CURLOPT_SSL_VERIFYHOST => 2,    // SEMPRE 2 em produção
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        $errno = curl_errno($curl);
        curl_close($curl);

        // 4. Tratar a resposta da requisição cURL
        if ($err) {
            $errorMessage = "Job WhatsApp: ERRO CRÍTICO CURL ao enviar para {$this->number}. Erro CURL ({$errno}): {$err}. Resposta: " . ($response ?: 'N/A');
            Log::error($errorMessage);
            throw new \Exception($errorMessage); // Lança exceção para retentativa
        } else {
            $responseData = json_decode($response, true);

            if (isset($responseData['status']) && $responseData['status'] === 'success') {
                Log::info("Job WhatsApp: Mensagem enviada com sucesso para {$this->number}.");

                // 5. Atualizar a tabela TelaPedidoDeli após o sucesso, usando o user_id da ConfigNota
                // Assumindo que TelaPedidoDeli tem 'user_id' e 'telefone'
                TelaPedidoDeli::where('user_id', $config->user_id) // Use o user_id da ConfigNota
                              ->where('telefone', $this->number) // Use o número do Job
                              ->update(['dataenvio' => now()]); // Atualiza o campo 'dataenvio'

                Log::info("Job WhatsApp: Campo 'dataenvio' atualizado em TelaPedidoDeli para user_id: {$config->user_id}, telefone: {$this->number}.");

            } else {
                $errorMessage = $responseData['message'] ?? 'Erro desconhecido da API. Nenhuma mensagem de erro específica fornecida.';
                Log::error("Job WhatsApp: Falha da API ao enviar para {$this->number}. Resposta da API: " . json_encode($responseData));
                throw new \Exception("Erro da API: {$errorMessage}"); // Lança exceção para retentativa
            }
        }
    }
}