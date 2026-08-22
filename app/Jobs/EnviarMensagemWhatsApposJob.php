<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Models\ApiBrasil; // Importar o modelo ApiBrasil

class EnviarMensagemWhatsApposJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $number;
    protected $text;
    protected $empresaId;

    /**
     * Cria uma nova instância do Job.
     *
     * @param string $number
     * @param string $text
     * @param int $empresaId
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
     *
     * @return void
     */
    public function handle()
    {
        // Buscar configuração da empresa usando o modelo ApiBrasil e empresa_id
        $apiBrasil = ApiBrasil::where('empresa_id', $this->empresaId)->first();

        if (!$apiBrasil || !$apiBrasil->DeviceToken || !$apiBrasil->Bearer) {
            Log::warning("Configuração de API Brasil ausente para enviar mensagem WhatsApp no Job. Empresa ID: " . $this->empresaId);
            return false; // Não tenta enviar se a configuração estiver ausente
        }

        $data = json_encode([
            'number' => $this->number,
            'text' => $this->text,
        ]);

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://gateway.apibrasil.io/api/v2/whatsapp/sendText",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "DeviceToken: {$apiBrasil->DeviceToken}",
                "Authorization: Bearer {$apiBrasil->Bearer}"
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl); // Captura erros do cURL
        curl_close($curl);

        if ($err) {
            Log::error("Erro cURL ao enviar WhatsApp no Job: " . $err . " para número " . $this->number);
            // Você pode adicionar lógica para tentar novamente ou notificar um administrador aqui
            return ['error' => true, 'message' => 'Erro cURL: ' . $err];
        }

        $decodedResponse = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error("Erro ao decodificar resposta JSON da API WhatsApp no Job: " . json_last_error_msg() . " - Resposta: " . $response . " para número " . $this->number);
            return ['error' => true, 'message' => 'Resposta inválida da API WhatsApp.'];
        }

        if (isset($decodedResponse['error']) && $decodedResponse['error']) {
            Log::error("Erro da API WhatsApp no Job para número " . $this->number . ": " . (is_array($decodedResponse['message']) ? implode(', ', $decodedResponse['message']) : $decodedResponse['message']));
        } else {
            Log::info("Mensagem WhatsApp enviada com sucesso no Job para número " . $this->number);
        }

        return $decodedResponse;
    }
}
