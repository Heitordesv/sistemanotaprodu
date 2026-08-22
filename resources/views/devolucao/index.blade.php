@extends('default.layout', ['title' => 'Devoluções'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
            </div>

            <div class="col">
                <h6 class="mb-0 text-uppercase">Devoluções</h6>
                {!! Form::open()->fill(request()->all())->get() !!}
                <div class="row mt-3">
                    <div class="col-md-5">
                        {!! Form::select('fornecedor_id', 'Fornecedor') !!}
                    </div>
                    <div class="col-md-2">
                        {!! Form::date('start_date', 'Data inicial')->attrs(['class' => '']) !!}
                    </div>
                    <div class="col-md-2">
                        {!! Form::date('end_date', 'Data final')->attrs(['class' => '']) !!}
                    </div>
                    <div class="col-md-4 text-left ">
                        <br>
                        <button class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
                        <a id="clear-filter" class="btn btn-danger" href="{{ route('devolucao.index') }}"><i class="bx bx-eraser"></i> Limpar</a>
                    </div>
                </div>
                {!! Form::close() !!}
                <hr />
                <br>
                <div class="card">
                    <h6 class="mb-0 text-uppercase m-3">Lista de devoluções</h6>
                    <div class="col-md-3 m-3">
                        <a type="btn" class="btn btn-danger" href="{{ route('devolucao.create') }}"><i class="bx bx-plus"></i>
                            Nova devolução</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table mb-0 table-striped">
                                <thead class="">
                                    <tr>
                                        <th>#</th>
                                        <th>Fornecedor</th>
                                        <th>Usuário</th>
                                        <th>Valor integral</th>
                                        <th>Valor devolvido</th>
                                        <th>Estado</th>
                                        <th>Data</th>
                                        <th>Motivo</th>
                                        <th>Tipo</th>
                                        <th>NFe entrada</th>
                                        <th>NFe devolução</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($data as $item)
                                    <tr>
                                        <td>
                                            <input type="checkbox" value="{{$item->id}}" class="checkbox" name="" data-status="{{$item->estado_emissao}}" data-numero_nfe="{{$item->numero_gerado}}">
                                        </td>
                                        <td>{{ $item->fornecedor->razao_social }}</td>
                                        <td>{{ $item->usuario->nome }}</td>
                                        <td>{{ __moeda($item->valor_integral) }}</td>
                                        <td>{{ __moeda($item->valor_devolvido) }}</td>
                                        <td>{!! $item->estadoEmissao($item->estado) !!}</td>
                                        <td>{{ __data_pt($item->data_registro, 0) }}</td>
                                        <td>{{ $item->motivo }}</td>
                                        <td>{{ $item->tipo == 1 ? 'Saida' : 'Entrada'}}</td>
                                        <td>{{ $item->nNf }}</td>
                                        <td></td>
                                        <td>
                                            <form action="{{ route('devolucao.destroy', $item->id) }}" method="post" id="form-{{$item->id}}">
                                                @method('delete')
                                                @csrf
                                                <a href="{{ route('devolucao.edit', $item->id) }}" title="Editar" class="btn btn-warning btn-sm text-white">
                                                    <i class="bx bx-edit"></i>
                                                </a>
                                                <button type="submit" title="Deletar" class="btn btn-danger btn-delete btn-sm text-white">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                                <a href="{{ route('devolucao.estadoFiscal', $item->id) }}" title="Estado Fiscal" class="btn btn-info btn-sm text-white">
                                                    <i class="bx bx-detail"></i>
                                                </a></form>

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
                                <button id="btn-enviar" type="button" class="btn btn-success btn-action spinner-white spinner-right px-2" style="width: 100%" href="">Enviar</button>
                            </div>
                            <div class="col-md-2">
                                <button id="btn-imprimir" type="button" class="btn btn-outline-secondary btn-action spinner-white spinner-right" style="width: 100%" href="">Imprimir</button>
                            </div>
                            <div class="col-md-2">
                                <button id="btn-consultar" type="button" class="btn btn-primary btn-action spinner-white spinner-right" style="width: 100%" href="">Consultar</button>
                            </div>
                            <div class="col-md-2">
                                <button id="btn-cancelar" type="button" class="btn btn-danger btn-action spinner-white spinner-right" style="width: 100%" href="">Cancelar</button>
                            </div>
                            <div class="col-md-2">
                                <button id="btn-corrigir" type="button" class="btn btn-warning btn-action spinner-white spinner-right" style="width: 100%" href="">CC-e</button>
                            </div>
                            <div class="col-md-2">
                                <button id="btn-xml-temp" type="button" class="btn btn-info spinner-white btn-action spinner-right" style="width: 100%" href="">XML Temporário</button>
                            </div>
                            <div class="col-md-2">
                                <button id="btn-danfe-temp" type="button" class="btn btn-primary spinner-white btn-action spinner-right px-1" style="width: 100%" href="">Danfe Temporária</button>
                            </div>
                            <div class="col-md-2">
                                <button id="btn-imprimir-cce" type="button" class="btn btn-warning btn-action spinner-white spinner-right" style="width: 100%" href="">Impimir CC-e</button>
                            </div>
                            <div class="col-md-2">
                                <button id="btn-imprimir-cancela" type="button" class="btn btn-danger btn-action spinner-white spinner-right" style="width: 100%" href="">Impimir Cancela</button>
                            </div>
                        </div>
                    </div>
                </div>
                {!! $data->appends(request()->all())->links() !!}
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-cancelar" aria-modal="true" role="dialog" style="overflow:scroll;" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cancelar Devolução <strong class="text-danger numero_nfe"></strong></h5>
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
<div class="modal fade" id="modal-corrigir" aria-modal="true" role="dialog" style="overflow:scroll;" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Corrigir Devolução <strong class="text-warning numero_nfe"></strong></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="col-md-12">
                    {!! Form::text('motivo-corrige', 'Descrição da correção') !!}
                </div>
            </div>
            <div class="modal-footer">
                <button id="btn-corrige-send" type="button" class="btn btn-warning px-5">Corrigir</button>
            </div>
        </div>
    </div>
</div>

@endsection
@section('js')
<script type="text/javascript" src="/js/devolucao.js?v=1"></script>

@endsection
