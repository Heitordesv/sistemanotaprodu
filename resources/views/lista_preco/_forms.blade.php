<div class="row g-3">
    <div class="col-md-4">
        {!!Form::text('nome', 'Nome')->required() 
        !!}
    </div>
    <div class="col-md-2">
        {!!Form::tel('percentual_alteracao', '% Alteração')->required() 
        ->attrs(['class' => 'moeda'])
        !!}
    </div>
    <div class="col-md-3">
        {!!Form::select('tipo', 'Definir valor por', [1 => 'Valor de Compra', 2 => 'Valor de Venda'])
        ->attrs(['class' => 'form-select'])
        !!}
    </div>
    <div class="col-md-3">
        {!!Form::select('tipo_inc_red', 'Tipo', [1 => 'Incremento', 2 => 'Redução'])
        ->attrs(['class' => 'form-select'])
        !!}
    </div>
    <div class="col-12">
        <button type="submit" class="btn btn-primary px-5">Salvar</button>
    </div>
</div>
