<div class="row g-3">
    <div class="col-md-5">
        {!! Form::tel('cnpj', 'CNPJ')->attrs(['class' => 'cpf_cnpj'])->required() !!}
    </div>
    <div class="col-md-6">
        {!! Form::text('razao_social', 'Razão social')->attrs(['class' => ''])->required() !!}
    </div>
    <div class="col-md-6">
        {!! Form::text('nome_fantasia', 'Nome fantasia')->attrs(['class' => ''])->required() !!}
    </div>
    <div class="col-md-3">
        {!! Form::tel('ie', 'Inscrição estadual')->attrs(['class' => ''])->required() !!}
    </div>
    <div class="col-md-2">
        {!! Form::tel('percentual_comissao', '% Comissão')->attrs(['class' => 'moeda'])->required() !!}
    </div>
    <div class="col-md-6">
        {!! Form::text('logradouro', 'Rua')->attrs(['class' => ''])->required() !!}
    </div>
    <div class="col-md-2">
        {!! Form::tel('numero', 'Número')->attrs(['class' => ''])->required() !!}
    </div>
    <div class="col-md-3">
        {!! Form::text('bairro', 'Bairro')->attrs(['class' => ''])->required() !!}
    </div>
    <div class="col-md-2">
        {!! Form::tel('cep', 'Cep')->attrs(['class' => 'cep'])->required() !!}
    </div>
    <div class="col-md-4">
        {!! Form::select('cidade_id', 'Cidade')->attrs(['class' => 'select2'])->required() !!}
    </div>
    <div class="col-md-2">
        {!! Form::tel('fone', 'Telefone')->attrs(['class' => 'fone'])->required() !!}
    </div>
    <div class="col-md-3">
        {!! Form::text('email', 'E-mail')->attrs(['class' => 'email'])->required() !!}
    </div>

    <div class="col-md-2">
        {!! Form::select('dados_bancarios', 'Dados bancário', [0 => 'Não', 1 => 'Sim'])->attrs(['class' => 'form-select']) !!}
    </div>
    <div class="row m-2 div-dados d-none">
        <div class="col-md-2">
            {!! Form::tel('agencia', 'Agência')->attrs(['class' => '']) !!}
        </div>
        <div class="col-md-2">
            {!! Form::tel('conta', 'Conta')->attrs(['class' => '']) !!}
        </div>
        <div class="col-md-3">
            {!! Form::text('banco', 'Banco')->attrs(['class' => '']) !!}
        </div>
        <div class="col-md-5">
            {!! Form::tel('chave_pix', 'Chave pix')->attrs(['class' => '']) !!}
        </div>
    </div>
    <div class="col-2">
        {!! Form::select('contador_parceiro', 'Contador parceiro', [0 => 'Não', 1 => 'Sim'])->attrs(['class' => 'form-select']) !!}
    </div>
    <div class="col-12 mt-4">
        <button class="btn btn-info px-5" type="submit">Salvar</button>
    </div>
</div>


@section('js')
<script>
    $('#inp-dados_bancarios').change(() => {
        dadosBancarios()
    })

    function dadosBancarios() {
        let is = $('#inp-dados_bancarios').val()
        if (is == 1) {
            $('.div-dados').removeClass('d-none')
        } else {
            $('.div-dados').addClass('d-none')
        }
    }

</script>
@endsection
