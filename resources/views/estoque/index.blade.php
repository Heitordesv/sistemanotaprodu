@extends('default.layout', ['title' => 'Estoque'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
            </div>
            {!!Form::open()->fill(request()->all())
            ->get()
            !!}
            <h5 class="">Ajuste de estoque</h5>
            <div class="row">
                <div class="col-md-6">
                    {!! Form::select('produto_id', 'Pesquise o produto')!!}
                </div>
                <div class="col-md-4">
                    <br>
                    <button class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
                    <a id="clear-filter" class="btn btn-danger" href="{{ route('estoque.index') }}"><i class="bx bx-eraser"></i> Limpar</a>
                </div>
            </div>
            {!!Form::close()!!}
            <hr>
            <h6 class="mt-4">Estoque</h6>
            <p>Total de registros: <strong>{{ $data->total() }}</strong></p>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Produto</th>
                                @if(empresaComFilial())
                                <th>Local</th>
                                @endif
                                <th>Categoria</th>
                                <th>Quantidade</th>
                                <th>Custo</th>
                                <th>Venda</th>
                                <th>Sub custo</th>
                                <th>Sub venda</th>
                                <th>Movimentação</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($data as $item)
                            <tr>
                                <td>{{ $item->produto->nome }}{{ $item->produto->grade ? " (" . $item->produto->str_grade . ")" : ""}}</td>
                                @if(empresaComFilial())
                                <td>{{ $item->filial ? $item->filial->descricao : 'Matriz'}}</td>
                                @endif
                                <td>{{ $item->produto->categoria->nome }}</td>
                                <td>
                                    @if(!$item->produto->unidadeQuebrada())
                                    {{ __estoque($item->quantidade)}}
                                    @else
                                    {{ __estoque($item->quantidade)}}
                                    @endif
                                </td>
                                <td>{{ __moeda($item->valor_compra) }}</td>
                                <td>{{ __moeda($item->produto->valor_venda) }}</td>
                                <td>{{ __moeda(($item->valor_compra) * ($item->quantidade)) }}</td>
                                <td>{{ __moeda(($item->produto->valor_venda) * ($item->quantidade)) }}</td>
                                <td>
                                    <a href="{{ route('produtos.movimentacao', $item->produto->id) }}" type="btn" class="btn btn-primary btn-sm">
                                        <i class="bx bx-list-ul"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="m-3">
            <h5>Total de produtos</h5>
            <h6 id="total_custo">Custo: {{ __moeda($somaEstoque['compra']) }}</h6>
            <h6 id="total_venda">Venda: {{ __moeda($somaEstoque['venda']) }}</h6>
            <a target="_blank" class="btn btn-info" href="{{ route('estoque.create') }}">Apontamento
                manual</a>
            <a target="_blank" class="btn btn-primary" href="{{ route('estoque.listaApontamento') }}">Listar
                alterações</a>
<form action="{{ route('estoque.zerarEstoque') }}" method="POST" 
      onsubmit="return confirm('Deseja realmente zerar todo o estoque desta empresa?');">
    @csrf
    <input type="hidden" name="empresa_id" value="{{ $request->empresa_id ?? 1 }}">
    <button type="submit" class="btn btn-danger">
        Zerar Estoque
    </button>
</form>

        </div>
        <div>
        </div>
    </div>
</div>
@endsection
