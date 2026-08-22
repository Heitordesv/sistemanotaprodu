<div class="row g-3">
    <div class="col-md-4">
        {!!Form::text('nome', 'Nome')->required() 
        !!}
    </div>
    <div class="col-md-4">
        {!!Form::select('tipo', 'Tipo', ['receber' => 'Receitas', 'pagar' => 'Despesas'])
        ->attrs(['class' => 'select2'])
        !!}
    </div>
    <div class="col-12">
        <button type="submit" class="btn btn-primary px-5">Salvar</button>
    </div>
</div>
