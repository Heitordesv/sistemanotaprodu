<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PlanoEmpresa;
use App\Mail\PlanoExpiracaoEmail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log; // Importação essencial para salvar no log

class EnviarNotificacaoEmpresaalertEmail extends Command
{
    // O nome do comando foi atualizado para ser mais claro
    protected $signature = 'plano:notificar-expiracao';
    protected $description = 'Envia notificações para clientes cujo plano está prestes a expirar';

    public function handle()
    {
        $this->info("Iniciando verificação de planos próximos da expiração...");

        $hoje = Carbon::now();

        // Pega todos planos que expiram nos próximos 60 dias
        $planos = PlanoEmpresa::with('empresa')
            ->whereDate('expiracao', '>=', $hoje)
            ->whereDate('expiracao', '<=', $hoje->copy()->addDays(60))
            ->get();

        $enviadas = 0;
        $puladas = 0;

        foreach ($planos as $planoEmpresa) {
            $empresa = $planoEmpresa->empresa;

            if (!$empresa || empty($empresa->email)) {
                $this->warn("PlanoEmpresa ID {$planoEmpresa->id} ignorado: Empresa sem email.");
                $puladas++;
                continue;
            }

            $diasRestantes = $hoje->diffInDays(Carbon::parse($planoEmpresa->expiracao), false);

            // Condição de envio: hoje OU no dia de expiração OU a cada 5 dias
            if (!$planoEmpresa->primeiro_envio_realizado || $diasRestantes == 0 || $diasRestantes % 5 == 0) {

                try {
                    // Tenta enviar o email
                    Mail::to($empresa->email)->send(new PlanoExpiracaoEmail($empresa, $planoEmpresa, $diasRestantes));

                    // Marca que o primeiro envio já foi realizado (dentro do bloco try)
                    if (!$planoEmpresa->primeiro_envio_realizado) {
                        $planoEmpresa->primeiro_envio_realizado = true;
                        $planoEmpresa->save();
                    }

                    $this->info("✅ Email enviado para {$empresa->nome_fantasia} (PlanoEmpresa ID {$planoEmpresa->id}) | Dias restantes: {$diasRestantes}");
                    $enviadas++;

                } catch (\Exception $e) {
                    // REGISTRA O ERRO NO LOG DO SISTEMA
                    Log::error("Erro ao enviar email de expiração de plano para {$empresa->email} (ID Plano: {$planoEmpresa->id}). Erro: " . $e->getMessage());
                    $this->error("❌ Erro ao enviar email para {$empresa->email}. Veja o log para detalhes.");
                    $puladas++;
                }

            } else {
                $this->info("PlanoEmpresa ID {$planoEmpresa->id} fora do intervalo de notificação ({$diasRestantes} dias restantes) | Empresa: {$empresa->nome_fantasia}");
                $puladas++;
            }
        }

        $this->info("✅ Total de emails enviados: $enviadas | ❌ Puladas: $puladas");
    }
}