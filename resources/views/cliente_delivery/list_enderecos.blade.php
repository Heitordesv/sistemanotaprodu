@extends('default.layout',['title' => 'Lista de Endereço de Delivery'])
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
                <h5 class="mb-0 text-primary">Endereços cliente delivery: {{ $item->nome }}</h5>
            </div>
            <hr>
            <div class="table-responsive">
                <table class="table mb-0 table-striped">
                    <thead class="">
                        <tr>
                            <th>Rua</th>
                            <th>Bairro</th>
                            <th>Referência</th>
                            <th>Latitude</th>
                            <th>Longitude</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($item->enderecos as $i)
                        <tr>
                            <td>{{ $i->rua }}</td>
                            <td>{{ $i->bairro() }}</td>
                            <td>{{ $i->referencia }}</td>
                            <td>{{ $i->latitude }}</td>
                            <td>{{ $i->longitude }}</td>
                            <td>
                                <a href="{{ route('enderecoDelivery.edit', $i) }}" class="btn btn-warning btn-sm text-white">
                                    <i class="bx bx-edit"></i>
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
@endsection
