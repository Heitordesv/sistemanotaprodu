<div class="row g-3 m-auto">
    <div class="row mt-3">

        <div class="col-md-4">
            {!! Form::text('referencia', 'Referência')->required() !!}
        </div>

        <div class="col-md-2">
            {!! Form::date('inicio', 'Data Inicial')->required() !!}
        </div>

        <div class="col-md-2">
            {!! Form::date('fim', 'Data Final')->required() !!}
        </div>

        <div class="col-md-2">
            {!! Form::select('tipo', 'Tipo', App\Models\Inventario::tipos())->attrs(['class' => 'select2']) !!}
        </div>

        <div class="col-md-10 mt-3">
            {!! Form::text('observacao', 'Observação') !!}
        </div>

        <div class="col-12 mt-5">
            <button type="submit" class="btn btn-primary px-5">Salvar</button>
        </div>
    </div>
</div>
