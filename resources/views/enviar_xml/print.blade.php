<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Relat&oacute;rio CFOP</title>
    <style>
        @page { margin: 1cm; }
        body { font-family: sans-serif; font-size: 11px; color: #333; line-height: 1.4; }
        .titulo { font-size: 16px; font-weight: bold; text-align: center; margin-bottom: 15px; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .b-top { border-top: 1px solid #000; }
        .b-bottom { border-bottom: 1px solid #000; }
        .bg-gray { background-color: #f2f2f2; }
        td, th { padding: 5px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .footer-box { margin-top: 20px; border: 1px solid #000; padding: 10px; }
    </style>
</head>
<body>
    <div class="titulo">{{ $config->razao_social }}</div>
    
    <table>
        <tr>
            <td style="width: 50%">CNPJ: <strong>{{ $config->cnpj }}</strong></td>
            <td style="width: 50%">IE: <strong>{{ $config->ie }}</strong></td>
        </tr>
        <tr>
            <td colspan="2" class="b-top">
                Endere&ccedil;o: {{ $config->logradouro }}, {{ $config->numero }} - {{ $config->bairro }} - {{ $config->municipio }} ({{ $config->UF }})
            </td>
        </tr>
    </table>

    <table class="b-top">
        <tr>
            <td><strong>Relat&oacute;rio de Sa&iacute;das por CFOP</strong></td>
            <td class="text-right">Per&iacute;odo: {{ date('d/m/Y', strtotime($dataInicial)) }} a {{ date('d/m/Y', strtotime($dataFinal)) }}</td>
        </tr>
        <tr>
            <td>CFOP Selecionado: <strong>{{ $cfop }}</strong></td>
            <td class="text-right">Gerado em: {{ date('d/m/Y H:i') }}</td>
        </tr>
    </table>

    <table>
        <thead>
            <tr class="bg-gray b-top b-bottom">
                <th align="left">C&oacute;digo</th>
                <th align="left">Descri&ccedil;&atilde;o</th>
                <th class="text-center">UN</th>
                <th class="text-right">Qtd</th>
                <th class="text-right">Valor Total (R$)</th>
            </tr>
        </thead>
        <tbody>
            @php $totalQtd = 0; $totalValor = 0; @endphp
            @foreach($itens as $i)
            <tr class="b-bottom">
                <td>{{ $i->produto_id }}</td>
                <td>{{ $i->nome }}</td>
                <td class="text-center">{{ $i->unidade }}</td>
                <td class="text-right">{{ number_format($i->qtd, 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($i->total, 2, ',', '.') }}</td>
            </tr>
            @php $totalQtd += $i->qtd; $totalValor += $i->total; @endphp
            @endforeach
        </tbody>
    </table>

    <div class="footer-box">
        <table style="margin-bottom: 0;">
            <tr>
                <td class="text-center"><strong>Registros:</strong> {{ count($itens) }}</td>
                <td class="text-center"><strong>Soma Qtd:</strong> {{ number_format($totalQtd, 2, ',', '.') }}</td>
                <td class="text-center">
                    <strong>Total CFOP: R$ {{ number_format($totalValor, 2, ',', '.') }}</strong>
                    <div style="font-size: 9px;">Representatividade: {{ $percentual }}%</div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>