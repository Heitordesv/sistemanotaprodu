<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">

    <style>

        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
        }

        body{
            font-family: monospace;
            font-size:10px;
            width:270px;
            color:#000;
            padding:8px;
        }

        .center{
            text-align:center;
        }

        .right{
            text-align:right;
        }

        .bold{
            font-weight:bold;
        }

        .line{
            border-top:1px dashed #000;
            margin:6px 0;
        }

        table{
            width:100%;
            border-collapse:collapse;
        }

        td{
            padding:2px 0;
            vertical-align:top;
            word-break:break-word;
        }

        .titulo{
            font-size:13px;
            font-weight:bold;
        }

        .small{
            font-size:9px;
        }

        .total{
            font-size:12px;
            font-weight:bold;
        }

    </style>
</head>

<body>

@php
    $valorEmDinheiro = 0;
    $somaVendas = 0;
    $somaSuprimento = 0;
    $somaSangria = 0;
@endphp

<!-- ================================= -->
<!-- CABEÇALHO -->
<!-- ================================= -->

<div class="center">

    @php
        $logoEmpresa = public_path('uploads/configEmitente/' . ($config->logo ?? ''));
    @endphp

    @if(!empty($config->logo) && file_exists($logoEmpresa))

        <img
            src="data:image/png;base64,{{ base64_encode(file_get_contents($logoEmpresa)) }}"
            width="80"
            style="margin-bottom:5px;">

    @endif

    <div class="titulo">
        FECHAMENTO DE CAIXA
    </div>

    <br>

    <div class="bold">
        {{ $config->razao_social ?? '' }}
    </div>

    CNPJ:
    {{ $config->cnpj ?? '' }}

    <br>

    {{ date('d/m/Y H:i') }}

</div>

<div class="line"></div>

<!-- ================================= -->
<!-- DADOS -->
<!-- ================================= -->

<table>

    <tr>
        <td>Abertura:</td>
        <td class="right">
            R$ {{ number_format($abertura->valor ?? 0, 2, ',', '.') }}
        </td>
    </tr>

    <tr>
        <td>Início:</td>
        <td class="right">
            {{ \Carbon\Carbon::parse($abertura->created_at)->format('d/m H:i') }}
        </td>
    </tr>

    <tr>
        <td>Fechamento:</td>
        <td class="right">
            {{ \Carbon\Carbon::parse($abertura->updated_at)->format('d/m H:i') }}
        </td>
    </tr>

</table>

<div class="line"></div>

<!-- ================================= -->
<!-- PAGAMENTOS -->
<!-- ================================= -->

<div class="center bold">
    PAGAMENTOS
</div>

<div class="line"></div>

<table>

@foreach($somaTiposPagamento as $key => $tp)

    @if($tp > 0)

        <tr>

            <td>
                {{ App\Models\VendaCaixa::getTipoPagamento($key) }}
            </td>

            <td class="right">
                {{ number_format($tp, 2, ',', '.') }}
            </td>

        </tr>

        @php
            if($key == '01'){
                $valorEmDinheiro = $tp;
            }
        @endphp

    @endif

@endforeach

</table>

<div class="line"></div>

<!-- ================================= -->
<!-- VENDAS -->
<!-- ================================= -->

<div class="center bold">
    VENDAS
</div>

<div class="line"></div>

@forelse($vendas as $v)

    <table>

        <tr>

            <td width="65%">
                {{ $v->cliente->razao_social ?? 'NÃO IDENTIFICADO' }}
            </td>

            <td width="35%" class="right">
                {{ number_format($v->valor_total ?? 0, 2, ',', '.') }}
            </td>

        </tr>

        <tr>

            <td class="small">
                {{ $v->getTipoPagamento($v->tipo_pagamento) }}
            </td>

            <td class="small right">
                {{ \Carbon\Carbon::parse($v->created_at)->format('d/m H:i') }}
            </td>

        </tr>

    </table>

    <div class="line"></div>

    @php
        $somaVendas += ($v->valor_total ?? 0);
    @endphp

@empty

    <div class="center">
        Nenhuma venda encontrada
    </div>

@endforelse

<!-- ================================= -->
<!-- TOTAL VENDAS -->
<!-- ================================= -->

<table>

    <tr>

        <td class="total">
            TOTAL VENDAS
        </td>

        <td class="right total">
            R$ {{ number_format($somaVendas, 2, ',', '.') }}
        </td>

    </tr>

</table>

<div class="line"></div>

<!-- ================================= -->
<!-- SUPRIMENTOS / SANGRIAS -->
<!-- ================================= -->

@foreach($suprimentos as $s)
    @php
        $somaSuprimento += ($s->valor ?? 0);
    @endphp
@endforeach

@foreach($sangrias as $s)
    @php
        $somaSangria += ($s->valor ?? 0);
    @endphp
@endforeach

@php

    $valorEsperado =
        ($abertura->valor ?? 0)
        + $valorEmDinheiro
        + $somaSuprimento
        - $somaSangria;

    $valorContagem = $abertura->valor_dinheiro_caixa ?? 0;

    $diferenca = $valorContagem - $valorEsperado;

@endphp

<table>

    <tr>
        <td>Suprimentos</td>
        <td class="right">
            {{ number_format($somaSuprimento, 2, ',', '.') }}
        </td>
    </tr>

    <tr>
        <td>Sangrias</td>
        <td class="right">
            {{ number_format($somaSangria, 2, ',', '.') }}
        </td>
    </tr>

    <tr>
        <td>Dinheiro</td>
        <td class="right">
            {{ number_format($valorEmDinheiro, 2, ',', '.') }}
        </td>
    </tr>

</table>

<div class="line"></div>

<!-- ================================= -->
<!-- RESUMO FINAL -->
<!-- ================================= -->

<table>

    <tr>

        <td class="bold">
            Esperado em Dinheiro
        </td>

        <td class="right bold">
            {{ number_format($valorEsperado, 2, ',', '.') }}
        </td>

    </tr>

    <tr>

    

    </tr>

</table>

<div class="line"></div>

<!-- ================================= -->
<!-- ASSINATURA -->
<!-- ================================= -->

<br><br>

<div class="center">
    ______________________
    <br>
    {{ $usuario->nome ?? '' }}
</div>

</body>
</html>