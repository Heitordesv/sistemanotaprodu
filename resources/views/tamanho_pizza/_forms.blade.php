<div class="row g-3">
    <div class="col-md-5">
        {!! Form::text('nome', 'Nome') !!}
    </div>
    <div class="col-md-3">
        {!! Form::tel('pedacos', 'Pedaços') !!}
    </div>
    <div class="col-md-3">
        {!! Form::tel('maximo_sabores', 'Máximo de sabores') !!}
    </div>
    <div class="col-12">
        <button type="submit" class="btn btn-primary px-5">Salvar</button>
    </div>
</div>
