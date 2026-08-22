<div class="row g-3">
    <div class="col-md-3">
        {!!Form::text('nome', 'Nome')->attrs(['class' => ''])
        !!}
    </div>
    <div class="col-md-2">
        {!!Form::tel('valor', 'Valor')->attrs(['class' => 'moeda'])
        !!}
    </div>
    <div class="col-md-2">
        {!!Form::select('tipo', 'Tipo', ['' => 'Normal', 'borda' => 'Borda'])->attrs(['class' => 'form-select'])
        !!}
    </div>
    <div class="col-md-5">
        <label>Categoria de produto</label>
        <select class="multiple-select" name="categoria[]" data-placeholder="Choose anything" multiple="multiple">
            @foreach($categorias as $c)
            <option @isset($item) @if(in_array($c->id, $item->categorias)) selected @endif @endif value="{{$c->id}}">{{$c->nome}}</option>
            @endforeach
        </select>
    </div>
    <div class="col-12">
        <button class="btn btn-info px-5">Salvar</button>
    </div>
</div>


@section('js')
<script type="text/javascript">
    

</script>
@endsection
