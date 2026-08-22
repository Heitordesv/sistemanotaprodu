<div class="modal fade" id="modal-troca_pdv" aria-modal="true" role="dialog" style="overflow:scroll;" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Realizar nova troca</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {!!Form::open()
            ->post()
            ->route('frenteCaixa.troca')
            !!}
            <h6 class="m-3">Forma de Identificação da Venda:</h6>
            <div class="modal-body row">
                <div class="col-md-10">
                    {!! Form::tel('numero_nfce', 'Número de Nfce') !!}
                </div>
                <h5 class="m-3">OU</h5>
                <div class="col-md-10">
                    {!! Form::tel('numero_venda', 'Número de Venda') !!}
                </div>
            </div>
            <div class="modal-footer">
                <button data-bs-dismiss="modal" type="button" class="btn btn-primary px-5">OK</button>
            </div>
            {!!Form::close()!!}
        </div>
    </div>
</div>

