
<div class="modal fade" id="modal-pedido_lista" aria-modal="true" role="dialog" style="overflow:scroll;" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content m-3">
            @isset($pedido)
            <div class="modal-header">
                <h5 class="modal-title">Finalizar Pedido</h5>
                <div class="ms-auto">
                    <h6 class="text-info">VALOR TOTAL - R$ <strong id="valor_pedido">{{__moeda($pedido->somaItens())}} </strong></h6>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="mt-3">
                <h6 style="margin-left: 15px; color:blue">Cliente: <strong>{{$pedido->cliente->nome}} - {{$pedido->cliente->celular}} </strong>
                    @isset($pedido->endereco)
                    - 
                    Endereço: {{ $pedido->endereco ? $pedido->endereco->rua : 'Balcão'}} - {{ $pedido->endereco ? $pedido->endereco->numero : ''}} - {{ $pedido->endereco ? $pedido->endereco->_bairro->nome : ''}} </h6>
                    @endisset
                </div>
                <hr>
                <div class="row m-1" style="max-height: 330px; overflow: auto;">
                    @foreach ($pedido->itens as $item)
                    <div class="card col-3">
                        <div class="d-flex order-actions ms-auto">
                            <form action="{{ route('pedidosDelivery.deleteItem', $item->id) }}" method="post" id="form-{{$item->id}}">
                                @method('delete')
                                @csrf
                                <button class="btn btn-light btn-delete btn-sm m-1">
                                    <i class="bx bx-x" style="margin-left: 4px"></i>
                                </button>
                            </form>
                        </div>
                        <div class="text-center">
                            @if($item->produto->img != null)
                            <img style="width: 40px; height: 100px;" class="m-1" src="{{$item->produto->img}}">
                            @else
                            <img style="width: auto; height: 200px;" class="m-2" src="/imgs/no_image.png" alt="image">
                            @endif
                        </div>
                        <div class="text-center">
                            <h6 class="card-title m-1">
                                <span class="text-info">{{$item->produto->produto->nome}}</span>
                                <span style="font-size: 12px; display: block;">{{ $item->observacao}}</span>
                            </h6>
                        </div>
                        <div class="text-center mt-5">
                            <h5>Valor: {{ __moeda($item->valor) }}</h5>
                            <h6>Quantidade: {{$item->quantidade}}</h6>
                        </div>
                        <div class="text-center mt-1">
                            <p class="m-2" style="color: rgb(15, 18, 230)">Adicionais:</p>
                            @foreach ($item->itensAdicionais as $ad)
                            {{$ad->adicional->nome}} @if(!$loop->last) | @endif
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>

                <hr>
                {!!Form::open()
                ->post()
                ->route('pedidosDelivery.frenteComPedidoFinalizar')
                !!}
                <input type="hidden" name="pedido_id" value="{{ $pedido->id }}">
                <div class="row m-3">
                    <h4>Forma de Pagamento</h4>
                    <div class="col-md-3">
                        {!! Form::select('forma_pagamento', 'Forma de Pagamento',['' => 'Selecione..'] + App\Models\VendaCaixa::tiposPagamento())->attrs(['class' => 'form-select']) !!}
                    </div>
                    <div class="col-md-2 troco_para d-none">
                        {!! Form::tel('troco_para', 'Troco Para R$')->attrs(['class' => 'moeda']) !!}
                    </div>
                    <div class="col-md-4">
                        {!! Form::select('estado', 'Estado do Pedido', ['novo' => 'Novo', 'aprovado' => 'Aprovado', 'reprovado' => 'Reprovado', 'finalizado' => 'Finalizado'])->attrs(['class' => 'form-select']) !!}
                    </div>
                    <div class="col-md-12 mt-2">
                        {!! Form::text('observacao', 'Observacao')->attrs(['class' => '']) !!}
                    </div>
                    <hr class="mt-2">
                    <div class="col-12 w-100">
                        <button class="btn btn-info float-right">Finalizar Pedido</button>
                    </div>
                </div>
                {!!Form::close()!!}
                @endisset
            </div>
        </div>
    </div>
