<div class="row g-3">
    <div class="col-md-4">
        {!!Form::text('nome', 'Nome')->required()
        ->attrs(['class' => 'form-control'])
        !!}
    </div>
    <div class="col-md-12 mt-5">
        <label for="">Imagem</label>
        @if (!isset($not_submit))
        <div id="image-preview" class="_image-preview col-md-4">
            <label for="" id="image-label" class="_image-label">Selecione a imagem</label>
            <input type="file" name="image" id="image-upload" class="_image-upload" accept="image/*" />
            @isset($item)

            @if ($item->img)
            <img src="/uploads/categoriaMasterDelivery/{{ $item->img }}" class="img-default">
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