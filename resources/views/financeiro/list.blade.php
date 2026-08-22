@extends('default.layout',['title' => 'Financeiro'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="col">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <h5>Lista de Pagamentos</h5>
                            <table class="table mb-0 table-striped">
                                <thead class="">
                                    <tr>
                                        <th>Empresa</th>
                                        <th>Data</th>
                                        <th>Plano</th>
                                        <th>Valor</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($data as $item)
                                        <tr>
                                            <td>{{ $item->empresa->nome }}</td>
                                            <td>{{ $item->created_at }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <h5 class="m-3">Soma: <strong style="color: blue">0,00</strong></h5>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
