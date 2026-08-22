<div class="modal fade" id="modal-suprimento_caixa" aria-modal="true" role="dialog" style="overflow:scroll;" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Suprimento Caixa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            {!!Form::open()
                ->post()
                ->route('suprimentoCaixa.store')
                !!}
            <div class="modal-body row">
                <div class="col-md-6">
                    {!! Form::tel('valor', 'Valor')->attrs(['class' => 'moeda']) !!}
                </div>
                <div class="col-md-12 mt-3">
                    {!! Form::text('observacoes', 'Observações')->attrs(['class' => 'form-control']) !!}
                </div>
            </div>
            <div class="modal-footer">
                <button data-bs-dismiss="modal" type="submit" class="btn btn-primary px-5">Salvar</button>
            </div>
            {!!Form::close()!!}
        </div>
    </div>
</div>

