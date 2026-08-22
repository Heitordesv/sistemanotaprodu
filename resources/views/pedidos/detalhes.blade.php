@extends('default.layout',['title' => 'Pedidos'])
@section('content')
<div class="page-content">
    <div class="card border-top border-0 border-3 border-success">
        <div class="card-body p-4">
            <div class="col-sm-12 col-lg-12 col-md-12 col-xl-12">
                <h4>Itens Da Comanda</h4>
                <div class="col-xl-12">
                    <div class="table-responsive">
                        <table class="table mb-0 table-striped">
                            <thead>
                                <tr>
                                    <th>Produto</th>
                                    <th>Tamanho de Pizza</th>
                                    <th>Sabores</th>
                                    <th>Adicionais</th>
                                    <th>Status</th>
                                    <th>Valor</th>
                                    <th>Quantidade</th>
                                    <th>Subtotal+adicional</th>
                                    <th>Observação</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $finalizado = 0; $pendente = 0; ?>
                                @foreach($pedido->itens as $i)
                                <?php $temp = $i; ?>
                                <tr>
                                    <td style="display: none" id="item_id">{{$i->id}}</td>
                                    <td id="estado_{{$i->id}}">{{ $i->produto->nome }}</span></td>
                                    <td>
                                        @if(!empty($i->tamanho))
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
                                    <td>{{$temp->quantidade}}</td>
                                    <td>{{ __moeda((($valorVenda * $i->quantidade) + $somaAdicionais)) }}</td>
                                    <td><a href="#!" onclick='swal("", "{{$i->observacao}}", "info")' class="btn btn-info btn-sm @if(!$i->observacao) disabled @endif">
                                            Ver
                                        </a>
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
                <br>
                <hr><br>
                <h4>Itens Removidos</h4>
                <div class="col-xl-12">
                    <div class="table-reponsive">
                        <table class="table mb-0 table-striped">
                            <thead>
                                <tr>
                                    <th>Produto</th>
                                    <th>Quantidade</th>
                                    <th>Valor</th>
                                    <th>Data de inserção</th>
                                    <th>Data de remoção</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($removidos as $r)
                                <tr>
                                    <td>{{ $r->produto }}</td>
                                    <td>{{ __moeda($r->quantidade) }}</td>
                                    <td>{{ __moeda($r->valor) }}</td>
                                    <td>{{ $r->data_insercao }}</td>
                                    <td>{{ \Carbon\Carbon::parse($r->updated_at)->format('d/m/Y H:i:s')}}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection
