@extends('default.layout',['title' => 'NFSe'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="col">
                <h6 class="mb-0 text-uppercase"></h6>
                {!!Form::open()->fill(request()->all())
                ->get()
                !!}
                <div class="row mt-3">
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
                        'novo' => 'Novo',
                        'rejeitado' => 'Rejeitadas',
                        'cancelado' => 'Canceladas',
                        'aprovado' => 'Aprovadas',
                        'processando' => 'Processando'
                        ])
                        ->attrs(['class' => 'select2'])
                        !!}
                    </div>
                    <div class="col-md-5 text-left">
                        <br>
                        <button class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
                        <a id="clear-filter" class="btn btn-danger" href="{{ route('vendas.index') }}"><i class="bx bx-eraser"></i> Limpar</a>
                    </div>
                </div>
                {!!Form::close()!!}
                <hr />
                <h6>Lista de NFSe</h6>
                <p>Registros: {{ sizeof($data) }}</p>
                <div class="row">
                    <div class="ms-auto">
                        <a href="{{ route('nfse.create')}}" type="button" class="btn btn-success">
                            <i class="bx bx-plus"></i> Nova NFSe
                        </a>
                    </div>
                </div>
                <div class="card mt-3">
                    <div class="card-body">
                        <div class="table-responsive tbl-400">
                            <table class="table mb-0 table-striped">
                                <thead class="">
                                    <tr>
                                        <th>#</th>
                                        <th>Tomador</th>
                                        <th>Valor total de serviço</th>
                                        <th>Estado</th>
                                        <th>Data de cadastro</th>
                                        <th>Número</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($data as $item)
                                    <tr>
                                        <td>
                                            <input type="checkbox" value="{{$item->id}}" data-email="{{$item->cliente}}" class="checkbox">
                                        </td>
                                        <td>{{ $item->razao_social }}</td>
                                        <td>{{ __moeda($item->valor_total) }}</td>
                                        <td>
                                            <span class="codigo" style="width: 120px;">
                                                @if($item->estado_emissao == 'novo')
                                                <span class="btn btn-primary btn-sm">Novo</span>

                                                @elseif($item->estado_emissao == 'aprovado')
                                                <span class="btn btn-success btn-sm">Aprovado</span>
                                                @elseif($item->estado_emissao == 'cancelado')
                                                <span class="btn btn-danger btn-sm">Cancelado</span>
                                                @elseif($item->estado_emissao == 'processando')
                                                <span class="btn btn-dark btn-sm">Processando</span>
                                                @elseif($item->estado_emissao == 'rejeitado')
                                                <span class="btn btn-warning btn-sm">Rejeitado</span>
                                                @endif
                                            </span>
                                        </td>
                                        <td>{{ $item->created_at }}</td>
                                        <td>
                                            <span class="codigo" style="width: 100px;">
                                                {{$item->numero_nfse > 0 ? $item->numero_nfse : '-' }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="row">
                                                <span style="width: 200px;">
                                                    @if($item->estado_emissao == 'novo' || $item->estado_emissao == 'rejeitado')
                                                    <form action="{{ route('nfse.destroy', $item->id) }}" method="post" id="form-{{$item->id}}">
                                                        @method('delete')
                                                        <a href="{{ route('nfse.edit', $item) }}" class="btn btn-warning btn-sm text-white">
                                                            <i class="bx bx-edit"></i>
                                                        </a>
                                                        @csrf
                                                        <button type="button" class="btn btn-delete btn-sm btn-danger">
                                                            <i class="bx bx-trash"></i>
                                                        </button>
                                                        @if($item->estado_emissao == 'aprovado')
                                                        <a target="_blank" href="/nfse/baixarXml/{{$item->id}}" class="btn btn-light btn-sm">
                                                            <i class="la la-download"></i>
                                                        </a>
                                                        @endif
                                                    </form>
                                                    @endif
                                                </span>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="12" class="text-center">Nada encontrado</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="row g-3 mt-2">
                            <div class="col-md-2">
                                <button id="btn-enviar" type="button" class="btn btn-info btn-action spinner-white spinner-right px-2" style="width: 100%" href="">Transmitir</button>
                            </div>
                            <div class="col-md-2">
                                <button id="btn-imprimir" class="btn btn-secondary btn-action spinner-white spinner-right" style="width: 100%">Imprimir</button>
                            </div>
                            <div class="col-md-2">
                                <button id="btn-consultar" type="button" class="btn btn-primary btn-action spinner-white spinner-right" style="width: 100%">Consultar</button>
                            </div>
                            <div class="col-md-2">
                                <button id="btn-cancelar" type="button" class="btn btn-danger btn-action spinner-white spinner-right" style="width: 100%">Cancelar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            {!! $data->appends(request()->all())->links() !!}
        </div>
    </div>
</div>

<div class="modal fade" id="modal-cancelar" aria-modal="true" role="dialog" style="overflow:scroll;" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">CANCELAR NFSe</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="col-md-6">
                    {!! Form::select('motivo', 'Motivo',
                    [
                    '1' => 'Erro na emissão',
                    '2' => 'Serviço não prestado',
                    '4' => 'Duplicidade de nota',
                    ])->attrs(['class' => 'form-select']) !!}
                </div>
            </div>
            <div class="modal-footer">
                <button id="btn-cancelar-send" type="button" class="btn btn-danger px-5">Cancelar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script type="text/javascript" src="/js/nfse.js"></script>
@endsection
