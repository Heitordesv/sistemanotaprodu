<div class="row g-3">
    <div class="col-md-4">
        {!! Form::text('nome', 'Nome')->required() !!}
    </div>
    <div class="col-md-2">
        {!! Form::select('uf', 'UF', App\Models\Cidade::estados())->attrs(['class' => 'select2'])->required() !!}
    </div>
    <div class="col-md-3">
        {!! Form::text('codigo', 'Código IBGE')->required() !!}
    </div>
    <div class="col-12">
        <button type="submit" class="btn btn-primary px-5">Salvar</button>
    </div>
</div>