<?php

namespace App\Console\Commands;

use App\Helpers\CobrancaMensagemHelper;
use App\Jobs\SendEvolutionWhatsAppJob;
use App\Models\CobrancaAgentConfig;
use App\Models\ContaReceber;
use App\Models\EmpresaIntegracao;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExecutarAgenteCobranca extends Command
{
    protected $signature = 'cobranca:agentes {--empresa=} {--force}';
    protected $description = 'Executa os agentes de cobrança WhatsApp configurados por empresa usando Evolution API.';

    public function handle(): int
    {
        $configs = CobrancaAgentConfig::query()
            ->where('ativo', true)
            ->when($this->option('empresa'), fn ($q, $empresa) => $q->where('empresa_id', $empresa))
            ->get();

        foreach ($configs as $config) {
            if (!$this->option('force') && !$this->podeExecutarAgora($config)) {
                continue;
            }

            if (!$this->evolutionCobrancaAtiva((int) $config->empresa_id)) {
                continue;
            }

            $this->executarEmpresa($config);
            $config->update(['ultima_execucao_em' => now()]);
        }

        return self::SUCCESS;
    }

    protected function podeExecutarAgora(CobrancaAgentConfig $config): bool
    {
        if ($config->ultima_execucao_em && $config->ultima_execucao_em->isToday()) {
            return false;
        }

        $horaConfigurada = Carbon::createFromFormat('H:i:s', $config->hora_envio ?: '09:00:00');
        return now()->format('H:i') >= $horaConfigurada->format('H:i');
    }

    protected function executarEmpresa(CobrancaAgentConfig $config): void
    {
        $diasAntes = collect($config->dias_antes ?: [5, 3, 1, 0])->map(fn ($v) => (int) $v)->all();
        $diasAtraso = collect($config->dias_atraso ?: [1, 3, 7, 15, 30])->map(fn ($v) => (int) $v)->all();

        $contas = ContaReceber::query()
            ->where('empresa_id', $config->empresa_id)
            ->where('status', 0)
            ->whereNotNull('data_vencimento')
            ->with(['cliente', 'venda.cliente'])
            ->orderBy('data_vencimento')
            ->get();

        $delay = 0;

        foreach ($contas as $conta) {
            $cliente = $conta->getCliente();
            $telefone = $cliente?->celular ?: $cliente?->telefone;

            if (!$cliente || empty($telefone)) {
                continue;
            }

            $vencimento = Carbon::parse($conta->data_vencimento)->startOfDay();
            $dias = now()->startOfDay()->diffInDays($vencimento, false);

            $deveEnviar = ($dias >= 0 && in_array($dias, $diasAntes, true))
                || ($dias < 0 && in_array(abs($dias), $diasAtraso, true));

            if (!$deveEnviar || $this->jaEnviouHoje($config->empresa_id, $conta->id)) {
                continue;
            }

            $numero = preg_replace('/\D/', '', $telefone);
            $mensagem = CobrancaMensagemHelper::montarCobranca($config, $conta, $cliente);

            SendEvolutionWhatsAppJob::dispatch(
                (int) $config->empresa_id,
                $numero,
                $mensagem,
                'cobranca',
                $cliente->id,
                $conta->id,
                null
            )->delay(now()->addSeconds($delay));

            $delay += 35;
        }
    }

    protected function jaEnviouHoje(int $empresaId, int $contaId): bool
    {
        return DB::table('whatsapp_message_logs')
            ->where('empresa_id', $empresaId)
            ->where('conta_receber_id', $contaId)
            ->whereIn('tipo', ['cobranca', 'cobranca_manual'])
            ->whereDate('created_at', today())
            ->whereIn('status', ['processando', 'enviado'])
            ->exists();
    }

    protected function evolutionCobrancaAtiva(int $empresaId): bool
    {
        return EmpresaIntegracao::where('empresa_id', $empresaId)
            ->where('whatsapp_provider', 'evolution')
            ->where('whatsapp_ativo', true)
            ->where('agente_cobranca', true)
            ->exists();
    }
}