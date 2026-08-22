@section('css')
<style type="text/css">
    .ck-editor__editable {
        width: 100%;
        min-height: 300px;
    }
</style>
@endsection
<div class="row g-3">
    <div class="col-md-5">
        {!! Form::text('titulo', 'Título')->required() !!}
    </div>
    <div class="col-md-5">
        {!! Form::tel('maximo_acessos', 'Máx. de acessos')->required() !!}
    </div>
    <div class="col-md-2">
        {!! Form::select('status', 'Status', [0 => 'Não', 1 => 'Sim'])->attrs(['class' => 'form-select'])->required() !!}
    </div>
    <div class="form-group validated col-12">
        <label class="col-form-label">Texto</label>
        <div class="col-12">
            <textarea name="texto" id="texto" value="{{{ isset($item) ? $item->texto : old('texto') }}}" style="width: 100%;height:300px;"></textarea>
        </div>
    </div>
    <div>
        <button type="submit" class="btn btn-info px-5">Salvar</button>
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
@endsection
