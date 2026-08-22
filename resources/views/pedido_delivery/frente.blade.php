@extends('default.layout',['title' => 'Frente de Pedido Delivery'])
@section('content')
@section('css')
<style>
    .sub_cat:hover {
        cursor: pointer;
    }
</style>

@endsection
<div class="page-content">
    <div class="card">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <h3>Frente de Pedido de Delivery</h3>
            </div>
            <hr>
            {{-- <input type="hidden" id="categorias" value="{{json_encode($categorias)}}" name=""> --}}
            {{-- <input type="text" name="pedido_id" value="{{ $pedido_id }}"> --}}
            <input type="hidden" id="tipo_divisao_pizza" value="{{ $config->tipo_divisao_pizza }}">
            <div class="card">
                <div class="row m-3">
                    @if(!isset($pedido))
                    <div class="col-12">
                        <p class="text-danger">Informe o cliente primeiramente!!</p>
                    </div>
                    @endif
                    @if(!isset($pedido))
                    <form class="form-group validated col-sm-5 col-lg-5 col-10" action="" id="form-cliente">
                        <label class="" id="">Cliente</label><br>
                        <div class="input-group">
                            <select class="form-control select2" id="inp-cliente" name="cliente">
                                <option value="null">Selecione o cliente</option>
                                @foreach($clientes as $c)
                                <option value="{{$c->id}}">{{$c->id}} - {{$c->nome}} {{$c->celular}}</option>
                                @endforeach
                            </select>
                            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modal-clienteRapido">
                                <i class="bx bx-plus"></i>
                            </button>
                        </div>
                    </form>
                    @else
                    <div class="form-group validated col-sm-5 col-lg-5 col-10"><br>
                        <h5>Cliente: <strong class="text-info">{{$pedido->cliente->nome}} {{$pedido->cliente->sobre_nome}}</strong></h5>
                        <h5>Celular: <strong class="text-info">{{$pedido->cliente->celular}}</strong></h5>
                    </div>
                    @endif

                    <div class="form-group validated col-sm-5 col-lg-5 col-10">
                        @isset($pedido)
                        <form action="{{ route('pedidosDelivery.frenteComEndereco', $pedido->id) }}" id="form-endereco">
                            <input type="hidden" name="pedido_id" value="{{ $pedido->id }}">
                            <label class="" id="">Endereço</label><br>
                            <div class="input-group">
                                <select class="form-control select2" id="inp-endereco" name="endereco_id">
                                    <option value="">Balcão</option>
                                    @if(isset($pedido))
                                    @foreach($pedido->cliente->enderecos as $e)
                                    <option value="{{$e->id}}" @if(isset($pedido)) @if($pedido->endereco_id == $e->id)
                                        selected
                                        @endif
                                        @endif
                                        >{{$e->rua}}, {{$e->numero}} - {{$e->_bairro->nome}}
                                    </option>
                                    @endforeach
                                    @endif
                                </select>
                                <button type="button" data-bs-toggle="modal" data-bs-target="#modal-enderecos" href="#" class="btn btn-icon btn-circle btn-info @if(!isset($pedido)) disabled @endif">
                                    <i class="bx bx-plus"></i>
                                </button>
                            </div>
                        </form>
                        @endisset
                    </div>
                    <div class="ms-auto">
                        <h6 class="mt-4">Valor da entrega: <strong class="text-danger vl_entrega">R$ 0,00</strong></h6>
                    </div>
                </div>
            </div>

            <hr>
            @isset($pedido)

            @if($pedido->cliente != null)
            <div class="card mt-4">
                <div class="m-4">
                    {!! Form::select('produto_id', 'Pesquisar produto') !!}
                </div>
                <div class="row m-3">
                    <div class="form-group validated col-sm-12 col-lg-12 col-12 col-sm-12">
                        <a type="button" id="cat_todos" onclick="todos()" class="btn btn-light px-5">Todos</a>
                        @foreach($categorias as $c)
                        <a type="button" onclick="selectCat('{{ $c->id }}')" class="btn btn-light btn_cat btn_cat_{{ $c->id }}">{{$c->nome}}</a>
                        @endforeach
                    </div>
                </div>
                <div class="row mt-6 prods">
                    @foreach($produtos as $p)
                    <div class="col-md-3 bd2 sub_cat sub_cat_{{ $p->categoria_id }}" onclick="addItem('{{$p->id}}', '{{$p->valor}}', '{{$p->categoria->tipo_pizza}}', '{{$p->produto->nome}}')">
                        <div class="card m-1" style="width: 95%; height: 400px">
                            @if($p->img != null)
                            <img style="width: auto; height: 200px; border-radius: 10px;" class="m-2" src="{{$p->img}}">
                            @else
                            <img style="width: auto; height: 200px; border-radius: 10px;" class="m-2" src="/imgs/no_image.png" alt="image">
                            @endif
                            <div class="m-2">
                                <div class="text-center">
                                    <a class="text-dark text-center" style="font-size: 25px">
                                        {{ $p->produto->nome }}
                                    </a>
                                </div>
                                <div class="text-gray-600 text-blue text-center">
                                    <div style="height: 20px; position: absolute; bottom: 55px; width: 90%; height: 2.5rem">
                                        {{$p->descricao}}
                                    </div>
                                </div>
                                <div class="text-center" style="position: absolute; bottom: 0; width: 100%; height: 2.5rem;">
                                    {{-- <a class="text-dark">{{ $p->categoria->nome }}</a> --}}
                                    @if(!$p->categoria->tipo_pizza)
                                    <span style="font-size: 15px; margin-left: -13px" class="text-danger"> R$ {{ __moeda($p->valor) }} </span>
                                    @else
                                    <span style="font-size: 15px; margin-left: -13px" class="text-danger">
                                        R$
                                        @foreach($p->pizza as $key => $pz)
                                        {{ __moeda($pz->valor) }} @if(!$loop->last) | @endif
                                        @endforeach
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <input type="hidden" id="ped_id" value="{{$pedido->id}}">

                <div class="col-3">
                    <button data-bs-toggle="modal" data-bs-target="#modal-pedido_lista" class="btn btn-info px-5" style="position: fixed; right: 95px; bottom: 45px"><i class="bx bx-cart"></i> Ver Pedido</button>
                </div>
            </div>
            @endif
            @endisset
        </div>
    </div>
</div>

@section('js')
<script>
    @if(session() -> has('flash_modal'))
    setTimeout(() => {
        $('#modal-pedido_lista').modal('show')
    }, 100);
    @endif
</script>
<script src="/js/frentePedidoDelivery.js"></script>

@endsection

@include('modals._clienteRapido')
@include('modals.pedido_delivery._pedido_lista')
@include('modals.pedido_delivery._adicionais')
@include('modals.pedido_delivery._enderecos')

@endsection
