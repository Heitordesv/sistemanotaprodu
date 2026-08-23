<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}body{font-family:monospace;font-size:10px;width:270px;color:#000;padding:8px}.center{text-align:center}.right{text-align:right}.bold{font-weight:bold}.line{border-top:1px dashed #000;margin:6px 0}table{width:100%;border-collapse:collapse}td{padding:2px 0;vertical-align:top;word-break:break-word}.titulo{font-size:13px;font-weight:bold}.small{font-size:9px}.total{font-size:12px;font-weight:bold}
    </style>
</head>
<body>
<div class="center">
    @php $logoEmpresa = public_path('uploads/configEmitente/' . ($config->logo ?? '')); @endphp
    @if(!empty($config->logo) && file_exists($logoEmpresa))<img src="data:image/png;base64,{{ base64_encode(file_get_contents($logoEmpresa)) }}" width="80" style="margin-bottom:5px;">@endif
    <div class="titulo">FECHAMENTO CAIXA #{{ $abertura->id }}</div><br><div class="bold">{{ $config->razao_social ?? '' }}</div>CNPJ: {{ $config->cnpj ?? '' }}<br>{{ date('d/m/Y H:i') }}
</div>
<div class="line"></div>
<table><tr><td>Operador:</td><td class="right">{{ $usuario->nome ?? '' }}</td></tr><tr><td>Abertura:</td><td class="right">R$ {{ number_format($abertura->valor ?? 0,2,',','.') }}</td></tr><tr><td>Início:</td><td class="right">{{ \Carbon\Carbon::parse($abertura->created_at)->format('d/m H:i') }}</td></tr></table>

<div class="line"></div><div class="center bold">VENDAS</div><div class="line"></div>
@forelse($vendas as $v)
<table><tr><td width="65%">{{ $v->cliente->razao_social ?? 'NÃO IDENTIFICADO' }}</td><td width="35%" class="right">{{ number_format($v->valor_total ?? 0,2,',','.') }}</td></tr><tr><td class="small">{{ $v->tipo_pagamento == '99' ? 'Múltiplo' : $v->getTipoPagamento($v->tipo_pagamento) }}</td><td class="small right">{{ \Carbon\Carbon::parse($v->created_at)->format('d/m H:i') }}</td></tr></table><div class="line"></div>
@empty<div class="center">Nenhuma venda encontrada</div><div class="line"></div>@endforelse
<table><tr><td class="total">TOTAL VENDAS</td><td class="right total">R$ {{ number_format($somaVendas,2,',','.') }}</td></tr></table>

<div class="line"></div><div class="center bold">RECEBIMENTOS DE CONTAS</div><div class="line"></div>
@forelse($recebimentos as $r)
<table><tr><td>Conta #{{ $r->conta_receber_id }}</td><td class="right bold">{{ number_format($r->valor,2,',','.') }}</td></tr><tr><td class="small">{{ $r->tipo_pagamento_nome }}</td><td class="small right">{{ $r->received_at ? \Carbon\Carbon::parse($r->received_at)->format('d/m H:i') : '--' }}</td></tr><tr><td colspan="2" class="small">Recebido por: {{ $r->usuario_nome }}</td></tr></table><div class="line"></div>
@empty<div class="center">Nenhum recebimento</div><div class="line"></div>@endforelse
<table><tr><td class="total">TOTAL RECEBIMENTOS</td><td class="right total">R$ {{ number_format($totalRecebimentos,2,',','.') }}</td></tr></table>

<div class="line"></div><table>
<tr><td>Suprimentos</td><td class="right">{{ number_format($totalSuprimentos,2,',','.') }}</td></tr>
<tr><td>Sangrias</td><td class="right">{{ number_format($totalSangrias,2,',','.') }}</td></tr>
<tr><td>Receb. dinheiro</td><td class="right">{{ number_format($totalRecebimentosDinheiro,2,',','.') }}</td></tr>
</table>
<div class="line"></div><table>
<tr><td class="bold">RESULTADO FINANCEIRO</td><td class="right bold">{{ number_format($resultadoFinanceiro,2,',','.') }}</td></tr>
<tr><td class="bold">DINHEIRO NA GAVETA</td><td class="right bold">{{ number_format($dinheiroNaGaveta,2,',','.') }}</td></tr>
</table>
<div class="line"></div><br><br><div class="center">______________________<br>{{ $usuario->nome ?? '' }}</div>
</body>
</html>
