@extends('default.layout',['title' => 'Caixa'])
@section('content')
<div class="page-content">
    <div class="card">
        {!! Form::open()
        ->post()
        ->route('frenteCaixa.fecharPost')
        !!}

        <div class="card-body p-4">
            @php
                $estado = $abertura != null;
                $somaDinheiro = 0;
                $somaSuprimento = 0;
                $somaSangria = 0;
                $soma = 0;
            @endphp

            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                <div>
                    <h4 class="mb-1">
                        <i class="bx bx-calculator"></i>
                        @if($estado)
                            Caixa #{{ $abertura->id }}
                        @else
                            Caixa
                        @endif
                    </h4>
                    @if($estado)
                        <div class="text-muted">
                            Operador: <strong>{{ $usuario->nome }}</strong>
                            &nbsp;•&nbsp;
                            Aberto em: <strong>{{ $abertura->created_at }}</strong>
                        </div>
                    @else
                        <div class="text-muted">
                            Operador: <strong>{{ $usuario->nome }}</strong>
                        </div>
                    @endif
                </div>

                <div class="d-flex gap-2 flex-wrap">
                    @if($estado)
                        <span class="badge bg-success fs-6">CAIXA ABERTO</span>
                    @else
                        <span class="badge bg-secondary fs-6">CAIXA FECHADO</span>
                    @endif

                    @if($estado && empresaComFilial() && $abertura->filial)
                        <span class="badge bg-info fs-6">
                            {{ $abertura->filial->descricao }}
                        </span>
                    @endif
                </div>
            </div>

            @if($estado)
                <div class="alert alert-info py-2">
                    <i class="bx bx-user me-1"></i>
                    Esta sessão pertence somente ao <strong>Caixa #{{ $abertura->id }}</strong>
                    do operador <strong>{{ $usuario->nome }}</strong>.
                    As movimentações de outros operadores não entram neste caixa.
                </div>
            @endif

            <div class="row mt-3">
                <div class="col-12 d-flex flex-wrap gap-2">
                    @if(!$estado)
                        <a class="btn btn-primary px-4" data-bs-toggle="modal" data-bs-target="#modal-abrir_caixa">
                            <i class="bx bx-money"></i> Abrir meu caixa
                        </a>
                    @endif

                    @if($estado)
                        <a class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-suprimento_caixa">
                            <i class="bx bx-money"></i> Suprimento do Caixa #{{ $abertura->id }}
                        </a>
                        <a class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#modal-sangria_caixa">
                            <i class="bx bx-downvote"></i> Sangria do Caixa #{{ $abertura->id }}
                        </a>
                    @endif

                    <a class="btn btn-info px-4" href="{{ route('caixa.list') }}">
                        <i class="bx bx-list-ol"></i> Lista de caixas
                    </a>
                </div>
            </div>

            @if($estado)
                <div class="row g-3 mt-2">
                    <div class="col-md-3">
                        <div class="card border shadow-sm h-100">
                            <div class="card-body">
                                <small class="text-muted">Caixa</small>
                                <h4 class="mb-0">#{{ $abertura->id }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border shadow-sm h-100">
                            <div class="card-body">
                                <small class="text-muted">Operador</small>
                                <h5 class="mb-0">{{ $usuario->nome }}</h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border shadow-sm h-100">
                            <div class="card-body">
                                <small class="text-muted">Valor de abertura</small>
                                <h5 class="mb-0 text-primary">R$ {{ __moeda($abertura->valor) }}</h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border shadow-sm h-100">
                            <div class="card-body">
                                <small class="text-muted">Vendas deste caixa</small>
                                <h4 class="mb-0">{{ sizeof($caixa['vendas'] ?? []) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if(sizeof($caixa) > 0)
                <div class="row mt-4">
                    <h5>Total por tipo de pagamento</h5>
                    @foreach($caixa['somaTiposPagamento'] as $key => $tp)
                        @if($tp > 0)
                            <div class="col-sm-4 col-lg-3 col-md-6 mb-3">
                                <div class="card card-custom gutter-b h-100">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">
                                            {{ App\Models\VendaCaixa::getTipoPagamento($key) }}
                                        </h6>
                                    </div>
                                    @php
                                        if($key == '01') $somaDinheiro = $tp;
                                    @endphp
                                    <div class="card-body">
                                        <h4 class="text-success">R$ {{ __moeda($tp) }}</h4>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif

            <div class="card mt-3">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table mb-0 table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>Cliente</th>
                                    <th>Data</th>
                                    <th>Tipo de pagamento</th>
                                    <th>Estado</th>
                                    <th>NFC-e / NF-e</th>
                                    <th>Tipo</th>
                                    <th>Valor</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(sizeof($caixa) > 0)
                                    @forelse ($caixa['vendas'] as $item)
                                        <tr>
                                            <td>{{ $item->cliente->razao_social ?? 'Consumidor' }}</td>
                                            <td>{{ $item->created_at }}</td>
                                            <td>
                                                @if($item->tipo_pagamento == '99')
                                                    Outros
                                                @else
                                                    {{ $item->getTipoPagamento($item->tipo_pagamento) }}
                                                @endif
                                            </td>
                                            <td>{{ $item->estado }} {{ $item->estado_emissao }}</td>
                                            <td>{{ $item->NFcNumero }} {{ $item->numero_nfe }}</td>
                                            <td>{{ $item->tipo }}</td>
                                            <td>R$ {{ __moeda($item->valor_total) }}</td>
                                        </tr>

                                        @if($item->estado != 'CANCELADO')
                                            @php
                                                if(!isset($item->cpf)) {
                                                    $soma += $item->valor_total - $item->desconto + $item->acrescimo;
                                                } elseif(!$item->rascunho && !$item->consignado) {
                                                    $soma += $item->valor_total;
                                                }
                                            @endphp
                                        @endif
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center">Nenhuma venda neste caixa.</td>
                                        </tr>
                                    @endforelse
                                @else
                                    <tr>
                                        <td colspan="7" class="text-center">Abra o seu caixa para iniciar as movimentações.</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                    @if(sizeof($caixa) > 0)
                        <div class="row mt-3">
                            <div class="col-12 col-xl-6">
                                <div class="card card-custom gutter-b bg-light-info">
                                    <div class="card-body">
                                        <h4 class="card-title">Suprimentos do Caixa #{{ $abertura->id }}</h4>
                                        @forelse($caixa['suprimentos'] as $s)
                                            <h5>R$ {{ number_format($s->valor, 2, ',', '.') }}</h5>
                                            @php $somaSuprimento += $s->valor; @endphp
                                        @empty
                                            <h5>R$ 0,00</h5>
                                        @endforelse
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-xl-6">
                                <div class="card card-custom gutter-b bg-light-danger">
                                    <div class="card-body">
                                        <h4 class="card-title">Sangrias do Caixa #{{ $abertura->id }}</h4>
                                        @forelse($caixa['sangrias'] as $s)
                                            <h5>R$ {{ number_format($s->valor, 2, ',', '.') }}</h5>
                                            @php $somaSangria += $s->valor; @endphp
                                        @empty
                                            <h5>R$ 0,00</h5>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            @if($estado)
                <div class="mt-3">
                    <h5 class="text-warning">
                        Soma de vendas: <strong>R$ {{ __moeda($soma) }}</strong>
                    </h5>
                    <h5 class="text-primary">
                        Recebimentos de contas:
                        <strong>R$ {{ __moeda($caixa['totalRecebimentos'] ?? 0) }}</strong>
                    </h5>
                    <h5 class="text-info">
                        Dinheiro na gaveta:
                        <strong>R$ {{ __moeda($caixa['dinheiroNaGaveta'] ?? 0) }}</strong>
                    </h5>
                    <h5 class="text-success">
                        Resultado financeiro:
                        <strong>R$ {{ __moeda($caixa['resultadoFinanceiro'] ?? 0) }}</strong>
                    </h5>
                </div>
            @endif

            <div class="row mt-3">
                <div class="col-12">
                    <input
                        type="hidden"
                        name="valor_dinheiro_caixa"
                        id="valor_dinheiro_caixa"
                        value="{{ $caixa['dinheiroNaGaveta'] ?? 0 }}"
                    >
                    <input type="hidden" name="abertura_id" value="{{ $abertura != null ? $abertura->id : 0 }}">
                    <input type="hidden" name="redirect" value="/caixa">

                    @if($estado)
                        <button type="submit" class="btn btn-lg btn-danger">
                            <i class="bx bx-x"></i>
                            Fechar somente o Caixa #{{ $abertura->id }}
                        </button>
                    @endif
                </div>
            </div>
        </div>

        {!! Form::close() !!}
    </div>
</div>

@include('modals._abrir_caixa')
@include('modals.frontBox._suprimento_caixa', ['not_submit' => true])
@include('modals.frontBox._sangria_caixa', ['not_submit' => true])

@endsection
