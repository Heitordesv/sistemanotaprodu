@extends('default.layout', ['title' => 'Lista de Vendas'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="col">
                    <h6 class="mb-0 text-uppercase">Lista de Vendas de Frente de Caixa</h6>

                    {!! Form::open()->fill(request()->all())->get() !!}
                    <div class="row mt-3">
                        <div class="col-md-2">
                            {!! Form::date('start_date', 'Data inicial')->value(date('Y-m-d')) !!}
                        </div>
                        <div class="col-md-2">
                            {!! Form::date('end_date', 'Data final')->value(date('Y-m-d')) !!}
                        </div>
                        <div class="col-md-2">
                            {!! Form::tel('valor', 'Valor')->attrs(['class' => 'moeda']) !!}
                        </div>
                        <div class="col-md-3">
                            {!! Form::select('estado', 'Estado', [
                            'novo' => 'Novas',
                            'rejeitado' => 'Rejeitadas',
                            'cancelado' => 'Canceladas',
                            'aprovado' => 'Aprovadas',
                            '' => 'Todos',
                            ])->attrs(['class' => 'select2']) !!}
                        </div>
                        <div class="col-md-2">
                            {!! Form::tel('numero_nfe', 'Número NFCe')->attrs(['class' => '']) !!}
                        </div>
                        <div class="col-md-3 text-left mt-1">
                            <br>
                            <button class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
                            <a id="clear-filter" class="btn btn-danger" href="{{ route('frenteCaixa.list') }}">Limpar</a>
                        </div>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>

            @if($config->arquivo != null)
            <div class="col-12">
                <button class="btn btn-dark btn-consulta-status">
                    Consultar Status Sefaz
                </button>
            </div>
            @endif

            @if($contigencia != null)
            <h3 class="text-danger mt-3">Contigência ativada</h3>
            <span class="text-danger">Tipo: {{$contigencia->tipo}}</span><br>
            <span class="text-danger">Data de ínicio: {{ __data_pt($contigencia->created_at) }}</span>
            @endif
            <hr>
            <div class="row row-cols-auto g-3" style="margin-left: 60px">
                <div class="col">
                    <a class="btn btn-info px-3 radius-10" href="{{ route('frenteCaixa.index') }}">Frente de caixa</a>
                </div>
                <div class="col">
                    <a class="btn btn-primary px-3 radius-10" href="/relatorios/filtroVendaDiaria?start_date={{date('Y-m-d')}}&nr_resultados='">Baixar
                        relatório</a>
                </div>
             <!--   <div class="col">
                    <button class="btn btn-dark px-3 radius-10" data-bs-toggle="modal" data-bs-target="#modal-soma_detalhada">Soma detalhada</button>
                </div>-->
                <div class="col">
                    <a class="btn btn-success px-3 radius-10" href="{{ route('caixa.list') }}">Caixas fechados</a>
                </div>
            </div>
            <hr>
            <div class="table-responsive tbl-400">
                <table class="table mb-0 table-striped">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Data</th>
                            <th>Tipo de pagamento</th>
                            <th>Estado</th>
                            <th>Nº NFCe</th>
                            <th>Usuário</th>
                            <th>Valor</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($data as $item)
                        <tr>
                            <td>{{ $item->cliente->razao_social ?? 'Consumidor Final' }}</td>
                            <td>{{ __data_pt($item->created_at, 1) }}</td>
                            <td>{{ $item->getTipoPagamento($item->tipo_pagamento) }}</td>
                            <td>
                                {!! $item->estadoEmissao($item->estado_emissao) !!}
                                @if($item->contigencia)
                                <br>
                                <span class="text-danger">contigência</span>
                                @endif
                            </td>
                            <td>{{ $item->numero_nfce ?? '0' }}</td>
                            <td>{{ $item->usuario->nome }}</td>
                            <td>{{ __moeda($item->valor_total) }}</td>

                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Ações</button>
                                    <ul class="dropdown-menu" style="z-index: 999">

                                        @if($item->estado_emissao != 'aprovado')
                                        <li>
                                            <a title="XML Temporário" target="_blank" href="{{ route('nfce.xml-temp', [$item->id]) }}" class="dropdown-item">
                                                XML Temporário
                                            </a>
                                        </li>
                                        @endif

                                        @if($item->numero_nfce && $item->estado_emissao == 'aprovado')
                                        <li>
                                            <a title="CUPOM FISCAL" target="_blank" href="{{ route('nfce.imprimir', [$item->id]) }}" class="dropdown-item">
                                                Imprimir
                                            </a>
                                        </li>
                                        <li>
                                            <a id="btn_consulta_{{ $item->id }}" title="CONSULTAR NFCE" onclick="consultarNFCe('{{ $item->id }}')" href="#!" class="dropdown-item">
                                                Consultar NFCe
                                            </a>
                                        </li>
                                        <li>
                                            <a title="BAIXAR XML" href="{{ route('nfce.baixar-xml', [$item->id]) }}" class="dropdown-item">
                                                Baixar XML
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('nfce.state-fiscal', $item->id) }}">Alterar estado fiscal</a>
                                        </li>
                                        @endif
                                        <li>
                                            <a title="CUPOM NÃO FISCAL" target="_blank" href="{{ route('frenteCaixa.imprimir-nao-fiscal', [$item->id]) }}" class="dropdown-item">
                                                Cupom Não Fiscal
                                            </a>
                                        </li>
                                        @if (!$item->numero_nfce)
                                        <li>
                                            <a title="GERAR NFCE" id="btn_envia_{{ $item->id }}" class="dropdown-item" onclick='swal("Atenção!", "Deseja enviar esta venda para Sefaz?", "warning").then((sim) => {if(sim){ emitirNFCe({{ $item->id }}) }else{return false} })' href="#!">
                                                Gerar NFCe
                                            </a>
                                        </li>
                                        @endif
                                        <li>
                                            <a title="DETALHES" target="_blank" href="{{ route('nfce.show', [$item->id]) }}" class="dropdown-item">
                                                Detalhes
                                            </a>
                                        </li>
                                        @if ($item->rascunho)
                                        <li>
                                            <a title="EDITAR RASCUNHO" href="/frenteCaixa/edit/{{ $item->id }}" class="dropdown-item">
                                                Editar Rascunho
                                            </a>
                                        </li>

                                        @endif
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center">Nada encontrado</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {!! $data->appends(request()->all())->links() !!}
        </div>
    </div>
</div>

@include('modals.frontBox._soma_detalhada', ['not_submit' => true])


@endsection
@section('js')
<script type="text/javascript" src="/js/nfce.js"></script>
@endsection
