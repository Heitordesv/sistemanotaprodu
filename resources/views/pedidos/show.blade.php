@extends('default.layout',['title' => 'Comanda', $item->id])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="card border-top border-0 border-3 border-success">
                <div class="row m-3 d-sm-flex align-items-center mb-3">
                    <h4 class="">Comanda: <strong class="text-danger">{{$item->comanda}}</strong></h4>
                    @if($item->mesa_id != NULL)
                    <h4>Mesa: <strong class="text-info">{{$item->mesa->nome}}</strong></h4>
                    @else
                    <h4>Mesa: <strong class="text-info">Avulsa</strong></h4>
                    @endif
                    <h5>Observação: {{$item->observacao }}</h5>
                </div>
            </div>
            <input type="hidden" id="adicionais-inp" value="{{json_encode($adicionais)}}" name="">

            <div class="card border-top border-0 border-3 border-success">
                {!!Form::open()
                ->post()
                ->route('pedidos.storeItem')
                !!}
                <input type="hidden" id="pedido_id" name="id" value="{{$item->id}}">
                <div class="row m-3">
                    <div class="col-md-5 mt-1">
                        <label class="col-form-label" id="">Produto</label>
                        <select class="form-control select2" style="width: 100%" id="inp-produto" name="produto">
                            <option value="null">Selecione o produto</option>
                            @foreach($produtos as $p)
                            <option value="{{$p->id}}">{{$p->id}} - {{$p->nome}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mt-3">
                        {!! Form::tel('quantidade', 'Quantidade') !!}
                    </div>
                    <div class="col-md-2 mt-3">
                        {!! Form::tel('valor', 'Valor')->attrs(['class' => 'moeda']) !!}
                    </div>
                    <div class="col-md-2 mt-3">
                        {!! Form::select('status', 'Entregue', [0 => 'Não', 1 => 'Sim'])->attrs(['class' => 'form-select']) !!}
                    </div>
                    <div class="col-md-6 mt-3">
                        {!! Form::select('adicionais', 'Adicionais',['' => 'Selecione...'] + $adicionais->pluck('nome', 'id')->all())->attrs(['class' => 'form-select']) !!}
                    </div>
                    <div class="col-md-3 mt-3">
                        <br>
                        <button type="button" id="btn-adicional" class="btn btn-info"><i class="bx bx-plus"></i></button>
                    </div>
                    <div class="col-md-12 mt-3" id="div-adicionais" style="display: none;">
                        <div class="row">
                        </div>
                    </div>
                    <div class="col-md-8 mt-3">
                        {!! Form::text('observacao', 'Observação') !!}
                    </div>

                    <input type="hidden" name="tamanho_pizza_id" id="tamanho_pizza_id">
                    <input type="hidden" name="sabores_escolhidos" id="sabores_escolhidos">
                    <input type="hidden" name="adicionais_escolhidos" id="adicionais_escolhidos">

                    <div class="col-md-4 mt-3">
                        <br>
                        <button class="btn btn-info"><i class="bx bx-plus"></i>Adicionar</button>
                    </div>
                </div>
                {!!Form::close()!!}
            </div>
            <div class="card border-top border-0 border-3 border-success">
                <div class="row m-4">
                    @if(sizeof($item->itens) > 0)
                    <a href="{{ route('pedidos.imprimirPedido', $item->id) }}" target="_blank" class="col-3 btn btn-info">
                        <i class="bx bx-printer"></i>
                        Imprimir pedido
                    </a>
                    <a style="margin-left: 15px" onclick="imprimirItens()" target="_blank" class="col-3 btn btn-danger">
                        <i class="bx bx-printer"></i>
                        Imprimir itens
                    </a>
                    @endif
                </div>
                <div class="row m-3">
                    <div class="table-responsive">
                        <table class="table mb-0 table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Produto</th>
                                    <th>Tamanho pizza</th>
                                    <th>Sabores</th>
                                    <th>Adicionais</th>
                                    <th>Status</th>
                                    <th>Valor total</th>
                                    <th>Quantidade</th>
                                    <th>Observação</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <?php $finalizado = 0; $pendente = 0; ?>
                            <tbody id="body">
                                @foreach ($item->itens as $i)
                                <tr>
                                    <td id="checkbox">
                                        <p style="width: 70px;">
                                            <input type="checkbox" class="check" @if($i->impresso == 0) checked @endif id="item_{{$i->id}}"/>
                                            <label for="item{{$i->id}}"></label>
                                        </p>
                                    </td>
                                    <td style="display: none" id="item_id">{{$i->id}}</td>
                                    <td>{{ $i->produto->nome }}</td>
                                    <td>@if(!empty($i->tamanho))
                                        <label>{{ $i->tamanho->nome }}</label>
                                        @else
                                        <label>--</label>
                                        @endif
                                    </td>
                                    <td>@if(count($i->sabores) > 0)
                                        <label>
                                            @foreach($i->sabores as $key => $s)
                                            {{$s->produto->produto->nome}}
                                            @if($key < count($i->sabores)-1)
                                                |
                                                @endif
                                                @endforeach
                                        </label>
                                        @else
                                        <label>--</label>
                                        @endif
                                    </td>
                                    <td>
                                        <?php $somaAdicionais = 0; ?>
                                        @if(count($i->itensAdicionais) > 0)
                                        <label>
                                            @foreach($i->itensAdicionais as $key => $a)
                                            {{$a->adicional->nome()}}
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
                                        <span class="btn btn-success btn-sm">Entregue</span>
                                        @else
                                        <span class="btn btn-danger btn-sm">Pendente</span>
                                        @endif
                                    </td>
                                    <td>{{ __moeda($i->valor) }}</td>
                                    <td>{{ $i->quantidade }}</td>
                                    <td>{{ $i->observacao }}</td>
                                    <td>
                                        <a onclick='swal("Atenção!", "Deseja excluir este registro?", "warning").then((sim) => {if(sim){ location.href="/pedidos/deleteItem/{{$i->id}}" }else{return false} })' href="#!" class="btn btn-danger btn-sm">
                                            <i class="bx bx-trash"></i>
                                        </a>
                                        @if(!$i->status)
                                        <a href="{{ route('pedidos.alterarStatus', $i->id) }}" class="btn btn-success btn-sm">
                                            <i class="bx bx-check"></i>
                                        </a>
                                        @endif
                                    </td>
                                </tr>
                                <?php 
									if($i->status) $finalizado++;
									else $pendente++;
									?>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="card border-top border-0 border-3 border-success">
                <div class="row m-3">
                    <h4>Total Produtos: <strong class="text-info">{{ __moeda($item->somaItems()) }}</strong></h4>
                    @if($item->bairro_id != null)
                    <h4>Entrega: <strong class="text-info">{{ __moeda($item->bairro->valor_entrega) }}</strong></h4>
                    <h3>Total Geral: <strong class="text-danger">{{ __moeda($item->somaItems() + $item->bairro->valor_entrega) }}</strong></h3>
                    @endif
                    <h4>Itens Finalizados: <strong class="">{{ $finalizado }}</strong> </h4>
                    <h4>Itens Pendentes: <strong class="text-warning">{{ $pendente }}</strong> </h4>
                </div>
                <div class="m-4">
                    <a class="btn btn-success px-5 @if($pendente > 0 || $item->status) disabled @endif green accent-4" href="{{ route('pedidos.finalizar', $item->id) }}">
                        <i class="bx bx-check"></i>
                        Finalizar Pedido</a>
                </div>
            </div>
        </div>
    </div>
</div>

@section('js')
<script src="/js/pedidos.js"></script>
@endsection

@endsection
