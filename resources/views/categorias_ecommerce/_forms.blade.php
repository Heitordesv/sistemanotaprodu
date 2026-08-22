<div class="row g-3">
    <div class="col-md-6">
        {!!Form::text('nome', 'Nome')->required()
        ->attrs(['class' => 'form-control'])
        !!}
    </div>
    <div class="col-md-3">
        {!!Form::select('destaque', 'Destaque', [0 => 'Não', 1 => 'Sim'])
        ->attrs(['class' => 'form-control'])->attrs(['class' => 'form-select'])
        !!}
    </div>
    <div class="col-12 mt-4">
        @if (!isset($not_submit))
        <div id="image-preview" class="_image-preview col-md-4">
            <label for="" id="image-label" class="_image-label">Selecione a imagem</label>
            <input type="file" name="image" id="image-upload" class="_image-upload" accept="image/*" />
            @isset($item)

            @if ($item->imagem)
            <img src="/uploads/ecommerce/{{ $item->imagem }}" class="img-default">
            @else
            <img src="/imgs/no_image.png" class="img-default">
            @endif
            @else
            <img src="/imgs/no_image.png" class="img-default">
            @endif
        </div>
        @endif
    </div>
    <div class="col-12">
        <button type="submit" class="btn btn-primary px-5" id="btn-store">Salvar</button>
    </div>
</div>
</div>

@section('js')
<script type="text/javascript" src="/assets/js/jquery.uploadPreview.min.js"></script>
@endsection
