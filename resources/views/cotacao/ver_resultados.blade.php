@extends('default.layout', ['title' => 'Resultado cotação'])
@section('content')

<div class="page-content">
    <div class="card border-top border-0 border-4 border-primary">
        <div class="card-body">
            <h4>Cotação Referência: <strong>{{$cotacoes[0]->referencia}}</strong></h4>
            <hr>
            <div class="">
                <div class="">
                    <h3 class="">
                        Fornecedores:
                    </h3>
                    <div class="">
                        @foreach($cotacoes as $c)
                        <h5 class="">{{$c->fornecedor->razao_social}}</h5>
                        @endforeach
                    </div>
                </div>
            </div>
            <hr>
            <h4>Itens da Cotação</h4>
            <div class="col-sm-12 col-lg-12 col-md-12 col-xl-12">
                <div class="card card-custom gutter-b example example-compact">
                    <div class="card-header">
                        <div id="kt_datatable" class="table-responsive">
                            <table class="table mb-0 table-striped">
                                <thead class="datatable-head">
                                    <tr class="datatable-row" style="left: 0px;">
                                        <th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 150px;">Item</span></th>
                                        <th data-field="Country" class="datatable-cell datatable-cell-sort"><span style="width: 80px;">Valor Unit.</span></th>
                                        <th data-field="ShipDate" class="datatable-cell datatable-cell-sort"><span style="width: 80px;">Quantidade</span></th>
                                        <th data-field="CompanyName" class="datatable-cell datatable-cell-sort"><span style="width: 80px;">Valor Total</span></th>
                                        <th data-field="CompanyName" class="datatable-cell datatable-cell-sort"><span style="width: 120px;">Fornecedor</span></th>
                                    </tr>
                                </thead>
                                <tbody class="datatable-body">
                                    @foreach($itens as $i)
                                    <tr class="datatable-row" style="left: 0px;">
                                        <td class="datatable-cell"><span class="codigo" style="width: 150px;">{{$i['item']}}</span></td>
                                        <td class="datatable-cell"><span class="codigo" style="width: 80px;">{{__moeda($i['valor_unitario'])}}</span></td>
                                        <td class="datatable-cell"><span class="codigo" style="width: 80px;">{{__moeda($i['quantidade'])}}</span></td>
                                        <td class="datatable-cell"><span class="codigo" style="width: 80px;">{{__moeda($i['valor_total'])}}</span></td>
                                        <td class="datatable-cell"><span class="codigo" style="width: 120px;">{{$i['fornecedor']}}</span></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12 col-lg-6 col-md-6 col-xl-6">
                    <div class="card card-custom gutter-b example example-compact">
                        <div class="card-header">
                            <div class="card-body">
                                <h3 class="card-title">Melhor Resultado</h3>
                                <div class="kt-widget__info">
                                    <h5 class="kt-widget__label">Fornecedor: <strong>{{$melhorResultado->fornecedor->razao_social}}</strong></h5>
                                    <h5 class="kt-widget__label">Valor: <strong>{{number_format($melhorResultado->valor, 2, ',', '.')}}</strong></h5>
                                    <a target="_blank" class="navi-text" href="/cotacao/view/{{$melhorResultado->id}}">
                                        <span class="btn btn-success px-5">Ir Para Cotação</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12 col-lg-6 col-md-6 col-xl-6">
                    <div class="card card-custom gutter-b example example-compact">
                        <div class="card-header">
                            <div class="card-body">
                                <h3 class="text-center">Imprimir Melhor Resultado Por Fornecedor</h3>
                                <div class="kt-widget__info">
                                    @foreach($fornecedores as $f)
                                    <form method="get" action="/cotacao/imprimirMelhorResultado">
                                        <input type="hidden" name="fornecedor" value="{{$f['fornecedor']}}">
                                        <input type="hidden" name="referencia" value="{{$cotacoes[0]->referencia}}">
                                        <button type="submit" style="width: 100%" href="" class="btn">
                                            <span class="btn btn-info">{{$f['fornecedor']}} - Itens Ganhos: {{$f['qtd']}}</span>
                                        </button>
                                    </form>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
