<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmailCobranca extends Mailable
{
    use Queueable, SerializesModels;

    public $clienteNome;
    public $valor;
    public $dataVencimento;
    public $linkPagamento;
    public $referencia;

    /**
     * Cria a instância do mailable.
     *
     * @param string $clienteNome
     * @param string $valor
     * @param string $dataVencimento
     * @param string $linkPagamento
     * @param string $referencia
     */
    public function __construct($clienteNome, $valor, $dataVencimento, $linkPagamento, $referencia)
    {
        $this->clienteNome = $clienteNome;
        $this->valor = $valor;
        $this->dataVencimento = $dataVencimento;
        $this->linkPagamento = $linkPagamento;
        $this->referencia = $referencia;
    }

    /**
     * Constrói o e-mail.
     */
    public function build()
    {
        return $this->subject("💰 Cobrança pendente - {$this->referencia}")
                    ->view('emails.cobranca')
                    ->with([
                        'clienteNome' => $this->clienteNome,
                        'valor' => $this->valor,
                        'dataVencimento' => $this->dataVencimento,
                        'linkPagamento' => $this->linkPagamento,
                        'referencia' => $this->referencia,
                    ]);
    }
}
