<div class="row g-3">
    <div class="col-md-4">
        {!!Form::text('nome', 'Nome')->required()
        ->attrs(['class' => 'form-control'])
        ->readOnly(!$podeEditar)
        !!}
    </div>

    <input type="hidden" value="{{$podeEditar}}" name="podeEditar">

    <div class="col-md-2">
        {!!Form::select('tipo_taxa', 'Tipo da Taxa', ['perc' => 'Percentual', 'valor' => 'Valor'])
        ->attrs(['class' => 'form-select'])
        ->readOnly(!$podeEditar)
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::text('taxa', 'Taxa')->required()
        ->attrs(['class' => 'moeda'])
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::text('prazo_dias', 'Prazo dias')->required()
        ->attrs(['data-mask' => '000'])
        ->readOnly(!$podeEditar)
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::select('status', 'Status', ['1' => 'Ativo', 'o' => 'Desativado'])
        !!}
    </div>

    <div class="col-md-12">
        {!!Form::text('infos', 'Informação adicional')
        ->attrs(['class' => ''])
        !!}
    </div>

    <div class="col-12">
        <button type="submit" class="btn btn-primary px-5">Salvar</button>
    </div>
</div>
