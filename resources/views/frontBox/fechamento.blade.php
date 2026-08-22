@extends('default.layout', ['title' => 'Caixa'])
@section('content')
<div class="page-content">
    <div class="card">
        <div class="card-body p-4">
            {!! Form::open()
            ->post()
            ->route('frenteCaixa.fecharPost')
            !!}
            <input type="hidden" name="abertura_id" value="{{$abertura->id}}"> 
            <div class="col-12">
                <h5>Total de Vendas: {{ sizeof($vendas) }}</h5>
                <br>
                @if ($abertura != null)
                <h6 class="mt-2" style="color: dodgerblue">Inicio do Caixa: {{ $abertura->created_at }}</h6>
                <br>
                <h6 class="mt-2">Totais por Tipo de Pagamento:</h6>
                <div class="row m-3">
                    @foreach ($somaTiposPagamento as $key => $tp)
                    @if ($tp > 0)
                    <div class="col-sm-4 col-lg-4 col-md-6">
                        <div class="card card-custom gutter-b">
                            <div class="card-header">
                                <h3 class="card-title">
                                    {{ App\Models\VendaCaixa::getTipoPagamento($key) }}
                                </h3>
                            </div>
                            <div class="card-body">
                                <h4 class="text-success">R$ {{ __moeda($tp) }}</h4>
                            </div>
                        </div>
                    </div>
                    @endif
                    @endforeach
                </div>
            </div>
            <div class="table-responsive mt-3">
                <table class="table mb-0 table-striped">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Data</th>
                            <th>Tipo de Pagamento</th>
                            <th>Estado</th>
                            <th>NFCe / NFe</th>
                            <th>Tipo de Venda</th>
                            <th>Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                        $soma = 0;
                        @endphp
                        @forelse ($vendas as $item)
                        <tr>
                            <td>{{ $item->cliente->razao_social ?? 'Consumidor Final' }}</td>
                            <td>{{ __data_pt($item->created_at, 0) }}</td>
                            <td>
                                @if ($item->tipo_pagamento == '99')
                                <a href="#!" onclick='swal("", "{{ $item->multiplo() }}", "info")' class="btn btn-info">
                                    Ver
                                </a>
                                @else
                                {{ $item->getTipoPagamento($item->tipo_pagamento) }}
                                @endif
                            </td>
                            <td>{{ $item->estado_emissao }} {{ $item->estado }}</td>
                            <td>{{ $item->NFcNumero }} {{ $item->numero_nfe }}</td>
                            <td>{{ $item->tipo }}</td>
                            <td>{{ __moeda($item->valor_total) }}</td>
                        </tr>
                        @php
                        if(!$item->consignado && !$item->rascunho)
                        $soma += $item->valor_total;
                        @endphp
                        @empty
                        @endforelse
                    </tbody>
                </table>
                <hr>
                @if(sizeof($vendas) == 0)
                <h2>Não é possível fechar Caixa sem nenhuma venda!</h2>
                @else
                <div class="mt-3">
                   <h5>Soma Total: <strong>R$ {{ __moeda($soma) }}</strong> </h5>
                </div>
                @endif
                <div class="mt-3">
                    <button @if(sizeof($vendas) == 0) disabled @endif class="btn btn-warning" type="submit">Fechar Caixa</button>
                </div>
            </div>
            {!! Form::close() !!}
            @else
            <div>
                <h5>Caixa Fechado !</h5>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
