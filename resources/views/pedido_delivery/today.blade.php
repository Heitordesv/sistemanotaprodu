@extends('default.layout',['title' => 'Pedidos Delivery'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                </div>
            </div>
            <div class="col">
                <h6 class="mb-0 text-uppercase">Pesquisar pedidos delivery</h6>
                {!!Form::open()->fill(request()->all())
                ->get()
                !!}
                <div class="row mt-3">
                    <div class="col-md-2">
                        {!!Form::date('datainicial', 'Data Inicial')
                        !!}
                    </div>
                    <div class="col-md-2">
                        {!!Form::date('datafinal', 'Data Final')
                        !!}
                    </div>
                    <div class="col-md-5 text-left">
                        <br>
                        <button class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
                        <a id="clear-filter" class="btn btn-danger" href=""><i class="bx bx-eraser"></i> Limpar</a>
                    </div>
                </div>
                {!!Form::close()!!}
                <hr />
                <h6>Lista de pedidos de hoje</h6>
                <p>Registros:</p>
                <div class="row">
                    <div class="col-lg-6 col-xl-4 col-sm-6 col-md-6 col-12">
                        <span style="width: 100%; margin-top: 5px;" class="btn btn-outline-info">Valor de Pedidos Novos: R$ {{number_format($somaNovos, 2, ',', '.')}}</span>
                    </div>
                    <div class="col-lg-6 col-xl-4 col-sm-6 col-md-6 col-12">
                        <span style="width: 100%; margin-top: 5px;" class="btn btn-outline-success">Valor de Pedidos Aprovados: R$ {{number_format($somaAprovados, 2, ',', '.')}}</span>
                    </div>
                    <div class="col-lg-6 col-xl-4 col-sm-6 col-md-6 col-12">
                        <span style="width: 100%; margin-top: 5px;" class="btn btn-outline-danger">Valor de Pedidos Cancelados: R$ {{number_format($somaCancelados, 2, ',', '.')}}</span>
                    </div>
                    <div class="col-lg-6 col-xl-4 col-sm-6 col-md-6 col-12 mt-3">
                        <span style="width: 100%; margin-top: 5px;" class="btn btn-outline-primary">Valor de Pedidos Finalizados: R$ {{number_format($somaFinalizados, 2, ',', '.')}}</span>
                    </div>
                </div>

                <div class="col-lg-12 col-xl-12 mt-3">
                    <div class="accordion accordion-flush" id="accordionFlushExample">
                        <div class="card">
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="flush-headingOne">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseOne" aria-expanded="false" aria-controls="flush-collapseOne">
                                        Pedidos Novos
                                    </button>
                                </h2>
                                <div id="flush-collapseOne" class="accordion-collapse collapse" aria-labelledby="flush-headingOne" data-bs-parent="#accordionFlushExample">
                                    <div class="accordion-body">
                                        @if(count($pedidosNovo) > 0)
                                        @foreach($pedidosNovo as $p)
                                        <a style="margin-top: 5px;" href="/pedidosDelivery/verPedido/{{$p->id}}" class="btn btn-primary">
                                            Pedido N: {{$p->id}}, Cliente: {{$p->cliente->nome}}, Valor do pedido R$
                                            {{number_format($p->somaItens(), 2, ',', '.')}},
                                            Horario: {{ \Carbon\Carbon::parse($p->data_registro)->format('H:i:s')}}
                                        </a>
                                        @endforeach
                                        @else
                                        <h5 class="text-danger">Nenhum pedido neste estado!</h5>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="flush-heading2">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse2" aria-expanded="false" aria-controls="flush-collapse2">
                                        Pedidos Aprovados
                                    </button>
                                </h2>
                                <div id="flush-collapse2" class="accordion-collapse collapse" aria-labelledby="flush-heading2" data-bs-parent="#accordionFlushExample">
                                    <div class="accordion-body">
                                        @if(count($pedidosAprovado) > 0)
                                        @foreach($pedidosAprovado as $p)
                                        <a style="margin-top: 5px;" href="/pedidosDelivery/verPedido/{{$p->id}}" class="btn btn-success">
                                            Pedido N: {{$p->id}}, Cliente: {{$p->cliente->nome}}, Valor R$
                                            {{number_format($p->somaItens(), 2, ',', '.')}},
                                            Horario: {{ \Carbon\Carbon::parse($p->data_registro)->format('H:i:s')}}
                                        </a>
                                        @endforeach
                                        @else
                                        <h5 class="text-danger">Nenhum pedido neste estado!</h5>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="flush-heading3">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse3" aria-expanded="false" aria-controls="flush-collapse3">
                                        Pedidos Recusados
                                    </button>
                                </h2>
                                <div id="flush-collapse3" class="accordion-collapse collapse" aria-labelledby="flush-heading3" data-bs-parent="#accordionFlushExample">
                                    <div class="accordion-body">
                                        @if(sizeof($pedidosCancelado) > 0)
                                        @foreach($pedidosCancelado as $p)
                                        <a style="margin-top: 5px;" href="/pedidosDelivery/verPedido/{{$p->id}}" class="btn btn-light-warning">
                                            Pedido N: {{$p->id}}, Cliente: {{$p->cliente->nome}}, Valor R$
                                            {{number_format($p->somaItens(), 2, ',', '.')}},
                                            Horario: {{ \Carbon\Carbon::parse($p->data_registro)->format('H:i:s')}}
                                        </a>
                                        @endforeach
                                        @else
                                        <h5 class="text-danger">Nenhum pedido neste estado!</h5>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="flush-heading4">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse4" aria-expanded="false" aria-controls="flush-collapse4">
                                        Pedidos Finalizados
                                    </button>
                                </h2>
                                <div id="flush-collapse4" class="accordion-collapse collapse" aria-labelledby="flush-heading4" data-bs-parent="#accordionFlushExample">
                                    <div class="accordion-body">
                                        @if(count($pedidosFinalizado) > 0)
                                        @foreach($pedidosFinalizado as $p)
                                        <a style="margin-top: 5px;" href="/pedidosDelivery/verPedido/{{$p->id}}" class="btn btn-light-info">
                                            Pedido N: {{$p->id}}, Cliente: {{$p->cliente->nome}}, Valor R$
                                            {{number_format($p->somaItens(), 2, ',', '.')}},
                                            Horario: {{ \Carbon\Carbon::parse($p->data_registro)->format('H:i:s')}}
                                        </a>
                                        @endforeach
                                        @else
                                        <h5 class="text-danger">Nenhum pedido neste estado!</h5>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- <div class="card">
                            <div class="card-header">
                                <div class="card-title collapsed" data-toggle="collapse" data-target="#collapseSix1">
                                    Pedidos do Carrinho/A Finalizar <i class="la la-angle-double-down"></i>
                                </div>
                            </div>
                            <div id="collapseSix1" class="collapse" data-parent="#accordionExample1">
                                <div class="card-body">
                                    @if(!empty($carrinho))
                                    <a href="/pedidosDelivery/verCarrinhos" class="btn btn-dark">
                                        Ver carrinhos em aberto
                                    </a>
                                    @else
                                    <h5>Nenhum carrinho em aberto!</h5>
                                    @endif
                                </div>
                            </div>
                        </div> -->
                    </div>
                </div>

                {{-- <div class="card mt-3">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table mb-0 table-striped">
                                <thead class="">
                                    <tr>
                                        <th>ID</th>
                                        <th>Cliente</th>
                                        <th>Data</th>
                                        <th>Forma de Pagamento</th>
                                        <th>Estado de Pagamento</th>
                                        <th>Estado de Envio</th>
                                        <th>Valor</th>
                                        <th>Valor Total</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>

                                </tbody>
                            </table>
                        </div>
                        {!! $data->appends(request()->all())->links() !!}
                    </div>
                </div> --}}
            </div>
        </div>
    </div>
</div>
@endsection
