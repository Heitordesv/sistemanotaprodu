@extends('default.layout', ['title' => 'Pedidos'])

@section('content')

<div class="page-content">
    <div class="card">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto"></div>
            </div>

            <hr>
            <h6 class="mb-0 text-uppercase">Pesquisar vendas</h6>

        <!--    <form method="GET" action="{{ route('telasPedido.grafico') }}" class="mb-3">
                <div class="row mt-3">
                    <div class="col-md-3">
                        <input type="text" name="pesquisa" class="form-control" placeholder="Pesquisar por nome, telefone ou código do pedido" value="{{ request('pesquisa') }}">
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-control">
                            <option value="">Selecione o status</option>
                            <option value="Aberto" {{ request('status') == 'Aberto' ? 'selected' : '' }}>Aberto</option>
                            <option value="Finalizado" {{ request('status') == 'Finalizado' ? 'selected' : '' }}>Finalizado</option>
                            <option value="Cancelado" {{ request('status') == 'Cancelado' ? 'selected' : '' }}>Cancelado</option>
                            <option value="Saiu para Entrega" {{ request('status') == 'Saiu para Entrega' ? 'selected' : '' }}>Saiu para Entrega</option>
                            <option value="Disponível para Retirada" {{ request('status') == 'Disponível para Retirada' ? 'selected' : '' }}>Disponível para Retirada</option>
                            <option value="Em Andamento" {{ request('status') == 'Em Andamento' ? 'selected' : '' }}>Em Andamento</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary"><i class="bx bx-search"></i> Pesquisar</button>
                        <a id="clear-filter" class="btn btn-danger" href="{{ route('telasPedido.grafico') }}"><i class="bx bx-eraser"></i> Limpar</a>
                    </div>
                </div>
            </form>-->

            <hr>

            {{-- TABELA DE PEDIDOS --}}
            <div class="row mt-5">
                <h5 class="mb-3">📋 Lista de Pedidos:</h5>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Código do Pedido</th>
                                <th>Nome</th>
                                <th>Telefone</th>
                                <th>Status</th>
                                <th>Forma de Pagamento</th>
                                <th>Total (R$)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pedidos as $pedido)
                                <tr>
                                    <td>{{ $pedido->id }}</td>
                                    <td>{{ $pedido->codigo_pedido }}</td>
                                    <td>{{ $pedido->nome }}</td>
                                    <td>{{ $pedido->telefone }}</td>
                                    <td>{{ $pedido->status }}</td>
                                    <td>{{ $pedido->forma_pagamento }}</td>
                                    <td>R$ {{ number_format($pedido->total, 2, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Soma total de todos os pedidos --}}
                <div class="mt-3">
                    <h5 class="text-right"><strong>Total Geral: R$ {{ number_format($somaTotalPedidos, 2, ',', '.') }}</strong></h5>
                </div>
            </div>

            {{-- TABELA DE RESUMO POR FORMA DE PAGAMENTO --}}
            @php
                $somaPorPagamento = [];
                $totalPorPagamento = 0;

                // Calcula a soma por forma de pagamento
                foreach ($pedidos as $pedido) {
                    $forma = $pedido->forma_pagamento ?? 'Não Informada';

                    if (!isset($somaPorPagamento[$forma])) {
                        $somaPorPagamento[$forma] = 0;
                    }

                    $somaPorPagamento[$forma] += $pedido->total;
                    $totalPorPagamento += $pedido->total;
                }
            @endphp

            <div class="row mt-5">
                <h5 class="mb-3">💳 Resumo por Forma de Pagamento:</h5>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-light">
                            <tr>
                                <th>Forma de Pagamento</th>
                                <th>Total (R$)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($somaPorPagamento as $forma => $valor)
                                <tr>
                                    <td>{{ $forma }}</td>
                                    <td>R$ {{ number_format($valor, 2, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="table-success">
                                <th>Total Geral</th>
                                <th>R$ {{ number_format($totalPorPagamento, 2, ',', '.') }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

@endsection
