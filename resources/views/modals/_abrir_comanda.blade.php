<div class="modal fade" id="modal-abrir_comanda" aria-modal="true" role="dialog" style="overflow:scroll;" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Abrir Comanda</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {!!Form::open()
            ->post()
            ->route('pedidos.abrir')
            !!}
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4">
                        {!! Form::tel('comanda', 'Código da comanda')->attrs(['class' => '']) !!}
                    </div>
                    <div class="col-md-4">
                        {!! Form::select('mesa_id', 'Mesa', ['' => 'Selecione...'] + $mesas->pluck('nome',
                        'id')->all())->attrs(['class' => 'form-select']) !!}
                    </div>
                    <div class="col-md-4">
                        {!! Form::select('cliente_id', 'Cliente')->attrs(['class' => 'select2'])->options(isset($item) ?
                        [$item->cliente_id => $item->cliente->razao_social] : []) !!}
                    </div>
                    <div class="col-md-12 mt-3">
                        {!! Form::text('observacao', 'Observação')->attrs(['class' => '']) !!}
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary px-5">Abrir</button>
            </div>
            {!!Form::close()!!}
        </div>
    </div>
</div>
