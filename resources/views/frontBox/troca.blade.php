@extends('default.layout', ['title' => 'Trocas'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="col">
                    <h6 class="mb-0 text-uppercase">Lista de trocas de pdv</h6>
                    {!!Form::open()->fill(request()->all())
                    ->get()
                    !!}
                    <div class="row mt-3">
                        <div class="col-md-3">
                            {!!Form::date('start_date', 'Data da Troca')
                            !!}
                        </div>
                        <div class="col-md-3 text-left">
                            <br>
                            <button class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
                            <a id="clear-filter" class="btn btn-danger" href="{{ route('frenteCaixa.index') }}">Limpar</a>
                        </div>
                    </div>
                    {!!Form::close()!!}
                    <div class="mt-4">
                        <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#modal-troca_pdv">
                            Nova Troca
                        </button>
                    </div>
                </div>
            </div>
            <hr>
            <div class="table-responsive">
                <table class="table mb-0 table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Venda original</th>
                            <th>Venda original Nº</th>
                            <th>Venda gerada</th>
                            <th>Venda gerada Nº</th>
                            <th>Prod. removidos</th>
                            <th>Prod. adicionados</th>
                            <th>Data</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($data as $item)
                        <tr>
                            <td></td>
                        </tr>
                        @empty
                        <tr>
                            <td>
                            <td colspan="8" class="text-center">Nada encontrado</td>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@include('modals.frontBox._troca_pdv', ['not_submit' => true])

@endsection
