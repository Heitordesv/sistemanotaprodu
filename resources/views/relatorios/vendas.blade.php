@extends('default.layout', ['title' => 'Relatórios'])
@section('content')
<div class="page-content">
    <div class="card m-2">
        <div class="ms-auto">
            <a href="{{route('relatorios.index')}}" type="button" class="btn btn-light btn-sm">
                <i class="bx bx-arrow-back"></i> Voltar
            </a>
        </div>
        <h5 style="color: cornflowerblue" class="text-center">Relatorio de Vendas</h5>
        <table class="table mb-0 table-striped m-1"
        style="border-bottom: 1px solid rgb(206, 206, 206); margin-bottom:10px;  width: 99%;">
        <thead>
            <tr>
                <th width="10%" class="text-left">Data</th>
                <th width="10%" class="text-left">Id</th>
                <th width="15%" class="text-left">Vendedor</th>
                <th width="15%" class="text-left">Cliente</th>
                <th width="10%" class="text-left">Forma de pagamento</th>
                <th width="10%" class="text-left">Despesas operacionais</th>
                <th width="10%" class="text-left">Desconto</th>
                <th width="10%" class="text-left">Valor venda liq.</th>
                <th width="10%" class="text-left">Valor total</th>
            </tr>
        </thead>

        @php
            $somaPedido = 0;
            $somaPedidoLiquido = 0;
            $somaPdv = 0;
            $somaDesconto = 0;
        @endphp

        <tbody>
            @foreach ($data as $key => $item)
                <tr class="@if ($key % 2 == 0) pure-table-odd @endif">
                    <td>{{ __data_pt($item->data_registro, 0) }}</td>
                    <td>{{ $item->id }}</td>
                    <td>{{ $item->vendedor() }}</td>
                    <td>{{ $item->cliente ? $item->cliente->razao_social : 'Consumidor final' }}</td>
                    @if (isset($item->cpf))
                        <td>{{ $item->getTipoPagamento2() }}</td>
                    @else
                        <td>{{ $item->getTipoPagamento() }}</td>
                    @endif
                    @if ($item->tbl == 'pdv')
                        <td>R$ 0,00</td>
                        <td>R$ {{ __moeda($item->desconto) }}</td>
                        <td>R$ {{ __moeda($item->valor_total) }}</td>
                        <td>R$ {{ __moeda($item->valor_total) }}</td>
                    @else
                        <td>R$ {{ $item->valorDespesaOperacionais() }}</td>
                        <td>R$ {{ __moeda($item->desconto) }}</td>
                        <td>R$ {{ __moeda($item->valorLiquido()) }}</td>
                        <td>R$ {{ __moeda($item->valor_total) }}</td>
                    @endif
                </tr>

                @php
                    if (!isset($item->cpf)) {
                        $somaPedido += $item->valor_total;
                        $somaPedidoLiquido += $item->valorLiquido();
                    } else {
                        $somaPdv += $item->valor_total;
                    }
                    $somaDesconto += $item->desconto;
                @endphp
            @endforeach
        </tbody>
    </table>

    <table style="width: 100%;" class="m-3">
        <tbody>
            <tr>
                <th>Soma Pedido: <strong>{{ __moeda($somaPedido) }}</strong></th>
                <th>Soma Pedido Liquido: <strong>{{ __moeda($somaPedidoLiquido) }}</strong></th>
            </tr>
            <tr>
                <th>Soma PDV: <strong>{{ __moeda($somaPdv) }}</strong></th>
                <th>Total: <strong>{{ __moeda($somaPedidoLiquido + $somaPdv) }}</strong></th>
            </tr>
            <tr>
                <th>Soma Desconto: <strong>{{ __moeda($somaDesconto) }}</strong></th>
                <th></th>
            </tr>
        </tbody>
    </table>
    </div>
</div>
@endsection

