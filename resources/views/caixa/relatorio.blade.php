<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <title>Relatório de Fechamento de Caixa</title>
    <style>
        *{box-sizing:border-box} body{font-family:Arial,Helvetica,sans-serif;font-size:12px;color:#222;margin:25px}
        .header{width:100%;border-bottom:2px solid #000;padding-bottom:15px;margin-bottom:20px}.titulo{font-size:24px;font-weight:bold}.subtitulo{font-size:13px;color:#666;margin-top:5px}.empresa{font-size:15px;font-weight:bold;margin-top:8px}
        .text-center{text-align:center}.text-right{text-align:right}.box{border:1px solid #dcdcdc;border-radius:6px;padding:14px;margin-bottom:18px;background:#fafafa}.box-title{font-size:14px;font-weight:bold;margin-bottom:12px;border-bottom:1px solid #ddd;padding-bottom:6px}
        table{width:100%;border-collapse:collapse}td{padding:7px;vertical-align:top}th{padding:8px;background:#f3f3f3;border-bottom:1px solid #ccc;text-align:left}.table-data tr:nth-child(even){background:#f7f7f7}.table-data td{border-bottom:1px solid #ececec}
        .valor{font-weight:bold}.resumo td{border:1px solid #ddd;padding:12px;background:#fff}.assinatura{margin-top:70px}.assinatura-linha{width:320px;border-top:1px solid #000;padding-top:5px;text-align:center}
    </style>
</head>
<body>
<table class="header"><tr>
    <td width="20%">@if(!empty($config->logo) && file_exists(public_path('uploads/configEmitente/'.$config->logo)))<img src="{{ 'data:image/png;base64,' . base64_encode(file_get_contents(public_path('uploads/configEmitente/'.$config->logo))) }}" width="120">@endif</td>
    <td width="60%" class="text-center"><div class="titulo">FECHAMENTO DE CAIXA #{{ $abertura->id }}</div><div class="subtitulo">Vendas e recebimentos são eventos financeiros separados</div><div class="empresa">{{ $config->razao_social ?? '' }}</div>CNPJ: {{ $config->cnpj ?? '' }}</td>
    <td width="20%" class="text-right">{{ date('d/m/Y') }}<br>{{ date('H:i:s') }}</td>
</tr></table>

<div class="box"><div class="box-title">INFORMAÇÕES DO CAIXA</div><table><tr>
    <td><strong>Operador</strong><br><br>{{ $usuario->nome ?? 'NÃO INFORMADO' }}</td>
    <td><strong>Valor inicial</strong><br><br>R$ {{ number_format($abertura->valor,2,',','.') }}</td>
    <td><strong>Abertura</strong><br><br>{{ \Carbon\Carbon::parse($abertura->created_at)->format('d/m/Y H:i') }}</td>
    <td><strong>Fechamento</strong><br><br>{{ \Carbon\Carbon::parse($abertura->updated_at)->format('d/m/Y H:i') }}</td>
</tr></table></div>

<div class="box"><div class="box-title">VENDAS POR TIPO DE PAGAMENTO</div><table>
@foreach($somaTiposPagamento as $key => $tp)
    @if($tp > 0)<tr><td>{{ App\Models\VendaCaixa::getTipoPagamento($key) }}</td><td class="text-right valor">R$ {{ number_format($tp,2,',','.') }}</td></tr>@endif
@endforeach
</table></div>

<div class="box"><div class="box-title">VENDAS REALIZADAS</div><table class="table-data">
<thead><tr><th>Cliente</th><th>Data</th><th>Pagamento</th><th>Status</th><th class="text-right">Valor</th></tr></thead><tbody>
@php $somaVendas = 0; @endphp
@forelse($vendas as $v)
<tr><td>{{ $v->cliente->razao_social ?? 'NÃO IDENTIFICADO' }}</td><td>{{ \Carbon\Carbon::parse($v->created_at)->format('d/m/Y H:i') }}</td><td>{{ $v->tipo_pagamento == '99' ? 'Múltiplo' : $v->getTipoPagamento($v->tipo_pagamento) }}</td><td>{{ $v->estado ?? $v->estado_emissao ?? 'FINALIZADO' }}</td><td class="text-right valor">R$ {{ number_format($v->valor_total,2,',','.') }}</td></tr>
@php if (!$v->rascunho && !$v->consignado && strtoupper((string)($v->estado ?? '')) !== 'CANCELADO' && strtoupper((string)($v->estado_emissao ?? '')) !== 'CANCELADO') { $somaVendas += (float) $v->valor_total; } @endphp
@empty<tr><td colspan="5" class="text-center">Nenhuma venda encontrada</td></tr>@endforelse
</tbody></table></div>

<div class="box"><div class="box-title">RECEBIMENTOS DE CONTAS</div><table class="table-data">
<thead><tr><th>Conta</th><th>Recebido por</th><th>Forma</th><th>Horário</th><th class="text-right">Valor</th></tr></thead><tbody>
@forelse($recebimentos as $r)
<tr><td>#{{ $r->conta_receber_id }}</td><td>{{ $r->usuario_nome }}</td><td>{{ $r->tipo_pagamento_nome }}</td><td>{{ $r->received_at ? \Carbon\Carbon::parse($r->received_at)->format('d/m/Y H:i') : '--' }}</td><td class="text-right valor">R$ {{ number_format($r->valor,2,',','.') }}</td></tr>
@empty<tr><td colspan="5" class="text-center">Nenhum recebimento neste caixa</td></tr>@endforelse
</tbody><tfoot><tr><th colspan="4">TOTAL DE RECEBIMENTOS</th><th class="text-right">R$ {{ number_format($totalRecebimentos,2,',','.') }}</th></tr></tfoot></table></div>

<div class="box"><div class="box-title">MOVIMENTAÇÃO E RESUMO</div><table class="resumo"><tr>
<td><strong>Total de vendas</strong><br><br>R$ {{ number_format($somaVendas,2,',','.') }}</td>
<td><strong>Total de recebimentos</strong><br><br>R$ {{ number_format($totalRecebimentos,2,',','.') }}</td>
<td><strong>Recebimentos em dinheiro</strong><br><br>R$ {{ number_format($totalRecebimentosDinheiro,2,',','.') }}</td>
<td><strong>Suprimentos</strong><br><br>R$ {{ number_format($totalSuprimentos,2,',','.') }}</td>
<td><strong>Sangrias</strong><br><br>R$ {{ number_format($totalSangrias,2,',','.') }}</td>
</tr><tr><td colspan="3"><strong>RESULTADO FINANCEIRO</strong><br><br>R$ {{ number_format($resultadoFinanceiro,2,',','.') }}</td><td colspan="2"><strong>DINHEIRO NA GAVETA</strong><br><br>R$ {{ number_format($dinheiroNaGaveta,2,',','.') }}</td></tr></table></div>

<div class="assinatura"><div class="assinatura-linha">{{ $usuario->nome ?? 'Usuário do sistema' }}</div></div>
</body>
</html>
