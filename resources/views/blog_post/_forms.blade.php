@section('css')
<style type="text/css">
    .ck-editor__editable {
        width: 100%;
        min-height: 300px;
    }

</style>
@endsection
<div class="row g-3">
    <div class="col-md-6">
        {!!Form::text('titulo', 'Título')->required()->attrs(['class' => ''])
        !!}
    </div>
    <div class="col-md-6">
        {!!Form::select('categoria_id', 'Categoria', ['' => 'Selecione'] + $categoriaPosts->pluck('nome', 'id')->all())->required()->attrs(['class' => 'form-select'])
        !!}
    </div>
    <div class="col-md-5">
        {!!Form::select('autor_id', 'Autor', ['' => 'Selecione'] + $autorPost->pluck('nome', 'id')->all())->required()->attrs(['class' => 'form-select'])
        !!}
    </div>
    <div class="col-md-7">
        {!!Form::text('tags', 'Tags')->attrs(['class' => ''])
        !!}
    </div>
    <div class="form-group validated col-12">
        <label class="col-form-label required">Texto</label>
        <div class="col-12">
            <textarea name="texto" id="texto" class="text-area" value="{{{ isset($item) ? $item->texto : old('texto') }}}"></textarea>
        </div>
    </div>
    <div class="col-12 mt-4">
        @if (!isset($not_submit))
        <div id="image-preview" class="_image-preview col-md-4">
            <label for="" id="image-label" class="_image-label">Selecione a imagem</label>
            <input type="file" name="image" id="image-upload" class="_image-upload" accept="image/*" />
            @isset($item)
            @if ($item->img)
            <img src="/uploads/postBlog/{{ $item->img }}" class="img-default">
            @else
            <img src="/imgs/no_image.png" class="img-default">
            @endif
            @else
            <img src="/imgs/no_image.png" class="img-default">
            @endif
        </div>
        @endif
        @if($errors->has('image'))
        <div class="text-danger mt-2">
            {{ $errors->first('image') }}
        </div>
        @endif      
    </div>
    <div class="col">
        <button type="submit" class="btn btn-primary px-5">Salvar</button>
    </div>
</div>

@section('js')
<script>
    ClassicEditor
        .create(document.querySelector('#texto'))
        .then(editor => {
            editor.ui.view.editable.element.style.height = '300px';
        })
        .catch(error => {
            console.error(error);
        });

</script>

<script type="text/javascript" src="/assets/js/jquery.uploadPreview.min.js"></script>
@endsection
