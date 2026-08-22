@extends('default.layout',['title' => 'Etiquetas'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <a href="{{ route('etiquetas.create')}}" type="button" class="btn btn-success">
                        <i class="bx bx-plus"></i> Nova etiqueta
                    </a>
                </div>
            </div>
            <hr>
            <div class="col">
                <h5 class="">Lista de etiquetas</h5>
                <p style="color: rgb(14, 14, 226)">Registros: {{ sizeof($data) }}</p>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table mb-0 table-striped">
                            <thead class="">
                                <tr>
                                    <th>Nome</th>
                                    <th>Largura</th>
                                    <th>Altura</th>
                                    <th>Tamanho da Fonte</th>
                                    <th>Tamanho do Cód. Barras</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($data as $item)
                                <tr>
                                    <td>{{ $item->nome }}</td>
                                    <td>{{ $item->largura }}</td>
                                    <td>{{ $item->altura }}</td>
                                    <td>{{ $item->tamanho_fonte }}</td>
                                    <td>{{ $item->tamanho_codigo_barras }}</td>
                                    <td>
                                        <form action="{{ route('etiquetas.destroy', $item->id) }}" method="post" id="form-{{$item->id}}">
                                            @method('delete')
                                            <a href="{{ route('etiquetas.edit', $item) }}" class="btn btn-warning btn-sm text-white">
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
                                    <td colspan="6" class="text-center">Nada encontrado</td>
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
@endsection
