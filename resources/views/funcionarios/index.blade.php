@extends('default.layout',['title' => 'Funcionários'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <a href="{{ route('funcionarios.comissao')}}" type="button" class="btn btn-primary">
                        <i class="bx bx-money"></i> Comissão
                    </a>
                    <a href="{{ route('funcionarios.create')}}" type="button" class="btn btn-success">
                        <i class="bx bx-plus"></i> Novo funcionário
                    </a>
                </div>
            </div>
            <div class="col">
                <h6 class="mb-0 text-uppercase">Funcionários</h6>
                {!!Form::open()->fill(request()->all())
                ->get()
                !!}
                <div class="row">
                    <div class="col-md-3">
                        {!!Form::text('nome', 'Pesquisar por nome')
                        !!}
                    </div>
                    <div class="col-md-3">
                        {!!Form::text('cpf', 'Pesquisar por CPF')
                        ->attrs(['class' => 'cpf_cnpj'])
                        ->type('tel')
                        !!}
                    </div>
                    <div class="col-md-3 text-left ">
                        <br>
                        <button class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
                        <a id="clear-filter" class="btn btn-danger" href="{{ route('funcionarios.index') }}"><i class="bx bx-eraser"></i>Limpar</a>
                    </div>
                </div>
                {!!Form::close()!!}
                <hr />
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table mb-0 table-striped">
                                <thead class="">
                                    <tr>
                                        <th>Nome</th>
                                        <th>CPF</th>
                                        <th>Data de cadastro</th>
                                        <th>Celular</th>
                                        <th>Cidade</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($data as $item)
                                    <tr>
                                        <td>{{ $item->nome }}</td>
                                        <td>{{ $item->cpf }}</td>
                                        <td>{{ __data_pt($item->created_at) }}</td>
                                        <td>{{ $item->celular }}</td>
                                        <td>{{ $item->cidade->info }}</td>
                                        <td>
                                            <form action="{{ route('funcionarios.destroy', $item->id) }}" method="post" id="form-{{$item->id}}">
                                                @method('delete')
                                                <a href="{{ route('funcionarios.edit', $item) }}" class="btn btn-warning btn-sm text-white">
                                                    <i class="bx bx-edit"></i>
                                                </a>
                                                @csrf
                                                <button type="button" class="btn btn-delete btn-sm btn-danger">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center">Nada encontrado</td>
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
