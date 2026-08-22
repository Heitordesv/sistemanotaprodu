@extends('default.layout',['title' => 'Lista de CTe'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <a href="{{ route('cte.create')}}" type="button" class="btn btn-success">
                        <i class="bx bx-plus"></i> Nova CTe
                    </a>
                </div>
            </div>
            <hr>
            <div class="col">
                <h6 class="mb-0 text-uppercase mt-4">LISTA DE CTe</h6>
                {!!Form::open()->fill(request()->all())
                ->get()
                !!}
                <div class="row mt-3">
                    <div class="col-md-3 mt-3">
                        {!!Form::date('start_date', 'Data inicial')
                        !!}
                    </div>
                    <div class="col-md-3 mt-3">
                        {!!Form::date('start_date', 'Data final')
                        !!}
                    </div>
                    <div class="col-md-3 mt-3">
                        {!!Form::select('estado', 'Estado',
                        [
                        'novo' => 'Disponiveis',
                        'rejeitado' => 'Rejeitadas',
                        'canccelado' => 'Canceladas',
                        'aprovado' => 'Aprovadas',
                        '' => 'Todas'
                        ])->attrs(['class' => 'form-select'])
                        !!}
                    </div>
                    @if(empresaComFilial())
                    {!! __view_locais_select_filtro("Local", isset($filial_id) ? $filial_id : '') !!}
                    @endif
                    <div class="col-md-4 text-left">
                        <br>
                        <button class="btn btn-primary" type="submit"><i class="bx bx-search"></i>Pesquisar</button>
                        <a id="clear-filter" class="btn btn-danger" href="{{ route('cte.index') }}"><i class="bx bx-eraser"></i> Limpar</a>
                    </div>
                </div>

                {!!Form::close()!!}

                <hr />
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive tbl-400">
                            <table class="table mb-0 table-striped">
                                <thead class="">
                                    <tr>
                                        <th>#</th>
                                        <th>Ações</th>
                                        <th>Destinatário</th>
                                        <th>Remetente</th>
                                        <th>Valor serviço</th>
                                        <th>Valor a receber</th>
                                        <th>Status de pagamento</th>
                                        <th>Estado fiscal</th>
                                        @if(empresaComFilial())
                                        <th>Local</th>
                                        @endif
                                        <th>Data cadastro</th>
                                        <th>Tomador</th>
                                        <th>Nº</th>
                                        <th>Saldo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($data as $item)
                                    <tr>
                                        <td>
                                            <input type="checkbox" value="{{$item->id}}" class="checkbox" name="" data-status="{{$item->estado_emissao}}" data-numero_cte="{{$item->cte_numero}}">
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Ações</button>
                                                <ul class="dropdown-menu" style="z-index: 999">
                                                    <form action="{{ route('cte.destroy', $item->id) }}" method="post" id="form-{{$item->id}}">
                                                        @method('delete')
                                                        @csrf
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('cte.edit', $item) }}">Editar</a>
                                                        </li>
                                                        <li>
                                                            <button type="button" class="dropdown-item btn-delete">Remover</button>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('cte.detalhes', $item->id) }}" id="info">Detalhes</a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('cte.custos', $item->id) }}">Custos</a>
                                                        </li>
                                                        {{-- <li>
                                                            <a class="dropdown-item" href="{{ route('cte.state-fiscal', $item->id) }}">Alterar estado fiscal</a>
                                                        </li> --}}
                                                    </form>
                                                </ul>
                                            </div>
                                        </td>
                                        <td>{{ $item->destinatario->razao_social }}</td>
                                        <td>{{ $item->remetente->razao_social }}</td>
                                        <td>{{ __moeda($item->valor_transporte) }}</td>
                                        <td>{{ __moeda($item->valor_receber) }}</td>
                                        <td>
                                            <span class="codigo" style="width: 100px;">
                                                @if($item->status_pagamento)
                                                <span class="btn btn-success btn-sm">Pago</span>
                                                @else
                                                <span class="btn btn-danger btn-sm">Pendente</span>
                                                @endif
                                            </span>
                                        </td>
                                        <td>{!! $item->estadoEmissao() !!}</td>
                                        @if(empresaComFilial())
                                        <td>
                                            <span>
                                                {{ $item->filial_id ? $item->filial->descricao : 'Matriz' }}
                                            </span>
                                        </td>
                                        @endif
                                        <td>{{ __data_pt($item->created_at, 0) }}</td>
                                        <td>{{ $item->getTomador() }}</td>
                                        <td>
                                            <span class="codigo" style="width: 100px;">
                                                {{$item->cte_numero > 0 ? $item->cte_numero : '--' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="codigo" style="width: 100px;">
                                                {{ __moeda($item->somaReceita() - $item->somaDespesa(), 2) }}
                                            </span>
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
                                <button id="btn-enviar" type="button" class="btn btn-success spinner-white spinner-right px-2" disabled style="width: 100%">Transmitir</button>
                            </div>
                            <div class="col-md-2">
                                <button id="btn-imprimir" type="button" class="btn btn-outline-secondary spinner-white spinner-right" disabled style="width: 100%">Imprimir</button>
                            </div>
                            <div class="col-md-2">
                                <button id="btn-consultar" type="button" class="btn btn-primary spinner-white spinner-right" disabled style="width: 100%">Consultar</button>
                            </div>
                            <div class="col-md-2">
                                <button id="btn-cancelar" type="button" class="btn btn-danger spinner-white spinner-right" style="width: 100%">Cancelar</button>
                            </div>
                            <div class="col-md-2">
                                <button id="btn-corrigir" type="button" class="btn btn-warning spinner-white spinner-right" style="width: 100%">CC-e</button>
                            </div>
                            <div class="col-md-2">
                                <button id="btn-inutilizar" type="button" class="btn btn-outline-secondary spinner-white spinner-right" style="width: 100%">Inutilizar</button>
                            </div>
                            {{-- e-mail --}}
                            <div class="col-md-2">
                                <button id="btn-enviar-email" type="button" data-bs-toggle="modal" data-bs-target="#modal-emailCte" class="btn btn-primary spinner-white btn-action spinner-right" style="width: 100%">Enviar E-mail</button>
                            </div>
                            <div class="col-md-2">
                                <button id="btn-imprimir-cce" type="button" class="btn btn-warning spinner-white spinner-right" disabled style="width: 100%">Imprimir CC-e</button>
                            </div>
                            <div class="col-md-2">
                                <button id="btn-imprimir-cancela" type="button" class="btn btn-danger spinner-white spinner-right" disabled style="width: 100%">Imprimir Cancela</button>
                            </div>
                            <div class="col-md-2">
                                <button id="btn-xml-temp" type="button" class="btn btn-outline-secondary spinner-white spinner-right" disabled style="width: 100%">XML Temporário</button>
                            </div>
                            <div class="col-md-2">
                                <button id="btn-dacte-temp" type="button" class="btn btn-info spinner-white spinner-right" disabled style="width: 100%">Dacte Temporário</button>
                            </div>
                            <div class="col-md-2">
                                <button id="btn-baixar-xml" type="button" class="btn btn-success btn-action spinner-white spinner-right px-2" style="width: 100%">Baixar XML</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @isset($data->appends)
            {!! $data->appends(request()->all())->links() !!}
            @endisset
        </div>
    </div>
</div>

<div class="modal fade" id="modal-corrigir" aria-modal="true" role="dialog" style="overflow:scroll;" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Corrigir CTe <strong class="text-warning numero_cte"></strong></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        {!! Form::select('grupo', 'Grupo')
                        ->attrs(['class' => 'form-select'])
                        ->options(App\Models\Cte::gruposCte()) !!}
                    </div>
                    <div class="col-md-6">
                        {!! Form::text('campo', 'Campo') !!}
                    </div>
                    <div class="col-md-12">
                        {!! Form::text('motivo-corrige', 'Descrição da correção') !!}
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button id="btn-corrige-send" type="button" class="btn btn-warning px-5">Corrigir</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-cancelar" aria-modal="true" role="dialog" style="overflow:scroll;" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cancelar CTe <strong class="text-danger numero_cte"></strong></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="col-md-12">
                    {!! Form::text('motivo-cancela', 'Justificativa') !!}
                </div>
            </div>
            <div class="modal-footer">
                <button id="btn-cancelar-send" type="button" class="btn btn-danger px-5">Cancelar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-inutilizar" aria-modal="true" role="dialog" style="overflow:scroll;" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">INUTILIZAÇÃO DE NÚMERO(s) DE CTe</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-3">
                        {!! Form::tel('numero_inicial', 'Nº inicial') !!}
                    </div>
                    <div class="col-md-3">
                        {!! Form::tel('numero_final', 'Nº final') !!}
                    </div>
                    <div class="col-md-12 mt-3">
                        {!! Form::text('motivo-inutiliza', 'Justificativa') !!}
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button id="btn-inutiliza-send" type="button" class="btn btn-primary px-5">Inutilizar</button>
            </div>
        </div>
    </div>
</div>

@include('modals._emailCte', ['not_submit' => true])

@endsection

@section('js')
<script src="/js/cte.js"></script>
@endsection
