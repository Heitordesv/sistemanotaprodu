<div class="g-3">
    <div class="col-12">
        {!! Form::textarea('texto', 'Texto')->attrs(['class' => 'ignore', 'rows' => '10']) !!}
    </div>
    <button type="submit" class="btn btn-primary px-5 mt-3">Salvar</button>
</div>

@section('js')

<script>

    ClassicEditor
        .create(document.querySelector('#inp-texto'))
        .then(editor => {
            editor.ui.view.editable.element.style.height = '300px';
        })
        .catch(error => {});

</script>
@endsection