<div class="row g-3">
    <div class="col-md-6">
        {!!Form::text('nome', 'Nome')->required()->attrs(['class' => ''])
        !!}
    </div>
    <div class="col-md-6">
        {!!Form::text('tipo', 'Tipo')->required()->attrs(['class' => ''])
        !!}
    </div>
    <div class="col-12 mt-4">
        @if (!isset($not_submit))
        <div id="image-preview" class="_image-preview col-md-4">
            <label for="" id="image-label" class="_image-label">Selecione a imagem</label>
            <input type="file" name="image" id="image-upload" class="_image-upload" accept="image/*" />
            @isset($item)
            @if ($item->img)
            <img src="/uploads/autorPost/{{ $item->img }}" class="img-default">
            @else
            <img src="/imgs/no_image.png" class="img-default">
            @endif
            @else
            <img src="/imgs/no_image.png" class="img-default">
            @endif
        </div>
        @endif
    </div>
    <div class="col">
        <button type="submit" class="btn btn-primary px-5">Salvar</button>
    </div>
</div>

@section('js')
<script type="text/javascript" src="/assets/js/jquery.uploadPreview.min.js"></script>
@endsection
