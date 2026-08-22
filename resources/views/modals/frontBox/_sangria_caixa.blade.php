<div class="modal fade" id="modal-sangria_caixa" aria-modal="true" role="dialog" style="overflow:scroll;" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Sangria de Caixa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            {!!Form::open()
            ->post()
            ->route('sangriaCaixa.store')
            !!}
            <div class="modal-body row">
                <div class="col-md-8">
                    {!! Form::tel('valor', 'Valor')->attrs(['class' => 'moeda']) !!}
                </div>
                <div class="col-md-12 mt-3">
                    {!! Form::text('observacao', 'Observação')->attrs(['class' => '']) !!}
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary px-5">Salvar</button>
            </div>
            {!!Form::close()!!}
        </div>
    </div>
</div>

