<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PlanoExpiracaoEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $empresa;
    public $planoEmpresa;
    public $diasRestantes;

    public function __construct($empresa, $planoEmpresa, $diasRestantes)
    {
        $this->empresa = $empresa;
        $this->planoEmpresa = $planoEmpresa;
        $this->diasRestantes = $diasRestantes;
    }

    public function build()
    {
        return $this->subject('Alerta: Seu plano está prestes a expirar')
                    ->view('emails.plano_expiracao');
    }
}
