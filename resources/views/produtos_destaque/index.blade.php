@extends('default.layout',['title' => 'Produtos Destaque'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="m-3">
                    <a href="{{ route('categoriasParaDestaque.indexCategoria')}}" type="button" class="btn btn-info">
                        <i class="bx bx-list-ul"></i> Categoria de destaque
                    </a>
                    <a href="{{ route('produtosDestaque.create')}}" type="button" class="btn btn-success">
                        <i class="bx bx-plus"></i> Produto de destaque
                    </a>
                </div>
            </div>
            <hr>
        </div>
        <div class="card m-3">
            <div class="table-resposive m-3">
                <h5>Lista de Produtos Destaques</h5>
                <table class="table mb-0 table-striped">
                    <thead>
                        <tr>
                            <th width="85%">Nome</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data as $item)
                            <tr>
                                <td>{{ $item->produto->produto->nome }}</td>
                           
                                <td>
                                    <form action="{{ route('produtosDestaque.destroy', $item->id) }}" method="post" id="form-{{$item->id}}">
                                        @method('delete')
                                        <a href="{{ route('produtosDestaque.edit', $item) }}" class="btn btn-warning btn-sm text-white">
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
</div>
@endsection
