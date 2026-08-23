@extends('default.layout', ['title' => 'Detalhes de Vendas'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="row">
                    <h6 class="mb-0 text-uppercase">Detalhes da Venda</h6>
                </div>
            </div>
            <div class="mt-5">
                <h6>Venda Nº: <strong style="color: rgb(20, 60, 241)">{{ $item->id }} </strong></h6>
                <h6>Chave NFCe: {{ $item->chave != "" ? $item->chave : '--' }} </h6>
                <h5>Estado:
                    @if($item->estado_emissao == 'novo')
                    <span class="btn bn-xl btn-inline btn-primary">Novo</span>
                    @elseif($item->estado_emissao == 'aprovado')
                    <span class="btn btn-xl btn-inline btn-success">Aprovado</span>
                    @elseif($item->estado_emissao == 'cancelado')
                    <span class="btn btn-xl btn-inline btn-danger">Cancelado</span>
                    @else
                    <span class="btn btn-xl btn-inline btn-warning">Rejeitado</span>
                    @endif
                </h5>
                @if($adm)
                <a href="{{ route('nfce.estadoFiscal', $item->id) }}" class="btn btn-danger">
                    <i class="bx bx-error"></i>
                    Alterar estado fiscal da venda
                </a>
                @endif
            </div>
            <hr>
            <div class="table-responsive">
                <h5>Itens da venda</h5>
                @php
                $somaItens = 0;
                @endphp
                <table class="table mb-0 table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Produto</th>
                            <th>Quantidade</th>
                            <th>Valor</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($item->itens as $i)
                        <tr>
                            <td>{{ $i->id }}</td>
                            <td>{{ $i->produto->nome }}</td>
                            <td>{{ $i->quantidade }}</td>
                            <td>{{ __moeda($i->valor) }}</td>
                            <td>{{ __moeda($i->valor * $i->quantidade) }}</td>
                        </tr>
                        @php
                        $somaItens += $i->valor * $i->quantidade
                        @endphp
                        @empty
                        <tr>
                            <td colspan="5" class="text-center">Nada encontrado</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                <h6>Subtotal dos produtos: <strong>{{ __moeda($somaItens) }}</strong></h6>
                @if(($item->taxa_entrega ?? 0) > 0)
                    <h6>Taxa de entrega: <strong>+ R$ {{ __moeda($item->taxa_entrega) }}</strong></h6>
                @endif
                @if($item->acrescimo > 0)
                    <h6>
                        Acréscimo:
                        <strong>
                            + R$ {{ __moeda($item->acrescimo) }}
                            @if($item->acrescimo_tipo === 'percentual')
                                ({{ __moeda($item->acrescimo_percentual) }}%)
                            @endif
                        </strong>
                    </h6>
                @endif
                @if($item->desconto > 0)
                    <h6>
                        Desconto:
                        <strong>
                            - R$ {{ __moeda($item->desconto) }}
                            @if($item->desconto_tipo === 'percentual')
                                ({{ __moeda($item->desconto_percentual) }}%)
                            @endif
                        </strong>
                    </h6>
                @endif
                <h5>Total da venda: <strong style="color: rgb(20, 60, 241)">R$ {{ __moeda($item->valor_total) }}</strong></h5>
            </div>
            <hr>
            <div class="col-5">
                @if($item->NFcNumero && $item->estado == 'APROVADO')
                <a target="_blank" href="{{ route('nfce.imprimir', $item->id)}}" class="btn btn-success">
                    <i class="bx bx-printer"></i>
                    Imprimir fiscal
                </a>
                @endif
                <a style="margin-left: 5px;" target="_blank" href="{{ route('nfce.imprimirNaoFiscal', $item->id) }}" class="btn btn-info">
                    <i class="bx bx-printer"></i>
                    Imprimir não fiscal
                </a>
                @if($item->isComprovanteAssessor())
                <a style="margin-left: 5px;" target="_blank" href="{{ route('nfce.imprimirComprovanteAssessor', $iem->id) }}" class="btn btn-primary">
                    <i class="bx bx-printer"></i>
                    Imprimir comprovante assessor
                </a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
