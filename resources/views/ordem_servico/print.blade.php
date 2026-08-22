<!DOCTYPE html>
<html>
<head>
    <title>Ordem de Serviço</title>
    <style type="text/css">
        .content{ margin-top: -30px; }
        .titulo{ font-size: 20px; margin-bottom: 0px; font-weight: bold; }
        .b-top{ border-top: 1px solid #000; }
        .b-bottom{ border-bottom: 1px solid #000; }
        .page_break { page-break-before: always; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 4px; vertical-align: top; }
        .bg-cinza { background: #f2f2f2; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <div class="content">
        <table>
            <tr>
                @php $config = App\Models\ConfigNota::configStatic(); @endphp
                <td style="width: 50%;">
                    @if($config->logo != "" && file_exists(public_path('uploads/configEmitente/').$config->logo))
                        <img src="{{ 'data:image/png;base64,' . base64_encode(file_get_contents(public_path('uploads/configEmitente/').$config->logo)) }}" alt="Logo" style="max-width: 150px; max-height: 80px; border-radius: 5px;">
                    @else
                        <img src="{{ 'data:image/png;base64,' . base64_encode(file_get_contents(public_path('logos/logonfenotas.png'))) }}" alt="Logo" style="max-width: 150px; max-height: 80px; border-radius: 5px;">
                    @endif
                </td>
                <td style="width: 50%; text-align: right;">
                    <label class="titulo">ORDEM DE SERVIÇO: OS_{{$ordem->id}}</label>
                </td>
            </tr>
        </table>
    </div>

    <br>
    <strong>Dados da empresa</strong>
    <table>
        <tr>
            <td class="b-top" style="width: 480px;">Razão social: <strong>{{$config->razao_social}}</strong></td>
            <td class="b-top" style="width: 220px;">Documento: <strong>{{ \App\Models\ConfigNota::formataCnpj($config->cnpj) }}</strong></td>
        </tr>
        <tr>
            <td class="b-top" colspan="2">Endereço: <strong>{{$config->logradouro}}, {{$config->numero}} - {{$config->bairro}} - {{$config->municipio}} ({{$config->UF}})</strong></td>
        </tr>
        <tr>
            <td class="b-top b-bottom">Complemento: <strong>{{$config->complemento}}</strong></td>
            <td class="b-top b-bottom">CEP: <strong>{{$config->cep}}</strong> | Tel: <strong>{{$config->fone}}</strong></td>
        </tr>
    </table>

    <br>
    <strong>Dados do cliente</strong>
    <table class="b-top">
        <tr>
            <td style="width: 450px;">Nome: <strong>{{$ordem->cliente->razao_social}}</strong></td>
            <td style="width: 247px;">CPF/CNPJ: <strong>{{$ordem->cliente->cpf_cnpj}}</strong></td>
        </tr>
        <tr>
            <td colspan="2" class="b-bottom">Endereço: <strong>{{$ordem->cliente->rua}}, {{$ordem->cliente->numero}} - {{$ordem->cliente->bairro}} - {{$ordem->cliente->cidade->nome}} ({{$ordem->cliente->cidade->uf}})</strong></td>
        </tr>
    </table>

    <!-- SEÇÃO DE FUNCIONÁRIOS ADICIONADA AQUI -->
    @if(isset($ordem->funcionarios) && count($ordem->funcionarios) > 0)
    <br>
    <strong>Equipe Técnica / Funcionários</strong>
    <table class="b-top b-bottom">
        <thead>
            <tr class="bg-cinza">
                <td style="width: 50%;">Nome do Funcionário</td>
                <td style="width: 50%;">Função</td>
            </tr>
        </thead>
        <tbody>
            @foreach($ordem->funcionarios as $f)
            <tr>
                <td>{{ $f->funcionario->nome }}</td>
                <td>{{ $f->funcao ?? 'Não informada' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <br>
    <strong>Serviços</strong>
    <table class="b-top b-bottom">
        <thead>
            <tr class="bg-cinza">
                <td style="width: 350px;">Descrição</td>
                <td style="width: 100px;">Horas</td>
                <td style="width: 100px;">Valor/Hora</td>
                <td style="width: 100px;">Subtotal</td>
            </tr>
        </thead>
        <tbody>
            @foreach($ordem->servicos as $item)
            <tr>
                <td>{{ $item->servico->nome }}</td>
                <td>
                    @php
                        $totalMinutos = $item->quantidade * 60;
                        $h = floor($totalMinutos / 60);
                        $m = $totalMinutos % 60;
                        echo sprintf('%02d:%02u', $h, $m);
                    @endphp
                </td>
                <td>R$ {{ __moeda($item->valor_unitario) }}</td>
                <td>R$ {{ __moeda($item->sub_total) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table>
        <tr>
            <td class="b-bottom text-center" style="width: 50%;">
                <strong>Total de Horas (Serviços): 
                    @php
                        $sumMinutosS = $ordem->servicos->sum('quantidade') * 60;
                        echo sprintf('%02d:%02u', floor($sumMinutosS / 60), $sumMinutosS % 60);
                    @endphp
                </strong>
            </td>
            <td class="b-bottom text-center" style="width: 50%;">
                <strong>Total Serviços: R$ {{ __moeda($ordem->servicos->sum('sub_total')) }}</strong>
            </td>
        </tr>
    </table>

    <br>
    <strong>Produtos / Materiais</strong>
    <table class="b-top b-bottom">
        <thead>
            <tr class="bg-cinza">
                <td style="width: 350px;">Descrição</td>
                <td style="width: 100px;">Qtd/Horas</td>
                <td style="width: 100px;">Valor/Un.</td>
                <td style="width: 100px;">Subtotal</td>
            </tr>
        </thead>
        <tbody>
            @foreach($ordem->produtos as $item)
            <tr>
                <td>{{ $item->produto->nome }}</td>
                <td>
                    @php
                        $totalMinutosP = $item->quantidade * 60;
                        $hp = floor($totalMinutosP / 60);
                        $mp = $totalMinutosP % 60;
                        echo sprintf('%02d:%02u', $hp, $mp);
                    @endphp
                </td>
                <td>R$ {{ __moeda($item->valor_unitario) }}</td>
                <td>R$ {{ __moeda($item->sub_total) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table>
        <tr>
            <td class="b-bottom text-center" style="width: 50%;">
                <strong>Total Horas (Produtos): 
                    @php
                        $sumMinutosP = $ordem->produtos->sum('quantidade') * 60;
                        echo sprintf('%02d:%02u', floor($sumMinutosP / 60), $sumMinutosP % 60);
                    @endphp
                </strong>
            </td>
            <td class="b-bottom text-center" style="width: 50%;">
                <strong>Total Produtos: R$ {{ __moeda($ordem->produtos->sum('sub_total')) }}</strong>
            </td>
        </tr>
    </table>

    <br>
    <table style="width: 100%;">
        <tr>
            <td>Forma de pagamento: <strong>{{ $ordem->forma_pagamento }}</strong></td>
            <td class="text-right">Data: <strong>{{\Carbon\Carbon::parse($ordem->created_at)->format('d/m/Y H:i')}}</strong></td>
        </tr>
    </table>

    <table class="b-top" style="margin-top: 10px;">
        <tr>
            <td>Desconto (-): <strong>R$ {{__moeda($ordem->desconto)}}</strong></td>
            <td>Acréscimo (+): <strong>R$ {{__moeda($ordem->acrescimo)}}</strong></td>
            <td style="font-size: 14px;"><strong>VALOR LÍQUIDO: R$ {{ __moeda($ordem->valor) }}</strong></td>
        </tr>
    </table>

    @if($ordem->descricao != '')
        <hr>
        <h4 style="margin-bottom: 5px;">Descrição da OS:</h4>
        <p style="margin-top: 0;">{{ $ordem->descricao }}</p>
    @endif

    <br><br><br>
    <table>
        <tr>
            <td class="text-center">
                ________________________________________<br>
                <span style="font-size: 11px;">{{$config->razao_social}}</span>
            </td>
            <td class="text-center">
                ________________________________________<br>
                <span style="font-size: 11px;">{{$ordem->cliente->razao_social}}</span>
            </td>
        </tr>
    </table>
</body>
</html>