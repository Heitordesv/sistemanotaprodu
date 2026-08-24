<style>
    #modal-dados_cartao .modal-dialog {
        width: min(760px, calc(100vw - 24px));
        margin-right: auto;
        margin-left: auto;
    }

    #modal-dados_cartao .dados-cartao-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(min(100%, 220px), 1fr));
        gap: 16px;
        width: 100%;
    }

    #modal-dados_cartao .dados-cartao-grid > div,
    #modal-dados_cartao .form-control,
    #modal-dados_cartao .form-select {
        width: 100%;
        min-width: 0;
    }

    #modal-dados_cartao label {
        display: block;
        margin-bottom: 6px;
        line-height: 1.35;
    }

    @media (max-width: 575.98px) {
        #modal-dados_cartao .modal-header,
        #modal-dados_cartao .modal-body,
        #modal-dados_cartao .modal-footer {
            padding-right: 16px;
            padding-left: 16px;
        }

        #modal-dados_cartao .modal-title {
            font-size: 1rem;
        }

        #modal-dados_cartao .modal-footer .btn {
            width: 100%;
        }
    }
</style>

<div class="modal fade" id="modal-dados_cartao" tabindex="-1"
    aria-labelledby="modal-dados-cartao-titulo" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-dados-cartao-titulo">Informe os dados do cartão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <div class="dados-cartao-grid">
                    <div>
                        {!! Form::select('bandeira_cartao', 'Bandeira', ["" => "Selecione"] + App\Models\VendaCaixa::bandeiras())
                        ->attrs(['class' => 'form-select']) !!}
                    </div>
                    <div>
                        {!! Form::tel('cAut_cartao', 'Código de autorização (opcional)') !!}
                    </div>
                    <div>
                        {!! Form::tel('cnpj_cartao', 'CNPJ (opcional)')->attrs(['class' => 'cnpj']) !!}
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button data-bs-dismiss="modal" type="button" class="btn btn-primary px-5">OK</button>
            </div>
        </div>
    </div>
</div>
