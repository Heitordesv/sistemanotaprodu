@extends('default.layout', ['title' => 'Nova Cotação'])
@section('content')
<div class="page-content">
    <div class="card border-top border-0 border-4 border-primary">
        <div class="card-body p-5">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div>
                    <h6>Fornecedor: {{ $item->fornecedor->razao_social }} </h6>
                    <h6>Data de Registro: {{ __data_pt($item->created_at, 0) }}</h6>
                    <h6>Referência: {{ $item->referencia }}</h6>
                    <h6>Observação: {{ $item->observacao }}</h6>
                    <h6>Link: {{ $item->link }}</h6>
                    <h6>Ativa: {{ $item->ativa == 0 ? 'Sim' : 'Não'}}</h6>
                    <h6>Respondida: {{ $item->resposta == 0 ? 'Sim' : 'Não' }} </h6>

                </div>

            </div>
            <div class="table-reponsive">
                <h5>Itens da Cotação:</h5>
                <table class="table mb-0 table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Produto</th>
                            <th>Quantidade</th>
                            <th>Valor</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($item->itens as $itens)
                        <tr>
                            <td>{{ $itens->cotacao_id }}</td>
                            <td>{{ $itens->produto->nome }}</td>
                            <td>{{ $itens->quantidade }}</td>
                            <td>{{ __moeda($itens->valor) }}</td>
                            <td>
                                <a href="{{ route('cotacao.destroyItem', $item) }}" class="btn btn-danger btn-sm text-white" title="Desativar">
                                    <i class="bx bx-trash"></i>
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
@endsection
