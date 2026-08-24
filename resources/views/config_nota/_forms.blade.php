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
    <div class="row m-3">
        <div class="col-md-6 row">
            <button type="button" class="btn btn-outline-primary btn-dados link-active px-6" onclick="selectDiv('dadosEmissor')">Dados do Emissor</button>
        </div>
        <div class="col-md-6 row m-auto">
            <button type="button" class="btn btn-outline-primary btn-outros" onclick="selectDiv('outros')">Outros</button>
        </div>
    </div>

    <div class="div-dadosEmissor row mt-5">
    @if($infoCertificado)
    <div class="card">
        <div class="col-lg-12 col-sm-12 col-md-12 mt-2">
            {{-- Este botão agora aparecerá sempre que existir um registro de certificado, 
                 mesmo que o arquivo esteja corrompido ou sem serial --}}
            <a href="{{ route('configNF.deleteCertificado', $item->id) }}" class="btn btn-danger">
                Remover certificado
            </a>
            
            <a href="{{ route('contigencia.index') }}" class="btn btn-dark spinner-white spinner-right">
                Contingência
            </a>
        </div>

        <div class="card-body">
            <h4>Dados do certificado</h4>
            
            {{-- Verificamos se a chave 'serial' existe antes de tentar mostrar os dados --}}
            @if(isset($infoCertificado['serial']))
                <h6>serial <strong>{{ $infoCertificado['serial'] }}</strong></h6>
                <h6>inicio <strong>{{ $infoCertificado['inicio'] }}</strong></h6>
                <h6>expiracao <strong>{{ $infoCertificado['expiracao'] }}</strong></h6>
                <h6>id <strong>{{ $infoCertificado['id'] }}</strong></h6>
            @else
                {{-- Caso o array esteja vazio ou sem as chaves, mostramos um aviso --}}
                <div class="alert alert-warning mt-3">
                    <strong>Aviso:</strong> Não foi possível ler as informações do certificado (Arquivo ausente ou senha incorreta). 
                    Utilize o botão acima para remover e enviar novamente.
                </div>
            @endif
        </div>
    </div>
    @endif
</div>


        {{-- <div class="col-12">
            <a href="{{ route('contigencia.index') }}" class="btn btn-dark">Contingência</a>
    </div> --}}
    <div class="col-md-4 mt-3">
        {!! Form::tel('cnpj', 'CNPJ/CPF')->attrs(['class' => 'cpf_cnpj'])->required() !!}
    </div>
    <div class="col-md-1 col-6 mt-3"><br>
        <button class="btn btn-dark btn-block w-100" type="button" id="btn-consulta">
            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span><i class="bx bx-search"></i>
        </button>
    </div>

    <div class="col-md-6 mt-3">
        {!! Form::text('razao_social', 'Razão social')->required() !!}
    </div>

    <div class="col-md-6 mt-3">
        {!! Form::text('nome_fantasia', 'Nome fantasia')->required() !!}
    </div>

    <div class="col-md-4 mt-3">
        {!! Form::tel('ie', 'Inscrição estadual')->attrs(['class' => 'ie_rg'])->required() !!}
    </div>

    <div class="row">
        <div class="col-md-3 mt-3 file-certificado">
            {!! Form::file('certificado', 'Certificado Digital')->attrs(['accept' => '.bin, .pfx, .p12']) !!}
            <span class="text-danger" id="filename"></span>
        </div>

        <div class="col-md-2 mt-3">
            {!! Form::tel('senha', 'Senha do certificado') !!}
        </div>
    </div>

    <h5 class="mt-4">Endereço</h5>

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

    <div class="col-md-4 mt-3">
        {!!
        Form::select('cidade_id', 'Cidade')->attrs(['class' => 'select2', 'style'=> 'width: 100%!important'])
        ->options(($item != null && $item->cidade) ? [$item->cidade_id => $item->cidade->info] : [])->required()
        !!}
    </div>

    <div class="col-md-2 mt-3">
        {!! Form::tel('fone', 'Telefone')->attrs(['class' => 'fone'])->required() !!}
    </div>

    <div class="col-md-4 mt-3">
        {!! Form::text('email', 'E-mail')->attrs(['class' => 'e-mail'])->required() !!}
    </div>

    <div class="col-md-12 mt-3">
        {!! Form::select('CST_CSOSN_padrao', 'CST/CSOSN Padrão', App\Models\ConfigNota::listaCST())->attrs([
        'class' => 'select2',
        ]) !!}
    </div>

    <div class="col-md-6 mt-3">
        {!! Form::select('CST_PIS_padrao', 'CST/PIS Padrão', App\Models\ConfigNota::listaCST_PIS_COFINS())->attrs([
        'class' => 'select2',
        ]) !!}
    </div>

    <div class="col-md-6 mt-3">
        {!! Form::select('CST_COFINS_padrao', 'CST/COFINS Padrão', App\Models\ConfigNota::listaCST_PIS_COFINS())->attrs([
        'class' => 'select2',
        ]) !!}
    </div>

    <div class="col-md-12 mt-3">
        {!! Form::select('CST_IPI_padrao', 'CST/IPI Padrão', App\Models\ConfigNota::listaCST_IPI())->attrs([
        'class' => 'select2',
        ]) !!}
    </div>

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
        {!! Form::tel('ultimo_numero_nfe', 'Último Nº NFe')->attrs(['class' => ''])->required() !!}
    </div>

    <div class="col-md-2 mt-3">
        {!! Form::tel('ultimo_numero_nfce', 'Último Nº NFCe')->attrs(['class' => ''])->required() !!}
    </div>

    <div class="col-md-2 mt-3">
        {!! Form::tel('ultimo_numero_cte', 'Último Nº CTe')->attrs(['class' => ''])->required() !!}
    </div>

    <div class="col-md-2 mt-3">
        {!! Form::tel('csc_id', 'CSCID')->attrs(['class' => ''])->required() !!}
    </div>

    <div class="col-md-4 mt-3">
        <label for="csc" class="form-label">CSC</label>
        <input
            type="password"
            id="csc"
            name="csc"
            class="form-control"
            value="{{ old('csc') }}"
            autocomplete="new-password"
            placeholder="{{ $cscCadastrado ? 'CSC já cadastrado — digite somente para substituir' : 'Informe o CSC' }}"
            @if (!$cscCadastrado) required @endif
        >
        @if ($cscCadastrado)
            <small class="text-success">CSC cadastrado. O valor não é exibido por segurança.</small>
        @endif
    </div>

    <div class="col-md-3 mt-3">
        {!! Form::tel('inscricao_municipal', 'Inscrição municipal (opcional)')->attrs(['class' => '']) !!}
    </div>

    <div class="col-md-3 mt-3">
        {!! Form::tel('auto_xml', 'CNPJ autorizado (opcional)')->attrs(['class' => 'cpf_cnpj']) !!}
    </div>
    <div class="col-12 mt-4">
        @if (!isset($not_submit))
        <div id="image-preview" class="_image-preview col-md-4">
            <label for="image-upload" id="image-label">Selecione a imagem</label>
            <input type="file" name="image" id="image-upload" class="_image-upload" accept="image/*" />
            @isset($item)
            @if($item->logo)
            <img src="/uploads/configEmitente/{{ $item->logo }}" class="img-default">
            @else
            <img src="/imgs/no_image.png" class="img-default">
            @endif
            @else
            <img src="/imgs/no_image.png" class="img-default">
            @endif
        </div>
        @endif

        @if(isset($item) && $item->logo != "")
        <a class="btn btn-sm btn-danger mt-3" href="{{ route('configNF.remove-logo') }}">remover logo</a>
        @endif
    </div>
</div>
<div class="row div-outros d-none mt-4">

    <!-- Campo CST/CSOSN Consumidor Final -->
    <div class="col-md-6 mt-3">
        {!! Form::select('sobrescrita_csonn_consumidor_final', 'CST/CSOSN Consumidor final (opcional)', ['' => 'Selecione'] + App\Models\ConfigNota::listaCST())->attrs(['class' => 'select2']) !!}
    </div>

    <!-- Campo Token IBPT -->
    <div class="col-md-6 mt-2">
        @php
            $appendAttr = '<label class="text-info label-popover" data-bs-container="body" data-bs-toggle="popover" data-bs-placement="bottom" data-bs-content="Realize o cadastro no site https://deolhonoimposto.ibpt.org.br/Site/Entrar para gerar o seu token" data-bs-original-title="" title="">
                <i class="m-1 bx bx-info-circle"></i>
            </label>';
        @endphp
        {!! Form::text('token_ibpt', 'Token IBPT (opcional)' . $appendAttr)->attrs(['class' => 'form-control']) !!}
        @if(isset($item) && $item)
        <a href="{{ route('configNF.ibpt') }}" class="btn btn-outline-primary btn-sm mt-2">
            <i class="bx bx-refresh"></i> Atualizar IBPT dos produtos desta empresa
        </a>
        @endif
    </div>

    <!-- Campo Token NFSe -->
    <div class="col-md-6 mt-2">
        @php
            $appendAttr = '<label class="text-info label-popover" data-bs-container="body" data-bs-toggle="popover" data-bs-placement="bottom" data-bs-content="Integração com Webmania, entre em contato com o Admin para gerar o token da empresa" data-bs-original-title="" title="">
                <i class="m-1 bx bx-info-circle"></i>
            </label>';
        @endphp
        {!! Form::text('token_nfse', 'Token NFSe (opcional)' . $appendAttr)->attrs(['class' => 'form-control']) !!}
    </div>

    <!-- Campo Bearer Token -->
    <div class="col-md-6 mt-2">
        @php
            $appendAttr = '<label class="text-info label-popover" data-bs-container="body" data-bs-toggle="popover" data-bs-placement="bottom" data-bs-content="Integração com Webmania, entre em contato com o Admin para gerar o token da empresa" data-bs-original-title="" title="">
                <i class="m-1 bx bx-info-circle"></i>
            </label>';
        @endphp
        {!! Form::text('background_color', 'cor de fundo (opcional)' . $appendAttr)->attrs(['class' => 'form-control']) !!}
    </div>

    <!-- Campo User ID Token -->
  
    <!-- Campo DeviceToken -->
    <div class="col-md-6 mt-2">
        @php
            $appendAttr = '<label class="text-info label-popover" data-bs-container="body" data-bs-toggle="popover" data-bs-placement="bottom" data-bs-content="Integração com Webmania, entre em contato com o Admin para gerar o token da empresa" data-bs-original-title="" title="">
                <i class="m-1 bx bx-info-circle"></i>
            </label>';
        @endphp
        {!! Form::text('text_color', 'cor texto (opcional)' . $appendAttr)->attrs(['class' => 'form-control']) !!}
    </div>

    <!-- Campo Cód. Tributação Município -->
    <div class="col-md-3 mt-3">
        {!! Form::text('codigo_tributacao_municipio', 'Cód. de tributação do município (opcional)')->attrs(['class' => '']) !!}
    </div>

    <!-- Campo Parcelamento Máximo -->
    <div class="col-md-3 mt-3">
        {!! Form::tel('parcelamento_maximo', 'Parcelamento Max.')->attrs(['class' => '']) !!}
    </div>

    <!-- Campo Lucro Padrão -->
    <div class="col-md-2 mt-3">
        {!! Form::tel('percentual_lucro_padrao', 'Lucro padrão')->attrs(['class' => '']) !!}
    </div>

    <!-- Campo Máximo Desconto -->
    <div class="col-md-2 mt-3">
        {!! Form::tel('parcentual_max_desconto', 'Max desconto')->attrs(['class' => '']) !!}
    </div>

    <!-- Campo Validade Orçamento -->
    <div class="col-md-2 mt-3">
        {!! Form::tel('validade_orcamento', 'Validade orçamento')->attrs(['class' => '']) !!}
    </div>

    <!-- Campo Tipo Caixa -->
    <div class="col-md-2 mt-3">
        {!! Form::select('caixa_por_usuario', 'Tipo caixa', [0 => 'Por Usuário', 1 => 'Por Empresa'])->attrs(['class' => 'select2']) !!}
    </div>

    <!-- Campo Senha Remover Venda -->
    <div class="col-md-4 mt-3">
        <div class="input-group" id="show_hide_password">
            <input type="password" class="form-control" id="senha_remover" name="senha_remover" placeholder="Senha remover venda (opcional)" autocomplete="off" value="{{ isset($item) ? $item->senha_remover : '' }}">
            <a href="javascript:;" class="input-group-text bg-transparent"><i class='bx bx-hide'></i></a>
        </div>
        @if(isset($item))
        <a href="{{ route('configNF.removeSenha', $item->id) }}">remover senha</a>
        @endif
    </div>

    <!-- Campo Casas Decimais -->
    <div class="col-md-2 mt-3">
        {!! Form::tel('casas_decimais', 'Casas decimais')->attrs(['class' => '']) !!}
    </div>

    <!-- Campo Frete Padrão -->
    <div class="col-md-3 mt-3">
        {!! Form::select('frete_padrao', 'Frete padrão', App\Models\ConfigNota::tiposFrete())->attrs(['class' => 'select2']) !!}
    </div>

    <!-- Campo Tipo Pagamento Padrão -->
    <div class="col-md-3 mt-3">
        {!! Form::select('tipo_pagamento_padrao', 'Tipo pagamento padrão', App\Models\ConfigNota::tiposPagamento())->attrs(['class' => 'select2']) !!}
    </div>

    <!-- Campo Email Próprio -->
    <div class="col-md-3 mt-2">
        @php
            $appendAttr = '<label class="text-info label-popover" data-bs-container="body" data-bs-toggle="popover" data-bs-placement="bottom" data-bs-content="Se sim configurar as credencias de email no menu Configurações > Configurar email" data-bs-original-title="" title="">
                <i class="bx bx-info-circle m-1"></i>
            </label>';
        @endphp
        {!! Form::select('usar_email_proprio', 'Email próprio' . $appendAttr, [0 => 'Não', 1 => 'Sim'])->attrs(['class' => 'popover-button'])->wrapperAttrs(['']) !!}
    </div>

    <!-- Campo Tipo de Referência Produto -->
    <div class="col-md-3 mt-3">
        {!! Form::select('cProdTipo', 'Tipo de referencia produto NFe', ['id' => 'ID', 'referencia' => 'Referência'])->attrs(['class' => 'select2']) !!}
    </div>

    <!-- Observações para NFe -->
    <div class="col-md-12 mt-3">
        @php
            $appendAttr = '<label class="text-info label-popover" data-bs-container="body" data-bs-toggle="popover" data-bs-placement="bottom" data-bs-content="Para o cálculo automático de % aproveitamento crédito, utilize R$ e %, ex: Permite o aproveitamento de ICMS no valor de R$ correspondente a alíquota de %." data-bs-original-title="" title="">
                <i class="m-1 bx bx-info-circle"></i>
            </label>';
        @endphp
        {!! Form::textarea('observacao_nfe', 'Observações para NFe' . $appendAttr)->attrs(['class' => 'form-control']) !!}
    </div>

</div>


</div>
<div class="col-12 mt-5">
    <button type="submit" class="btn btn-primary px-5">Salvar</button>
</div>
</div>


@section('js')
<script>
    $(function() {
        $('[data-bs-toggle="popover"]').popover();
    });

    function selectDiv(ref) {
        $('.btn-outline-primary').removeClass('link-active')
        if (ref == 'outros') {
            $('.div-outros').removeClass('d-none')
            $('.div-dadosEmissor').addClass('d-none')
            $('.btn-outros').addClass('link-active')
        } else {
            $('.div-outros').addClass('d-none')
            $('.div-dadosEmissor').removeClass('d-none')
            $('.btn-dados').addClass('link-active')
        }
    }

    $("#btn-consulta").click(() => {
        let cnpj = $("#inp-cnpj").val();
        cnpj = cnpj.replace(/[^0-9]/g, '')

        if (cnpj.length == 14) {

            $.get('https://publica.cnpj.ws/cnpj/' + cnpj)

                .done((data) => {
                    console.log(data);

                    let ie = ''
                    if (data.estabelecimento.inscricoes_estaduais.length > 0) {
                        ie = data.estabelecimento.inscricoes_estaduais[0].inscricao_estadual
                    }

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
                        "Alerta"
                        , err.responseJSON.titulo
                        , "warning"
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

    function findCidade(codigo_ibge) {

        $.get(path_url + "api/cidadePorCodigoIbge/" + codigo_ibge)
            .done((res) => {

                var newOption = new Option(
                    res.nome + " (" + res.uf + ")"
                    , res.id
                    , false
                    , false
                );
                $("#inp-cidade_id")
                    .html(newOption)
                    .trigger("change");
            })
            .fail((err) => {
                console.log(err)
            })
    }

    $(document).ready(function() {
        $("#show_hide_password a").on('click', function(event) {
            event.preventDefault();
            if ($('#show_hide_password input').attr("type") == "text") {
                $('#show_hide_password input').attr('type', 'password');
                $('#show_hide_password i').addClass("bx-hide");
                $('#show_hide_password i').removeClass("bx-show");
            } else if ($('#show_hide_password input').attr("type") == "password") {
                $('#show_hide_password input').attr('type', 'text');
                $('#show_hide_password i').removeClass("bx-hide");
                $('#show_hide_password i').addClass("bx-show");
            }
        });
    });

</script>

<script type="text/javascript" src="/assets/js/jquery.uploadPreview.min.js"></script>

@endsection
