@extends('default.layout', ['title' => 'Fluxo de Caixa'])

@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
            </div>
            <div class="col">
                <h6 class="mb-0 text-uppercase">MOVIMENTAÇÃO DE CAIXA</h6>
                {!! Form::open()->fill(request()->all())->get() !!}
                <div class="row">
                    <div class="col-md-3">
                        {!! Form::date('start_date', 'Data inicial') !!}
                    </div>
                    <div class="col-md-3">
                        {!! Form::date('end_date', 'Data final') !!}
                    </div>
                    <div class="col-md-5 text-left ">
                        <br>
                        <button class="btn btn-primary" type="submit">
                            <i class="bx bx-search"></i>Pesquisar
                        </button>
                        <a id="clear-filter" class="btn btn-danger" href="{{ route('fluxoCaixa.index') }}">
                            <i class="bx bx-eraser"></i> Limpar
                        </a>
                    </div>
                </div>
                {!! Form::close() !!}
                <hr />
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table mb-0 table-striped">
                                <thead>
                                    <tr>
                                        <th>Data</th>
                                        <th>Vendas</th>
                                        <th>Frente de caixa</th>
                                        <th>Soma de vendas</th>
                                        <th>Contas recebidas</th>
                                        <th>Ordem de serviço</th>
                                        <th>Ordem de Entrada</th>
                                        <th>Contas pagas</th>
                                        <th>Resultado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $totalVendas = 0;
                                        $totalCaixa = 0;
                                        $totalRecebido = 0;
                                        $totalOS = 0;
                                        $totalEntrada = 0;
                                        $totalPago = 0;
                                        $totalResultado = 0;
                                    @endphp

                                    @forelse ($fluxo as $item)
                                        @php
                                            $somaVendas = $item['venda'] + $item['venda_caixa'];
                                            $resultado = $somaVendas + $item['conta_receber'] + $item['os'] - $item['conta_pagar'];

                                            $totalVendas += $item['venda'];
                                            $totalCaixa += $item['venda_caixa'];
                                            $totalRecebido += $item['conta_receber'];
                                            $totalOS += $item['os'];
                                            $totalEntrada += $item['osentrada'];
                                            $totalPago += $item['conta_pagar'];
                                            $totalResultado += $resultado;
                                        @endphp
                                        <tr>
                                            <td>{{ $item['data'] }}</td>
                                            <td>{{ __moeda($item['venda']) }}</td>
                                            <td>{{ __moeda($item['venda_caixa']) }}</td>
                                            <td>{{ __moeda($somaVendas) }}</td>
                                            <td>{{ __moeda($item['conta_receber']) }}</td>
                                            <td>{{ __moeda($item['os']) }}</td>
                                            <td>{{ __moeda($item['osentrada']) }}</td>
                                            <td>{{ __moeda($item['conta_pagar']) }}</td>
                                            <td>{{ __moeda($resultado) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center">Nada encontrado</td>
                                        </tr>
                                    @endforelse

                                    {{-- Linha de total --}}
                                    @if(count($fluxo) > 0)
                                        <tr class="fw-bold bg-light">
                                            <td>Total</td>
                                            <td>{{ __moeda($totalVendas) }}</td>
                                            <td>{{ __moeda($totalCaixa) }}</td>
                                            <td>{{ __moeda($totalVendas + $totalCaixa) }}</td>
                                            <td>{{ __moeda($totalRecebido) }}</td>
                                            <td>{{ __moeda($totalOS) }}</td>
                                            <td>{{ __moeda($totalEntrada) }}</td>
                                            <td>{{ __moeda($totalPago) }}</td>
                                            <td>{{ __moeda($totalResultado) }}</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
