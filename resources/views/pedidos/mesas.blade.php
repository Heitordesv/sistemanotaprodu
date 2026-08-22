@extends('default.layout',['title' => 'Mesas'])
@section('content')
<div class="page-content">
    <div class="card border-top border-0 border-3 border-success">
        <div class="card-body">
            <div class="row">
                @if(count($pedidos) > 0)
                <div class="col-sm-12 col-lg-12 col-md-12 col-xl-12">
                    <div class="row">
                        @foreach($pedidos as $p)
                        <div class="col-sm-4 col-lg-4 col-md-6">
                            <div class="card border-top border-0 border-3 border-success @if($p->status) green lighten-4 @endif">
                                <div class="card-body">
                                    <img src="/imgs/mesa.jpg" class="img-mesa">
                                    <hr>
                                    <h4 class="center-align">{{$p->mesa->nome}}</h4>
                                    <h6>Total: <strong>R$ {{$p->mesa->somaItens()}}</strong></h6>
                                    <h6>Horário Abertura: <strong>{{ \Carbon\Carbon::parse($p->data_registro)->format('H:i')}}</strong></h6>
                                    <h6>Total de Comandas: <strong class="red-text">{{$p->mesa->comandas()}}</strong></h6>
                                </div>
                                <a href="/pedidos/verMesa/{{$p->mesa->id}}" style="width: 100%;" class="btn btn-success">Visualizar</a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @else
                <h4 class="text-danger">Nenhuma mesa aberta!</h4>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection
