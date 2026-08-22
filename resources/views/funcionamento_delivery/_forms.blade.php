<div class="row g-4">
    <div class="col-3 mt-5">
        {!! Form::select('dia', 'Dia', App\Models\FuncionamentoDelivery::dias())->attrs(['class' => 'select2'])->required() !!}
    </div>
    <div class="col-2 mt-5">
        {!! Form::time('inicio_expediente', 'Início')->required() !!}

    </div>
    <div class="col-2 mt-5">
        {!! Form::time('fim_expediente', 'Fim')->required() !!}
    </div>
    <div class="col-3 mt-5">
        <br>
        <button class="btn btn-info px-5">Salvar</button>
    </div>
    <hr>
</div>
