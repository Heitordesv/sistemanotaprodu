@extends('default.layout', ['title' => 'Detalhes'])

@section('content')
<div class="page-content">
    <div class="card border-top border-0 border-4 border-primary">
        <div class="card-body p-4">
            <div class="d-sm-flex align-items-center justify-content-between mb-3">
                <h4 class="mb-0 text-primary"><i class="bx bx-calculator"></i> Detalhes do Caixa #{{ $abertura->id }}</h4>
                <a href="{{ route('caixa.list') }}" class="btn btn-light btn-sm"><i class="bx bx-arrow-back"></i> Voltar</a>
            </div>
            <hr>

            <div class="row mb-4">
                <div class="col-md-3"><div class="card bg-light-primary shadow-none border h-100"><div class="card-body"><h6 class="text-primary">Total de vendas</h6><h3 class="mb-0">{{ sizeof($vendas) }}</h3></div></div></div>
                <div class="col-md-3"><div class="card bg-light-success shadow-none border h-100"><div class="card-body"><h6 class="text-success">Valor de abertura</h6><h3 class="mb-0">R$ {{ __moeda($abertura->valor) }}</h3></div></div></div>
                <div class="col-md-3"><div class="card bg-light-info shadow-none border h-100"><div class="card-body"><h6 class="text-info">Resultado financeiro</h6><h3 class="mb-0">R$ {{ __moeda($resultadoFinanceiro) }}</h3></div></div></div>
                <div class="col-md-3"><div class="card bg-light-warning shadow-none border h-100"><div class="card-body"><h6 class="text-warning">Dinheiro na gaveta</h6><h3 class="mb-0">R$ {{ __moeda($dinheiroNaGaveta) }}</h3></div></div></div>
            </div>

            <div class="mb-4">
                <h4 class="text-primary mb-3"><i class="bx bx-wallet"></i> Total de vendas por tipo de pagamento</h4>
                <div class="row">
                    @foreach ($somaTiposPagamento as $key => $tp)
                        @if ($tp > 0)
                            <div class="col-md-4 col-lg-3 mb-3"><div class="card border shadow-sm h-100"><div class="card-header bg-light"><strong>{{ App\Models\VendaCaixa::getTipoPagamento($key) }}</strong></div><div class="card-body text-center"><h4 class="text-success mb-0">R$ {{ number_format($tp, 2, ',', '.') }}</h4></div></div></div>
                        @endif
                    @endforeach
                </div>
            </div>

            <div class="card border shadow-sm mb-4">
                <div class="card-header bg-light"><h5 class="mb-0"><i class="bx bx-list-ul"></i> Vendas</h5></div>
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle mb-0">
                        <thead class="table-light"><tr><th>Cliente</th><th>Data</th><th>Pagamento</th><th>Estado</th><th>NFCe / NFe</th><th>Tipo</th><th class="text-end">Valor</th></tr></thead>
                        <tbody>
                            @php $somaVendas = 0; @endphp
                            @forelse ($vendas as $item)
                                <tr>
                                    <td>{{ $item->cliente->razao_social ?? 'Consumidor Final' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y H:i') }}</td>
                                    <td>
                                        @if($item->tipo_pagamento == '99')
                                            <span class="badge bg-dark mb-1">Pagamento múltiplo</span>
                                            @if(isset($item->fatura) && count($item->fatura) > 0)
                                                @foreach($item->fatura as $f)
                                                    <div style="font-size:12px">• {{ $item->getTipoPagamento($f->forma_pagamento) }} - <strong>R$ {{ number_format($f->valor, 2, ',', '.') }}</strong></div>
                                                @endforeach
                                            @endif
                                        @else
                                            <span class="badge bg-primary">{{ $item->getTipoPagamento($item->tipo_pagamento) }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $item->estado }}</td><td>{{ $item->numero_nfe > 0 ? $item->NFcNumero : '--' }}</td><td>{{ $item->tipo }}</td>
                                    <td class="text-end"><strong class="text-success">R$ {{ __moeda($item->valor_total) }}</strong></td>
                                </tr>
                                @php
                                    if (!$item->rascunho && !$item->consignado && strtoupper((string)($item->estado ?? '')) !== 'CANCELADO' && strtoupper((string)($item->estado_emissao ?? '')) !== 'CANCELADO') {
                                        $somaVendas += (float) $item->valor_total;
                                    }
                                @endphp
                            @empty
                                <tr><td colspan="7" class="text-center py-4">Nenhuma venda encontrada</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card border shadow-sm mb-4">
                <div class="card-header bg-light"><h5 class="mb-0"><i class="bx bx-money"></i> Recebimentos de contas</h5></div>
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle mb-0">
                        <thead class="table-light"><tr><th>Conta</th><th>Recebido por</th><th>Forma de pagamento</th><th>Horário</th><th class="text-end">Valor recebido</th></tr></thead>
                        <tbody>
                            @forelse ($recebimentos as $recebimento)
                                <tr>
                                    <td><strong>#{{ $recebimento->conta_receber_id }}</strong></td><td>{{ $recebimento->usuario_nome }}</td><td>{{ $recebimento->tipo_pagamento_nome }}</td>
                                    <td>{{ $recebimento->received_at ? \Carbon\Carbon::parse($recebimento->received_at)->format('d/m/Y H:i') : '--' }}</td>
                                    <td class="text-end text-success"><strong>R$ {{ __moeda($recebimento->valor) }}</strong></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center py-4">Nenhum recebimento de conta neste caixa.</td></tr>
                            @endforelse
                        </tbody>
                        <tfoot><tr class="table-light"><th colspan="4">TOTAL DE RECEBIMENTOS</th><th class="text-end">R$ {{ __moeda($totalRecebimentos) }}</th></tr></tfoot>
                    </table>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-lg-6 mb-3"><div class="card bg-light-info border shadow-sm h-100"><div class="card-body"><h4 class="text-info mb-3"><i class="bx bx-plus-circle"></i> Suprimentos</h4>@forelse($suprimentos as $s)<div class="border-bottom pb-2 mb-2"><h5 class="mb-0">R$ {{ __moeda($s->valor) }}</h5></div>@empty<h5>R$ 0,00</h5>@endforelse</div></div></div>
                <div class="col-lg-6 mb-3"><div class="card bg-light-danger border shadow-sm h-100"><div class="card-body"><h4 class="text-danger mb-3"><i class="bx bx-minus-circle"></i> Sangrias</h4>@forelse($sangrias as $s)<div class="border-bottom pb-2 mb-2"><h5 class="mb-1">R$ {{ __moeda($s->valor) }}</h5><small class="text-muted">{{ $s->observacao ?? 'Sem observação' }}</small></div>@empty<h5>R$ 0,00</h5>@endforelse</div></div></div>
            </div>

            <div class="card mt-4 border shadow-sm"><div class="card-body"><div class="row g-3">
                <div class="col-md-3"><div class="p-3 bg-light rounded"><h6>Soma das vendas</h6><h4>R$ {{ __moeda($somaVendas) }}</h4></div></div>
                <div class="col-md-3"><div class="p-3 bg-light rounded"><h6>Recebimentos</h6><h4>R$ {{ __moeda($totalRecebimentos) }}</h4></div></div>
                <div class="col-md-3"><div class="p-3 bg-light rounded"><h6 class="text-success">Suprimentos</h6><h4>R$ {{ __moeda($totalSuprimentos) }}</h4></div></div>
                <div class="col-md-3"><div class="p-3 bg-light rounded"><h6 class="text-danger">Sangrias</h6><h4>R$ {{ __moeda($totalSangrias) }}</h4></div></div>
                <div class="col-md-6"><div class="p-3 bg-light rounded"><h6 class="text-info">Resultado financeiro</h6><h3>R$ {{ __moeda($resultadoFinanceiro) }}</h3></div></div>
                <div class="col-md-6"><div class="p-3 bg-light rounded"><h6 class="text-warning">Dinheiro na gaveta</h6><h3>R$ {{ __moeda($dinheiroNaGaveta) }}</h3></div></div>
            </div></div></div>

            <div class="alert alert-secondary mt-3 mb-0"><strong>Regra:</strong> recebimentos por PIX/cartão entram no resultado financeiro, mas somente recebimentos em dinheiro entram na gaveta.</div>
            <div class="mt-4 d-flex gap-2 flex-wrap">
                <a target="_blank" class="btn btn-primary" href="{{ route('caixa.imprimir', $abertura->id) }}"><i class="bx bx-printer"></i> Imprimir A4</a>
                <a target="_blank" class="btn btn-info" href="{{ route('caixa.imprimir80', $abertura->id) }}"><i class="bx bx-receipt"></i> Imprimir 80mm</a>
            </div>
        </div>
    </div>
</div>
@endsection
