<?php

namespace App\Jobs;

use App\Models\ApiBrasil;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class EnviarWhatsAppJobOrdens implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $empresa_id;
    protected $numero;
    protected $mensagem;

    /**
     * Create a new job instance.
     *
     * @param int $empresa_id
     * @param string $numero
     * @param string $mensagem
     * @return void
     */
    public function __construct($empresa_id, $numero, $mensagem)
    {
        $this->empresa_id = $empresa_id;
        $this->numero = $numero;
        $this->mensagem = $mensagem;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $apiBrasil = ApiBrasil::where('empresa_id', $this->empresa_id)->first();

        if (!$apiBrasil || !$apiBrasil->DeviceToken || !$apiBrasil->Bearer) {
            Log::warning("API Brasil não configurada corretamente para empresa ID: {$this->empresa_id}");
            return;
        }

        $payload = [
            'number' => $this->numero,
            'text' => $this->mensagem,
        ];

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://gateway.apibrasil.io/api/v2/whatsapp/sendText",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "DeviceToken: {$apiBrasil->DeviceToken}",
                "Authorization: Bearer {$apiBrasil->Bearer}",
            ],
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);

        if ($error) {
            Log::error("Erro ao enviar WhatsApp (Job): " . $error);
        } else {
            Log::info("Mensagem WhatsApp enviada com sucesso para {$this->numero}. Resposta: " . $response);
        }
    }
}
