<?php

namespace App\Jobs;

use App\Models\ConfigNota;
use App\Models\EmpresaIntegracao;
use App\Models\TelaPedidoDeli;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendWhatsappMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $telefone;
    protected $nome;
    protected $mensagem;
    protected $userId;

    public $tries = 3;
    public $backoff = 60;

    public function __construct($telefone, $nome, $mensagem, $userId)
    {
        $this->telefone = $telefone;
        $this->nome = $nome;
        $this->mensagem = $mensagem;
        $this->userId = $userId;
    }

    public function handle()
    {
        $config = ConfigNota::where('user_id', $this->userId)->first();
        $empresaId = (int) ($config?->empresa_id ?: 0);

        if (!$empresaId) {
            Log::warning('Não foi possível identificar empresa para envio WhatsApp legado', [
                'user_id' => $this->userId,
                'telefone' => $this->telefone,
            ]);
            return;
        }

        $integracao = EmpresaIntegracao::where('empresa_id', $empresaId)
            ->where('whatsapp_provider', 'evolution')
            ->where('whatsapp_ativo', true)
            ->first();

        if (!$integracao) {
            Log::warning('Evolution não configurada/ativa para envio WhatsApp legado', [
                'empresa_id' => $empresaId,
                'telefone' => $this->telefone,
            ]);
            return;
        }

        $mensagemPersonalizada = str_replace('[Nome do Cliente]', (string) $this->nome, (string) $this->mensagem);

        SendEvolutionWhatsAppJob::dispatch(
            $empresaId,
            (string) $this->telefone,
            $mensagemPersonalizada,
            'mensagem_legacy'
        );

        TelaPedidoDeli::where('user_id', $this->userId)
            ->where('telefone', $this->telefone)
            ->update(['dataenvio' => now()]);
    }
}