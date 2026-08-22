<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\VendaCaixa;

class NovaVendaEmpresaMail extends Mailable
{

    use Queueable, SerializesModels;


    public $vendaCaixa;
    public $empresa;



    public function __construct($vendaCaixa, $empresa)
    {

        $this->vendaCaixa = $vendaCaixa;
        $this->empresa = $empresa;

    }




    public function build()
    {


        // Carrega os dados da venda
        $this->vendaCaixa->load([
            'itens.produto',
            'fatura'
        ]);



        // ==========================
        // PEGA NOMES DOS PRODUTOS
        // ==========================

        $produtos = $this->vendaCaixa->itens
            ->map(function($item){

                return $item->produto->nome ?? 'Produto '.$item->produto_id;

            })
            ->implode(', ');





        // ==========================
        // PEGA FORMAS DE PAGAMENTO
        // ==========================

        $pagamentos = '';



        if($this->vendaCaixa->fatura && $this->vendaCaixa->fatura->count()){


            $pagamentos = $this->vendaCaixa->fatura
                ->map(function($fatura){

                    return VendaCaixa::getTipoPagamento(
                        $fatura->forma_pagamento
                    );

                })
                ->unique()
                ->implode(', ');


        }else{


            $pagamentos = VendaCaixa::getTipoPagamento(
                $this->vendaCaixa->tipo_pagamento
            );


        }





        // ==========================
        // ASSUNTO DO EMAIL
        // ==========================

        $assunto = "Nova Venda #{$this->vendaCaixa->id}";



        if($produtos){

            $assunto .= " - Produtos: ".$produtos;

        }



        if($pagamentos){

            $assunto .= " - Pagamento: ".$pagamentos;

        }





        return $this

            ->subject($assunto)

            ->view('mail.nova_venda_empresa');


    }


}