@extends('default.layout',['title' => 'Planos'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <a href="{{ route('planos.create')}}" type="button" class="btn btn-success">
                        <i class="bx bx-plus"></i> Novo plano
                    </a>
                    <a href="{{ route('perfilAcesso.index')}}" type="button" class="btn btn-primary">
                        <i class="bx bx-list-ol"></i> Perfis
                    </a>
                </div>
            </div>
            <div class="col">
                <h6 class="mb-0 text-uppercase">Planos</h6>
                {!!Form::open()->fill(request()->all())
                ->get()
                !!}
                <div class="row mt-3">
                    <div class="col-md-3">
                        {!!Form::text('nome', 'Pesquisar por nome')
                        !!}
                    </div>
                    <div class="col-md-3 text-left ">
                        <br>
                        <button class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
                        <a id="clear-filter" class="btn btn-danger" href="{{ route('planos.index') }}"><i class="bx bx-eraser"></i> Limpar</a>
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
                                        <th>#</th>
                                        <th>Nome</th>
                                        <th>Valor</th>
                                        <th>Max clientes</th>
                                        <th>Max produtos</th>
                                        <th>Max fornecedores</th>
                                        <th>Max NFe</th>
                                        <th>Max NFCe</th>
                                        <th>Max CTe</th>
                                        <th>Max MDFe</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($data as $item)
                                    <tr>
                                        <td>{{ $item->id }}</td>
                                        <td>{{ $item->nome }}</td>
                                        <td>{{ __moeda($item->valor) }}</td>
                                        <td>{{ $item->maximo_clientes }}</td>
                                        <td>{{ $item->maximo_produtos }}</td>
                                        <td>{{ $item->maximo_fornecedores }}</td>
                                        <td>{{ $item->maximo_nfes }}</td>
                                        <td>{{ $item->maximo_nfces }}</td>
                                        <td>{{ $item->maximo_cte }}</td>
                                        <td>{{ $item->maximo_mdfe }}</td>
                                    
                                        <td>
                                            <form action="{{ route('planos.destroy', $item->id) }}" method="post" id="form-{{$item->id}}">
                                                @method('delete')
                                                <a href="{{ route('planos.edit', $item) }}" class="btn btn-warning btn-sm text-white">
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
                                        <td colspan="11" class="text-center">Nada encontrado</td>
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
