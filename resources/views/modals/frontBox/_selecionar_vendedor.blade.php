<div class="modal fade" id="modal-selecionar_vendedor" aria-modal="true" role="dialog" style="overflow:scroll;" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Selecione um Vendedor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body row">
                <div class="col-md-10">
                    {!! Form::select('vendedor_id', '', ['' => 'Selecione...'] + $vendedor->pluck('nome','id')->all())->attrs(['class' => 'form-select'])->value(isset($item) ? $item->funcionario_id : null) !!}
                </div>
            </div>
            <div class="modal-footer">
                <button data-bs-dismiss="modal" type="button" class="btn btn-primary px-5">OK</button>
            </div>
        </div>
    </div>
</div>

