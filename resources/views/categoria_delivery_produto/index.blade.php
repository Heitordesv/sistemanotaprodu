@extends('default.layout', ['title' => 'Categorias Produto Delivery'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <a href="{{ route('categoriaDelivery.create') }}" type="button" class="btn btn-success">
                        <i class="bx bx-plus"></i> Nova categoria produto delivery
                    </a>
                </div>
            </div>
            <div class="col">
                <h6 class="mb-0 text-uppercase">Categorias produto delivery</h6>
                {!! Form::open()->fill(request()->all())->get() !!}
                <div class="row">
                    <div class="col-md-3">
                        {!! Form::text('nome', 'Pesquisar por nome') !!}
                    </div>
                    <div class="col-md-3 text-left ">
                        <br>
                        <button class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
                        <a id="clear-filter" class="btn btn-danger" href="{{ route('categoriaDelivery.index') }}"><i class="bx bx-eraser"></i> Limpar</a>
                    </div>
                </div>
                {!! Form::close() !!}
                <hr />
                <div class="card">
                    <div class="card-body">
                        @if($existeCategoriaPizza)
                        <p class="text-danger">VOCE AINDA NÃO FEZ CADASTROS DE TAMANHOS DE PIZZA!</p>
                        <a href="{{ route('tamanhosPizza.index') }}" class="btn btn-info">
                            <i class="bx bx-pizza"></i>
                            Ir para tamanhos de pizza</a>
                        <br><br>
                        @endif
                        <div class="table-responsive">
                            <p>Total de produtos: {{ sizeof($data) }}</p>
                            <table class="table mb-0 table-striped">
                                <thead class="">
                                    <tr>
                                        <th>#</th>
                                        <th>Nome</th>
                                        <th>Tipo pizza</th>
                                        <th>Descrição</th>
                                        <th>Total de produtos</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($data as $item)
                                    <tr>
										<td><img class="img-round" src="uploads/categoriaDelivery/{{ $item->path }}"></td>
                                        <td>{{ $item->nome }}</td>
                                        <td>
                                            <strong>{{ ($item->tipo_pizza) ? 'Sim' : 'Não' }}</strong>
                                        </td>
                                        <td>
                                            <a onclick='swal("", "{{$item->descricao}}", "info")' class="btn btn-info btn-sm">Descrição</a>
                                        </td>
                                        <td>
                                            {{ sizeof($item->produtos) }}
                                        </td>
                                        <td>
                                            <form action="{{ route('categoriaDelivery.destroy', $item->id) }}" method="post" id="form-{{ $item->id }}">
                                                @method('delete')
                                                <a href="{{ route('categoriaDelivery.edit', $item) }}" class="btn btn-warning btn-sm text-white">
                                                    <i class="bx bx-edit"></i>
                                                </a>
                                                @csrf
                                                <button type="button" class="btn btn-delete btn-sm btn-danger">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                            </form>
                                        </td>
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
            {{-- {!! $data->appends(request()->all())->links() !!} --}}
        </div>
    </div>
</div>
@endsection
