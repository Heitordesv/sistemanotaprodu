@extends('default.layout',['title' => 'Locação'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <a href="{{ route('locacao.create')}}" type="button" class="btn btn-success">
                        <i class="bx bx-plus"></i> Nova locação
                    </a>
                </div>
            </div>
            <div class="col">
                <h5 class="">Locação</h5>
                <p style="color: rgb(14, 14, 226)">Locações: {{ sizeof($data) }}</p>
                {!!Form::open()->fill(request()->all())
                ->get()
                !!}
                <div class="row">
                    <div class="col-md-5">
                        {!!Form::text('nome', 'Pesquisar por nome')
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
                        {!!Form::select('status', 'Estado',
                        ['' => 'Todos',
                        '0' => 'Novo',
                        '1' => 'Finalizado',
                        ])
                        ->attrs(['class' => 'select2'])
                        !!}
                    </div>
                    <div class="col-md-3 text-left ">
                        <br>
                        <button class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
                        <a id="clear-filter" class="btn btn-danger" href="{{ route('locacao.index') }}"><i class="bx bx-eraser"></i>Limpar</a>
                    </div>
                </div>

                {!!Form::close()!!}

                <hr />
                <div class="row">
                    @foreach($data as $e)
                    <div class="col-4">
                        <div class="card radius-10">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div>
                                        <h5>
                                            R$ {{ __moeda($e->total)}}
                                        </h5>
                                    </div>
                                    <div class="dropdown ms-auto">
                                        <div class="dropdown-toggle dropdown-toggle-nocaret cursor-pointer" data-bs-toggle="dropdown"> <i class='bx bx-dots-horizontal-rounded font-22'></i>
                                        </div>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="{{ route('locacao.edit', $e->id) }}">Editar</a>
                                            </li>
                                            <li>
                                                <a onclick='swal("Atenção!", "Deseja remover este registro?", "warning").then((sim) => {if(sim){ location.href="/locacao/delete/{{ $e->id }}" }else{return false} })' href="#!" class="dropdown-item">
                                                Excluir
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="m-1">
                                <span class="">Cliente:</span>
                                <a target="_blank" class="text-success">
                                    {{$e->cliente->razao_social}}
                                </a>
                            </div>
                            <div class="m-1">
                                <span class="kt-widget__label">Status:</span>
                                <a target="_blank" class="text-success">
                                    @if($e->status)
                                    <span class="btn btn-success btn-sm">FINALIZADO</span>
                                    @else
                                    <span class="btn btn-info btn-sm">NOVO</span>
                                    @endif
                                </a>
                            </div>
                            <div class="m-1">
                                <span class="kt-widget__label">Início:</span>
                                <a target="_blank" class="text-success">
                                    {{ __data_pt($e->inicio, 0) }}
                                </a>
                            </div>
                            <div class="m-1">
                                <span class="kt-widget__label">Fim:</span>
                                <a target="_blank" class="text-danger">
                                    @if($e->fim != '1969-12-31')
                                    {{ __data_pt($e->fim, 0) }}
                                    @else
                                    --
                                    @endif
                                </a>
                            </div>
                            <div class="card-footer">
                                <a style="width: 100%;" href="{{ route('locacao.itens', $e->id) }}" class="btn btn-light-primary">
                                    Detalhes
                                </a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        {!! $data->appends(request()->all())->links() !!}
    </div>
</div>

@endsection
