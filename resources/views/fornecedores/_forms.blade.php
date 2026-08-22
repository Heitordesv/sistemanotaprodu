<div class="row g-3">
    <div class="col-md-4">
        {!! Form::text('cpf_cnpj', 'CPF/CNPJ')->attrs(['class' => 'cpf_cnpj'])->required() !!}
    </div>
    <div class="col-md-1 col-6"><br>
        <button class="btn btn-dark btn-block w-100" type="button" id="btn-consulta-cnpj">
            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span><i class="bx bx-search"></i>
        </button>
    </div>
    <div class="col-md-6">
        {!! Form::text('razao_social', 'Razão social')->required() !!}
    </div>
    <div class="col-md-6">
        {!! Form::text('nome_fantasia', 'Nome fantasia')->required() !!}
    </div>
    <div class="col-md-3">
        {!! Form::text('ie_rg', 'IE')->attrs(['class' => 'ignore ie_rg']) !!}
    </div>

    <div class="col-md-2">
        {!! Form::select('contribuinte', 'Contribuinte', [1 => 'Sim', 0 => 'Não'])->attrs(['class' => 'form-select']) !!}
    </div>

    <div class="col-md-4">
        {!! Form::text('email', 'Email')->type('email')->attrs(['class' => 'ignore']) !!}
    </div>

    <div class="col-md-2">
        {!! Form::tel('celular', 'Celular')->attrs(['class' => 'fone ignore']) !!}
    </div>

    <div class="col-md-2">
        {!! Form::tel('telefone', 'Telefone')->attrs(['class' => 'fone ignore']) !!}
    </div>

    <hr class="mt-4">

    <h5>Endereço</h5>

    <div class="col-md-2">
        {!! Form::text('cep', 'CEP')->attrs(['class' => 'cep'])->required() !!}
    </div>
    
    <div class="col-md-6">
        {!! Form::text('rua', 'Rua')->required() !!}
    </div>

    <div class="col-md-2">
        {!! Form::text('numero', 'Número')->required() !!}
    </div>

    <div class="col-md-2">
        {!! Form::text('bairro', 'Bairro')->required() !!}
    </div>

    <div class="col-md-5">
        {!! Form::select('cidade_id', 'Cidade')->required()->attrs(['class' => 'select2'])->options((isset($item) && isset($item->cidade_id)) ? [$item->cidade_id => $item->cidade->info] : []) !!}
    </div>

    <hr>
    <div class="col-12" style="text-align: right;">
        @isset($not_submit)
        <button type="button" class="btn btn-primary px-5" id="btn-store-fornecedor">Salvar</button>
        @else
        <button type="submit" class="btn btn-primary px-5">Salvar</button>
        @endif
    </div>
</div>
@section('js')
<script type="text/javascript">

    $(document).on("blur", "#inp-cep", function () {

        let cep = $(this).val().replace(/[^0-9]/g,'')

        $url = "https://viacep.com.br/ws/"+cep+"/json";
        $.get($url)
        .done((success) => {
            console.log(success)
            $('#inp-rua').val(success.logradouro)
            $('#inp-numero').val(success.numero)
            $('#inp-bairro').val(success.bairro)

            findCidade(success.ibge)
        })
        .fail((err) => {
            console.log(err)
        })

    });

    function findCidade(codigo_ibge) {

        $.get(path_url + "api/cidadePorCodigoIbge/" + codigo_ibge)
        .done((res) => {

            var newOption = new Option(
                res.nome + " (" + res.uf + ")",
                res.id,
                false,
                false
                );
            $("#inp-cidade_id")
            .html(newOption)
            .trigger("change");
        })
        .fail((err) => {
            console.log(err)
        })
    }

</script>
@endsection
