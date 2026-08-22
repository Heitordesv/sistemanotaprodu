<?php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Lead;
use App\Models\ApiBrasil;

class EnviarMensagemWhatsAppLeds implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $leadId;
    public $mensagem;
    public $atendente;
    public $empresa_id;

    /**
     * Construtor do Job
     *
     * @param int $leadId
     * @param string $mensagem
     * @param string $atendente
     * @param int|null $empresa_id
     */
    public function __construct($leadId, $mensagem, $atendente = 'Equipe Mixk Solutions', $empresa_id = null)
    {
        $this->leadId = $leadId;
        $this->mensagem = $mensagem;
        $this->atendente = $atendente;
        $this->empresa_id = $empresa_id;
    }

    public function handle()
    {
        // Adiciona uma linha de log para verificar se o Job está sendo executado
        Log::info("Job EnviarMensagemWhatsAppLeds iniciado para Lead ID: {$this->leadId} e Empresa ID: {$this->empresa_id}");

        $lead = Lead::find($this->leadId);
        if (!$lead || !$lead->whatsapp) {
            // Loga se o lead não foi encontrado ou não tem número de WhatsApp
            Log::warning("Job EnviarMensagemWhatsAppLeds: Lead ID {$this->leadId} não encontrado ou sem WhatsApp.");
            return;
        }

        $numero = $this->formatarWhatsapp($lead->whatsapp);

        // Substitui {atendente} e {lead} na mensagem, caso existam placeholders
        $mensagemFinal = str_replace(
            ['{lead}', '{atendente}'],
            [$lead->nome_completo ?? 'Cliente', $this->atendente],
            $this->mensagem
        );

        // Loga a mensagem final que será enviada
        Log::info("Mensagem final formatada: {$mensagemFinal} para o número: {$numero}");

        // Busca credenciais da API Brasil por empresa
      // Busca credenciais da API Brasil por empresa (corrigido)
$empresaId = $this->empresa_id ?? 1; // fallback para 1, se não informado
$apiBrasil = ApiBrasil::where('empresa_id', $empresaId)->first();

if (!$apiBrasil || !$apiBrasil->DeviceToken || !$apiBrasil->Bearer) {
    Log::warning("API Brasil não configurada corretamente para empresa ID: {$empresaId}. Não foi possível enviar a mensagem.");
    $lead->status = "Descartado";
    $lead->save();
    return;
        }

        try {
            Log::info("Enviando requisição para a API Brasil...");
            $data = [
                'number' => $numero,
                'text'   => $mensagemFinal,
            ];

            $response = Http::withHeaders([
                "Content-Type"  => "application/json",
                "DeviceToken"   => $apiBrasil->DeviceToken,
                "Authorization" => "Bearer {$apiBrasil->Bearer}",
            ])->post("https://gateway.apibrasil.io/api/v2/whatsapp/sendText", $data);

            if ($response->successful()) {
                $lead->status = "Em Contato";
                Log::info("Mensagem enviada com sucesso para Lead {$lead->id}. Resposta da API: " . $response->body());
            } else {
                $lead->status = "Descartado";
                Log::error("Erro API Brasil ao enviar para Lead {$lead->id}. Resposta: " . $response->body());
            }

            $lead->save();

        } catch (\Exception $e) {
            $lead->status = "Descartado";
            $lead->save();
            Log::error("Falha geral ao enviar WhatsApp para Lead {$lead->id}: " . $e->getMessage());
        }
    }

    private function formatarWhatsapp($numero)
    {
        $numero = preg_replace('/\D/', '', $numero);
        if (strlen($numero) == 11) {
            $numero = '55' . $numero;
        }
        return $numero;
    }
}