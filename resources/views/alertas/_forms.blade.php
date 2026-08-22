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
    <div class="col-md-2">
        {!! Form::select('status', 'Status', [1 => 'Ativo', 0 => 'Desativado'])->attrs(['class' => 'form-select'])->required() !!}
    </div>
    <div class="col-md-3">
        <label for="">Prioridade</label>
        <select class="form-select form-control" id="prioridade" name="prioridade">
            @foreach(App\Models\Alerta::prioridades() as $p)
            <option @if(isset($item)) @if($item->prioridade == $p) selected @endif @endif value="{{$p}}"
                @if(old('prioridade') == $p)
                selected
                @endif>
                {{strtoupper($p)}}
            </option>
            @endforeach
        </select>
    </div>
    <div class="form-group validated col-12">
        <label class="col-form-label">Texto</label>
        <div class="col-12">
            <textarea name="texto" id="texto" style="width: 100%;height:300px;">
                {{{ isset($item) ? $item->texto : old('texto') }}}
            </textarea>
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
        .catch(error => {
            console.error(error);
        });
</script>

@endsection
