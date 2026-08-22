@extends('default.layout', ['title' => 'Visualizando remessa'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <a href="{{ route('remessa-boletos.index')}}" type="button" class="btn btn-light btn-sm">
                        <i class="bx bx-arrow-back"></i> Voltar
                    </a>
                </div>
            </div>
            <div class="col">
                <h6 class="mb-0 text-uppercase">Remessas</h6>
                <hr />
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table mb-0 table-striped">
                                <thead class="">
                                    <tr>
                                        <th>Cliente</th>
                                        <th>Valor</th>
                                        <th>Vencimento</th>
                                        <th>Banco</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($item->boletos as $b)
                                    <tr>
                                        <td>{{ $b->boleto->conta->getCliente()->razao_social }}</td>
                                        <td>{{ __moeda($b->boleto->conta->valor_integral) }}</td>
                                        <td>{{ __data_pt($b->boleto->conta->data_vencimento) }}</td>
                                        <td>{{ $b->boleto->banco->banco }}</td>
                                        <td>
                                            <a target="_blank" href="{{ route('boletos.print', $b->boleto->id) }}" class="btn btn-dark btn-sm text-white">
                                                <i class="bx bx-printer"></i>
                                            </a>
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
