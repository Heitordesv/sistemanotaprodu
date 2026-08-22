<div class="row g-3">
    <div class="col-md-4">
        {!!Form::text('nome', 'Nome')
        ->attrs(['class' => ''])
        !!}
    </div>
    <div class="col-md-2">
        {!!Form::tel('valor_entrega', 'Valor de entrega')
        ->attrs(['class' => 'moeda'])
        !!}
    </div>
    <div class="col-md-4">
        {!!Form::select('cidade', 'Cidade', $cidades->pluck('nome', 'id')->all())
        ->attrs(['class' => 'form-select'])
        !!}
    </div>

    <div class="col-12">
    <button type="submit" class="btn btn-primary px-5">Salvar</button>

    </div>
</div>
