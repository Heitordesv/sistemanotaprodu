@extends('default.layout',['title' => 'Cupom de Desconto Ecommerce'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <a href="{{ route('cuponsEcommerce.create')}}" type="button" class="btn btn-success">
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
                                        <th>Código</th>
                                        <th>Status</th>
                                        <th>Tipo</th>
                                        <th>Valor</th>
                                        <th>Valor Mínimo Pedido</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($data as $item)
                                    <tr>
                                        <td>{{ $item->descricao }}</td>
                                        <td>{{ $item->codigo }}</td>
                                        <td>
                                            @if($item->status)
                                            <span class="btn btn-success btn-sm">Ativo</span>
                                            @else
                                            <span class="btn btn-warning btn-sm">Desativado</span>
                                            @endif
                                        </td>
                                        <td>{{ $item->tipo }}</td>
                                        <td>{{ __moeda($item->valor) }}</td>
                                        <td>{{ __moeda($item->valor_minimo_pedido) }}</td>
                                        <td>
                                            <form action="{{ route('cuponsEcommerce.destroy', $item->id) }}" method="post" id="form-{{ $item->id }}">
                                                @method('delete')
                                                <a href="{{ route('cuponsEcommerce.edit', $item) }}" class="btn btn-warning btn-sm text-white">
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
