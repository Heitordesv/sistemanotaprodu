@extends('default.layout', ['title' => 'Apontamento Produção'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <h6>Apontamento de produção</h6>
            </div>
            <hr>
            {!!Form::open()
            ->get()
            ->route('estoque.storeApontamento')
            !!}
            <div class="row">
                <div class="col-md-4">
                    {!! Form::select('produto_id', 'Produto')->attrs(['class' => 'produto_id']) !!}
                </div>
                <div class="col-md-2">
                    {!! Form::tel('quantidade', 'Quantidade')->attrs(['class' => '']) !!}
                </div>
                <div class="col-md-2 col-6 mt-3">
                    <button type="submit" class="btn btn-primary w-100 mt-1">Salvar</button>
                </div>
            </div>
            {!!Form::close()!!}
            <hr>
            <div class="mt-4">
                <div class="text-center">
                    <h5>Ultimos 5 apontamentos</h5>
                </div>
                <div class="table-reponsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Produto</th>
                                <th>Quantidade</th>
                                <th>Data registro</th>
                                <th>Usuário</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($data as $item)
                            <tr>
                                <td>{{ $item->produto->nome }}</td>
                                <td>{{ __moeda($item->quantidade) }}</td>
                                <td>{{ __data_pt($item->created_at, 0) }}</td>
                                <td>{{ $item->usuario->nome }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center">Nada encontrado</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="m-3">
                <a type="button" class="btn btn-info" href="{{ route('estoque.todosApontamentos') }}"><i class="bx bx-list-ol"></i> Ver todos</a>
            </div>
        </div>
    </div>
</div>
@endsection
