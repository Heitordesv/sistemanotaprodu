<div class="modal fade" id="modal-comanda_pdv" aria-modal="true" role="dialog" style="overflow:scroll;" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Informe a Comanda</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body row">
                <div class="col-md-10">
                    {!! Form::tel('codigo', 'Código da Comanda')->attrs(['class' => '']) !!}
                </div>
            </div>
            <div class="modal-footer">
                <button onclick="apontarComanda()" data-bs-dismiss="modal" type="button" class="btn btn-primary px-5">OK</button>
            </div>
        </div>
    </div>
</div>

