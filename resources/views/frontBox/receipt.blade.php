<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 2mm; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            color: #000;
            font-family: DejaVu Sans, sans-serif;
            font-size: {{ $paperWidth < 70 ? '7px' : '9px' }};
        }
        .center { text-align: center; }
        .right { text-align: right; }
        .bold { font-weight: bold; }
        .separator { border-top: 1px dashed #000; margin: 3px 0; }
        .logo { display: block; max-width: 22mm; max-height: 13mm; margin: 0 auto 2px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 1px 0; vertical-align: top; }
        .items .description { width: 48%; }
        .items .number { text-align: right; white-space: nowrap; }
        .totals td:first-child { width: 66%; }
        .grand-total { font-size: {{ $paperWidth < 70 ? '10px' : '13px' }}; font-weight: bold; }
        .meta td:first-child { width: 42%; }
    </style>
</head>
<body>
    @if($logo)
        <img class="logo" src="{{ $logo }}" alt="Logo">
    @endif

    <div class="center bold">{{ $config->razao_social ?? '' }}</div>
    @if(!empty($config->nome_fantasia))
        <div class="center">{{ $config->nome_fantasia }}</div>
    @endif
    <div class="center">CNPJ: {{ $config->cnpj ?? '' }} @if(!empty($config->ie)) | IE: {{ $config->ie }} @endif</div>
    <div class="center">
        {{ $config->logradouro ?? '' }}@if(!empty($config->numero)), {{ $config->numero }}@endif
        @if(!empty($config->bairro)) - {{ $config->bairro }}@endif
    </div>
    <div class="center">{{ $config->municipio ?? '' }}@if(!empty($config->UF))-{{ $config->UF }}@endif</div>

    <div class="separator"></div>
    <div class="center bold">DOCUMENTO AUXILIAR DE VENDA</div>
    <div class="center">NÃO É DOCUMENTO FISCAL</div>
    <div class="separator"></div>

    <table class="items">
        <thead>
            <tr>
                <th class="description">Código / Descrição</th>
                <th class="number">Qtde</th>
                <th class="number">Vl Unit.</th>
                <th class="number">Vl Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($venda->itens as $item)
                <tr>
                    <td class="description">{{ $item->produto_id }} - {{ optional($item->produto)->nome ?? 'Produto' }}</td>
                    <td class="number">{{ number_format((float) $item->quantidade, 2, ',', '.') }}</td>
                    <td class="number">{{ number_format((float) $item->valor, 2, ',', '.') }}</td>
                    <td class="number">{{ number_format((float) $item->valor * (float) $item->quantidade, 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="separator"></div>
    <table class="totals">
        <tr><td>Qtde total de itens</td><td class="right">{{ $venda->itens->count() }}</td></tr>
        <tr><td>Subtotal dos produtos R$</td><td class="right">{{ number_format($totals['subtotal'], 2, ',', '.') }}</td></tr>
        <tr><td>Taxa de entrega R$</td><td class="right">{{ number_format($totals['taxa_entrega'], 2, ',', '.') }}</td></tr>
        <tr><td>{{ $totals['acrescimo_label'] }} R$</td><td class="right">{{ number_format($totals['acrescimo'], 2, ',', '.') }}</td></tr>
        <tr><td>{{ $totals['desconto_label'] }} R$</td><td class="right">{{ number_format($totals['desconto'], 2, ',', '.') }}</td></tr>
        <tr class="grand-total"><td>Valor a pagar R$</td><td class="right">{{ number_format($totals['total'], 2, ',', '.') }}</td></tr>
    </table>

    <div class="separator"></div>
    <table>
        <thead><tr><th>FORMA DE PAGAMENTO</th><th class="right">VALOR PAGO R$</th></tr></thead>
        <tbody>
            @forelse($venda->fatura as $fatura)
                <tr>
                    <td>{{ \App\Models\VendaCaixa::getTipoPagamento($fatura->forma_pagamento) }}</td>
                    <td class="right">{{ number_format((float) $fatura->valor, 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td>{{ \App\Models\VendaCaixa::getTipoPagamento($venda->tipo_pagamento) }}</td>
                    <td class="right">{{ number_format($totals['total'], 2, ',', '.') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="separator"></div>
    <table class="meta">
        <tr><td>Troco R$</td><td class="right">{{ number_format((float) ($venda->troco ?? 0), 2, ',', '.') }}</td></tr>
        <tr><td>Data</td><td class="right">{{ optional($venda->created_at)->format('d/m/Y H:i:s') }}</td></tr>
        <tr><td>Código da venda</td><td class="right">{{ $venda->id }}</td></tr>
        <tr><td>Vendedor</td><td class="right">{{ optional($venda->usuario)->nome ?? '--' }}</td></tr>
    </table>
</body>
</html>
