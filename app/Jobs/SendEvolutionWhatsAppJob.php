<?php

namespace App\Jobs;

use App\Models\EmpresaIntegracao;
use App\Services\EvolutionApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class SendEvolutionWhatsAppJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [30, 120, 300];

    public function __construct(
        protected int $empresaId,
        protected string $numero,
        protected string $mensagem,
        protected string $tipo = 'mensagem',
        protected ?int $clienteId = null,
        protected ?int $contaReceberId = null,
        protected ?int $ordemServicoId = null,
    ) {
    }

    public function handle(EvolutionApiService $service): void
    {
        $config = EmpresaIntegracao::where('empresa_id', $this->empresaId)
            ->where('whatsapp_provider', 'evolution')
            ->where('whatsapp_ativo', true)
            ->first();

        if (!$config) {
            throw new RuntimeException('Evolution não está ativa para a empresa ' . $this->empresaId . '.');
        }

        $numero = preg_replace('/\D/', '', $this->numero);
        if (!str_starts_with($numero, '55')) {
            $numero = '55' . $numero;
        }

        $logId = DB::table('whatsapp_message_logs')->insertGetId([
            'empresa_id' => $this->empresaId,
            'cliente_id' => $this->clienteId,
            'conta_receber_id' => $this->contaReceberId,
            'ordem_servico_id' => $this->ordemServicoId,
            'tipo' => $this->tipo,
            'direcao' => 'saida',
            'numero' => $numero,
            'mensagem' => $this->mensagem,
            'provider' => 'evolution',
            'status' => 'processando',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        try {
            $response = $service->sendText($config, $numero, $this->mensagem);
            $messageId = data_get($response, 'key.id') ?? data_get($response, 'message.key.id');

            DB::table('whatsapp_message_logs')->where('id', $logId)->update([
                'provider_message_id' => $messageId,
                'status' => 'enviado',
                'response' => json_encode($response, JSON_UNESCAPED_UNICODE),
                'enviado_em' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            DB::table('whatsapp_message_logs')->where('id', $logId)->update([
                'status' => 'erro',
                'erro' => $e->getMessage(),
                'updated_at' => now(),
            ]);

            Log::error('Falha no envio Evolution', [
                'empresa_id' => $this->empresaId,
                'numero' => $numero,
                'erro' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}