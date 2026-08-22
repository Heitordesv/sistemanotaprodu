@extends('default.layout',['title' => 'Produtos'])
@section('content')

<div class="page-content">
    <div class="card">
        <div class="card-body p-4">
            <div class="card-body">
                {!!Form::open()->fill(request()->all())
                ->get()
                !!}
                <div class="row">
                    <div class="col-md-6">
                        {!!Form::text('nome', 'Pesquisar por nome')
                        !!}
                    </div>
                    <div class="col-md-5 text-left ">
                        <br>
                        <button class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
                        <a id="clear-filter" class="btn btn-danger" href="{{ route('contador.produtos') }}"><i class="bx bx-eraser"></i> Limpar</a>
                    </div>
                </div>
                {!!Form::close()!!}
                <div class="card mt-3">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table mb-0 table-striped">
                                <thead>
                                    <tr>
                                        <th>Nome</th>
                                        <th>Valor de compra</th>
                                        <th>Valor de venda</th>
                                        <th>Código de barras</th>
                                        <th>Unidade de venda</th>
                                        <th>CFOP Interno</th>
                                        <th>CFOP Externo</th>
                                        <th>%ICMS</th>
                                        <th>%PIS</th>
                                        <th>%COFINS</th>
                                        <th>%IPI</th>
                                        <th>CST/CSOSN</th>
                                        <th>CST PIS</th>
                                        <th>CST COFINS</th>
                                        <th>CST IPI</th>
                                        <th>NCM</th>
                                        <th>CEST</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($data as $item)
                                    <tr>
                                        <td>{{ $item->nome }}</td>
                                        <td>{{ __moeda($item->valor_compra) }}</td>
                                        <td>{{ __moeda($item->valor_venda) }}</td>
                                        <td>{{ $item->codBarras }}</td>
                                        <td>{{ $item->unidade_venda }}</td>
                                        <td>{{ $item->CFOP_saida_estadual }}</td>
                                        <td>{{ $item->CFOP_saida_inter_estadual }}</td>
                                        <td>{{ $item->perc_icms }}</td>
                                        <td>{{ $item->perc_pis }}</td>
                                        <td>{{ $item->perc_cofins }}</td>
                                        <td>{{ $item->perc_ipi }}</td>
                                        <td>{{ $item->CST_CSOSN }}</td>
                                        <td>{{ $item->CST_PIS }}</td>
                                        <td>{{ $item->CST_COFINS }}</td>
                                        <td>{{ $item->CST_IPI }}</td>
                                        <td>{{ $item->NCM }}</td>
                                        <td>{{ $item->CEST }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="17" class="text-center">Nada encontrado</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            {!! $data->appends(request()->all())->links() !!}
        </div>
    </div>
</div>
@endsection
