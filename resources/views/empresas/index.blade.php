@extends('default.layout', ['title' => 'Empresas'])

@section('content')
<div class="page-content">
    <div class="card">
        <div class="card-body p-4">

            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <a href="{{ route('empresas.create') }}" class="btn btn-success">
                        <i class="bx bx-plus"></i> Nova empresa
                    </a>
                </div>
            </div>

            <div class="col">
                <h6 class="mb-0 text-uppercase">Empresas</h6>

                @if(env("APP_ENV") == "demo")
                    <p class="text-danger">Aplicação em modo demonstração</p>
                @endif

                {!! Form::open()->fill(request()->all())->get() !!}
                <div class="row mt-3">
                    <div class="col-md-4">
                        {!! Form::text('nome', 'Pesquisar por razão social') !!}
                    </div>
                    <div class="col-md-4">
                        {!! Form::text('fantasia', 'Pesquisar por nome fantasia') !!}
                    </div>
                    <div class="col-md-3">
                        {!! Form::text('cnpj', 'Pesquisar por CPF/CNPJ')
                            ->attrs(['class' => 'cpf_cnpj'])
                            ->type('tel') !!}
                    </div>
                    <div class="col-md-3 mt-3">
                        {!! Form::select('estado', 'Estado', ['' => 'Selecione'])
                            ->attrs(['class' => 'form-select']) !!}
                    </div>
                    <div class="col-md-3 mt-3">
                        {!! Form::select('planos', 'Planos', ['' => 'Selecione'] + $planos->pluck('nome', 'id')->all())
                            ->attrs(['class' => 'form-select']) !!}
                    </div>
                    <div class="col-md-2 mt-3">
                        {!! Form::text('dias_expiracao', 'Dias para expirar') !!}
                    </div>
                    <div class="col-md-3 text-left mt-3">
                        <br>
                        <button class="btn btn-primary" type="submit">
                            <i class="bx bx-search"></i> Pesquisar
                        </button>
                        <a id="clear-filter" class="btn btn-danger" href="{{ route('empresas.index') }}">
                            <i class="bx bx-eraser"></i> Limpar
                        </a>
                    </div>
                </div>
                <hr>
                {!! Form::close() !!}

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive tbl-400">
                            <h5>Lista de Empresas</h5>
                            <table class="table table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Ações</th>
                                        <th>Data Cadastro</th>
                                        <th>Nome/Razão Social</th>
                                        <th>Nome Fantasia</th>
                                        <th>CNPJ</th>
                                        <th>Telefone</th>
                                        <th>Cidade</th>
                                        <th>Plano</th>
                                        <th>Status</th>
                                        <th>Representante</th>
                                        <th>Contador</th>
                                        <th>Último Login</th>
                                        <th>Dias para Expirar</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($data as $item)
                                    <tr>
                                        <td>{{ $item->id }}</td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Ações</button>
                                                <ul class="dropdown-menu" style="z-index: 999">
                                                    <form action="{{ route('empresas.destroy', $item->id) }}" method="post" id="form-{{$item->id}}">
                                                        @method('delete')
                                                        @csrf
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('empresas.detalhes', $item->id) }}">Detalhes</a>
                                                        </li>
                                                        <li>
                                                            <a href="{{ route('empresas.alterarStatus', $item->id) }}" class="dropdown-item">
                                                                {{ $item->status ? 'Bloquear' : 'Desbloquear' }}
                                                            </a>
                                                        </li>
                                                        @if(!$item->isMaster())
                                                            <button class="dropdown-item btn-delete" type="submit">Remover</button>
                                                        @endif
                                                    </form>
                                                </ul>
                                            </div>
                                        </td>
                                        <td>{{ __data_pt($item->created_at) }}</td>

                                        @if(env("APP_ENV") != "demo")
                                            <td>{{ $item->razao_social }}</td>
                                            <td>{{ $item->nome_fantasia }}</td>
                                            <td>{{ $item->cpf_cnpj }}</td>
                                            <td>{{ $item->telefone }}</td>
                                        @else
                                            <td>--</td>
                                            <td>--</td>
                                            <td>--</td>
                                            <td>--</td>
                                        @endif

                                        <td>{{ $item->cidade ? $item->cidade->nome : '' }}</td>
                                     <td><a href="https://sistema.nfenotas.com.br/empresas/{{ $item->id }}/planos">Pagar Plano</a></td>
                                        <td>{{ $item->planoEmpresa ? $item->planoEmpresa->plano->nome : "--" }}</td>
                                        <td><button class="btn btn-info btn-sm">{{ $item->status ? "Ativo" : "Desativado" }}</button></td>
                                        <td><button class="btn btn-primary btn-sm">{{ $item->tipo_representante == 1 ? 'Sim' : 'Não' }}</button></td>
                                        <td>
                                            <button class="btn btn-sm {{ $item->tipo_contador == 1 ? 'btn-warning' : 'btn-success' }}">
                                                {{ $item->tipo_contador == 1 ? 'Sim' : 'Não' }}
                                            </button>
                                        </td>
                                        <td>{{ $item->ultimoLogin($item->id) ? $item->ultimoLogin($item->id)->created_at : '--' }}</td>
                                        <td>{{ $item->planoEmpresa ? __data_pt($item->planoEmpresa->expiracao, 0) : '--' }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="14" class="text-center">Nada encontrado</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {!! $data->appends(request()->all())->links() !!}
            </div>
        </div>
    </div>
</div>
@endsection

