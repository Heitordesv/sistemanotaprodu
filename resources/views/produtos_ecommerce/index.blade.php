@extends('default.layout',['title' => 'Produtos Ecommerce'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <a href="{{ route('produtoEcommerce.create')}}" type="button" class="btn btn-success">
                        <i class="bx bx-plus"></i> Novo Produto Ecommerce
                    </a>
                </div>
            </div>
            <div class="col">
                <h6 class="mb-0 text-uppercase">Produtos Ecommerce</h6>
                {!!Form::open()->fill(request()->all())
                ->get()
                !!}
                <div class="row">
                    <div class="col-md-3">
                        {!!Form::text('nome', 'Pesquisar por nome')
                        !!}
                    </div>
                    <div class="col-md-4 text-left ">
                        <br>
                        <button class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
                        <a id="clear-filter" class="btn btn-danger" href="{{ route('produtoEcommerce.index') }}"><i class="bx bx-eraser"></i> Limpar</a>
                    </div>
                </div>
                {!!Form::close()!!}
                <hr />
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table mb-0 table-striped">
                                <thead class="">
                                    <tr>
                                        <th>Descrição</th>
                                        <th>Valor</th>
                                        <th>Controle de Estoque</th>
                                        <th>Destaque</th>
                                        <th>Grade</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($data as $p)
                                    <tr>
                                        <td>{{ $p->produto->nome }}</td>
                                        <td>{{ __moeda($p->valor) }}</td>
                                        <td>{{ $p->controlar_estoque == 0 ? 'Não' : 'Sim' }}</td>
                                        <td>{{ $p->destaque == 0 ? 'Não' : 'Sim' }}</td>
                                        <td>{{ $p->produto->grade == 0 ? 'Não' : 'Sim' }}</td>
                                        <td>
                                            <form action="{{ route('produtoEcommerce.destroy', $p->id) }}" method="post" id="form-{{$p->id}}">
                                                @method('delete')
                                                <a href="{{ route('produtoEcommerce.edit', $p) }}" class="btn btn-warning btn-sm text-white">
                                                    <i class="bx bx-edit"></i>
                                                </a>
                                                @csrf
                                                <button type="button" class="btn btn-delete btn-sm btn-danger">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                                <a href="{{ route('produtoEcommerce.galeria', $p->id) }}" class="btn btn-primary btn-sm" title="Galeria"><i class="bx bx-images"></i></a>
                                            </form>
                                        </td>
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
            {!! $data->appends(request()->all())->links() !!}
        </div>
    </div>
</div>
@endsection
