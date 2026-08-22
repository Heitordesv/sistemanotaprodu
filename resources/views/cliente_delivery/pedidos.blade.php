@extends('default.layout',['title' => 'Lista de Pedidos'])
@section('content')
<div class="page-content">
    <div class="card border-top border-0 border-4 border-primary">
        <div class="card-body p-5">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <a href="{{ route('clientesDelivery.index')}}" type="button" class="btn btn-light btn-sm">
                        <i class="bx bx-arrow-back"></i> Voltar
                    </a>
                </div>
            </div>
            <div class="card-title d-flex align-items-center">
                <h5 class="mb-0 text-primary">Lista de Pedidos do Cliente: {{ $item->nome }}</h5>
            </div>
            <hr>
            <p class="mt-2">Registros: </p>
            <div class="card mt-2">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table mb-0 table-striped">
                            <thead class="">
                                <tr>
                                    <th>#</th>
                                    <th>Valor</th>
                                    <th>Data</th>
                                    <th>Forma de Pagamento</th>
                                    <th>Estado</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($item->pedidos as $i)
                                <tr>
                                    <td>{{ $i->id }}</td>
                                    <td>{{ $i->valor_total }}</td>
                                    <td>{{ $i->created_at }}</td>
                                    <td>{{ $i->forma_pagamento }}</td>
                                    <td>@if($i->estado == 'novo')
                                        <button class="btn btn-success btn-sm">Novo</button>
                                        @else
                                        <button class="btn btn-primary btn-sm">Finalizado</button>
                                        @endif
                                    </td>   
                                    <td>
                                        <a title="Ver Pedido" href="" class="btn btn-info btn-sm text-white">
                                            <i class="bx bx-show"></i>
                                        </a>
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
        </div>
    </div>
</div>
@endsection
