<div class="modal fade" id="modal-setar_mesa" aria-modal="true" role="dialog" style="overflow:scroll;" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Setar Mesa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            {!!Form::open()
            ->post()
            ->route('pedidos.atribuirMesa')
            !!}
            @isset($p)
            <input type="hidden" name="pedido_id_modal" value="{{ $p->id }}">
            <div class="modal-body">
                <div class="col-md-12">
                    {!! Form::text('comanda_modal', 'Comanda')->attrs(['class' => ''])->value($p->comanda)->disabled() !!}
                </div>
                <div class="col-md-12 mt-3">
                    {!! Form::select('mesa', 'Mesa', $mesas->pluck('nome', 'id')->all())->attrs(['class' => 'form-select']) !!}
                </div>
            </div>
            @endif
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary px-5">Salvar</button>
            </div>
            {!!Form::close()!!}
        </div>
    </div>
</div>
