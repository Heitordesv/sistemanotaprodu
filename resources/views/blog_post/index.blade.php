@extends('default.layout',['title' => 'Blog Post'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">

                <div class="ms-auto">
                    <a href="{{ route('postBlog.create')}}" type="button" class="btn btn-success">
                        <i class="bx bx-plus"></i> Novo Post
                    </a>
                </div>
            </div>
            <div class="col">
                <h6 class="mb-0 text-uppercase">Lista de Posts</h6>
                <hr />
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table mb-0 table-striped">
                                <thead class="">
                                    <tr>
                                        <th></th>
                                        <th>Título</th>
                                        <th>Categoria</th>
                                        <th>Autor</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($data as $item)
                                    <tr>
                                        <td><img class="img-round" src="/uploads/postBlog/{{ $item->img }}"></td>
                                        <td>{{ $item->titulo }}</td>
                                        <td>{{ $item->categoria->nome }}</td>
                                        <td>{{ $item->autor->nome }}</td>
                                        <td>
                                            <form action="{{ route('postBlog.destroy', $item->id) }}" method="post" id="form-{{$item->id}}">
                                                @method('delete')
                                                @csrf
                                                <a href="{{ route('postBlog.edit', $item) }}" class="btn btn-warning btn-sm text-white">
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
                                        <td colspan="5" class="text-center">Nada encontrado</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        {!! $data->appends(request()->all())->links() !!}

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
