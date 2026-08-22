@extends('default.layout', ['title' => 'Todos Apontamentos'])
@section('content')
<div class="page-content">
    <div class="card border-top border-0 border-4 border-primary">
        <div class="card-body p-5">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <a href="{{ route('estoque.apontamentoProducao') }}" type="button" class="btn btn-light btn-sm">
                        <i class="bx bx-arrow-back"></i> Voltar
                    </a>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-3">
                    {!! Form::date('start_date', 'Data Inicial') !!}
                </div>
                <div class="col-md-3">
                    {!! Form::date('end_date', 'Data Final') !!}
                </div>
                <div class="col-md-3">
                    <br>
                    <button type="btn" class="btn btn-primary"><i class="bx bx-search"></i> Pesquisar</button>
                </div>
            </div>
            <h5 class="mt-5">Todos os Apontamentos</h5>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th>Quantidade</th>
                            <th>Data Registro</th>
                            <th>Un de Compra</th>
                            <th>Un de Venda</th>
                            <th>Valor de Venda</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($data as $item)
                        <tr>
                            <td>{{ $item->produto->nome }}</td>
                            <td>{{ $item->quantidade }}</td>
                            <td>{{ __data_pt($item->created_at, 0) }}</td>
                            <td>{{ $item->produto->unidade_compra }}</td>
                            <td>{{ $item->produto->unidade_venda }}</td>
                            <td>{{ __moeda($item->produto->valor_venda) }}</td>
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
