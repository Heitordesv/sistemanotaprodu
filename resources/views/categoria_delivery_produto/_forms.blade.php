<div class="row g-3">
    <div class="col-md-4">
        {!!Form::text('nome', 'Nome')
        ->attrs(['class' => 'form-control'])
        !!}
    </div>
    <div class="col-md-2">
        {!!Form::select('tipo_pizza', 'Tipo Pizza', [0 => 'Não', 1 => 'Sim'])
        ->attrs(['class' => 'form-select'])
        !!}
    </div>
    <div class="col-md-6">
        {!!Form::textarea('descricao', 'Descrição')
        ->attrs(['class' => 'form-control'])
        !!}
    </div>
    <div class="col-md-4 mt-3">
        <label for="">Imagem</label>
        @if (!isset($not_submit))
        <div id="image-preview" class="_image-preview col-md-4">
            <label for="" id="image-label" class="_image-label">Selecione a imagem</label>
            <input type="file" name="image" id="image-upload" class="_image-upload" accept="image/*" />
            @isset($item)

            @if ($item->path)
            <img src="/uploads/categoriaDelivery/{{ $item->path }}" class="img-default">
            @else
            <img src="/imgs/no_product.png" class="img-default">
            @endif
            @else
            <img src="/imgs/no_product.png" class="img-default">
            @endif
        </div>
        @endif
        @if($errors->has('image'))
        <div class="text-danger mt-2">
            {{ $errors->first('image') }}
        </div>
        @endif
    </div>
    <div class="col-12">
        <button type="submit" class="btn btn-primary px-5" id="btn-store">Salvar</button>
    </div>
</div>
@section('js')
<script type="text/javascript" src="/assets/js/jquery.uploadPreview.min.js"></script>
@endsection
