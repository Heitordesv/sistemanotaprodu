@extends('default.layout',['title' => 'Produtos Delivery'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <a href="{{ route('produtoDelivery.create')}}" type="button" class="btn btn-success">
                        <i class="bx bx-plus"></i> Novo produto delivery
                    </a>
                    {{-- <a href="{{ route ('divisaoGrade.index')}}" type="button" class="btn btn-info">
                        <i class="bx bx-dice-6"></i> Divisão de grade</a> --}}
                </div>
            </div>
            <div class="col">
                <h6 class="mb-0 text-uppercase">Produtos Delivery</h6>

                {!!Form::open()->fill(request()->all())
                ->get()
                !!}
                <div class="row">
                    <div class="col-md-3">
                        {!!Form::text('descricao', 'Pesquisar por nome')
                        !!}
                    </div>
                    <div class="col-md-3 text-left ">
                        <br>
                        <button class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
                        <a id="clear-filter" class="btn btn-danger" href="{{ route('produtoDelivery.index') }}"><i class="bx bx-eraser"></i> Limpar</a>
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
                                        <th>Categoria</th>
                                        <th>Valor</th>
                                        <th>Limite diário</th>
                                        <th>Destque</th>
                                        <th>Ativo</th>
                                        <th>Descrição</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($data as $p)
                                    <tr>
                                        <td>{{ $p->produto->nome }}</td>
                                        <td>{{ $p->categoria->nome }}</td>
                                        <td>{{ __moeda($p->valor) }}</td>
                                        <td>{{ $p->limite_diario == '-1' ? 'Sem limite' : $p->limite_diario }}</td>
                                        <td>{{ $p->destaque ? 'Sim' : 'Não' }}</td>
                                        <td>{{ $p->status ? 'Sim' : 'Não' }}</td>
                                        <td>
                                            <a onclick='swal("", "{{$p->descricao}}", "info")' class="btn btn-info btn-sm">Descrição</a>
                                        </td>
                                        <td>
                                            <form action="{{ route('produtoDelivery.destroy', $p->id) }}" method="post" id="form-{{$p->id}}">
                                                @method('delete')
                                                <a href="{{ route('produtoDelivery.edit', $p) }}" class="btn btn-warning btn-sm text-white">
                                                    <i class="bx bx-edit"></i>
                                                </a>
                                                @csrf
                                                <button type="button" class="btn btn-delete btn-sm btn-danger">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                                <!-- <a href="{{ route('produtoDelivery.push', $p->id) }}" class="btn btn-info btn-sm" title="Criar push"><i class="bx bx-upvote"></i></a> -->
                                                <a href="{{ route('produtoDelivery.galeria', $p->id) }}" class="btn btn-primary btn-sm" title="Galeria"><i class="bx bx-images"></i></a>
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
                    </div>
                </div>
            </div>
            {{-- {!! $data->appends(request()->all())->links() !!} --}}
        </div>
    </div>
</div>
@endsection
