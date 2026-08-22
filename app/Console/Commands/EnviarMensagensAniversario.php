<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cliente;
use App\Jobs\EnviarMensagemAniversarioJob;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class EnviarMensagensAniversario extends Command
{
    protected $signature = 'aniversarios:enviar';
    protected $description = 'Enviar mensagens de aniversário para clientes';

    public function handle()
    {
        $hoje = Carbon::now()->format('m-d');

        Cliente::whereNotNull('data_aniversario')
            ->whereRaw("DATE_FORMAT(data_aniversario, '%m-%d') = ?", [$hoje])
            ->chunk(100, function ($clientes) {

                foreach ($clientes as $cliente) {

                    if (!$cliente->empresa_id) {
                        continue;
                    }

                    dispatch(new EnviarMensagemAniversarioJob(
                        $cliente->id,
                        $cliente->empresa_id
                    ));

                    Log::info("Aniversário enfileirado para {$cliente->id}");
                }
            });

        $this->info('Processo de aniversários finalizado.');
    }
}
