@extends('default.layout', ['title' => 'Ver Cotação'])
@section('content')

<div class="page-content">
    <div class="card border-top border-0 border-4 border-primary">
        <div class="card-body">
            <h4>Fornecedor: <strong>{{$cotacao->fornecedor->razao_social}}</strong></h4>
            <h4>Data de registro: <strong>{{ \Carbon\Carbon::parse($cotacao->data_registro)->format('d/m/Y H:i')}}</strong></h4>
            <h5>Referencia: {{$cotacao->referencia}}</h5>
            <h5>Observação: {{$cotacao->observacao}}</h5>
            <h5>Link: <strong class="danger-text"> <a href="{{env('PATH_URL')}}/response/{{$cotacao->link}}" target="_blank">{{env('PATH_URL')}}/response/{{$cotacao->link}}</a></strong></h5>
            <h5>Ativa:
                @if($cotacao->ativa)
                <span class="btn btn-success">Sim</span>
                @else
                <span class="btn btn-danger">Não</span>
                @endif
            </h5>
            <h5>Respondida:
                @if($cotacao->resposta)
                <span class="btn btn-success">Sim</span>
                @else
                <span class="btn btn-danger">Não</span>
                @endif
            </h5>
            <hr>
            <h4>Itens da Cotação</h4>
            <div class="card card-custom gutter-b example example-compact">
                <div class="table-responsive">
                    <table class="table mb-0 table-striped">
                        <thead class="datatable-head">
                            <tr class="datatable-row" style="left: 0px;">
                                <th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 80px;">#</span></th>
                                <th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 150px;">Produto</span></th>
                                <th data-field="Country" class="datatable-cell datatable-cell-sort"><span style="width: 100px;">Quantidade</span></th>
                                <th data-field="ShipDate" class="datatable-cell datatable-cell-sort"><span style="width: 80px;">Valor</span></th>
                                <th data-field="CompanyName" class="datatable-cell datatable-cell-sort"><span style="width: 120px;">Ações</span></th>
                            </tr>
                        </thead>
                        <tbody class="datatable-body">
                            @foreach($cotacao->itens as $i)
                            <tr class="datatable-row" style="left: 0px;">
                                <td class="datatable-cell"><span class="codigo" style="width: 80px;">{{$i->id}}</span></td>
                                <td class="datatable-cell"><span class="codigo" style="width: 150px;">{{$i->produto->nome}}</span></td>
                                <td class="datatable-cell"><span class="codigo" style="width: 100px;">{{$i->quantidade}}</span></td>
                                <td class="datatable-cell"><span class="codigo" style="width: 120px;">{{number_format($i->valor, 2, ',', '.')}}</span></td>
                                <th class="datatable-cell">
                                    <span style="width: 120px;">
                                        <a href="/cotacao/destroyItem/{{$i->id}}" class="btn btn-danger btn-sm">
                                            <span class="bx bx-trash">
                                            </span>
                                        </a>
                                    </span>
                                </th>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card card-custom gutter-b example example-compact">
                <div class="card-header">
                    <div class="card-body">
                        <h3 class="card-title">Total: <strong class="red-text">R$
                                {{number_format($cotacao->valor, 2, ',', '.')}}</strong></h3>
                        <div class="kt-widget__info">
                            <h5 class="kt-widget__label">Total de itens: <strong>{{count($cotacao->itens)}}</strong></h5>
                            <h5 class="kt-widget__label">Forma de pagamento: <strong>{{$cotacao->forma_pagamento}}</strong></h5>
                            <h5 class="kt-widget__label">Responsável: <strong>{{$cotacao->responsavel}}</strong></h5>
                            <a target="_blank" class="navi-text" href="/cotacao/clonar/{{$cotacao->id}}">
                                <span class="btn btn-warning">Clonar</span>
                            </a>
                            @if(!$cotacao->escolhida())
                            <a onclick="if (! confirm('Deseja marcar como escolhida esta cotação?')) { return false; }" href="/cotacao/escolher/{{$cotacao->id}}" class="btn green">
                                <span class="btn btn-success">Marcar como Escolhida</span>
                            </a>
                            @else
                            @if($cotacao->escolhida()->id == $cotacao->id)
                            <h5 class="text-danger mt-3">Essa cotação já foi escolhida!</h5>
                            @else
                            <br>
                            <h5><a href="/cotacao/view/{{$cotacao->escolhida()->id}}">
                                    <span class="btn btn-danger">
                                        Essa refernência já foi definida para cotação {{$cotacao->escolhida()->id}}
                                    </span>
                                </a></h5>
                            @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="row">



</div>


@endsection
