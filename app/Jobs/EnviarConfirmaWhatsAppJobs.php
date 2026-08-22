<?php

namespace App\Jobs;

use App\Models\ApiBrasil;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class EnviarConfirmaWhatsAppJobs implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $numero;
    protected $mensagem;
    protected $empresaId;

    public function __construct(string $numero, string $mensagem, int $empresaId)
    {
        $this->numero = $numero;
        $this->mensagem = $mensagem;
        $this->empresaId = $empresaId;
    }

    public function handle()
    {
        $apiBrasil = ApiBrasil::where('empresa_id', $this->empresaId)->first();

        if (!$apiBrasil || !$apiBrasil->DeviceToken || !$apiBrasil->Bearer) {
            Log::warning("Configuração ApiBrasil ausente para empresa ID: {$this->empresaId}");
            return;
        }

        $payload = json_encode([
            'number' => $this->numero,
            'text' => $this->mensagem,
        ]);

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://gateway.apibrasil.io/api/v2/whatsapp/sendText",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "DeviceToken: {$apiBrasil->DeviceToken}",
                "Authorization: Bearer {$apiBrasil->Bearer}"
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            Log::error("Erro ao enviar WhatsApp via API Brasil: " . $err);
        } else {
            Log::info("Mensagem WhatsApp enviada com sucesso para {$this->numero}");
        }
    }
}
