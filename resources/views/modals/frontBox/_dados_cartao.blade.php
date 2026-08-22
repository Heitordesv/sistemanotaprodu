<div class="modal fade" id="modal-dados_cartao" aria-modal="true" role="dialog" style="overflow:scroll;"
tabindex="-1">
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">INFORME OS DADOS DO CARTÃO</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body row">
            <div class="col-md-4">
                {!! Form::select('bandeira_cartao', 'Bandeira', ["" => "Selecione"] + App\Models\VendaCaixa::bandeiras())
                ->attrs(['class' => 'form-select']) !!}
            </div>
            <div class="col-md-4">
                {!! Form::tel('cAut_cartao', 'Código autorização(opcional)')->attrs(['class' => '']) !!}
            </div>
            <div class="col-md-4">
                {!! Form::tel('cnpj_cartao', 'CNPJ(opcional)')->attrs(['class' => 'cnpj']) !!}
            </div>
        </div>
        <div class="modal-footer">
            <button data-bs-dismiss="modal" type="button" class="btn btn-primary px-5">OK</button>
        </div>
    </div>
</div>
