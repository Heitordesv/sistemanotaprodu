@extends('default.layout',['title' => 'Contador'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <a href="{{ route('contadores.create')}}" type="button" class="btn btn-success">
                        <i class="bx bx-plus"></i> Novo contador
                    </a>
                </div>
            </div>
            <div class="col">
                {!!Form::open()->fill(request()->all())
                ->get()
                !!}
                <div class="row">
                    <div class="col-md-4">
                        {!!Form::text('nome', 'Pesquisar')
                        !!}
                    </div>
                    <div class="col-md-3 text-left ">
                        <br>
                        <button class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pequisar</button>
                        <a id="clear-filter" class="btn btn-danger" href="{{ route('contadores.index') }}"><i class="bx bx-eraser"></i> Limpar</a>
                    </div>
                </div>
                {!!Form::close()!!}
                <br>
                <hr>
                <h5 class="mt-3">Lista de contadores</h5>
                <p style="color: mediumblue">Registros: {{ sizeof($data) }}</p>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table mb-0 table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Razão social</th>
                                    <th>Nome fantasia</th>
                                    <th>CNPJ</th>
                                    <th>Cidade</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($data as $item)
                                <tr>
                                    <td>{{ $item->id }}</td>
                                    <td>{{ $item->razao_social }}</td>
                                    <td>{{ $item->nome_fantasia }}</td>
                                    <td>{{ $item->cnpj }}</td>
                                    <td>{{ $item->cidade->nome }}</td>
                                    <td>
                                        <form action="{{ route('contadores.destroy', $item->id) }}" method="post" id="form-{{$item->id}}">
                                            @method('delete')
                                            @csrf
                                            <a href="{{ route('contadores.edit', $item) }}" class="btn btn-warning btn-sm text-white">
                                                <i class="bx bx-edit"></i>
                                            </a>
                                            <button class="btn btn-danger btn-sm"><i class="bx bx-trash"></i></button>
                                            <a href="{{ route('contadores.show', $item->id) }}" class="btn btn-info btn-sm"><i class="bx bx-show"></i></a>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">Nada encontrado</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
