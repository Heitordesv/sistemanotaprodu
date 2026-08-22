<div class="row g-3">
    <div class="col-md-8">
        {!!Form::text('titulo', 'Título')->required()
        ->attrs(['class' => 'form-control'])
        !!}
    </div>
    <div class="col-md-4 mt-3">
        <label for="">Cor</label>
        <input name="cor_titulo" class="form-control" type="color" value="" />
    </div>
    <div class="col-md-6">
        {!!Form::text('link_acao', 'Link ação (opcional)')
        ->attrs(['class' => 'form-control'])
        !!}
    </div>
    <div class="col-md-6">
        {!!Form::text('nome_botao', 'Nome do botão (opcional)')
        ->attrs(['class' => 'form-control'])
        !!}
    </div>
    <div class="col-md-8">
        {!!Form::text('descricao', 'Descrição')->required()
        ->attrs(['class' => 'form-control'])
        !!}
    </div>
    <div class="col-md-4 mt-3">
        <label for="">Cor</label>
        <input name="cor_descricao" class="form-control" type="color" value="" />
    </div>
    <div class="col-12 mt-4">
        @if (!isset($not_submit))
        <label for="">Selecione uma Imagem</label>
        <div id="image-preview" class="_image-preview col-md-4 mt-2">
            <label for="image-upload" id="image-label" class="_image-label">Selecione a imagem</label>
            <input type="file" name="image" id="image-upload" class="_image-upload" accept="image/*" />
            @isset($item)
            @if ($item->img)
            <img src="/uploads/carrosselEcommerce/{{ $item->img }}" class="img-default">
            @else
            <img src="/imgs/no_image.png" class="img-default">
            @endif
            @else
            <img src="/imgs/no_image.png" class="img-default">
            @endif
        </div>
        @endif
    </div>

    <div class="col-12 mt-5">
        @isset($not_submit)
        <button type="button" class="btn btn-primary px-5" id="">Salvar</button>
        @else
        <button type="submit" class="btn btn-primary px-5">Salvar</button>
        @endif
    </div>
</div>

@section('js')
<script type="text/javascript" src="/assets/js/jquery.uploadPreview.min.js">
</script>
@endsection
