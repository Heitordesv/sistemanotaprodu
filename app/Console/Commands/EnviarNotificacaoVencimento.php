<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ContaReceber;
use App\Jobs\NotificarClienteVencimento;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log; // Importação essencial para salvar no log

class EnviarNotificacaoVencimento extends Command
{
    protected $signature = 'notificacao:vencimento';
    protected $description = 'Envia notificações via WhatsApp para contas a vencer (até 5 dias) ou vencidas.';

    public function handle()
    {
        $this->info("🔔 Iniciando envio de notificações de vencimento...");

        $contas = ContaReceber::paraNotificacaoVencimento()->get();
        $total = $contas->count();

        $this->info("📦 Contas encontradas: {$total}");

        $enviadas = 0;
        $puladas = 0;
        $delayTime = 0;
        $delayInterval = 40;

        // 🔹 Array para registrar números já notificados no dia
        $numerosNotificadosHoje = [];

        foreach ($contas as $conta) {
            $cliente = $conta->cliente ?? null;

            if (!$cliente || empty($cliente->celular)) {
                $this->warn("⚠️ Conta ID {$conta->id} ignorada: cliente sem celular.");
                $puladas++;
                continue;
            }

            $numero = '55' . preg_replace('/\D/', '', $cliente->celular);

            // 🔹 Evita notificar o mesmo número mais de uma vez no mesmo dia
            if (in_array($numero, $numerosNotificadosHoje)) {
                $this->warn("⏭️ Número {$numero} já recebeu mensagem hoje. Pulando.");
                $puladas++;
                continue;
            }

            // 🔹 Evita notificar a mesma conta mais de uma vez no mesmo dia
            if ($conta->last_whatsapp_notified_at && Carbon::parse($conta->last_whatsapp_notified_at)->isToday()) {
                $this->warn("⏭️ Conta ID {$conta->id} já notificada hoje. Pulando.");
                $puladas++;
                continue;
            }

            $dataVenc = Carbon::parse($conta->data_vencimento)->format('d/m/Y');
            $valor = number_format($conta->valor_integral, 2, ',', '.');
            $referencia = $conta->referencia ?? 'N/A';
            $nome = $cliente->razao_social ?? $cliente->nome_fantasia ?? $cliente->nome;
            $link = rtrim(config('app.url'), '/') . "/pg/{$conta->id}";

            $dias = Carbon::now()->diffInDays(Carbon::parse($conta->data_vencimento), false);

            if ($dias > 0) {
                $status = "vence em {$dataVenc}";
                $mensagem = "Olá {$nome}! 👋 Sua conta de R\$ {$valor} {$status}. Pague aqui: {$link}. Ref: {$referencia}.";
            } elseif ($dias === 0) {
                $status = "vence hoje ({$dataVenc})";
                $mensagem = "Olá {$nome}! 👋 Sua fatura de R\$ {$valor} {$status}. Pague aqui: {$link}. Ref: {$referencia}.";
            }  else {
                $diasAtraso = abs($dias);
                $plural = $diasAtraso > 1 ? 'dias' : 'dia';
                $status = "vencida há {$diasAtraso} {$plural}";
                $mensagem = "Olá {$nome}! 👋 Verificamos que sua conta de R\$ {$valor} está {$status} (vencimento em {$dataVenc}). 💡 Você pode regularizar facilmente acessando: {$link}. Ref: {$referencia}.";
                }
        
            try {
                // 🔹 Enfileira o envio com atraso para evitar bloqueio
                NotificarClienteVencimento::dispatch(
                    $conta->empresa_id,
                    $numero,
                    $mensagem
                )->delay(now()->addSeconds($delayTime));

                // 🔹 Atualiza a data de última notificação da conta
                $conta->update(['last_whatsapp_notified_at' => now()]);

                // 🔹 Marca o número como já notificado hoje
                $numerosNotificadosHoje[] = $numero;

                $this->info("✅ Enviado: {$nome} ({$numero}) | Conta {$conta->id} | {$status}");
                $enviadas++;
            } catch (\Exception $e) {
                $this->error("❌ Falha ao enviar para {$nome} ({$numero}): " . $e->getMessage());
            }

            $delayTime += $delayInterval;
        }

        $this->info("📨 Concluído: {$enviadas} enviadas | {$puladas} puladas");
        $this->info("🔚 Fim da execução.");
    }
}
