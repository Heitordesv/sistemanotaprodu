@section('css')
<style type="text/css">
    input[type="file"] {
        display: none;
    }

    .file-certificado label {
        padding: 10px 10px;
        width: 100%;
        background-color: #8833FF;
        color: #FFF;
        text-transform: uppercase;
        text-align: center;
        display: block;
        margin-top: 15px;
        cursor: pointer;
        border-radius: 5px;
    }

</style>
@endsection

<div class="row g-3">
    <div class="col-md-4 mt-3">
        {!! Form::tel('cnpj', 'CNPJ/CPF')->attrs(['class' => 'cpf_cnpj'])->required() !!}
    </div>
    <div class="col-md-1 col-6 mt-3"><br>
        <button class="btn btn-dark btn-block w-100" type="button" id="btn-consulta">
            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span><i class="bx bx-search"></i>
        </button>
    </div>
    <div class="col-12">
    </div>
    <div class="col-md-5 mt-3">
        {!! Form::text('descricao', 'Descrição')->required() !!}
    </div>
    <div class="col-md-6 mt-3">
        {!! Form::text('razao_social', 'Razão social')->required() !!}
    </div>
    <div class="col-md-6 mt-3">
        {!! Form::text('nome_fantasia', 'Nome fantasia')->required() !!}
    </div>
    <div class="col-md-3 mt-3">
        {!! Form::tel('ie', 'Inscrição estadual')->attrs(['class' => 'ie_rg'])->required() !!}
    </div>
    <div class="col-md-2 mt-3">
        {!! Form::select('status', 'Status', [1 => 'Ativo', 0 => 'Desativado'])->attrs(['class'=> 'form-select'])->required() !!}
    </div>

    <hr class="mt-4">

    <div class="col-md-6 mt-3">
        {!! Form::text('logradouro', 'Rua')->required() !!}
    </div>
    <div class="col-md-2 mt-3">
        {!! Form::text('numero', 'Número')->required() !!}
    </div>
    <div class="col-md-4 mt-3">
        {!! Form::text('complemento', 'Complemento') !!}
    </div>
    <div class="col-md-4 mt-3">
        {!! Form::text('bairro', 'Bairro')->required() !!}
    </div>
    <div class="col-md-2 mt-3">
        {!! Form::text('cep', 'Cep')->attrs(['class' => 'cep'])->required() !!}
    </div>
    @isset($item)
    <div class="col-md-4 mt-3">
        {!!
        Form::select('cidade_id', 'Cidade')->attrs(['class' => 'select2', 'style'=> 'width: 100%!important'])
        ->options($item != null ? [$item->cidade_id => $item->cidade->info] : [])->required()
        !!}
    </div>
    @else
    <div class="col-md-4 mt-3">
        {!!
        Form::select('cidade_id', 'Cidade')->attrs(['class' => 'select2', 'style'=> 'width: 100%!important'])->required()
        !!}
    </div>
    @endisset
    <div class="col-md-2 mt-3">
        {!! Form::tel('fone', 'Telefone')->attrs(['class' => 'fone'])->required() !!}
    </div>
    <div class="col-md-4 mt-3">
        {!! Form::text('email', 'E-mail')->attrs(['class' => 'e-mail'])->required() !!}
    </div>

    <hr class="mt-4">

    <div class="col-md-4 mt-3">
        {!! Form::select(
        'nat_op_padrao',
        'Natureza de Op. padrão frente de caixa',
        $naturezas->pluck('natureza', 'id')->all(),
        )->attrs(['class' => 'select2']) !!}
    </div>
    <div class="col-md-2 mt-3">
        {!! Form::select('ambiente', 'Ambiente', [2 => 'Homologação', 1 => 'Produção'])->attrs(['class' => 'form-select']) !!}
    </div>
    <div class="col-md-2 mt-3">
        {!! Form::tel('numero_serie_nfe', 'Nº Série NFe')->attrs(['class' => ''])->required() !!}
    </div>
    <div class="col-md-2 mt-3">
        {!! Form::tel('numero_serie_nfce', 'Nº Série NFCe')->attrs(['class' => ''])->required() !!}
    </div>
    <div class="col-md-2 mt-3">
        {!! Form::tel('numero_serie_cte', 'Nº Série CTe')->attrs(['class' => ''])->required() !!}
    </div>
    <div class="col-md-2 mt-3">
        {!! Form::tel('numero_serie_mdfe', 'Nº Série MDFe')->attrs(['class' => ''])->required() !!}
    </div>
    <div class="col-md-2 mt-3">
        {!! Form::tel('ultimo_numero_nfe', 'Último Nº NFe')->attrs(['class' => ''])->required() !!}
    </div>
    <div class="col-md-2 mt-3">
        {!! Form::tel('ultimo_numero_nfce', 'Último Nº NFCe')->attrs(['class' => ''])->required() !!}
    </div>
    <div class="col-md-2 mt-3">
        {!! Form::tel('ultimo_numero_cte', 'Último Nº CTe')->attrs(['class' => ''])->required() !!}
    </div>
    <div class="col-md-2 mt-3">
        {!! Form::tel('ultimo_numero_mdfe', 'Último Nº MDFe')->attrs(['class' => ''])->required() !!}
    </div>
    <div class="col-md-2 mt-3">
        {!! Form::tel('csc_id', 'CSCID')->attrs(['class' => ''])->required() !!}
    </div>
    <div class="col-md-4 mt-3">
        {!! Form::tel('csc', 'CSC')->attrs(['class' => ''])->required() !!}
    </div>
    <div class="col-md-3 mt-3">
        {!! Form::tel('inscricao_municipal', 'Inscrição municipal (opcional)')->attrs(['class' => '']) !!}
    </div>
    <div class="col-md-3 mt-3">
        {!! Form::tel('aut_xml', 'CNPJ autorizado (opcional)')->attrs(['class' => 'cpf_cnpj']) !!}
    </div>
    {{-- {{$infoCertificado}} --}}
    @isset($item)
    @if($infoCertificado)
    <div class="card">
        <div class="card-body">
            <h4>Dados do certificado</h4>
            <h6>serial <strong>{{ $infoCertificado['serial'] }}</strong></h6>
            <h6>inicio <strong>{{ $infoCertificado['inicio'] }}</strong></h6>
            <h6>expiracao <strong>{{ $infoCertificado['expiracao'] }}</strong></h6>
            <h6>id <strong>{{ $infoCertificado['id'] }}</strong></h6>
        </div>
    </div>
    @endif
    @endisset

    <div class="row mt-3">
        <div class="col-md-3 mt-3 file-certificado">
            {!! Form::file('certificado', 'Certificado Digital (opcional)') !!}
            <span class="text-danger" id="filename"></span>
        </div>
        <div class="col-md-3 mt-3">
            {!! Form::tel('senha', 'Senha do certificado') !!}
        </div>
    </div>
    <div class="col-12 mt-5">
        <h6>Logo</h6>
        @if (!isset($not_submit))
        <div id="image-preview" class="_image-preview col-md-4">
            <label for="image-upload" id="image-label">Selecione a imagem</label>
            <input type="file" name="image" id="image-upload" class="_image-upload" accept="image/*" />
            @isset($item)
            @if ($item->logo)
            <img src="/uploads/filial/{{ $item->logo }}" class="img-default">
            @else
            <img src="/imgs/no_image.png" class="img-default">
            @endif
            @else
            <img src="/imgs/no_image.png" class="img-default">
            @endif
        </div>
        @endif
    </div>
    <hr>
    <div class="col-12 mt-4">
        <button type="submit" class="btn btn-primary px-5">Salvar</button>
    </div>
</div>

@section('js')
<script type="text/javascript" src="/assets/js/jquery.uploadPreview.min.js"></script>
<script type="text/javascript">
    $("#btn-consulta").click(() => {
        let cnpj = $("#inp-cnpj").val();
        cnpj = cnpj.replace(/[^0-9]/g,'')

        if (cnpj.length == 14) {

            $.get('https://publica.cnpj.ws/cnpj/' + cnpj)
            .done((data) => {
                console.log(data);
                let ie = data.estabelecimento.inscricoes_estaduais[0].inscricao_estadual
                $('#inp-ie').val(ie)
                $("#inp-razao_social").val(data.razao_social);
                $("#inp-nome_fantasia").val(data.estabelecimento.nome_fantasia);

                $("#inp-logradouro").val(data.estabelecimento.tipo_logradouro + " " + data.estabelecimento.logradouro);
                $("#inp-numero").val(data.estabelecimento.numero);
                $("#inp-bairro").val(data.estabelecimento.bairro);
                let cep = data.estabelecimento.cep.replace(/[^\d]+/g, '');
                $('#inp-cep').val(cep.substring(0, 5) + '-' + cep.substring(5, 9))
                findCidade(data.estabelecimento.cidade.ibge_id)

            })
            .fail((err) => {
                console.log(err)
                swal(
                    "Alerta",
                    err.responseJSON.titulo,
                    "warning"
                    );
            })

        } else {
            swal("Alerta", "Informe o CNPJ corretamente", "warning");
        }
    });

    function cidadePorNome(nome, call) {
        $.get(path_url + "api/cidadePorNome/" + nome)
        .done((success) => {
            call(success);
        })
        .fail((err) => {
            call(err);
        });
    }

    function findCidade(codigo_ibge){

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
