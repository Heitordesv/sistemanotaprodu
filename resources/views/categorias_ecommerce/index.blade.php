@extends('default.layout', ['title' => 'Categorias Ecommerce'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <a href="{{ route('categoriaEcommerce.create') }}" type="button" class="btn btn-success">
                        <i class="bx bx-plus"></i> Nova categoria ecommerce
                    </a>
                </div>
            </div>
            <div class="col">
                <h6 class="mb-0 text-uppercase">Categorias Ecommerce</h6>
                {!! Form::open()->fill(request()->all())->get() !!}
                <div class="row">
                    <div class="col-md-3">
                        {!! Form::text('nome', 'Pesquisar por nome') !!}
                    </div>
                    <div class="col-md-3 text-left ">
                        <br>
                        <button class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
                        <a id="clear-filter" class="btn btn-danger" href="{{ route('categoriaEcommerce.index') }}"><i class="bx bx-eraser"></i> Limpar</a>
                    </div>
                </div>
                {!! Form::close() !!}
                <hr />
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table mb-0 table-striped">
                                <thead class="">
                                    <tr>
                                        <th></th>
                                        <th width="75%">Nome</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($data as $item)
                                    <tr>
                                        <td><img class="img-round" src="{{ $item->img }}"></td>
                                        <td>{{ $item->nome }}</td>
                                        <td>
                                            <form action="{{ route('categoriaEcommerce.destroy', $item->id) }}" method="post" id="form-{{ $item->id }}">
                                                @method('delete')
                                                <a href="{{ route('categoriaEcommerce.edit', $item) }}" class="btn btn-warning btn-sm text-white">
                                                    <i class="bx bx-edit"></i>
                                                </a>
                                                @csrf
                                                <button type="button" class="btn btn-delete btn-sm btn-danger">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                                <a href="{{ route('subCategoriaEcommerce.index', [$item->id]) }}" class="btn btn-info btn-sm text-white" id="info">
                                                    <i class="bx bx-menu-alt-left"></i>
                                                </a>
                                            </form>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="3" class="text-center">Nada encontrado</td>
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
