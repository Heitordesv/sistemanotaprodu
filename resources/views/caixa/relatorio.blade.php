<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <title>Relatório de Fechamento de Caixa</title>

    <style>

        *{
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body{
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            color: #222;
            margin: 25px;
        }

        .header{
            width: 100%;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .titulo{
            font-size: 24px;
            font-weight: bold;
            letter-spacing: 1px;
            color: #000;
        }

        .subtitulo{
            font-size: 13px;
            color: #666;
            margin-top: 5px;
        }

        .empresa{
            font-size: 15px;
            font-weight: bold;
            margin-top: 8px;
        }

        .text-center{
            text-align: center;
        }

        .text-right{
            text-align: right;
        }

        .box{
            border: 1px solid #dcdcdc;
            border-radius: 6px;
            padding: 14px;
            margin-bottom: 18px;
            background: #fafafa;
        }

        .box-title{
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 12px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 6px;
            color: #000;
        }

        table{
            width: 100%;
            border-collapse: collapse;
        }

        td{
            padding: 7px;
            vertical-align: top;
        }

        th{
            padding: 8px;
            background: #f3f3f3;
            border-bottom: 1px solid #ccc;
            font-size: 12px;
            text-align: left;
        }

        .table-vendas tr:nth-child(even){
            background: #f7f7f7;
        }

        .table-vendas td{
            border-bottom: 1px solid #ececec;
        }

        .valor{
            font-weight: bold;
            color: #000;
        }

        .total-geral{
            font-size: 18px;
            font-weight: bold;
            color: #000;
        }

        .resumo td{
            border: 1px solid #ddd;
            padding: 12px;
            background: #fff;
        }

        .assinatura{
            margin-top: 70px;
        }

        .assinatura-linha{
            width: 320px;
            border-top: 1px solid #000;
            padding-top: 5px;
            text-align: center;
        }

        .badge{
            background: #000;
            color: #fff;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 11px;
            display: inline-block;
        }

        .descricao{
            font-size: 11px;
            color: #555;
            line-height: 16px;
            margin-top: 3px;
        }

        .status{
            padding: 3px 6px;
            border-radius: 3px;
            font-size: 10px;
            background: #efefef;
            display: inline-block;
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

<!-- HEADER -->
<table class="header">

    <tr>

        <td width="20%">

            @if(!empty($config->logo) && file_exists(public_path('uploads/configEmitente/'.$config->logo)))

                <img
                    src="{{ 'data:image/png;base64,' . base64_encode(file_get_contents(public_path('uploads/configEmitente/'.$config->logo))) }}"
                    width="120">

            @endif

        </td>

        <td width="60%" class="text-center">

            <div class="titulo">
                FECHAMENTO DE CAIXA
            </div>

            <div class="subtitulo">
                Relatório consolidado da movimentação financeira
            </div>

            <div class="empresa">
                {{ $config->razao_social }}
            </div>

            CNPJ: {{ $config->cnpj }}

        </td>

        <td width="20%" class="text-right">

            <span class="badge">
                EMISSÃO
            </span>

            <br><br>

            {{ date('d/m/Y') }}

            <br>

            {{ date('H:i:s') }}

        </td>

    </tr>

</table>

<!-- DADOS DO CAIXA -->
<div class="box">

    <div class="box-title">
        INFORMAÇÕES DO CAIXA
    </div>

    <table>

        <tr>

            <td width="33%">

                <strong>Valor Inicial</strong>

                <br><br>

                <span class="valor">
                    R$ {{ number_format($abertura->valor,2,',','.') }}
                </span>

            </td>

            <td width="33%">

                <strong>Abertura</strong>

                <br><br>

                {{ \Carbon\Carbon::parse($abertura->created_at)->format('d/m/Y H:i') }}

            </td>

            <td width="33%">

                <strong>Fechamento</strong>

                <br><br>

                {{ \Carbon\Carbon::parse($abertura->updated_at)->format('d/m/Y H:i') }}

            </td>

        </tr>

    </table>

</div>

<!-- PAGAMENTOS -->
<div class="box">

    <div class="box-title">
        TOTAL POR TIPO DE PAGAMENTO
    </div>

    <table>

        @foreach($somaTiposPagamento as $key => $tp)

            @if($tp > 0)

                <tr>

                    <td>

                        {{ App\Models\VendaCaixa::getTipoPagamento($key) }}

                    </td>

                    <td class="text-right valor">

                        R$ {{ number_format($tp,2,',','.') }}

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

</div>

<!-- VENDAS -->
<div class="box">

    <div class="box-title">
        VENDAS REALIZADAS
    </div>

    <table class="table-vendas">

        <thead>

            <tr>

                <th>Cliente</th>
                <th>Data</th>
                <th>Pagamento</th>
                <th>Status</th>
                <th class="text-right">Valor</th>
                <th class="text-right">Desconto</th>

            </tr>

        </thead>

        <tbody>

            @forelse($vendas as $v)

                <tr>

                    <td>

                        {{ $v->cliente->razao_social ?? 'NÃO IDENTIFICADO' }}

                    </td>

                    <td>

                        {{ \Carbon\Carbon::parse($v->created_at)->format('d/m/Y H:i') }}

                    </td>

                    <td>

                        @if($v->tipo_pagamento == '99')

                            <div>

                                <strong>Múltiplo</strong>

                                @if(isset($v->fatura) && count($v->fatura) > 0)

                                    <div class="descricao">

                                        @foreach($v->fatura as $f)

                                            • {{ $v->getTipoPagamento($f->forma_pagamento) }}

                                            -

                                            R$ {{ number_format($f->valor,2,',','.') }}

                                            <br>

                                        @endforeach

                                    </div>

                                @endif

                            </div>

                        @else

                            {{ $v->getTipoPagamento($v->tipo_pagamento) }}

                        @endif

                    </td>

                    <td>

                        <span class="status">

                            {{ $v->estado ?? 'FINALIZADO' }}

                        </span>

                    </td>

                    <td class="text-right valor">

                        R$ {{ number_format($v->valor_total,2,',','.') }}

                    </td>

                    <td class="text-right">

                        R$ {{ number_format($v->desconto,2,',','.') }}

                    </td>

                </tr>

                @php

                    if(!$v->rascunho && !$v->consignado){
                        $somaVendas += $v->valor_total;
                    }

                @endphp

            @empty

                <tr>

                    <td colspan="6" class="text-center">

                        Nenhuma venda encontrada

                    </td>

                </tr>

            @endforelse

        </tbody>

    </table>

</div>

<!-- SANGRIAS -->
<div class="box">

    <div class="box-title">
        SANGRIAS
    </div>

    <table class="table-vendas">

        <thead>

            <tr>

                <th>Data</th>
                <th>Usuário</th>
                <th>Observação</th>
                <th class="text-right">Valor</th>

            </tr>

        </thead>

        <tbody>

            @if(sizeof($sangrias) > 0)

                @foreach($sangrias as $s)

                    <tr>

                        <td>

                            {{ \Carbon\Carbon::parse($s->created_at)->format('d/m/Y H:i') }}

                        </td>

                        <td>

                            {{ $s->usuario->nome ?? 'NÃO INFORMADO' }}

                        </td>

                        <td>

                            {{ $s->observacao ?? 'Sem observação' }}

                        </td>

                        <td class="text-right valor">

                            R$ {{ number_format($s->valor,2,',','.') }}

                        </td>

                    </tr>

                    @php
                        $somaSangria += $s->valor;
                    @endphp

                @endforeach

            @else

                <tr>

                    <td colspan="4" class="text-center">

                        Nenhuma sangria registrada

                    </td>

                </tr>

            @endif

        </tbody>

    </table>

</div>

<!-- SUPRIMENTOS -->
<div class="box">

    <div class="box-title">
        SUPRIMENTOS
    </div>

    <table class="table-vendas">

        <thead>

            <tr>

                <th>Data</th>
                <th>Usuário</th>
                <th class="text-right">Valor</th>

            </tr>

        </thead>

        <tbody>

            @if(sizeof($suprimentos) > 0)

                @foreach($suprimentos as $s)

                    <tr>

                        <td>

                            {{ \Carbon\Carbon::parse($s->created_at)->format('d/m/Y H:i') }}

                        </td>

                        <td>

                            {{ $s->usuario->nome ?? 'NÃO INFORMADO' }}

                        </td>

                        <td class="text-right valor">

                            R$ {{ number_format($s->valor,2,',','.') }}

                        </td>

                    </tr>

                    @php
                        $somaSuprimento += $s->valor;
                    @endphp

                @endforeach

            @else

                <tr>

                    <td colspan="3" class="text-center">

                        Nenhum suprimento registrado

                    </td>

                </tr>

            @endif

        </tbody>

    </table>

</div>

<!-- RESUMO -->
@php

    $valorEsperado =
        $abertura->valor +
        $valorEmDinheiro +
        $somaSuprimento -
        $somaSangria;

@endphp

<div class="box">

    <div class="box-title">
        RESUMO FINAL
    </div>

    <table class="resumo">

        <tr>

            <td>

                <strong>Total de vendas</strong>

                <br><br>

                <span class="valor">
                    R$ {{ number_format($somaVendas,2,',','.') }}
                </span>

            </td>

            <td>

                <strong>Suprimentos</strong>

                <br><br>

                <span class="valor">
                    R$ {{ number_format($somaSuprimento,2,',','.') }}
                </span>

            </td>

            <td>

                <strong>Sangrias</strong>

                <br><br>

                <span class="valor">
                    R$ {{ number_format($somaSangria,2,',','.') }}
                </span>

            </td>

            <td>

                <strong>Dinheiro</strong>

                <br><br>

                <span class="valor">
                    R$ {{ number_format($valorEmDinheiro,2,',','.') }}
                </span>

            </td>

        </tr>

        <tr>

            <td colspan="4" class="text-center">

                <div class="total-geral">

                    VALOR ESPERADO EM CAIXA:

                    R$ {{ number_format($valorEsperado,2,',','.') }}

                </div>

            </td>

        </tr>

    </table>

</div>

<!-- ASSINATURA -->
<div class="assinatura">

    <div class="assinatura-linha">

        {{ $usuario->nome ?? 'Usuário do sistema' }}

    </div>

</div>

</body>
</html>