<div class="row g-3">
    <div class="col-md-4">
        {!! Form::text('descricao', 'Descrição')->attrs(['class' => ''])->required() !!}
    </div>
    <div class="col-md-2">
        {!! Form::tel('codigo', 'Código')->attrs(['class' => ''])->required() !!}
    </div>
    <div style="margin-left: 20px" class="col-md-1 mt-3">
        <br>
        <button type="button" class="btn btn-info"><i class="bx bx-key" id="gerar-codigo"></i></button>
    </div>
    <div class="col-md-2">
        {!! Form::select('tipo', 'Tipo', ['valor' => 'Valor R$', 'percentual' => 'Percentual %'])->attrs(['class' => 'form-select']) !!}
    </div>
    <div class="col-md-2">
        {!! Form::tel('valor', 'Valor')->attrs(['class' => 'moeda'])->required() !!}
    </div>
    <div class="col-md-3">
        {!! Form::tel('valor_minimo_pedido', 'Valor mínimo do pedido')->attrs(['class' => 'moeda'])->required() !!}
    </div>
    <div class="col-md-3">
        {!! Form::date('expiracao', 'Data Expiração')->attrs(['class' => ''])->required() !!}
    </div>
    <div class="col-md-2">
        {!! Form::select('clientes', 'Todos os clientes', ['1' => 'Sim', '0' => 'Não'])->attrs(['class' => 'form-select']) !!}
    </div>
    <div class="col-md-2">
        {!! Form::select('ativo', 'Status', ['1' => 'Ativo', '0' => 'Desativado'])->attrs(['class' => 'form-select']) !!}
    </div>
    <div class="col-md-4 cliente d-none">
        {!! Form::select('cliente', 'Cliente', ['' => 'Selecione'] + $clientes->pluck('nome', 'id')->all())->attrs(['class' => 'select2']) !!}
    </div>
    <div class="col-12 mt-5">
        <button class="btn btn-info px-5">Salvar</button>
    </div>
</div>

@section('js')
<script>
    $('#inp-clientes').change(() => {
        cliente()
    })

    function cliente() {
        let c = $('#inp-clientes').val()
        if (c == 1) {
            $('.cliente').addClass('d-none')
        } else {
            $('.cliente').removeClass('d-none')
        }
    }


    $('#gerar-codigo').click(() => {
        let v = (Math.floor(Math.random() * 888888) + 111111)
        $('#inp-codigo').val(v)
    })

</script>
@endsection
