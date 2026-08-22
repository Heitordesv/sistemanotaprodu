<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        \App\Console\Commands\NotificarPlanoVencido::class,
        \App\Console\Commands\SendBirthdayMessages::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        // Agente multiempresa: verifica a agenda de cobrança de cada empresa.
        $schedule->command('cobranca:agentes')
            ->everyFiveMinutes()
            ->withoutOverlapping(10)
            ->onFailure(function () {
                Log::error('Falha na execução do agendamento: cobranca:agentes');
            });

        $schedule->command('aniversarios:enviar')
            ->dailyAt('21:27')
            ->onFailure(function () {
                Log::error('Falha na execução do agendamento: aniversarios:enviar');
            });
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}