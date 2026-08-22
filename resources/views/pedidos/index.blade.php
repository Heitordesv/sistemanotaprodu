@extends('default.layout',['title' => 'Pedidos'])
@section('content')
<div class="page-content">
    <div class="card">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                </div>
            </div>
            <div class="col">
                <div class="row">
                    @if(sizeof($mesasFechadas) > 0)
                    <div class="row">
                        <div class="">
                            <h5>Mesas com pedido de fechamento:</h5>
                            @foreach($mesasFechadas as $m)
                            <a href="{{route('pedidos.verMesa', $m->mesa->id) }}" target="_blank" class="btn btn-danger btn-sm">Ver {{$m->mesa->nome}}</a>
                            @endforeach
                        </div>
                    </div>
                    @endif
                    @if(sizeof($mesasParaAtivar) > 0)
                    <div class="row">
                        <div class="">
                            <h4>Mesas a serem ativadas:</h4>
                            @foreach($mesasParaAtivar as $m)
                            <a onclick='swal("Atenção!", "Deseja ativar esta mesa?", "warning").then((sim) => {if(sim){ location.href="/pedidos/ativarMesa/{{ $m->id }}" }else{return false} })' href="#!" class="btn btn-success btn-sm">Ativar {{$m->mesa->nome}}</a>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
                <hr>
                <div class="ms-auto mt-3">
                    <button data-bs-toggle="modal" data-bs-target="#modal-abrir_comanda" type="button" class="btn btn-success">
                        <i class="bx bx-purchase-tag-alt"></i> Abrir comanda
                    </button>
                    <button data-bs-toggle="modal" data-bs-target="#modal-clientePedido" type="button" class="btn btn-info">
                        <i class="bx bx-plus"></i> Novo cliente
                    </button>
                </div>
            </div>
            <hr>
            <div class="col-sm-12 col-lg-12 col-md-12 col-xl-12">
                @if(count($pedidos) > 0)
                <h5 class="text-success">Comandas em verde já finalizadas</h5>
                <div class="row mt-3">
                    @foreach($pedidos as $p)
                    <div class="col-sm-4 col-lg-4 col-md-6">
                        <div class="card card-custom @if($p->status) @endif">
                            <div class="card-header bg-dark text-white">
                                <h5 class="card-title text-white" style="margin-top: 10px;">COMANDA:
                                    @if($p->comanda == '')
                                    <a class="btn btn-info" onclick="atribuir('{{$p->id}}', '{{$p->mesa->nome}}')" data-toggle="modal" data-target="#modal-comanda">Atribuir comanda</a>
                                    <h2><br></h2>
                                    @else
                                    <span class="">{{$p->comanda}}</span>
                                    @endif
                                </h5>
                            </div>
                            <div class="card-body" style="height: 230px;">
                                <h5>Total: <strong class="text-info">R$ {{ __moeda($p->somaItems())}}</strong></h5>
                                <h5>Horário Abertura: <strong class="text-info">{{ \Carbon\Carbon::parse($p->data_registro)->format('H:i')}}</strong></h5>
                                <h5>Total de itens: <strong class="text-info">{{ count($p->itens) }}</strong></h5>
                                <h5>Itens Pendentes: <strong class="text-info">{{ $p->itensPendentes() }}</strong></h5>
                                <h5>Mesa:
                                    @if($p->mesa != null)
                                    <strong class="text-info">{{$p->mesa->nome}}</strong>
                                    @else
                                    <strong class="text-info">AVULSA</strong>
                                    <a onclick="setarMesa('{{$p->id}}', '{{$p->comanda}}')" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modal-setar_mesa">
                                        setar
                                    </a>
                                    @endif
                                </h5>
                                <h5>Cliente:
                                    @if($p->cliente != null)
                                    <strong class="text-success">{{$p->cliente->razao_social}} {{$p->cliente->cpf_cnpj}}</strong>
                                    @else
                                    --
                                    @endif
                                </h5>
                                @if($p->referencia_cliete != '')
                                <h5 class="text-danger">Mesa QrCode</h5>
                                @else
                                <h5><br></h5>
                                @endif
                            </div>
                            <div class="card-footer">
                                <a class="btn btn-danger btn-sm" style="width: 100%;" onclick='swal("Atenção!", "Deseja desativar esta comanda? os dados não poderam ser retomados!", "warning").then((sim) => {if(sim){ location.href="/pedidos/desativar/{{ $p->id }}" }else{return false} })' href="#!"><i class="bx bx-x"></i> Desativar</a>
                                <a href="{{ route('pedidos.show', $p->id) }}" style="width: 100%; margin-top: 5px;" class="btn btn-info btn-sm">
                                    <i class="bx bx-list-ul"></i>Ver Itens
                                </a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="row">
                    <div class="col s6 offset-s3">
                        <a href="{{ route('pedidos.mesas') }}" class="btn btn-info">VER MESAS</a>
                    </div>
                </div>
                @else
                <h4 class="center-align">Nenhuma comanda aberta!</h4>
                {{-- <a class="btn btn-lg btn-success" data-toggle="modal" data-target="#modal1">
                    <i class="bx bx-tag"></i>Abrir Comanda
                </a> --}}
                @endif
            </div>
        </div>
    </div>
</div>

@section('js')
<script src="/js/pedidos.js"></script>
@endsection

@include('modals._abrir_comanda', ['not_submit' => true])
@include('modals._clientePedido', ['not_submit' => true])
@include('modals._setar_mesa', ['not_submit' => true])

@endsection
