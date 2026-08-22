@extends('default.layout', ['title' => 'Nova Lista'])
@section('content')
    <div class="page-content">
        <div class="card border-top border-0 border-4 border-primary">
            <div class="card-body p-5">
                <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                    <div class="ms-auto">
                        <a href="{{ route('listaDePrecos.index') }}" type="button" class="btn btn-light btn-sm">
                            <i class="bx bx-arrow-back"></i> Voltar
                        </a>
                    </div>
                </div>
                <hr>
                <h5>Lista de Preço: <strong style="color: blue">{{ $data->nome }}</strong> </h5>

                <h6>Percentual de Alteração: <strong>{{ $data->percentual_alteracao }}% </strong> /
                    @if ($data->tipo == 1)
                        Valor de compra
                    @else
                        Valor de venda
                    @endif
                </h6>

                <h6>Total de Produtos Cadastrados no Sistema: {{ sizeof($produtos) }}</h6>

                @if (sizeof($data->itens) > 0)
                <a class="btn btn-info" href="{{ route('listaDePrecos.gerar', $data->id) }}"><i class="bx bx-recycle"></i>Atualizar</a>

                    <div class="table-responsive mt-3">
                        <table class="table mb-0 table-striped">
                            <thead>
                                <tr>
                                    <th>Produto</th>
                                    <th>Valor Venda Padrão</th>
                                    <th>Valor de Compra</th>
                                    <th>Valor Venda da Lista</th>
                                    <th>Percentual de Lucro</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($data->itens as $item)
                                    <tr>
                                        <td>{{ $item->produto->nome }}</td>
                                        <td>{{ __moeda($item->produto->valor_venda) }}</td>
                                        <td>{{ __moeda($item->produto->valor_compra) }}</td>
                                        <td>{{ __moeda($item->valor) }}</td>
                                        <td>{{ $item->percentual_lucro }}</td>
                                        <td>
                                            <a href="{{ route('listaDePrecos.editarValor', $item->id) }}" class="btn btn-warning"><i class="bx bx-edit"></i></a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                <div class="m-3">
                    <h5 class="center-align text-danger">ATENÇÃO! Esta lista ainda não tem produtos cadastrados. <a
                        class="btn btn-primary" href="{{ route('listaDePrecos.gerar', $data->id) }}">Gerar Lista de
                        Produtos</a></h5>
                </div>
                @endif
            </div>
        </div>
    </div>
@endsection
