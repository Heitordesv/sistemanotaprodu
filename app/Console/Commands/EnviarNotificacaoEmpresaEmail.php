<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\EmailCobranca;
use Carbon\Carbon;
use App\Models\ContaReceber;
use Illuminate\Support\Facades\Log; // Importação essencial para salvar no log

class EnviarNotificacaoEmpresaEmail extends Command
{
    protected $signature = 'notificacao:empresa-email';
    protected $description = 'Envia notificações de cobrança por e-mail para empresas (a vencer e vencidas).';

    private int $delaySegundos = 0;

    public function handle()
    {
        $this->info("📤 Iniciando envio de e-mails para empresas...");

        // Contas que vencerão em 2 dias, 1 dia e hoje
        $this->enviarNotificacoes(2);
        $this->enviarNotificacoes(1);
        $this->enviarNotificacoes(0);

        // Contas vencidas
        $this->enviarVencidas();

        $this->info("✅ E-mails finalizados.");
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
            $this->enviarEmail($conta, "vencer {$descricao}");
        }
    }

    private function enviarVencidas()
    {
        $contas = ContaReceber::contasVencidasEmpresa();

        foreach ($contas as $conta) {
            $this->enviarEmail($conta, "está vencida", true);
        }
    }

    private function enviarEmail($conta, $statusTexto, $isVencida = false)
    {
        $empresa = $conta->empresa;

        if (!$empresa || empty($empresa->email)) {
            $this->warn("⚠️ Empresa sem e-mail: Conta ID {$conta->id} ignorada.");
            return;
        }

        $nomeEmpresa = $empresa->razao_social ?? 'Cliente';
        $dataVenc = Carbon::parse($conta->data_vencimento)->format('d/m/Y');
        $valorFormatado = number_format($conta->valor_integral, 2, ',', '.');
        $linkPagamento = env('APP_URL') . "/pgempresa/?item={$conta->id}";
        $referenciaCobranca = $conta->referencia ?? 'N/A';
        $boletoLink = $conta->boleto_link ?? null;

        $diasRestantes = Carbon::parse($conta->data_vencimento)->diffInDays(now(), false);

        try {
            // Template para contas vencidas
            if ($isVencida || $diasRestantes < 0) {
                Mail::send('emails.cobranca_vencida', [
                    'clienteNome' => $nomeEmpresa,
                    'valor' => $valorFormatado,
                    'dataVencimento' => $dataVenc,
                    'linkPagamento' => $linkPagamento,
                    'referencia' => $referenciaCobranca,
                    'boletoLink' => $boletoLink,
                ], function ($message) use ($empresa, $referenciaCobranca) {
                    $message->to($empresa->email)
                            ->subject("⚠️ Fatura Vencida - {$referenciaCobranca}");
                });

                $this->info("📧 E-mail de cobrança VENCIDA enviado para {$empresa->email}");
            } else {
                // Contas a vencer
                Mail::to($empresa->email)->send(
                    new EmailCobranca(
                        $nomeEmpresa,
                        $valorFormatado,
                        $dataVenc,
                        $linkPagamento,
                        $referenciaCobranca,
                        $boletoLink // passa o link do boleto se existir
                    )
                );

                $this->info("📧 E-mail de cobrança enviado para {$empresa->email}");
            }
        } catch (\Exception $e) {
            $this->error("❌ Erro ao enviar e-mail para {$empresa->email}: " . $e->getMessage());
        }

        $this->delaySegundos += 40;
    }
}
