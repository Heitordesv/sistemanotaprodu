@extends('default.layout', ['title' => 'Emitente'])
@section('content')

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

<div class="page-content">
    <div class="card border-top border-0 border-4 border-primary">
        <div class="card-body p-5">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
            </div>
            <div class="card-title d-flex align-items-center">
                <h5 class="mb-0 text-primary">Configuração do emitente por super ADM</h5>
            </div>
            <hr>
            {!! Form::open()->fill($item)
            ->post()
            ->route('empresas.storeConfig')
            ->multipart() !!}

            <input type="hidden" name="id" value="{{{ isset($item->id) ? $item->id : 0 }}}">

            <input type="hidden" name="empresaId" value="{{$empresa->id}}">

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
                        <div class="card-body">
                            <h4>Dados do certificado</h4>
                            <h6>serial <strong>{{ $infoCertificado['serial'] }}</strong></h6>
                            <h6>inicio <strong>{{ $infoCertificado['inicio'] }}</strong></h6>
                            <h6>expiracao <strong>{{ $infoCertificado['expiracao'] }}</strong></h6>
                            <h6>id <strong>{{ $infoCertificado['id'] }}</strong></h6>
                        </div>
                    </div>
                    @endif
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
                        {!! Form::tel('ie', 'Inscrição estadual')->attrs(['class' => ''])->required() !!}
                    </div>

                    <div class="row">
                        <div class="col-md-3 mt-3 file-certificado">
                            {!! Form::file('certificado', 'Certificado Digital') !!}
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
                        ->options($item != null ? [$item->cidade_id => $item->cidade->info] : [])->required()
                        !!}
                    </div>

                    <div class="col-md-2 mt-3">
                        {!! Form::tel('fone', 'Telefone')->attrs(['class' => 'fone'])->required() !!}
                    </div>

                    <div class="col-md-4 mt-3">
                        {!! Form::text('email', 'Email')->attrs(['class' => 'email']) !!}
                    </div>

                    <div class="col-md-12 mt-3">
                        {!! Form::select('CST_CSOSN_padrao', 'CST/CSOSN Padrão', App\Models\ConfigNota::listaCST())->attrs([
                        'class' => 'select2',
                        ])->required() !!}
                    </div>

                    <div class="col-md-6 mt-3">
                        {!! Form::select('CST_PIS_padrao', 'CST/PIS Padrão', App\Models\ConfigNota::listaCST_PIS_COFINS())->attrs([
                        'class' => 'select2',
                        ])->required() !!}
                    </div>

                    <div class="col-md-6 mt-3">
                        {!! Form::select('CST_COFINS_padrao', 'CST/COFINS Padrão', App\Models\ConfigNota::listaCST_PIS_COFINS())->attrs([
                        'class' => 'select2',
                        ])->required() !!}
                    </div>

                    <div class="col-md-12 mt-3">
                        {!! Form::select('CST_IPI_padrao', 'CST/IPI Padrão', App\Models\ConfigNota::listaCST_IPI())->attrs([
                        'class' => 'select2',
                        ])->required() !!}
                    </div>

                    <div class="col-md-4 mt-3">
                        {!! Form::select(
                        'nat_op_padrao',
                        'Natureza de Op. padrão frente de caixa',
                        $naturezas->pluck('natureza', 'id')->all(),
                        )->attrs(['class' => 'select2']) !!}
                    </div>

                    <div class="col-md-2 mt-3">
                        {!! Form::select('ambiente', 'Ambiente', [2 => 'Homologação', 1 => 'Produção'])->attrs(['class' => 'form-select'])->required() !!}
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
                        {!! Form::tel('auto_xml', 'CNPJ autorizado (opcional)')->attrs(['class' => 'cpf_cnpj']) !!}
                    </div>
                    <div class="col-12 mt-4">
                        @if (!isset($not_submit))
                        <div id="image-preview" class="_image-preview col-md-4">
                            <label for="image-upload" id="image-label">Selecione a imagem</label>
                            <input type="file" name="image" id="image-upload" class="_image-upload" accept="image/*" />
                            @isset($item)
                            @if ($item->logo)
                            <img src="/uploads/configEmitente/{{ $item->logo }}" class="img-default">
                            @else
                            <img src="/imgs/no_image.png" class="img-default">
                            @endif
                            @else
                            <img src="/imgs/no_image.png" class="img-default">
                            @endif
                        </div>
                        @endif
                    </div>
                </div>


                <div class="row div-outros d-none  mt-4">
                    <div class="col-md-6 mt-3">
                        {!! Form::select('sobrescrita_csonn_consumidor_final', 'CST/CSOSN Consumidor final (opcional)',
                        ['' => 'Selecione'] +
                        App\Models\ConfigNota::listaCST())->attrs(['class' => 'select2']) !!}
                    </div>

                    <div class="col-md-6 mt-2">
                        @php
                        $appendAttr = '<label class="text-info label-popover" data-bs-container="body" data-bs-toggle="popover" data-bs-placement="bottom" data-bs-content="Realize o cadastro no site https://deolhonoimposto.ibpt.org.br/Site/Entrar para gerar o seu token" data-bs-original-title="" title="">
                            <i class="m-1 bx bx-info-circle"></i>
                        </label>';

                        @endphp
                        {!! Form::text('token_ibpt', 'Token IBPT (opcional)' . $appendAttr)->attrs(['class' => 'form-control']) !!}
                    </div>

                    <div class="col-md-2 mt-3">
                        {!! Form::tel('percentual_lucro_padrao', 'Lucro padrão')->attrs(['class' => '']) !!}
                    </div>

                    <div class="col-md-2 mt-3">
                        {!! Form::tel('parcentual_max_desconto', 'Max desconto')->attrs(['class' => '']) !!}
                    </div>

                    <div class="col-md-2 mt-3">
                        {!! Form::tel('validade_orcamento', 'Validade orçamento')->attrs(['class' => '']) !!}
                    </div>

                    <div class="col-md-2 mt-3">
                        {!! Form::select('caixa_por_usuario', 'Tipo caixa', [0 => 'Por Usuário', 1 => 'Por Empresa'])->attrs([
                        'class' => 'select2',
                        ]) !!}
                    </div>

                    <div class="col-md-4 mt-3">
                        <br>
                        <div class="input-group" id="show_hide_password">
                            <input type="password" class="form-control" id="senha_remover" name="senha_remover" placeholder="Senha remover venda (opcional)" autocomplete="off" value="{{ isset($item) ? $item->senha_remover : '' }}"> <a href="javascript:;" class="input-group-text bg-transparent"><i class='bx bx-hide'></i></a>
                        </div>
                        @if(isset($item))
                        <a href="{{ route('configNF.removeSenha', $item->id) }}">remover senha</a>
                        @endif
                    </div>

                    <div class="col-md-2 mt-3">
                        {!! Form::tel('casas_decimais', 'Casas decimais')->attrs(['class' => '']) !!}
                    </div>
                    <div class="col-md-3 mt-3">
                        {!! Form::select('frete_padrao', 'Frete padrão', App\Models\ConfigNota::tiposFrete())->attrs([
                        'class' => 'select2',
                        ]) !!}
                    </div>
                    <div class="col-md-3 mt-3">
                        {!! Form::select(
                        'tipo_pagamento_padrao',
                        'Tipo pagamento padrão',
                        App\Models\ConfigNota::tiposPagamento(),
                        )->attrs(['class' => 'select2']) !!}
                    </div>
                    <div class="col-md-3 mt-2">
                        @php
                        $appendAttr = '<label class="text-info label-popover" data-bs-container="body" data-bs-toggle="popover" data-bs-placement="bottom" data-bs-content="Se sim configurar as credencias de email no menu Configurações > Configurar email" data-bs-original-title="" title="">
                            <i class="bx bx-info-circle m-1"></i>
                        </label>';
                        @endphp
                        {!! Form::select('e-mail_proprio', 'E-mail próprio' . $appendAttr, [0 => 'Não', 1 => 'Sim'])->attrs(['class' => 'popover-button'])->wrapperAttrs(['']) !!}
                    </div>
                    <div class="col-md-12 mt-3">
                        @php
                        $appendAttr = '<label class="text-info label-popover" data-bs-container="body" data-bs-toggle="popover" data-bs-placement="bottom" data-bs-content="Para o cálculo automático de % aproveitamento crédito, utilize R$ e %, ex: Permite o aproveitamento de ICMS no valor de R$ correspondente a alíquota de % nos termos do art.23 da lc 123" data-bs-original-title="" title="">
                            <i class="bx bx-info-circle m-1"></i>
                        </label>';
                        @endphp
                        {!! Form::textarea('campo_obs_nfe', 'Observação para NFe (opcional)' . $appendAttr) !!}
                    </div>
                    <div class="col-md-12 mt-3">
                        @php
                        $appendAttr = '<label class="text-info label-popover" data-bs-container="body" data-bs-toggle="popover" data-bs-placement="bottom" data-bs-content="Está informação será inclusa na impressão de pedido/orçamento" data-bs-original-title="" title="">
                            <i class="bx bx-info-circle m-1"></i>
                        </label>';
                        @endphp
                        {!! Form::textarea('campo_obs_pedido', 'Observação padrão para pedido/orçamento (opcional)' . $appendAttr) !!}
                    </div>
                </div>
                <div class="col-12 mt-5">
                    <button type="submit" class="btn btn-primary px-5">Salvar</button>
                </div>
            </div>


            {!! Form::close() !!}
        </div>
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
        if (cnpj.length == 18) {
            $("#btn-consulta span").removeClass("d-none");

            consultaAlternativa(cnpj, (data) => {
                $("#btn-consulta span").addClass("d-none");

                if (data == false) {
                    swal(
                        "Alerta"
                        , "Nenhum retorno encontrado para este CNPJ, informe manualmente por gentileza"
                        , "warning"
                    );
                } else {
                    console.log(data);
                    $("#inp-razao_social").val(data.nome);
                    $("#inp-nome_fantasia").val(data.nome);

                    $("#inp-logradouro").val(data.logradouro);
                    $("#inp-numero").val(data.numero);
                    $("#inp-bairro").val(data.bairro);

                    $("#inp-cep").val(data.cep.replace(".", ""));

                    cidadePorNome(data.municipio, (res) => {
                        if (res) {
                            var newOption = new Option(
                                res.nome + "(" + res.uf + ")"
                                , res.id
                                , false
                                , false
                            );
                            $("#inp-cidade_id")
                                .html(newOption)
                                .trigger("change");
                        }
                    });
                }
            });

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

    function consultaAlternativa(cnpj, call) {
        cnpj = cnpj.replace(/[^0-9]/g, "");
        let res = null;
        $.ajax({
            url: "https://www.receitaws.com.br/v1/cnpj/" + cnpj
            , type: "GET"
            , crossDomain: true
            , dataType: "jsonp"
            , success: function(data) {
                if (data.status == "ERROR") {
                    swal("Erro", data.message, "error");
                    call(false);
                } else {
                    call(data);
                }
            }
            , error: function(e) {
                $("#consulta").removeClass("spinner");
                console.log(e);

                call(false);
            }
        , });
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

@endsection
