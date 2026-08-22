<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; margin: 0; padding: 0; line-height: 1.45; color: #111; }
        .header { text-align: center; font-weight: bold; font-size: 16px; text-transform: uppercase; border: 1px solid #000; padding: 10px; margin-bottom: 10px; background: #f5f5f5; }
        .section-title { background: #eee; font-weight: bold; text-transform: uppercase; padding: 6px; border: 1px solid #000; border-bottom: none; }
        .box { border: 1px solid #000; padding: 10px; margin-bottom: 10px; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .table th, .table td { border: 1px solid #000; padding: 6px; text-align: left; }
        .summary-table { width: 100%; border-collapse: collapse; margin-top: -10px; }
        .summary-table td { border: 1px solid #000; padding: 6px; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .footer-box { border: 1px solid #000; padding: 10px; font-size: 10px; text-align: justify; }
        .muted { color: #555; }
    </style>
</head>
<body>
@php
    $empresa = $pedido->empresa;
    $cliente = $pedido->cliente;
    $endereco = $pedido->endereco;
    $formaPagamento = trim(strtoupper((string) ($pedido->forma_pagamento ?? '')));
    $isPix = $formaPagamento === 'PIX';
    $porcentagemPix = (float) ($config_ecomercres->desconto_padrao_pix ?? 0);
    $somaBrutaProdutos = 0.0;
@endphp

<div class="header">Declaração de Conteúdo</div>

<div class="section-title">1. Remetente</div>
<div class="box">
    <strong>Nome/Razão Social:</strong> {{ $empresa->nome_fantasia ?? $empresa->nome ?? $empresa->razao_social ?? 'Não informado' }}<br>
    <strong>CPF/CNPJ:</strong> {{ $empresa->cpf_cnpj ?? 'Não informado' }}<br>
    <strong>Endereço:</strong> {{ $empresa->rua ?? 'Não informado' }}, {{ $empresa->numero ?? 'S/N' }} - {{ $empresa->bairro ?? '' }}<br>
    <strong>Cidade/UF:</strong> {{ $empresa->cidade->nome ?? '' }} / {{ $empresa->cidade->uf ?? '' }} - <strong>CEP:</strong> {{ $empresa->cep ?? 'Não informado' }}
</div>

<div class="section-title">2. Destinatário</div>
<div class="box">
    <strong>Nome:</strong> {{ trim((string) (($cliente->nome ?? 'Cliente') . ' ' . ($cliente->sobre_nome ?? ''))) }}<br>
    <strong>CPF/CNPJ:</strong> {{ $cliente->cpf ?? 'Não informado' }}<br>
    <strong>Endereço:</strong> {{ $endereco->rua ?? $endereco->logradouro ?? 'Não informado' }}, {{ $endereco->numero ?? 'S/N' }} - {{ $endereco->bairro ?? '' }}<br>
    <strong>Cidade/UF:</strong> {{ $endereco->cidade ?? '' }} / {{ $endereco->uf ?? '' }} - <strong>CEP:</strong> {{ $endereco->cep ?? 'Não informado' }}
</div>

<div class="section-title">3. Identificação do Conteúdo</div>
<table class="table">
    <thead>
        <tr style="background: #f2f2f2;">
            <th width="30">Item</th>
            <th>Conteúdo</th>
            <th width="40">Qtd.</th>
            <th width="85">Valor unitário</th>
            <th width="85">Subtotal</th>
        </tr>
    </thead>
    <tbody>
        @forelse($pedido->itens as $idx => $item)
            @php
                $produtoEcommerce = $item->produto;
                $produtoBase = $produtoEcommerce->produto ?? null;
                $nomeProduto = $produtoBase->nome ?? 'Produto não disponível';
                $valorOriginal = (float) ($produtoEcommerce->valor ?? $produtoBase->valor_venda ?? 0);
                $quantidade = max(1, (int) ($item->quantidade ?? 1));
                $somaBrutaProdutos += $valorOriginal * $quantidade;
                $valorUnitarioVisual = $valorOriginal;

                if ($isPix && $porcentagemPix > 0) {
                    $valorUnitarioVisual = $valorOriginal * (1 - ($porcentagemPix / 100));
                }
            @endphp
            <tr>
                <td style="text-align:center;">{{ $idx + 1 }}</td>
                <td>{{ $nomeProduto }}</td>
                <td style="text-align:center;">{{ $quantidade }}</td>
                <td>R$ {{ number_format($valorUnitarioVisual, 2, ',', '.') }}</td>
                <td>R$ {{ number_format($valorUnitarioVisual * $quantidade, 2, ',', '.') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="5" style="text-align:center;" class="muted">Nenhum item disponível neste pedido.</td>
            </tr>
        @endforelse
    </tbody>
</table>

<table class="summary-table">
    <tr>
        <td class="text-right font-bold">SOMA DOS PRODUTOS:</td>
        <td width="125">R$ {{ number_format($somaBrutaProdutos, 2, ',', '.') }}</td>
    </tr>
    <tr>
        <td class="text-right font-bold">FRETE ({{ strtoupper((string) ($pedido->tipo_frete ?? 'N/I')) }}):</td>
        <td>R$ {{ number_format((float) ($pedido->valor_frete ?? 0), 2, ',', '.') }}</td>
    </tr>

    @if((float) ($pedido->desconto ?? 0) > 0)
        <tr>
            <td class="text-right font-bold">DESCONTO:</td>
            <td>- R$ {{ number_format((float) $pedido->desconto, 2, ',', '.') }}</td>
        </tr>
    @elseif($isPix && $porcentagemPix > 0)
        @php($valorDescontoPix = $somaBrutaProdutos * ($porcentagemPix / 100))
        <tr>
            <td class="text-right font-bold">DESCONTO PIX ({{ number_format($porcentagemPix, 2, ',', '.') }}%):</td>
            <td>- R$ {{ number_format($valorDescontoPix, 2, ',', '.') }}</td>
        </tr>
    @endif

    <tr style="background:#f2f2f2;">
        <td class="text-right font-bold" style="font-size:12px;">VALOR TOTAL DO PEDIDO:</td>
        <td class="font-bold" style="font-size:12px;">R$ {{ number_format((float) ($pedido->valor_total ?? 0), 2, ',', '.') }}</td>
    </tr>
</table>

<div style="margin:10px 0;">
    <strong>FORMA DE PAGAMENTO:</strong> {{ $formaPagamento ?: 'Não informada' }}
</div>

@if($pedido->observacao)
    <div class="box">
        <strong>Observação do pedido:</strong><br>
        {{ $pedido->observacao }}
    </div>
@endif

<div class="footer-box">
    <strong>DECLARAÇÃO:</strong><br>
    Declaro que não me enquadro no conceito de contribuinte previsto no art. 4º da Lei Complementar nº 87/1996, logo, não estou obrigado à emissão de nota fiscal. Declaro ainda que o conteúdo deste volume não se constitui em objeto de mercancia e que sou o único responsável pelas informações prestadas, estando ciente das penalidades legais em caso de falsidade.
    <br><br>
    <div style="text-align:center; margin-top:20px;">
        ___________________________________________________________<br>
        <strong>Assinatura do Remetente</strong><br>
        Emitido em: {{ now()->format('d/m/Y H:i') }}
    </div>
</div>
</body>
</html>