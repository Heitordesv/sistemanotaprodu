<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Funcionario;
use App\Jobs\EnviarMensagemAniversarioFuncionarioJob;
use Illuminate\Support\Facades\Log;

class SendBirthdayMessages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'birthday:send-funcionario';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envia mensagens de aniversário para funcionários que fazem aniversário hoje.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando verificação de aniversariantes de funcionários...');

        // Importante: use a data_nasci no formato 'm-d' (mês-dia)
        $hoje = date('m-d');
        
        // Busca todos os funcionários que fazem aniversário hoje
        $aniversariantes = Funcionario::whereRaw("DATE_FORMAT(data_nasci, '%m-%d') = '{$hoje}'")
            ->whereNotNull('celular') // Garante que tem celular
            ->get();
        
        $count = $aniversariantes->count();
        $this->info("Encontrados {$count} funcionários aniversariantes hoje.");

        if ($count > 0) {
            foreach ($aniversariantes as $funcionario) {
                // Dispara o Job para cada funcionário, passando o objeto e o ID da empresa
                EnviarMensagemAniversarioFuncionarioJob::dispatch($funcionario, $funcionario->empresa_id);
                $this->comment("Job de aniversário disparado para Funcionário ID: {$funcionario->id} ({$funcionario->nome}).");
            }
        }

        $this->info('Verificação e despacho de Jobs concluídos.');
        Log::info('Agendamento de Mensagens de Aniversário para Funcionários executado com sucesso.');
    }
}