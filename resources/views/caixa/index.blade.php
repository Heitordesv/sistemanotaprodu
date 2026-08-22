@extends('default.layout',['title' => 'Caixa'])
@section('content')
<div class="page-content">
    <div class="card ">
        {!! Form::open()
        ->post()
        ->route('frenteCaixa.fecharPost')
        !!}
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                </div>
            </div>
            @php
            $estado = $abertura == null ? false : true;
            @endphp
            <div class="col">
                <div class="row">
                    <h6 class="mb-0 text-uppercase">Estado:
                        @if($estado)
                        <span class="btn btn-info btn-sm">Caixa aberto</span>
                        @if(empresaComFilial())
                        @if($abertura->filial)
                        <span class="btn btn-success btn-sm">Local:
                            <strong class="ml-1"> {{ $abertura->filial->descricao}}</strong>
                        </span>
                        @endif
                        @endif
                        @else
                        <span class="btn btn-warning btn-sm">Caixa fechado</span>
                        @endif
                    </h6>
                </div>
                <div class="row mt-3">
                    <div class="row">
                        <div class="col-9">
                            @if(!$estado)
                            <a class="btn btn-primary px-4" data-bs-toggle="modal" data-bs-target="#modal-abrir_caixa"><i class="bx bx-money" style="margin-top: -17px"></i>Abrir caixa</a>
                            @endif
                            @if($estado)
                            <a class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-suprimento_caixa"><i class="bx bx-money"></i> Suprimento de caixa</a>
                            <a class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#modal-sangria_caixa"><i class="bx bx-downvote"></i> Sangria de caixa</a>
                            @endif
                            <a class="btn btn-info px-4" href="{{ route('caixa.list') }}"><i class="bx bx-list-ol"></i>Lista</a>
                        </div>
                    </div>

                    @if($estado)
                    @if(sizeof($caixa) > 0)
                    <div class="mt-4">
                        <h5>Total de vendas: {{ sizeof($caixa['vendas']) }}</h5>
                    </div>
                    <div class="mt-3">
                        <h5 style="color: blue">Valor de abertura: {{ __moeda($abertura->valor) }}</h5>
                    </div>
                    @endif
                    @endif

                    @php
                    $somaDinheiro = 0;
                    @endphp

                    @if(sizeof($caixa) > 0)
                    <div class="row mt-3">
                        <h5>Total por tipo de pagamento:</h5>
                        @foreach($caixa['somaTiposPagamento'] as $key => $tp)
                        @if($tp > 0)
                        <div class="col-sm-4 col-lg-4 col-md-6">
                            <div class="card card-custom gutter-b">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        {{App\Models\VendaCaixa::getTipoPagamento($key)}}
                                    </h3>
                                </div>
                                @php
                                if($key == '01') $somaDinheiro = $tp;
                                @endphp
                                <div class="card-body">
                                    <h4 class="text-success">R$ {{ __moeda($tp) }}</h4>
                                </div>
                            </div>
                        </div>
                        @endif
                        @endforeach
                    </div>
                    @endif
                </div>
                <div class="card mt-3">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table mb-0 table-striped">
                                <thead class="">
                                    <tr>
                                        <th>Cliente</th>
                                        <th>Data</th>
                                        <th>Tipo de pagamento</th>
                                        <th>Estado</th>
                                        <th>Nfce/Nfe</th>
                                        <th>Tipo</th>
                                        <th>Valor</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                    $soma = 0;
                                    @endphp

                                    @if(sizeof($caixa) > 0)

                                    @forelse ($caixa['vendas'] as $item)
                                    <tr>
                                        <td>{{ $item->cliente->razao_social ?? 'Consumidor' }}</td>
                                        <td>{{ $item->created_at }}</td>
                                        <td>
                                            <span class="codigo" style="width: 100px;">
                                                @if($item->tipo_pagamento == '99')
                                                Outros
                                                @else
                                                {{$item->getTipoPagamento($item->tipo_pagamento)}}
                                                @endif
                                            </span>
                                        </td>
                                        <td>{{ $item->estado }} {{ $item->estado_emissao }}</td>
                                        <td>{{ $item->NFcNumero }} {{ $item->numero_nfe }}</td>
                                        <td>{{ $item->tipo }}</td>
                                        <td>{{ __moeda($item->valor_total) }}</td>
                                    </tr>
                                    @if($item->estado != 'CANCELADO')
                                    @php
                                    if(!isset($item->cpf)){
                                    $soma += $item->valor_total-$item->desconto+$item->acrescimo;
                                    }else{
                                    if(!$item->rascunho && !$item->consignado){
                                    $soma += $item->valor_total;
                                    }
                                    }
                                    @endphp
                                    @endif
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center">Nada encontrado</td>
                                    </tr>
                                    @endforelse
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        @php
                        $somaSuprimento = 0;
                        $somaSangria = 0;
                        @endphp
                        @if(sizeof($caixa) > 0)
                        <div class="row mt-3">
                            <div class="col-12 col-xl-6">
                                <div class="card card-custom gutter-b bg-light-info">
                                    <div class="card-body">
                                        <h2 class="card-title">Suprimentos:</h2>
                                        @if(sizeof($caixa['suprimentos']) > 0)
                                        @foreach($caixa['suprimentos'] as $s)
                                        <h4>Valor: R$ {{number_format($s->valor, 2, ',', '.')}}</h4>
                                        @php
                                        $somaSuprimento += $s->valor;
                                        @endphp
                                        @endforeach
                                        @else
                                        <h4>R$ 0,00</h4>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-xl-6">
                                <div class="card card-custom gutter-b bg-light-danger">
                                    <div class="card-body">
                                        <h2 class="card-title">Sangrias:</h2>
                                        @if(sizeof($caixa['sangrias']) > 0)
                                        @foreach($caixa['sangrias'] as $s)
                                        <h4>Valor: R$ {{number_format($s->valor, 2, ',', '.')}}</h4>
                                        @php
                                        $somaSangria += $s->valor;
                                        @endphp
                                        @endforeach
                                        @else
                                        <h4>R$ 0,00</h4>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                @if($estado)
                    
                @if( sizeof($caixa['vendas']) == 0)
                <h3 class="text-danger text-center">NÃO É POSSÍVEL FECHAR SEM NENHUMA VENDA</h3>
                @else
                <h3 class="text-warning">Soma de vendas:
                    <strong>{{ __moeda($soma) }}</strong>
                </h3>
                <h3 class="text-info">Total caixa dinheiro:
                    <strong>
                        {{ __moeda(($somaDinheiro + $somaSuprimento + $abertura->valor) - $somaSangria) }}
                    </strong>
                </h3>
                <h3 class="text-success">Total geral:
                    <strong>
                        {{ __moeda(($soma + $somaSuprimento + $abertura->valor) - $somaSangria) }}
                    </strong>
                </h3>
                @endif
                @endif

            </div>
            <div class="row">
                <div class="col-12">
                    <input type="hidden" name="valor_dinheiro_caixa" id="valor_dinheiro_caixa">
                    <input type="hidden" name="abertura_id" value="{{$abertura != null ? $abertura->id : 0}}">
                    <input type="hidden" name="redirect" value="/caixa">
                    <button type="submit" @if(sizeof($caixa)==0 || sizeof($caixa['vendas'])==0 ) disabled @endif class="btn btn-lg btn-danger">
                        <i class="bx bx-x" style="margin-top: -17px"></i>
                        Fechar caixa
                    </button>
                </div>
            </div>
        </div>
        {!! Form::close() !!}
    </div>
</div>

@include('modals._abrir_caixa')
@include('modals.frontBox._suprimento_caixa', ['not_submit' => true])
@include('modals.frontBox._sangria_caixa', ['not_submit' => true])

@endsection
