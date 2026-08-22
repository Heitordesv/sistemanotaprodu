<div class="modal fade" id="modal-locacao" aria-modal="true" role="dialog" style="overflow:scroll;" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Observação da locação</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            {!!Form::open()->fill($locacao)
            ->post()
            ->route('locacao.storeObs')
            !!}
            <div class="modal-body">
				<input type="hidden" name="locacao" value="{{$locacao->id}}" name="id">
                <div class="col-md-12">
                    {!! Form::text('observacao', 'Observação')->attrs(['class' => 'form-control ignore'])
                    ->value(isset($locacao) ? $locacao->observacao : '') !!}
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary px-5">Salvar</button>
            </div>
            {!!Form::close()!!}
        </div>
    </div>
</div>
