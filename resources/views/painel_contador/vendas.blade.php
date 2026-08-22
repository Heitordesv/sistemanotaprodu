@extends('default.layout',['title' => 'Vendas'])
@section('content')

<div class="page-content">
    <div class="card">
        <div class="card-body p-4">
            <div class="card-body">
                {!!Form::open()->fill(request()->all())
                ->get()
                !!}
                <div class="row">
                    <div class="col-md-6">
                        {!!Form::text('razao_social', 'Pesquisar por nome')
                        !!}
                    </div>
                    <div class="col-md-2">
                        {!!Form::date('start_date', 'Data inicial')
                        !!}
                    </div>
                    <div class="col-md-2">
                        {!!Form::date('end_date', 'Data final')
                        !!}
                    </div>
                    <div class="col-md-2">
                        {!!Form::select('estado_emissao', 'Estado',
                        ['' => 'Todas',
                        'rejeitado' => 'Rejeitadas',
                        'cancelado' => 'Canceladas',
                        'aprovado' => 'Aprovadas',
                        ])
                        ->attrs(['class' => 'select2'])
                        !!}
                    </div>
                    <div class="col-md-5 text-left ">
                        <br>
                        <button class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
                        <a id="clear-filter" class="btn btn-danger" href="{{ route('contador.vendas') }}"><i class="bx bx-eraser"></i> Limpar</a>
                    </div>
                </div>
                {!!Form::close()!!}
                <div class="card mt-3">
                    <div class="card-body">
                        <h4>Lista de Vendas</h4>
                        <div class="table-responsive">
                            <table class="table mb-0 table-striped">
                                <thead>
                                    <tr>
                                        <th>Cliente</th>
                                        <th>CPF/CPJ</th>
                                        <th>Total</th>
                                        <th>Estado</th>
                                        <th>Chave</th>
                                        <th>Núm. NFe</th>
                                        <th>Data emissão</th>
                                        <th>#</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($data as $item)
                                    <tr>
                                        <td>{{ $item->cliente->razao_social }}</td>
                                        <td>{{ $item->cliente->cpf_cnpj }}</td>
                                        <td>{{ __moeda($item->valor_total-$item->desconto+$item->acrescimo) }}</td>
                                        <td>
                                            @if($item->estado_emissao== 'disponivel')
                                            <span class="btn btn-primary btn-sm">Disponível</span>

                                            @elseif($item->estado_emissao == 'aprovado')
                                            <span class="btn btn-success btn-sm">Aprovado</span>
                                            @elseif($item->estado_emissao == 'cancelado')
                                            <span class="btn btn-danger btn-sm">Cancelado</span>
                                            @else
                                            <span class="btn btn-warning btn-sm">Rejeitado</span>
                                            @endif
                                        </td>
                                        <td>{{ $item->chave }}</td>
                                        <td>{{ $item->numero_nfe }}</td>
                                        <td>{{ $item->created_at }}</td>
                                        <td>
                                            @if($item->estado_emissao == 'aprovado')
                                            <a title="Download XML" class="btn btn-light btn-sm" href="{{ route('contador.venda-download-xml', $item->id) }}">
                                                <i class="bx bx-download"></i>
                                            </a>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8" class="text-center">Nada encontrado</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            @if($estado_emissao == 'aprovado' || $estado_emissao == 'cancelado')
                            <form method="get" action="{{ route('contador.download-xml-nfe') }}">
                                <input type="hidden" name="estado_emissao" value="{{ $estado_emissao }}">
                                <input type="hidden" name="cliente" value="{{ $cliente }}">
                                <input type="hidden" name="data_inicio" value="{{ $data_inicio }}">
                                <input type="hidden" name="data_fim" value="{{ $data_fim }}">
                                <button class="btn btn-success">
                                    <i class="bx bx-download"></i>
                                    Download XML
                                </button>
                            </form>
                            @endif
                            {!! $data->appends(request()->all())->links() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
