@extends('default.layout', ['title' => 'Fechamento de Caixa'])
@section('content')
<div class="page-content">
    <div class="card">
        <div class="card-body p-4">
            @if ($abertura != null)
                {!! Form::open()
                ->post()
                ->route('frenteCaixa.fecharPost')
                !!}
                <input type="hidden" name="abertura_id" value="{{ $abertura->id }}">

                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <div>
                        <h4 class="mb-1">Fechamento do Caixa #{{ $abertura->id }}</h4>
                        <div class="text-muted">
                            Operador: <strong>{{ $abertura->usuario->nome ?? 'Não identificado' }}</strong>
                        </div>
                    </div>
                    <span class="badge bg-success fs-6">CAIXA ABERTO</span>
                </div>

                <div class="alert alert-info">
                    <i class="bx bx-info-circle me-1"></i>
                    Este fechamento afeta somente o <strong>Caixa #{{ $abertura->id }}</strong>
                    do operador <strong>{{ $abertura->usuario->nome ?? 'atual' }}</strong>.
                    Outros caixas da empresa permanecem abertos e independentes.
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="card border shadow-sm h-100">
                            <div class="card-body">
                                <small class="text-muted">Caixa</small>
                                <h5 class="mb-0">#{{ $abertura->id }}</h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border shadow-sm h-100">
                            <div class="card-body">
                                <small class="text-muted">Operador</small>
                                <h5 class="mb-0">{{ $abertura->usuario->nome ?? 'Não identificado' }}</h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border shadow-sm h-100">
                            <div class="card-body">
                                <small class="text-muted">Abertura</small>
                                <h5 class="mb-0">{{ $abertura->created_at }}</h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border shadow-sm h-100">
                            <div class="card-body">
                                <small class="text-muted">Valor de abertura</small>
                                <h5 class="mb-0 text-success">R$ {{ __moeda($abertura->valor) }}</h5>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <h5>Total de vendas deste caixa: {{ sizeof($vendas) }}</h5>
                    <h6 class="mt-3">Totais por tipo de pagamento</h6>
                    <div class="row mt-2 mb-3">
                        @foreach ($somaTiposPagamento as $key => $tp)
                            @if ($tp > 0)
                                <div class="col-sm-4 col-lg-3 col-md-6 mb-3">
                                    <div class="card card-custom gutter-b h-100">
                                        <div class="card-header">
                                            <h6 class="card-title mb-0">
                                                {{ App\Models\VendaCaixa::getTipoPagamento($key) }}
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <h4 class="text-success mb-0">R$ {{ __moeda($tp) }}</h4>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>

                <div class="table-responsive mt-3">
                    <table class="table mb-0 table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Data</th>
                                <th>Tipo de pagamento</th>
                                <th>Estado</th>
                                <th>NFC-e / NF-e</th>
                                <th>Tipo de venda</th>
                                <th>Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $soma = 0; @endphp
                            @forelse ($vendas as $item)
                                <tr>
                                    <td>{{ $item->cliente->razao_social ?? 'Consumidor Final' }}</td>
                                    <td>{{ __data_pt($item->created_at, 0) }}</td>
                                    <td>
                                        @if ($item->tipo_pagamento == '99')
                                            <a href="#!" onclick='swal("", "{{ $item->multiplo() }}", "info")' class="btn btn-info btn-sm">
                                                Ver
                                            </a>
                                        @else
                                            {{ $item->getTipoPagamento($item->tipo_pagamento) }}
                                        @endif
                                    </td>
                                    <td>{{ $item->estado_emissao }} {{ $item->estado }}</td>
                                    <td>{{ $item->NFcNumero }} {{ $item->numero_nfe }}</td>
                                    <td>{{ $item->tipo }}</td>
                                    <td>R$ {{ __moeda($item->valor_total) }}</td>
                                </tr>
                                @php
                                    if(!$item->consignado && !$item->rascunho) {
                                        $soma += $item->valor_total;
                                    }
                                @endphp
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">Nenhuma venda neste caixa.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <hr>

                    @if(sizeof($vendas) == 0)
                        <div class="alert alert-warning">
                            Não é possível fechar este caixa sem nenhuma venda.
                        </div>
                    @else
                        <div class="mt-3">
                            <h5>Soma total deste caixa: <strong>R$ {{ __moeda($soma) }}</strong></h5>
                        </div>
                    @endif

                    <div class="mt-3">
                        <button @if(sizeof($vendas) == 0) disabled @endif class="btn btn-warning" type="submit">
                            <i class="bx bx-lock-alt"></i>
                            Fechar somente o Caixa #{{ $abertura->id }}
                        </button>
                    </div>
                </div>
                {!! Form::close() !!}
            @else
                <div class="alert alert-warning mb-0">
                    Não existe caixa aberto para este usuário.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
