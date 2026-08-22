<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\EmailCobranca;
use App\Models\ContaReceber;
use App\Jobs\NotificarEmpresaVencimento;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log; // Importação essencial para salvar no log

class EnviarNotificacaoEmpresaVencimento extends Command
{
    protected $signature = 'notificacao:empresa-vencimento';
    protected $description = 'Envia notificações para empresas com contas a vencer e vencidas, incluindo renovações de planos, por WhatsApp e e-mail.';

    private int $delaySegundos = 0;

    public function handle()
    {
        $this->info("📤 Iniciando envio de notificações para empresas...");

        $this->enviarNotificacoes(2);
        $this->enviarNotificacoes(1);
        $this->enviarNotificacoes(0);
        $this->enviarVencidas();

        $this->info("✅ Notificações finalizadas.");
        return 0;
    }

    private function enviarNotificacoes($diasAntes)
    {
        $contas = ContaReceber::contasParaNotificacaoEmpresa($diasAntes);
        $descricao = match ($diasAntes) {
            2 => 'em 2 dias',
            1 => 'amanhã',
            0 => 'hoje',
        };

        foreach ($contas as $conta) {
            $this->enviarMensagem($conta, "vencer {$descricao}");
        }
    }

    private function enviarVencidas()
    {
        $contas = ContaReceber::contasVencidasEmpresa();
        foreach ($contas as $conta) {
            $this->enviarMensagem($conta, "está vencida");
        }
    }

    private function enviarMensagem($conta, $statusTexto)
    {
        $empresa = $conta->empresa;

        if (!$empresa) {
            $this->warn("⚠️ Conta ID {$conta->id} ignorada: empresa não encontrada.");
            return;
        }

        if (empty($empresa->telefone) && empty($empresa->email)) {
            $this->warn("⚠️ Empresa sem telefone e e-mail: Conta ID {$conta->id} ignorada.");
            return;
        }

        $numero = !empty($empresa->telefone)
            ? '55' . preg_replace('/\D/', '', $empresa->telefone)
            : null;

        $nomeEmpresa = $empresa->razao_social ?? 'Cliente';
        $dataVenc = Carbon::parse($conta->data_vencimento)->format('d/m/Y');
        $valorFormatado = number_format($conta->valor_integral, 2, ',', '.');
     
            $linkPrincipal = config('app.url');
            $linkPagamento = "{$linkPrincipal}pgempresa/{$conta->id}";        $referenciaCobranca = $conta->referencia ?? 'N/A';

        // Dias restantes para vencimento
        $diasRestantes = Carbon::parse($conta->data_vencimento)->diffInDays(now(), false);

        // Mensagem base
        $mensagem = "Olá {$nomeEmpresa}! 👋 Sua fatura no valor de R$ {$valorFormatado}, com vencimento em {$dataVenc}, {$statusTexto}. Referência: {$referenciaCobranca}. Para evitar qualquer interrupção no seu acesso ao sistema, por favor, realize o pagamento o quanto antes. Acesse: {$linkPagamento} Agradecemos a sua atenção! 🙏";

        // Mensagem específica para renovação de plano
        if (method_exists($conta, 'isForPlanRenewal') && $conta->isForPlanRenewal()) {
            $mensagem = "Olá {$nomeEmpresa}! 👋 A renovação do seu plano, no valor de R$ {$valorFormatado}, com vencimento em {$dataVenc}, {$statusTexto}. Referência: {$referenciaCobranca}. Para garantir a continuidade dos seus serviços e evitar o bloqueio do sistema, por favor, faça o pagamento o mais breve possível. Renove agora: {$linkPagamento} Contamos com sua compreensão! 😊";
        }

        // ===== Envio WhatsApp =====
        if ($numero) {
            NotificarEmpresaVencimento::dispatch(
                $conta->empresa_id,
                $numero,
                $mensagem
            )->delay(now()->addSeconds($this->delaySegundos));

            $this->info("📲 WhatsApp agendado para {$nomeEmpresa} ({$numero}) - Conta ID {$conta->id}");
        }

        // ===== Envio e-mail com condição =====
        if (!empty($empresa->email)) {
            if ($diasRestantes <= 2 || $diasRestantes < 0 || (method_exists($conta, 'isForPlanRenewal') && $conta->isForPlanRenewal())) {
                try {
                    Mail::to($empresa->email)->send(
                        new EmailCobranca(
                            $nomeEmpresa,
                            $valorFormatado,
                            $dataVenc,
                            $linkPagamento,
                            $referenciaCobranca
                        )
                    );
                    $this->info("📧 E-mail de cobrança enviado para {$empresa->email}");
                } catch (\Exception $e) {
                    $this->error("❌ Erro ao enviar e-mail para {$empresa->email}: " . $e->getMessage());
                }
            } else {
                $this->info("ℹ️ E-mail NÃO enviado para {$empresa->email} - {$diasRestantes} dia(s) restantes");
            }
        }

        // Incrementa atraso entre envios para evitar sobrecarga
        $this->delaySegundos += 40;
    }
}
