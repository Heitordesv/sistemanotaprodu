@extends('default.layout', ['title' => 'Cidades'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <a href="{{ route('cidades.create') }}" type="button" class="btn btn-success">
                        <i class="bx bx-plus"></i> Nova cidade
                    </a>
                </div>
            </div>
            <div class="col">
                {!! Form::open()->fill(request()->all())->get() !!}
                <div class="row">
                    <div class="col-md-4">
                        {!! Form::text('nome', 'Pesquisar') !!}
                    </div>
                    <div class="col-md-6 text-left ">
                        <br>
                        <button class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
                        <a id="clear-filter" class="btn btn-danger" href="{{ route('cidades.index') }}"><i class="bx bx-eraser"></i> Limpar</a>
                    </div>
                </div>
                {!! Form::close() !!}
                <hr>
                <div class="mt-4">
                    <h5>Lista de cidades</h5>
                    <p style="color: mediumblue">Registros: {{ sizeof($data) }} de {{ $count }}</p>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table mb-0 table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nome</th>
                                    <th>UF</th>
                                    <th>Código IBGE</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($data as $item)
                                <tr>
                                    <td>{{ $item->id }}</td>
                                    <td>{{ $item->nome }}</td>
                                    <td>{{ $item->uf }}</td>
                                    <td>{{ $item->codigo }}</td>
                                    <td>
                                        <form action="{{ route('cidades.destroy', $item->id) }}" method="post" id="form-{{ $item->id }}">
                                            @method('delete')
                                            <a href="{{ route('cidades.edit', $item) }}" class="btn btn-warning btn-sm text-white">
                                                <i class="bx bx-edit"></i>
                                            </a>

                                            @csrf
                                            <button type="button" class="btn btn-delete btn-sm btn-danger">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            {!! $data->appends(request()->all())->links() !!}
        </div>
    </div>
</div>
@endsection
