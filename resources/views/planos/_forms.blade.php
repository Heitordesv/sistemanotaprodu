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
        {!! Form::text('nome', 'Descrição')->required() !!}
    </div>
    <div class="col-md-2">
        {!! Form::tel('valor', 'Valor')->required()
        ->attrs(['class' => 'moeda'])!!}
    </div>
    <div class="col-md-2">
        {!! Form::tel('intervalo_dias', 'Intervalo dias')->required() !!}
    </div>

    <p class="text-danger">-1 = Infinito</p>

    <div class="col-md-2">
        {!! Form::tel('maximo_clientes', 'Máx. clientes')->required() !!}
    </div>
    <div class="col-md-2">
        {!! Form::tel('maximo_produtos', 'Máx. produtos')->required() !!}
    </div>
    <div class="col-md-2">
        {!! Form::tel('maximo_fornecedores', 'Máx. fornecedores')->required() !!}
    </div>
    <div class="col-md-2">
        {!! Form::tel('maximo_nfes', 'Máx. NFes')->required() !!}
    </div>
    <div class="col-md-2">
        {!! Form::tel('maximo_nfces', 'Máx. NFCes')->required() !!}
    </div>
    <div class="col-md-2">
        {!! Form::tel('maximo_cte', 'Máx. CTes')->required() !!}
    </div>
    
    <div class="col-md-2">
        {!! Form::tel('maximo_usuario', 'Máx. usuários')->required() !!}
    </div>
    <div class="col-md-2">
        {!! Form::tel('maximo_usuario_simultaneo', 'Máx. usuários logados')->required() !!}
    </div>
    <div class="col-md-2">
        {!! Form::select('perfil_id', 'Perfil', $perfil->pluck('nome', 'id'))->attrs(['class' => 'form-select']) !!}
    </div>
    <div class="col-md-2">
        {!! Form::select('visivel', 'Visível', [1 => 'Sim', 0 => 'Não'])->attrs(['class' => 'form-select'])
        ->required() !!}
    </div>
    <div class="col-md-3">
        {!! Form::text('armazenamento', 'Armazenamento (Mb)') !!}
    </div>
    <div class="col-md-12">
        <label class="col-form-label">Texto</label>
        <div class="col-12">
            <textarea name="descricao" id="descricao" style="width: 100%;height:300px;">{{ isset($item) ? $item->descricao : old('descricao') }}</textarea>
        </div>
    </div>
    <div class="col-12 mt-4">
        @if (!isset($not_submit))
        <div id="image-preview" class="col-md-4">
            <label for="image-upload" id="image-label">Selecione a imagem</label>
            <input type="file" name="image" id="image-upload" class="_image-upload" accept="image/*" />
            @isset($item)
            @if ($item->img)
            <img src="/uploads/planos/{{ $item->img }}" class="img-default">
            @else
            <img src="/imgs/no_image.png" class="img-default">
            @endif
            @else
            <img src="/imgs/no_image.png" class="img-default">
            @endif
        </div>
        @endif
    </div>
    <div class="col-12 mt-4">
        <button type="submit" class="btn btn-primary px-5">Salvar</button>
    </div>
</div>

@section('js')
<script>
    ClassicEditor
        .create(document.querySelector('#descricao'))
        .then(editor => {
            editor.ui.view.editable.element.style.height = '300px';
        })
        .catch(error => {
            console.error(error);
        });

</script>
<script type="text/javascript" src="/assets/js/jquery.uploadPreview.min.js"></script>


@endsection
