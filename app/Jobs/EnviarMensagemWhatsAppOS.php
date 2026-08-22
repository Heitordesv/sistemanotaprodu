<?php

namespace App\Jobs;

use App\Helpers\CobrancaMensagemHelper;
use App\Models\Cliente;
use App\Models\EmpresaIntegracao;
use App\Models\OrdemServico;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class EnviarMensagemWhatsAppOS implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $empresa_id;
    protected $numero;
    protected $mensagem;

    public function __construct($empresa_id, $numero, $mensagem)
    {
        $this->empresa_id = $empresa_id;
        $this->numero = $numero;
        $this->mensagem = $mensagem;
    }

    public function handle()
    {
        // Notificações de Ordem de Serviço dependem somente da Evolution/WhatsApp.
        // Não devem depender de Gemini ou das antigas flags do agente de IA.
        $integracao = EmpresaIntegracao::where('empresa_id', $this->empresa_id)
            ->where('whatsapp_provider', 'evolution')
            ->where('whatsapp_ativo', true)
            ->first();

        if (!$integracao) {
            Log::warning('Evolution não configurada/ativa para envio de OS', [
                'empresa_id' => $this->empresa_id,
            ]);
            return;
        }

        $mensagem = $this->mensagemCadastradaDaOs() ?: (string) $this->mensagem;

        SendEvolutionWhatsAppJob::dispatch(
            (int) $this->empresa_id,
            (string) $this->numero,
            $mensagem,
            'ordem_servico'
        );
    }

    private function mensagemCadastradaDaOs(): ?string
    {
        $estado = $this->detectarEstado();
        if (!$estado) {
            return null;
        }

        $numero = preg_replace('/\D/', '', (string) $this->numero);
        $ultimos8 = substr($numero, -8);

        $cliente = Cliente::where('empresa_id', $this->empresa_id)
            ->where(function ($q) use ($ultimos8) {
                $q->where('celular', 'like', '%' . $ultimos8 . '%')
                    ->orWhere('telefone', 'like', '%' . $ultimos8 . '%');
            })
            ->first();

        if (!$cliente) {
            return null;
        }

        // A OS que acabou de mudar de status é a atualização mais recente do cliente.
        $ordem = OrdemServico::where('empresa_id', $this->empresa_id)
            ->where('cliente_id', $cliente->id)
            ->whereRaw('LOWER(estado) = ?', [mb_strtolower($estado)])
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->first();

        if (!$ordem) {
            return null;
        }

        return CobrancaMensagemHelper::osAtualizacao($ordem, $cliente);
    }

    private function detectarEstado(): ?string
    {
        $texto = Str::lower(Str::ascii((string) $this->mensagem));

        if (Str::contains($texto, ['finalizada', 'finalizado'])) {
            return 'finalizado';
        }
        if (Str::contains($texto, ['em andamento'])) {
            return 'Em Andamento';
        }
        if (Str::contains($texto, ['reprovada', 'reprovado'])) {
            return 'reprovado';
        }
        if (Str::contains($texto, ['pronta', 'pronto'])) {
            return 'pronto';
        }
        if (Str::contains($texto, ['pendente'])) {
            return 'pendente';
        }

        return null;
    }
}