@extends('default.layout', ['title' => 'Etiqueta Correios'])

@section('content')
@php
    $total = 0;
@endphp

<style>
    .etiqueta-ecommerce .print-sheet { max-width: 900px; margin: 0 auto; background: #fff; border: 1px solid #dee2e6; }
    .etiqueta-ecommerce .section-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: #6c757d; }
    .etiqueta-ecommerce .address-block { line-height: 1.65; }
    @media print {
        .etiqueta-ecommerce .no-print, .header, .aside, .footer, nav { display: none !important; }
        .etiqueta-ecommerce .print-sheet { max-width: none; border: 1px solid #000; }
        .etiqueta-ecommerce { margin: 0 !important; padding: 0 !important; }
    }
</style>

<div class="container-fluid etiqueta-ecommerce">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 no-print">
        <div>
            <div class="text-primary font-weight-bold text-uppercase small mb-1">Loja Online</div>
            <h3 class="font-weight-bold mb-1">Etiqueta / declaração</h3>
            <p class="text-muted mb-0">Pedido #{{ $pedido->id }}</p>
        </div>
        <div class="mt-3 mt-md-0">
            <a href="{{ route('pedidosEcommerce.show', $pedido->id) }}" class="btn btn-light border btn-sm">
                <i class="bx bx-arrow-back mr-1"></i> Voltar
            </a>
            <button type="button" onclick="window.print()" class="btn btn-primary btn-sm ml-1">
                <i class="bx bx-printer mr-1"></i> Imprimir
            </button>
        </div>
    </div>

    <div class="card print-sheet">
        <div class="card-body p-4">
            <div class="text-center border-bottom pb-3 mb-4">
                <h4 class="font-weight-bold mb-1">DECLARAÇÃO DE CONTEÚDO / ETIQUETA DE ENVIO</h4>
                <div class="text-muted">Pedido #{{ $pedido->id }}</div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="border rounded p-3 h-100">
                        <div class="section-label mb-2">Remetente</div>
                        <div class="font-weight-bold">{{ $pedido->empresa->razao_social ?? $pedido->empresa->nome_fantasia ?? 'Empresa não informada' }}</div>
                        <div class="address-block mt-2">
                            CNPJ: {{ $pedido->empresa->cpf_cnpj ?? 'Não informado' }}<br>
                            {{ $pedido->empresa->rua ?? 'Endereço não informado' }}, {{ $pedido->empresa->numero ?? 'S/N' }}
                            @if($pedido->empresa->bairro ?? null) - {{ $pedido->empresa->bairro }} @endif<br>
                            {{ $pedido->empresa->cidade->nome ?? '' }}
                            @if($pedido->empresa->cidade->uf ?? null) / {{ $pedido->empresa->cidade->uf }} @endif<br>
                            CEP: {{ $pedido->empresa->cep ?? 'Não informado' }}<br>
                            Telefone: {{ $pedido->empresa->telefone ?? 'Não informado' }}
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <div class="border rounded p-3 h-100">
                        <div class="section-label mb-2">Destinatário</div>
                        <div class="font-weight-bold">{{ trim(($pedido->cliente->nome ?? '') . ' ' . ($pedido->cliente->sobre_nome ?? '')) ?: 'Cliente não informado' }}</div>
                        <div class="address-block mt-2">
                            @if($pedido->endereco)
                                {{ $pedido->endereco->rua ?? 'Endereço não informado' }}, {{ $pedido->endereco->numero ?? 'S/N' }}
                                @if($pedido->endereco->complemento ?? null) - {{ $pedido->endereco->complemento }} @endif<br>
                                {{ $pedido->endereco->bairro ?? '' }}<br>
                                {{ $pedido->endereco->cidade ?? '' }}
                                @if($pedido->endereco->uf ?? null) / {{ $pedido->endereco->uf }} @endif<br>
                                CEP: {{ $pedido->endereco->cep ?? 'Não informado' }}<br>
                            @else
                                Endereço não informado.<br>
                            @endif
                            Telefone: {{ $pedido->cliente->telefone ?? 'Não informado' }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="border rounded p-3 mb-3">
                <div class="section-label mb-2">Dados do pedido</div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <tbody>
                            <tr><th style="width: 32%;">Nº do pedido</th><td>#{{ $pedido->id }}</td></tr>
                            <tr><th>Data</th><td>{{ optional($pedido->created_at)->format('d/m/Y H:i') ?: 'Não informada' }}</td></tr>
                            <tr><th>Forma de pagamento</th><td>{{ $pedido->forma_pagamento ?: 'Não informada' }}</td></tr>
                            <tr><th>Status do pagamento</th><td>{{ $pedido->status_pagamento_normalizado }}</td></tr>
                            @if($pedido->codigo_rastreio)
                                <tr><th>Código de rastreio</th><td>{{ $pedido->codigo_rastreio }}</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="border rounded p-3 mb-3">
                <div class="section-label mb-2">Conteúdo</div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Produto</th>
                                <th class="text-center" style="width: 80px;">Qtd.</th>
                                <th class="text-right" style="width: 130px;">Valor</th>
                                <th class="text-right" style="width: 130px;">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pedido->itens as $item)
                                @php
                                    $produtoEcommerce = $item->produto;
                                    $produtoFiscal = $produtoEcommerce->produto ?? null;
                                    $valor = (float) ($produtoEcommerce->valor ?? 0);
                                    $qtd = max(1, (int) $item->quantidade);
                                    $subtotal = $valor * $qtd;
                                    $total += $subtotal;
                                    $nome = $produtoFiscal->nome ?? $produtoEcommerce->descricao ?? 'Produto';
                                @endphp
                                <tr>
                                    <td>{{ $nome }}</td>
                                    <td class="text-center">{{ $qtd }}</td>
                                    <td class="text-right">R$ {{ number_format($valor, 2, ',', '.') }}</td>
                                    <td class="text-right">R$ {{ number_format($subtotal, 2, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted">Nenhum item disponível.</td></tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-right">Total dos produtos</th>
                                <th class="text-right">R$ {{ number_format($total, 2, ',', '.') }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="border rounded p-3">
                <p class="mb-4">Declaro que os itens descritos acima foram vendidos legalmente e não possuem conteúdo proibido ou ilícito.</p>
                <div class="row pt-3">
                    <div class="col-8 text-center">
                        _________________________________________________<br>
                        <strong>Assinatura do remetente</strong>
                    </div>
                    <div class="col-4 text-center">
                        ____ / ____ / ______<br>
                        <strong>Data</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection