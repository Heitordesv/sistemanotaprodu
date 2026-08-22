<div class="row g-3">
    <div class="col-md-6">
        {!!Form::text('rua', 'Rua')->attrs(['class' => '']) !!}
    </div>
    <div class="col-md-2">
        {!!Form::tel('numero', 'Número')->attrs(['class' => '']) !!}
    </div>
    <div class="col-md-4">
        {!!Form::text('bairro', 'Bairro')->attrs(['class' => '']) !!}
    </div>
    <div class="col-md-3">
        {!!Form::tel('cep', 'CEP')->attrs(['class' => 'cep']) !!}
    </div>
    <div class="col-md-6">
        {!!Form::tel('complemento', 'Complemento')->attrs(['class' => '']) !!}
    </div>
    <div class="col-md-4">
        {!!Form::select('cidade_id', 'Cidade')->attrs(['class' => 'select2']) !!}
    </div>
    <div class="col">
        <button type="submit" class="btn btn-info px-5">Salvar</button>
    </div>
</div>