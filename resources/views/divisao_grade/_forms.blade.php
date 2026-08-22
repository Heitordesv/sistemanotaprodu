<div class="row g-3">
    <div class="col-md-4">
        {!! Form::text('nome', 'Nome')->attrs(['class' => '']) !!}
    </div>
    <div class="col-md-2">
        {!! Form::select('sub_divisao', 'Sub divisão', [0 => 'Não', 1 => 'Sim'])->attrs(['class' => '']) !!}
    </div>
    <hr>
    <div class="col-12">
        <button type="submit" class="btn btn-primary px-5">Salvar</button>
    </div>
</div>
