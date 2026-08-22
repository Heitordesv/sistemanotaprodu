@extends('default.layout',['title' => 'Pedidos Nuvem Shop'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">

            <div class="col">
                <h6 class="mb-0 text-uppercase">Pedidos Nuvem Shop</h6>
                {!!Form::open()->fill(request()->all())
                ->get()
                !!}
                <div class="row">
                    <div class="col-md-3">
                        {!!Form::text('cliente', 'Pesquisar por cliente')
                        !!}
                    </div>

                    <div class="col-md-2">
                        {!!Form::date('data_inicial', 'Data inicial')
                        !!}
                    </div>
                    <div class="col-md-2">
                        {!!Form::date('data_final', 'Data final')
                        !!}
                    </div>
                    <div class="col-md-3 text-left ">
                        <br>
                        <button class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
                        <a id="clear-filter" class="btn btn-danger" href="{{ route('nuvemshop-pedidos.index') }}"><i class="bx bx-eraser"></i> Limpar</a>
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
                                        <th width="">ID</th>
                                        <th width="">Cliente</th>
                                        <th width="">Data</th>
                                        <th width="">NFe</th>
                                        <th width="">Valor Total</th>
                                        <th width="">Desconto</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($pedidos as $item)
                                    <tr>
                                        <td>{{ $item->id }}</td>
                                        <td>{{ $item->customer->name }}</td>
                                        <td>{{ __data_pt($item->created_at) }}</td>
                                        <td>{{ $item->numero_nfe > 0 ? $item->numero_nfe : '--' }}</td>
                                        <td>{{ __moeda($item->total) }}</td>
                                        <td>{{ __moeda($item->discount) }}</td>
                                        <td>
                                            <form action="{{ route('nuvemshop-pedidos.destroy', $item->id) }}" method="post" id="form-{{$item->id}}">
                                                @method('delete')
                                                <a href="{{ route('nuvemshop-pedidos.show', $item->id) }}" class="btn btn-info btn-sm text-white">
                                                    <i class="bx bx-file"></i>
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
            @if(!isset($cliente))
            <div class="row">
                <div class="col-sm-1">
                    @if($page > 1)
                    <a class="btn btn-light-primary" href="/nuvemshop-pedidos?page={{$page-1}}" class="float-left">
                        <i class="la la-angle-double-left"></i>
                    </a>
                    @endif
                </div>
                <div class="col-sm-10"></div>
                <div class="col-sm-1">
                    <a class="btn btn-light-primary" href="/nuvemshop-pedidos?page={{$page+1}}" class="float-right">
                        <i class="la la-angle-double-right"></i>
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
