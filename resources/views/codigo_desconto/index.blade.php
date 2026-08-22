@extends('default.layout',['title' => 'Cupom de Desconto'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <a href="{{ route('codigoDesconto.create')}}" type="button" class="btn btn-success">
                        <i class="bx bx-plus"></i> Novo cupom de desconto
                    </a>
                </div>
            </div>
            <hr>
            <div class="col">
                <h6 class="mb-0 text-uppercase mt-4">Cupom de desconto</h6>
                <div class="card mt-2">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table mb-0 table-striped">
                                <thead class="">
                                    <tr>
                                        <th>Descrição</th>
                                        <th>Cliente</th>
                                        <th>Código</th>
                                        <th>Expiração</th>
                                        <th>Status</th>
                                        <th>Tipo</th>
                                        <th>Valor</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($data as $item)
                                    <tr>
                                        <td>{{ $item->descricao }}</td>
                                        <td>{{ isset($item->cliente->nome) ? $item->cliente->nome : 'TODOS' }}</td>
                                        <td>{{ $item->codigo }}</td>
                                        <td>{{ isset($item->expiracao) ? __data_pt($item->expiracao, 0) : '--' }}</td>
                                        <td>{{ $item->ativo }}</td>
                                        <td>{{ $item->tipo }}</td>
                                        <td>{{ __moeda($item->valor) }}</td>
                                        <td>
                                            <form action="{{ route('codigoDesconto.destroy', $item->id) }}" method="post" id="form-{{ $item->id }}">
                                                @method('delete')
                                                <a href="{{ route('codigoDesconto.edit', $item) }}" class="btn btn-warning btn-sm text-white">
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
        </div>
    </div>
</div>
@endsection
