@extends('default.layout',['title' => 'Autor Post'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">

                <div class="ms-auto">
                    <a href="{{ route('autorPost.create')}}" type="button" class="btn btn-success">
                        <i class="bx bx-plus"></i> Novo Autor
                    </a>
                </div>
            </div>
            <div class="col">
                <h6 class="mb-0 text-uppercase">Lista de Autores Post</h6>
                <hr />
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table mb-0 table-striped">
                                <thead class="">
                                    <tr>
                                        <th></th>
                                        <th>Nome</th>
                                        <th>Tipo</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($data as $item)
                                    <td><img class="img-round" src="/uploads/autorPost/{{ $item->img }}"></td>
                                    <td>{{ $item->nome }}</td>
                                    <td>{{ $item->tipo }}</td>
                                    <td>
                                        <form action="{{ route('autorPost.destroy', $item->id) }}" method="post" id="form-{{$item->id}}">
                                            @method('delete')
                                            @csrf
                                            <a href="{{ route('autorPost.edit', $item) }}" class="btn btn-warning btn-sm text-white">
                                                <i class="bx bx-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-delete btn-sm btn-danger">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center">Nada encontrado</td>
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
