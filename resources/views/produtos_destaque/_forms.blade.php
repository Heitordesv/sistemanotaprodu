<div class="row g-3">
    <div class="col-md-4">
        {!! Form::select('produto_id', 'Produto')->required()->attrs(['class' => 'select2'])->options(isset($item) ? [$item->produto_id => $item->produto->produto->nome] : []) !!}
    </div>
    <div class="col-md-4">
        {!! Form::select('categoria_id', 'Categoria', $categoria->pluck('nome', 'id')->all())->required()->attrs(['class' => 'form-select']) !!}
    </div>
    <div class="col-12 mt-5">
        <button type="submit" class="btn btn-primary px-5">Salvar</button>
    </div>
</div>