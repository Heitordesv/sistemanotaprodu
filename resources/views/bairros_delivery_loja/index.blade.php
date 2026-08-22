@extends('default.layout', ['title' => 'Bairros Delivery'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <a href="{{ route('bairrosDeliveryLoja.create') }}" type="button" class="btn btn-success">
                        <i class="bx bx-plus"></i> Novo bairro
                    </a>
                    @if(sizeof($bairrosDoSuper) > 0)
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-bairros">
                        <i class="bx bx-list-ul"></i> Bairros da sua cidade
                    </button>
                    @endif
                </div>
            </div>
            <div class="col">
                <h6 class="mb-0 text-uppercase">Bairros Delivery</h6>
                {!! Form::open()->fill(request()->all())->get() !!}
                <div class="row">
                    <div class="col-md-3">
                        {!! Form::text('nome', 'Pesquisar por nome') !!}
                    </div>
                    <div class="col-md-3">
                        {!! Form::select('cidade', 'Cidade', $cidades->pluck('nome', 'id')->all())->attrs(['class' => 'form-select']) !!}
                    </div>
                    <div class="col-md-3 text-left ">
                        <br>
                        <button class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
                        <a id="clear-filter" class="btn btn-danger" href="{{ route('bairrosDeliveryLoja.index') }}"><i class="bx bx-eraser"></i> Limpar</a>
                    </div>
                </div>
                {!! Form::close() !!}
                <hr />
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table mb-0 table-striped">
                                <thead class="">
                                    <tr>
                                        <th width="55%">Bairro</th>
                                        <th>Valor de entrega</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($data as $item)
                                    <tr>
                                        <td>{{ $item->nome }}</td>
                                        <td>{{ __moeda($item->valor_entrega) }}</td>
                                        <td>
                                            <form action="{{ route('bairrosDeliveryLoja.destroy', $item->id) }}" method="post" id="form-{{ $item->id }}">
                                                @method('delete')
                                                <a href="{{ route('bairrosDeliveryLoja.edit', $item) }}" class="btn btn-warning btn-sm text-white">
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
                                        <td colspan="4" class="text-center">Nada encontrado</td>
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
@include('modals._bairros')

@endsection

