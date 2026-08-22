<?php

namespace App\Jobs;

use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\ApiBrasil;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class EnviarMensagemAniversarioJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $cliente_id;
    protected int $empresa_id;

    public $tries = 1; // evita repetir erro
    public $timeout = 30; // evita travar worker

    public function __construct(int $cliente_id, int $empresa_id)
    {
        $this->cliente_id = $cliente_id;
        $this->empresa_id = $empresa_id;
    }

    public function handle(): void
    {
        $cliente = Cliente::find($this->cliente_id);
        $empresa = Empresa::find($this->empresa_id);
        $apiBrasil = ApiBrasil::where('empresa_id', $this->empresa_id)->first();

        if (!$cliente || !$empresa || !$apiBrasil) {
            return;
        }

        if (!$apiBrasil->DeviceToken || !$apiBrasil->Bearer) {
            Log::warning("API Brasil não configurada para empresa {$this->empresa_id}");
            return;
        }

        if (empty($cliente->celular)) {
            return;
        }

        $primeiroNome = explode(' ', trim($cliente->razao_social))[0];
        $nomeEmpresa = $empresa->nome_fantasia ?? $empresa->razao_social;

        $mensagem = "🎉 Olá *{$primeiroNome}*!\n\n" .
            "Hoje é um dia especial e toda a equipe da *{$nomeEmpresa}* quer celebrar você! 🥳\n\n" .
            "Desejamos muita saúde, felicidade e momentos incríveis! 💖\n\n" .
            "Parabéns por mais um ano de vida! 🎂✨";

        $numero = preg_replace('/\D/', '', $cliente->celular);

        $payload = json_encode([
            'number' => "55{$numero}",
            'text'   => $mensagem,
        ], JSON_UNESCAPED_UNICODE);

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://gateway.apibrasil.io/api/v2/whatsapp/sendText",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "DeviceToken: {$apiBrasil->DeviceToken}",
                "Authorization: Bearer {$apiBrasil->Bearer}",
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            Log::error("Erro ao enviar aniversário para {$numero}: {$err}");
        }
    }
}
