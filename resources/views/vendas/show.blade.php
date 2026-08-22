@extends('default.layout', ['title' => 'Vendas'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">

            <div class="col">
                <h5>Número da Venda:</h5>
                @if($item->pedido_nuvemshop_id)
                <h4>PEDIDO NUVEMSHOP <a class="btn btn-link" href="{{ route('nuvemshop-pedidos.show', [$item->pedidoNuvemShop->pedido_id]) }}">ver pedido</a></h4>
                @endif
                <div class="row mt-3">

                    <div class="col-6">
                        <h6>Cliente: <strong>{{ $item->cliente->razao_social }} </strong></h6>
                        <h6>Cnpj/Cpf: <strong>{{ $item->cliente->cpf_cnpj }} </strong></h6>
                        <h6>Data: <strong>{{ $item->data_registro }} </strong></h6>
                        <h6>Valor Total: <strong>{{ __moeda($item->valor_total) }}</strong></h6>
                        <h6>Cidade: <strong>{{ $item->cliente->cidade->nome }}</strong></h6>
                    </div>

                    <div class="col-6">
                        <h6>Estado:</h6>
                        <h6>Chave NFe:</h6>
                        <h6>Data Entrega: <strong>{{ $item->data_entrega }}</strong></h6>
                        {{-- <a type="btn" class="btn btn-danger" href="">Alterar estado fiscal da venda</a> --}}
                    </div>
                </div>
            </div>
            <hr>
            <div class="row">
                <h5>Itens da Venda:</h5>
                <div class="row m-1">
                    <table class="table-responsive">
                        <tr>
                            <thead>
                                <th>ID</th>
                                <th>Produto</th>
                                <th>Quantidade</th>
                                <th>Valor</th>
                                <th>SubTotal</th>
                            </thead>
                            <tbody>
                                @foreach ($item->itens as $p)
                                <tr>
                                    <td>{{ $p->id }}</td>
                                    <td>{{ $p->produto->nome }}</td>
                                    <td>{{ __moeda($p->quantidade) }}</td>
                                    <td>{{ __moeda($p->valor) }}</td>
                                    <td>{{ __moeda($p->quantidade * $p->valor) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </tr>
                    </table>
                </div>
            </div>

            <hr>
            <div class="row">
                <h5>Soma dos Itens: <strong>{{ __moeda($item->valor_total) }}</strong></h5>
                <h6>Desconto: <strong>{{ __moeda($item->desconto) }}</strong></h6>
                <h6>Acréscimo: <strong>{{ __moeda($item->acrescimo) }}</strong></h6>
                <h6>TOTAL: <strong>{{ __moeda($item->valor_total) }}</strong></h6>
            </div>
            <div>
                <br>
                <p>Forma de Pagamento: <strong>{{ $item->forma_pagamento }}</strong></p>
                <p>Tipo de Pagamento: <strong>{{ $item->tipo_pagamento }}</p>
            </div>
            <hr>
            <div class="table col-8">
                <h6>Fatura</h6>
                <table class="table-responsive col-4">
                    <thead>
                        <tr>
                            <th>Vencimento</th>
                            <th>Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(sizeof($item->duplicatas)>0)
                        @foreach ($item->duplicatas as $duplicatas )
                        <tr>
                            <td>{{ (isset($duplicatas) ? $item->data_registro : $duplicatas->data_vencimento) }}</td>
                            <td>{{__moeda($duplicatas->valor_integral)}}</td>
                        </tr>

                        @endforeach
                        @else
                        <tr>
                            <td>{{ __data_pt($item->created_at, 0) }}</td>
                            <td>{{ __moeda($item->valor_total) }}</td>
                        </tr>
                        @endif

                    </tbody>
                </table>
            </div>
            <div class="">
                <a type="btn" class="btn btn-warning" href="{{ route('vendas.edit', $item->id) }}">Editar</a>
                <a type="btn" class="btn btn-info" href="{{ route('vendas.print', $item->id) }}">Imprimir</a>
            </div>
            <hr>

            @if(sizeof($item->duplicatas) > 0)
            {!! Form::open()
            ->get()
            ->route('vendas.carne')
            !!}
            <div class="row">
                <input type="hidden" value="{{$item->id}}" name="id">
                <div class="col-md-2">
                    {!! Form::text('juros', 'Juros')->attrs(['class' => 'moeda']) !!}
                </div>
                <div class="col-md-2">
                    {!! Form::text('multa', 'Multa')->attrs(['class' => 'moeda']) !!}
                </div>
                <div class="col-md-2">
                    <br>
                    <button type="submit" class="btn btn-primary"><i class="bx bx-plus"></i>
                        Gerar Carnê</button>
                </div>
            </div>
            {!! Form::close() !!}
            @endif
        </div>
    </div>
</div>
@endsection
