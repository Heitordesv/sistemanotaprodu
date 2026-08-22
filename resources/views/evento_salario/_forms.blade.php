<div class="row">
    <div class="col-md-4">
        {!!Form::text('nome', 'Nome')->attrs(['class' => ''])->required() !!}
    </div>
    <div class="col-md-2">
        {!!Form::select('tipo', 'Tipo', ['semanal' => 'Semanal', 'mensal' => 'Mensal', 'anual' => 'Anual'])->attrs(['class' => 'form-select'])->required() !!}
    </div>
    <div class="col-md-2">
        {!!Form::select('metodo', 'Método', ['informado' => 'Informado', 'fixo' => 'Fixo'])->attrs(['class' => 'form-select'])->required() !!}
    </div>
    <div class="col-md-2">
        {!!Form::select('condicao', 'Condição', ['soma' => 'Soma', 'diminui' => 'Diminui'])->attrs(['class' => 'form-select'])->required() !!}
    </div>
    <div class="col-md-2">
        {!!Form::select('ativo', 'Ativo', [1 => 'Sim', 0 => 'Não'])->attrs(['class' => 'form-select'])->required() !!}
    </div>
    <div class="col-md-2 mt-3">
        {!!Form::select('tipo_valor', 'Tipo valor', ['fixo' => 'Valor fixo', 'percentual' => 'Percentual'])->attrs(['class' => 'form-select'])->required() !!}
    </div>
    <div class="col-12 mt-3" style="text-align: right;">
        <button type="submit" class="btn btn-primary px-5">Salvar</button>
    </div>
</div>
