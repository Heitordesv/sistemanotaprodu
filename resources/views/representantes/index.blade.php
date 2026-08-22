@extends('default.layout',['title' => 'Representante'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <a href="{{ route('representantes.create')}}" type="button" class="btn btn-success">
                        <i class="bx bx-plus"></i> Novo representante
                    </a>
                </div>
            </div>
            <div class="col">
                {!!Form::open()->fill(request()->all())
                ->get()
                !!}
                <div class="row">
                    <div class="col-md-5">
                        {!!Form::text('nome', 'Pesquisar por nome')
                        !!}
                    </div>
                    <div class="col-md-3">
                        {!!Form::select('estado', 'Estado', ['todos' => 'Todos', 'ativo' => 'Ativo', 'desativo' => 'Desativo'])->attrs(['class' => 'form-select'])
                        !!}
                    </div>
                    <div class="col-md-4 text-left ">
                        <br>
                        <button class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
                        <a id="clear-filter" class="btn btn-danger" href="{{ route('representantes.index') }}"><i class="bx bx-eraser"></i> Limpar</a>
                    </div>
                </div>
                {!!Form::close()!!}
                <hr />
                <div>
                    <h5>Lista de representantes</h5>
                    <p style="color: mediumblue">Registros: {{ sizeof($data) }} </p>
                </div>
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive tbl-400">
                            <table class="table mb-0 table-striped">
                                <thead class="">
                                    <tr>
                                        <th>#</th>
                                        <th>Ações</th>
                                        <th>Nome</th>
                                        <th>Data cadastro</th>
                                        <th>Endereço</th>
                                        <th>Cidade</th>
                                        <th>Comissão %</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($data as $item)
                                    <tr>
                                        <td>{{ $item->id }}</td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Ações</button>
                                                <ul class="dropdown-menu" style="z-index: 999">
                                                    <form action="{{ route('representantes.destroy', $item->id) }}" method="post" id="form-{{$item->id}}">
                                                        @method('delete')
                                                        @csrf
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('representantes.show', $item->id) }}">Detalhes</a>
                                                        </li>
                                                
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('representantes.financeiro', $item->id) }}">Financeiro</a>
                                                        </li>
                                                        <button class="dropdown-item btn-delete">
                                                            Remover
                                                        </button>
                                                    </form>
                                                </ul>
                                            </div>
                                        </td>
                                        <td>{{ $item->nome }}</td>
                                        <td>{{ $item->created_at }}</td>
                                        <td>{{ $item->rua }}, {{ $item->numero }} - {{ $item->bairro }}</td>
                                        <td>{{ $item->cidade->nome }}</td>
                                        <td>{{ $item->comissao }}</td>
                                        <td>
                                            <span class="codigo" style="width: 100px;">
                                                @if($item->status)
                                                <span class="btn btn-success btn-sm">
                                                    ATIVO
                                                </span>
                                                @else
                                                <span class="btn btn-danger btn-sm">
                                                    DESATIVADO
                                                </span>
                                                @endif
                                            </span>
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
                    </div>
                </div>
            </div>
            {!! $data->appends(request()->all())->links() !!}
        </div>
    </div>
</div>
@endsection
