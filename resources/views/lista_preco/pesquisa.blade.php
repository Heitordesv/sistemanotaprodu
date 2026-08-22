@extends('default.layout',['title' => 'Pesquisa de Preços'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <a href="{{ route('listaDePrecos.index')}}" type="button" class="btn btn-light btn-sm">
                        <i class="bx bx-arrow-back"></i> Voltar
                    </a>
                </div>
            </div>
            <div class="col">
                <h6 class="mb-0 text-uppercase">Pesquisa de Preço</h6>
                {!!Form::open()
                ->get()
                ->route('listaDePrecos.filtro')
                !!}
                <div class="row">
                    <div class="col-md-3">
                        {!!Form::text('nome', 'Produto')
                        !!}
                    </div>
                    <div class="col-md-3">
                        {!!Form::select('lista_id', 'Lista', $listas->pluck('nome', 'id')->all())
                        ->attrs(['class' => 'select2'])
                        !!}
                    </div>
                    <div class="col-md-3">
                        <br>
                        <button id="clear-filter" class="btn btn-primary" type="submit"><i class="bx bx-search"></i> Consultar</button>
                    </div>
                </div>
                {!!Form::close()!!}
                <hr>
                <div class="card">
                    <div class="card-body">
                        {{-- <p>Registros: {{ sizeof($resultados) }}</p> --}}
                        <div class="table-responsive">
                            <table class="table mb-0 table-striped">
                                <thead class="">
                                    <tr>
                                        <th>Produto</th>
                                        <th>Valor Venda (PADRÃO)</th>
                                        <th>Valor de Compra</th>
                                        <th>Valor Venda da Lista</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($resultados as $item)
                                    <tr>
                                        <td>{{ $item->nome }}</td>
                                        <td>{{ __moeda($item->valor_venda) }}</td>
                                        <td>{{ __moeda($item->valor_compra) }}</td>
                                        <td>{{ $item->valor_lista }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center">Nada encontrado</td>
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
</div>
@endsection
