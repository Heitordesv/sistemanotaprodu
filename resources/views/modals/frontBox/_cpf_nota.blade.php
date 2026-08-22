<div class="modal fade" id="modal-cpf_nota" aria-modal="true" role="dialog" style="overflow:scroll;" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">CPF/CNPJ NA NOTA?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        {!! Form::tel('cpf_cnpj', 'CPF/CNPJ')->attrs(['class' => 'cpf_cnpj']) !!}
                    </div>
                    <div class="col-md-12 mt-3">
                        {!! Form::text('nome', 'Nome (opcional)')->attrs(['class' => '']) !!}
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button style="width: 100%" id="btn-emitir-nfce" class="btn btn-success font-weight-bold spinner-white spinner-right pula">EMITIR</button>
            </div>
        </div>
    </div>
</div>
