<?php

namespace App\Jobs;

use App\Models\Funcionario;
use App\Models\Empresa;
use App\Models\ApiBrasil;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class EnviarMensagemAniversarioFuncionarioJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Funcionario $funcionario;
    protected int $empresa_id;

    /**
     * Cria uma nova instância do job.
     *
     * @param \App\Models\Funcionario $funcionario O funcionário que está fazendo aniversário.
     * @param int $empresa_id O ID da empresa para buscar a configuração da API.
     */
    public function __construct(Funcionario $funcionario, int $empresa_id)
    {
        $this->funcionario = $funcionario;
        $this->empresa_id = $empresa_id;
    }

    /**
     * Executa o job (lógica de envio).
     */
    public function handle(): void
    {
        // 1. Configurações da API e Empresa
        $apiBrasil = ApiBrasil::where('empresa_id', $this->empresa_id)->first();
        $empresa   = Empresa::find($this->empresa_id);

        if (!$apiBrasil || !$apiBrasil->DeviceToken || !$apiBrasil->Bearer) {
            Log::warning("Configuração API Brasil ausente para empresa ID: {$this->empresa_id}");
            return;
        }

        if (empty($this->funcionario->celular)) {
            Log::info("Funcionário {$this->funcionario->id} ({$this->funcionario->nome}) sem número de celular. Ignorado.");
            return;
        }
        
        // 2. Criação da Mensagem
        $nomeCompleto = $this->funcionario->nome;
        $primeiroNome = explode(' ', trim($nomeCompleto))[0];
        $nomeEmpresa = $empresa->nome_fantasia ?? $empresa->razao_social;

        $mensagem = "🥳 Olá *{$primeiroNome}*! \n\n" .
                    "Hoje é um dia especial! Toda a equipe da *{$nomeEmpresa}* te deseja um Feliz Aniversário! 🎉\n\n" .
                    "Agradecemos o seu empenho e dedicação. Que este novo ciclo traga muita saúde e sucesso! 🎂✨\n\n" .
                    "Parabéns por mais um ano de vida!";


        // 3. Preparação para Envio
        $numero = preg_replace('/\D/', '', $this->funcionario->celular);

        $payload = json_encode([
            'number' => "55{$numero}", // Adiciona o DDI brasileiro
            'text'   => $mensagem,
        ]);

        // 4. Envio via cURL
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://gateway.apibrasil.io/api/v2/whatsapp/sendText",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
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
            Log::error("Erro ao enviar mensagem de aniversário para funcionário {$numero}: " . $err);
        } else {
            Log::info("🎂 Mensagem de aniversário enviada com sucesso para funcionário {$numero}");
        }
    }
}