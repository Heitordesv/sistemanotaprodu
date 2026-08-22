<div class="row g-3">
    <div class="col-md-4">
        {!! Form::text('descricao', 'Descrição')->attrs(['class' => 'form-control'])->required() !!}
    </div>

    <div class="col-md-2">
        {!! Form::text('codigo', 'Código')->attrs(['class' => 'form-control', 'id' => 'inp-codigo'])->required() !!}
    </div>

    <div style="margin-left: 20px" class="col-md-1 mt-3">
        <br>
        <button type="button" class="btn btn-info" id="gerar-codigo">
            <i class="bx bx-key"></i>
        </button>
    </div>

  <div class="col-md-2">
    {!! Form::select('tipo', 'Tipo', ['fixo' => 'Fixo', 'percentual' => 'Percentual'])->attrs(['class' => 'form-select']) !!}
</div>

    <div class="col-md-2">
        {!! Form::text('valor', 'Valor')->attrs(['class' => 'form-control moeda'])->required() !!}
    </div>

    <div class="col-md-3">
        {!! Form::text('valor_minimo_pedido', 'Valor mínimo do pedido')->attrs(['class' => 'form-control moeda'])->required() !!}
    </div>

    <div class="col-md-2">
        {!! Form::select('status', 'Status', ['1' => 'Ativo', '0' => 'Desativado'])->attrs(['class' => 'form-select']) !!}
    </div>

    <div class="col-12 mt-5">
        <button class="btn btn-info px-5">Salvar</button>
    </div>
</div>

@section('js')
<script>
    $('#gerar-codigo').click(() => {
        let v = (Math.floor(Math.random() * 888888) + 111111);
        $('#inp-codigo').val(v);
    })
</script>
@endsection
