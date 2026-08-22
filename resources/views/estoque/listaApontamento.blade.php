@extends('default.layout', ['title' => 'Alterações'])
@section('content')
    <div class="page-content">
        <div class="card ">
            <div class="card-body p-4">
                <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                </div>
                <h5 class="">Lista de Alterações</h5>

            </div>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Produto</th>
                                    <th>Categoria</th>
                                    <th>Quantidade Alterada</th>
                                    <th>Observação</th>
                                    <th>Tipo</th>
                                    <th>Usuário</th>
                                    <th>Data</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($data as $item)
                                    <tr>
                                        <td>{{ $item->produto->nome }}</td>
                                        <td>{{ $item->produto->categoria->nome }}</td>
                                        <td>{{ __estoque($item->quantidade) }}</td>
                                        <td>{{ $item->observacao }}</td>
                                        <td>{{ $item->tipo == 0 ? 'Redução Estoque' : 'Incremento Estoque' }}</td>
                                        <td>{{ $item->usuario->nome }}</td>
                                        <td>{{ __data_pt($item->created_at, 0) }}</td>
                                        <td>
                                            <form action="{{ route('estoque.apontamentoDestroy', $item->id) }}" method="post" id="form-{{$item->id}}">
                                                @method('delete')

                                                @csrf
                                                <button type="button" class="btn btn-delete btn-sm btn-danger">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                            </form>
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
            <div>
            </div>
        </div>
    </div>

@endsection
