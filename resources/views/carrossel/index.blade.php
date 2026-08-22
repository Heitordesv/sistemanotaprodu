@extends('default.layout', ['title' => 'Carrossel'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <a href="{{ route('carrosselEcommerce.create') }}" type="button" class="btn btn-success">
                        <i class="bx bx-plus"></i> Novo Carrossel Ecommerce
                    </a>
                </div>
            </div>
            <div class="col mt-5">
                <h6 class="mb-0 text-uppercase">Lista de Carrossel de Ecommerce</h6>
                <hr />
            </div>
            <div class="table-responsive">
                <table class="table mb-0 table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Título</th>
                            <th>Link</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $item)
                        <tr>
                            <td><img class="img-round" src="uploads/carrosselEcommerce/{{ $item->img }}"></td>
                            <td>{{ $item->titulo }}</td>
                            <td>{{ $item->link_acao }}</td>
                            <td>
                                <form action="{{ route('carrosselEcommerce.destroy', $item->id) }}" method="post" id="form-{{$item->id}}">
                                    @method('delete')
                                    <a href="{{ route('carrosselEcommerce.edit', $item) }}" class="btn btn-warning btn-sm text-white">
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
                            <td colspan="4" class="text-center">Nada encontrado</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {!! $data->appends(request()->all())->links() !!}

        </div>
    </div>
</div>
@endsection
