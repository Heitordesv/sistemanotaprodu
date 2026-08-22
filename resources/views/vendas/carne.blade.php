<!DOCTYPE html>
<html>
<head>
    <title></title>
    <!--  -->

    <style type="text/css">
        body {
            line-height: 1px;
        }

        div {
            display: inline-block;
        }

        .logo {
            border: 1px solid #000;
        }

        .dados-emitente {
            border: 1px solid #000;
            margin-left: -1px;
            margin-bottom: 30px;
        }

        .div-financeira {
            border: 1px solid #000;
            margin-top: -214px;
        }

        .text-financeira {
            transform: rotate(-90deg);
            margin-top: 75px;
            position: absolute;
            margin-left: -50px;
        }

        .div-text-financeira {
            border: 1px solid #000;
            width: 35px;
            height: 150px;
            margin-left: -1px;
            margin-top: -1px;
        }

        .data-aceite {
            margin-left: -1px;
            margin-top: -1px;
            border: 1px solid #000;
            width: 140px;
            height: 57px;
        }

        .fatura {
            border: 1px solid #000;
            width: 190px;
            height: 70px;
            display: inline-block;
            margin-left: 141px;
            margin-top: -57px;
            background-color: silver;

        }

        .fatura-valor {
            border: 1px solid #000;
            margin-top: -1px;
            margin-left: -1px;
            width: 93px;
            height: 19px;
        }

        .fatura-numero {
            border-top: 1px solid #000;
            border-left: 1px solid #000;
            border-bottom: 1px solid #000;
            margin-top: -1px;
            margin-left: -5px;
            width: 96px;
            height: 19px;
        }

        .duplicata {
            border: 1px solid #000;
            width: 210px;
            height: 70px;
            display: inline-block;
            margin-left: -1px;
            margin-top: -59px;
            background-color: silver;

        }

        .vencimento {
            border: 1px solid #000;
            width: 163px;
            height: 70px;
            display: inline-block;
            margin-left: -1px;
            margin-top: -53px;
            background-color: silver;

        }

        .dados-cliente {
            border: 1px solid #000;
            margin-left: 141px;
            margin-top: -5px;
            width: 565px;
            height: 89px;
        }

        .valor-fatura {
            border: 1px solid #000;
            width: 93px;
            height: 29px;
            margin-top: -14px;
            margin-left: -1px;
        }

        .valor-duplicata {
            border: 1px solid #000;
            width: 106px;
            height: 29px;
            margin-top: -14px;
            margin-left: -1px;
        }

        .data-vencimento {
            border: 1px solid #000;
            height: 34px;
            width: 163px;
            margin-top: -11px;
            margin-left: -1px;
            /* background-color: silver;  */
        }

        .duplicata-valor {
            border: 1px solid #000;
            margin-top: -1px;
            margin-left: -1px;
            width: 106px;
            height: 19px;
        }

        .numero-fatura-duplicata {
            border-top: 1px solid #000;
            border-left: 1px solid #000;
            border-bottom: 1px solid #000;
            width: 103px;
            height: 19px;
            margin-top: -14px;
            margin-left: -5px;
        }

        .div-extenso {
            border: 1px solid #000;
            width: 565px;
            height: 15px;
            margin-left: 141px;
            margin-top: -1px;
            background-color: silver;

        }

        .valor-extenso {
            border-left: 1px solid #000;
            width: 470px;
            height: 15px;
            margin-left: 94px;
            margin-top: -23px;
        }

        .div-final {
            border: 1px solid #000;
            width: 565px;
            height: 60px;
            margin-left: 141px;
            margin-top: -1px;
        }

    </style>

</head>
<body>
    @foreach($venda->duplicatas as $key => $d)
    @if(!$d->entrada)
    <!-- inicio -->
    <div style="margin-top: -5px">
        <table class="div-first">
            <tr>
                <div class="logo" style="width: 140px; height: 130px">
                    @if($config->logo != "")
                    <img src="{{'data:image/png;base64,' . base64_encode(file_get_contents(@public_path('uploads/configEmitente/').$config->logo))}}" style="width: 100px; margin: 5px">
                    @endif
                </div>
                {{-- dados emitente --}}
                <div class="dados-emitente" style="width: 565px; height: 100px;">
                    <h4 style="margin-left: 5px;">{{$config->razao_social}}</h4>
                    <p style="margin-left: 5px; font-size: 14px;">ENDEREÇO: {{ $config->logradouro }} <span style="margin-left: 55px;">N: {{ $config->numero }}</span> </p>
                    <p style="margin-left: 5px; font-size: 14px;">
                        CIDADE: {{ $config->municipio }} <span style="margin-left: 45px;">BAIRRO: {{ $config->bairro }}</span><span style="margin-left: 75px;">UF: {{ $config->UF }}</span>
                    </p>
                    <p style="margin-left: 5px; font-size: 14px;">
                        CNPJ: {{ $config->cnpj }} <span style="margin-left: 39px;">IE: {{ $config->ie }}</span>
                    </p>
                    <p style="margin-left: 5px; font-size: 14px;">
                        CEP: {{ $config->cep }} <span style="margin-left: 15px;">FONE: {{$config->fone }}</span> <span style="margin-left: 15px; font-size: 14px;">DATA EMISSÃO: {{ $config->created_at }}</span>
                    </p>
                </div>
                {{-- fatura --}}
                <div class="fatura row">
                    <p style="font-size: 12px; margin-left: 70px;">FATURA</p>
                    <div class="fatura-valor">
                        <p style="margin-left: 25px; font-size: 16px">Valor:</p>
                        <div class="valor-fatura">
                            <p style="margin-left: 18px; margin-top: 20px;">R$ {{number_format($d->valor_integral, 2, ',', '.')}}</p>
                        </div>
                    </div>

                    <div class="fatura-numero">
                        <p style="margin-left: 20px; font-size: 16px">Número:</p>
                        <p style="margin-left: 25px; margin-top: 23px;">{{$key+1}}</p>
                    </div>
                </div>
                {{-- duplicata --}}
                <div class="duplicata">
                    <p style="font-size: 12px; margin-left: 70px;">DUPLICATA</p>
                    <div class="duplicata-valor">
                        <p style="margin-left: 30px; font-size: 16px">Valor:</p>
                        <div class="valor-duplicata">
                            <p style="margin-left: 18px; margin-top: 20px;">R$ {{number_format($d->valor_integral, 2, ',', '.')}}</p>
                        </div>
                    </div>
                    <div class="numero-fatura-duplicata">
                        <p style="margin-left: 18px; margin-top: 16px;">Nº Ordem:</p>
                        <p style="margin-left: 25px; margin-top: 23px;">{{$key+1}}</p>
                    </div>
                </div>
                {{-- vencimento --}}
                <div class="vencimento">
                    <div class="vencimento-data">
                        <p style="font-size: 20px; margin-left: 15px; margin-top: 25px;">VENCIMENTO</p>
                        <div class="data-vencimento">
                            <p style="margin-left: 33px; margin-top: 22px; font-size: 20px;">{{\Carbon\Carbon::parse($d->data_vencimento)->format('d/m/Y')}}</p>
                        </div>
                    </div>
                </div>

                <div class="dados-cliente">
                    <p style="font-size: 11px; margin-top: 13px; margin-left: 5px;">RAZÃO SOCIAL SACADO: {{$venda->cliente_id}} -- {{$venda->cliente->razao_social}}</p>
                    <p style="font-size: 11px; margin-top: 11px; margin-left: 5px;">FANTASIA SACADO: {{$venda->cliente->nome_fantasia}}</p>
                    <span style="font-size: 11px; margin-top: 11px; margin-left: 5px;">CPF/CNPJ: {{$venda->cliente->cpf_cnpj}}</span><span style="font-size: 11px; margin-top: 13px; margin-left: 100px;">IE/RG: {{$venda->cliente->ie_rg}}</span>
                    <p style="font-size: 11px; margin-top: 11px; margin-left: 5px;">ENDEREÇO: {{$venda->cliente->rua}}<span style="font-size: 11px; margin-top: 13px; margin-left: 50px;">BAIRRO: {{$venda->cliente->ie_rg}}</span><span style="margin-left: 75px;">N: {{$venda->cliente->numero}}</span></p>
                    <p style="font-size: 11px; margin-top: 11px; margin-left: 5px;">CIDADE: {{ $venda->cliente->cidade->nome }}<span style="font-size: 11px; margin-top: 13px; margin-left: 199px;">TELEFONE: {{$venda->cliente->telefone}}</span><span style="margin-left: 29px;">UF: {{$venda->cliente->cidade->uf}}</span></p>
                    <p style="font-size: 11px; margin-top: 11px; margin-left: 5px;">E-MAIL: {{ $venda->cliente->email }}<span style="margin-left: 257px;">CEP: {{$venda->cliente->cep}}</span></p>
                    <p style="font-size: 11px; margin-top: 11px; margin-left: 5px;">PRAÇA: {{ $venda->cliente->cidade->nome }}</p>
                </div>

                <div class="div-extenso">
                    <p style="font-size: 10px; margin-top: 12px; margin-left: 10px;">
                        Valor Por Extenso
                    </p>

                    <div class="valor-extenso">
                        <p style="margin-top: 11px; margin-left: 10px; font-size: 10px;">Valor por extenso......</p>
                    </div>
                </div>
                <div class="div-final">
                    <p style="font-size: 9px; margin-left: 5px;">Reconheço(emos) a exatidão desta duplicata de venda MERCANTIL/PRESTAÇÃO DE SERVIÇOS, na importância acima que pagarei(emos) à,</p>
                    <p style="font-size: 9px; margin-left: 5px;">{{ $config->razao_social }}, ou a sua ordem na praça e vencimento indicados.</p>
                    <p style="font-size: 9px; margin-left: 25px; margin-top: 23px;">________________________________________________ <span style="margin-left: 65px;">___________________________________________</span></p>
                    <p style="font-size: 9px; margin-left: 55px;">{{ $config->razao_social}} <span style="margin-left: 175px;">ASSINATURA DO SACADO</span></p>
                </div>

            </tr>
        </table>
        <table>
            <!-- 2 linha -->
            <tr>
                <div class="div-financeira" style="width: 140px; height: 205px">
                    <div class="div-text-financeira">
                        <p class="text-financeira" style="font-size: 10px;">Para uso da Instituição Financeira</p>
                    </div>
                    <div class="data-aceite">
                        <p style="margin-left: 10px; margin-top: 20px;">____/____/_____</p>
                        <p class="text-data-aceite" style="margin-top: 20px; margin-left: 25px; font-size: 12px;">DATA ACEITE</p>
                    </div>
                </div>
            </tr>

        </table>
    </div>
    <!-- fim -->

    <table style="margin-top:-12px; margin-bottom: 10px;">
        <tr>
            <td class="" style="width: 700px;">
                <p>--------------------------------------------------------------------------------------------------------------------------------------</p>
            </td>
        </tr>
    </table>
    @endif
    @endforeach
</body>
</html>
