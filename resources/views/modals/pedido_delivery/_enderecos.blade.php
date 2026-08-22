<div class="modal fade" id="modal-enderecos" aria-modal="true" role="dialog" style="overflow:scroll;" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            {!!Form::open()
            ->post()
            ->route('enderecoDelivery.store')
            !!}
            @isset($pedido)
            <input type="hidden" name="cliente_id" value="{{$pedido->cliente->id}}">
            <input type="hidden" name="pedido_id" value="{{$pedido->id}}">
            @endisset
            <div class="modal-header">
                <h5 class="modal-title">Cadastrar Endereço</h5>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-5">
                        {!! Form::text('rua', 'Rua')->attrs(['class' => '']) !!}
                    </div>
                    <div class="col-md-2">
                        {!! Form::tel('numero', 'Número')->attrs(['class' => '']) !!}
                    </div>
                    @isset($bairros)
                    <div class="col-md-5">
                        {!! Form::select('bairro_id', 'Bairro', $bairros->pluck('nome', 'id')->all())->attrs(['class' => 'form-select']) !!}
                    </div>
                    @endisset
                    <div class="col-md-6 mt-2">
                        {!! Form::text('referencia', 'Referência')->attrs(['class' => '']) !!}
                    </div>
                    <div class="col-md-2 mt-2">
                        {!! Form::select('tipo', 'Tipo', ['casa' => 'Casa', 'trabalho' => 'Trabalho'])->attrs(['class' => 'form-select']) !!}
                    </div>
                    {{-- <div class="col-md-2 mt-2">
                        {!! Form::tel('latitude', 'Latitude')->attrs(['data-mask' => '0000000000']) !!}
                    </div>
                    <div class="col-md-2 mt-2">
                        {!! Form::tel('longitude', 'Longitude')->attrs(['data-mask' => '0000000000']) !!}
                    </div> --}}
                </div>
            </div>
            <div class="modal-footer">
                <button href="" class="btn btn-primary px-5">Salvar</button>
            </div>
            {!!Form::close()!!}
        </div>
    </div>
</div>
