@extends('default.layout', ['title' => 'Produtos'])
@section('content')
    <div class="page-content">
        <div class="card ">
            <div class="card-body p-4">
                <div class="card">
                    <h5 class="m-3">Lista de Inventário: <strong>{{ $inventario->referencia }}</strong> </h5>
                    <div class="card-body">
                        {!! Form::open()->fill(request()->all())->get() !!}
                        <div class="col-md-8">
                            {!! Form::select('produto_id', 'Pesquisa Produto')->attrs(['class' => 'select2']) !!}
                        </div>
                        <div class="col-md-2 text-left ">
                            <br>
                            <button class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
                        </div>
                        {!! Form::close() !!}
                        <div class="table-responsive mt-3">
                            <table class="table mb-0 table-striped">
                                <thead class="">
                                    <tr>
                                        <th>Produto</th>
                                        <th>Quantidade</th>
                                        <th>Valor Compra</th>
                                        <th>Valor Venda</th>
                                        <th>Subtotal Compra</th>
                                        <th>Subtotal Venda</th>
                                        <th>Observação</th>
                                        <th>Estado</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($itens as $i)
                                        <tr>
                                            <td>{{ $i->produto->nome }}</td>
                                            <td>{{ $i->quantidade }}</td>
                                            <td>{{ __moeda($i->produto->valor_compra) }}</td>
                                            <td>{{ __moeda($i->produto->valor_venda) }}</td>
                                            <td>{{ __moeda($i->quantidade * $i->produto->valor_compra) }}</td>
                                            <td>{{ __moeda($i->quantidade * $i->produto->valor_venda) }}</td>
                                            <td>{{ $i->observacao }}</td>
                                            <td>{{ $i->estado }}</td>
                                            <td>
                                                <form action="{{ route('inventario.destroy-item', $i->id) }}" method="post"
                                                    id="form-{{ $i->id }}">
                                                    @method('delete')
                                                    @csrf
                                                    <button type="button" class="btn btn-delete btn-sm btn-danger">
                                                        <i class="bx bx-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center">Nada encontrado</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="m-5">
                            <h5>Total em estoque valor de compra: <strong style="color: red">R$ {{ __moeda($totaliza['compra']) }}</strong> </h5>
                            <h5>Total em estoque valor de venda: <strong style="color: blue">R$ {{ __moeda($totaliza['venda']) }}</strong> </h5>
                            <a class="btn btn-info" href="{{ route('inventario.print', $inventario->id) }}"><i class="bx bx-printer"></i> imprimir</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
