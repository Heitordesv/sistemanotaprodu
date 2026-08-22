@extends('default.layout',['title' => 'Comanda e mesa'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="card border-top border-0 border-3 border-info">
                <h5 class="m-3">Mesa: {{$mesa->nome}}</h5>
            </div>

            @if(sizeof($pedidos) > 0)
            @foreach($pedidos as $p)
            <?php $pedido = $p; ?>

            <div class="card border-top border-0 border-3 border-info">
                <div class="row m-4">
                    <h6>Comanda: <strong class="text-danger">{{$pedido->comanda}}</strong></h6>
                    @if(sizeof($pedido->itens) > 0)
                    <div class="col-3">
                        <a href="{{ route('pedidos.imprimirPedido' , $pedido->id) }}" target="_blank" class="col-3 btn btn-info" style="width: 100%">
                            <i class="bx bx-printer"></i>
                            Imprimir pedido</a>
                    </div>
                    @endif
                </div>
                <div class="row m-3">
                    <div class="table-responsive">
                        <table class="table mb-0 table-striped">
                            <thead>
                                <tr>
                                    <th>Produto</th>
                                    <th>Tamanho pizza</th>
                                    <th>Sabores</th>
                                    <th>Adicionais</th>
                                    <th>Status</th>
                                    <th>Valor</th>
                                    <th>Quantidade</th>
                                    <th>Subtotal + Adicional</th>
                                    <th>Observação</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $finalizado = 0; $pendente = 0; ?>
                                @foreach($pedido->itens as $i)
                                <?php $temp = $i; ?>
                                <tr>
                                    <td>{{$i->produto->nome}}</td>
                                    <td>@if(!empty($i->tamanho))
                                        <label>{{$i->tamanho->nome}}</label>
                                        @else
                                        <label>--</label>
                                        @endif
                                    </td>
                                    <td>
                                        @if(count($i->sabores) > 0)
                                        @foreach($i->sabores as $key => $s)
                                        {{$s->produto->produto->nome}}
                                        @if($key < count($i->sabores)-1)
                                            |
                                            @endif
                                            @endforeach
                                            @else
                                            <label>--</label>
                                            @endif
                                    </td>
                                    <td>
                                        <?php $somaAdicionais = 0; ?>
                                        @if(count($i->itensAdicionais) > 0)
                                        <label>
                                            @foreach($i->itensAdicionais as $key => $a)
                                            {{$a->adicional->nome}}
                                            <?php $somaAdicionais += $a->adicional->valor * $i->quantidade?>
                                            @if($key < count($i->itensAdicionais)-1)
                                                |
                                                @endif
                                                @endforeach
                                        </label>
                                        @else
                                        <label>--</label>
                                        @endif
                                    </td>
                                    <td>
                                        @if($i->status)
                                        <span class="btn btn-success btn-sm">
                                            Feito
                                        </span>
                                        @else
                                        <span class="btn btn-danger btn-sm">
                                            Pendente
                                        </span>
                                        @endif
                                    </td>

                                    <?php 
                                    $valorVenda = 0;
									$valorVenda = $i->valor;
									?>

                                    <td>{{ __moeda($valorVenda) }}</td>
                                    <td>{{ $temp->quantidade }}</td>
                                    <td>{{ __moeda(($valorVenda * $i->quantidade) + $somaAdicionais) }}</td>
                                    <td><a href="#!" onclick='swal("", "{{$i->observacao}}", "info")' class="btn btn-info btn-sm @if(!$i->observacao) disabled @endif">
                                            Ver
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="row m-3">
                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <br>
                            <h5>Total Produtos: <strong class="text-success">{{ __moeda($pedido->somaItems()) }}</strong></h5>
                            @if($pedido->bairro_id != null)
                            <h5>Entrega: <strong class="text-success">{{ __moeda($pedido->bairro->valor_entrega) }}</strong></h5>
                            <h4>Total Geral: <strong class="text-success">{{ __moeda($pedido->somaItems() + $pedido->bairro->valor_entrega) }}</strong></h4>
                            @endif

                            <h5>Itens Finalizados: <strong class="text-success">{{$finalizado}}</strong></h5>
                            <h5>Itens Pendentes: <strong class="text-warning">{{$pendente}}</strong></h5>
                        </div>
                    </div>
                    <div class="col-3 mt-3">
                        <a style="width: 100%;" class="btn btn-ls btn-success @if($pendente > 0 || $pedido->status) disabled @endif green accent-4" href="/pedidos/finalizar/{{$pedido->id}}">
                            <i class="bx bx-check"></i>
                            Finalizar Pedido</a>
                    </div>
                </div>
            </div>

            @endforeach

            @else
            <hr>
            <h4 class="text-danger">Nada encontrado!!</h4>
            @endif
        </div>
    </div>
</div>

@endsection
