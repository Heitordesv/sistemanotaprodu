@extends('default.layout', ['title' => 'Compras'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
            </div>
            <div class="">
                <h6 class="mb-0 text-uppercase">Compras</h6>
                {!! Form::open()
                ->fill(request()
                ->all())
                ->get() !!}
                <div class="row">
                    <div class="col-md-5 mt-3">
                        {!! Form::select('fornecedor_id', 'Pesquisar por fornecedor') !!}
                    </div>
                    <div class="col-md-2 mt-3">
                        {!! Form::date('start_date', 'Data inicial') !!}
                    </div>
                    <div class="col-md-2 mt-3">
                        {!! Form::date('end_date', 'Data final') !!}
                    </div>
                    @if(empresaComFilial())
                    {!! __view_locais_select_filtro("Local", isset($filial_id) ? $filial_id : '') !!}
                    @endif
                    <div class="col-md-3 text-left mt-3">
                        <br>
                        <button class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
                        <a id="clear-filter" class="btn btn-danger" href="{{ route('compras.index') }}"><i class="bx bx-eraser"></i>Limpar</a>
                    </div>
                </div>
                {!! Form::close() !!}
                <hr />
                <h6>Lista de compras</h6>
                {{-- <p>Registros: {{ $data->total() }}</p> --}}
                <div class="row">
                    <div class="ms-auto">
                        <a href="{{ route('compraManual.index')}}" type="button" class="btn btn-success">
                            <i class="bx bx-plus"></i> Nova compra
                        </a>
                    </div>
                </div>
                <div class="card mt-3">
                    <div class="card-body">
                        <div class="table-responsive tbl-400">
                            <table class="table mb-0 table-striped">
                                <thead class="">
                                    <tr>
                                        <th>Ações</th>
                                        <th>Fornecedor</th>
                                        <th>Data da compra</th>
                                        <th>Total</th>
                                        @if(empresaComFilial())
                                        <th>Local</th>
                                        @endif
                                        <th>Desconto</th>
                                        <th>Usuário</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $totalGeral = 0;
                                    @endphp
                                    @forelse($data as $item)
                                    @php
                                        $totalGeral += $item->total;
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Ações</button>
                                                <ul class="dropdown-menu">
                                                    <form action="{{ route('compras.destroy', $item->id) }}" method="post" id="form-{{$item->id}}">
                                                        @method('delete')
                                                        @csrf
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('compras.edit', $item->id) }}">Editar</a>
                                                        </li>
                                                        @if($item->estado == 'novo' || $item->estado == 'rejeitado' || !$item->estado)
                                                        <li>
                                                            <button class="dropdown-item btn-delete">Apagar</button>
                                                        </li>
                                                        @endif
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('compras.nfe-entrada', $item->id) }}">Emitir NFe Entrada</a>
                                                        </li>
                                                    </form>
                                                </ul>
                                            </div>
                                        </td>
                                        <td>{{ $item->fornecedor->razao_social }}</td>
                                        <td>{{ __data_pt($item->created_at, 1) }}</td>
                                        <td>{{ __moeda($item->total) }}</td>
                                        @if(empresaComFilial())
                                        <td>
                                            {{ $item->filial_id ? $item->filial->descricao : 'Matriz' }}
                                        </td>
                                        @endif
                                        <td>{{ __moeda($item->desconto) }}</td>
                                        <td>{{ $item->usuario->nome }}</td>
                                        <td>{{ strtoupper($item->estado) }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8" class="text-center">Nada encontrado</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                               
                            </table>
                        </div>
<div class="mt-3 text-right">   
<strong>Total:</strong> 

                                       {{ __moeda($totalGeral) }}
                                        @if(empresaComFilial())
                                        @endif
                    </div>
                </div>
            </div>
            @isset($data->appends)
            {!! $data->appends(request()->all())->links() !!}
            @endisset
        </div>
    </div>
</div>
@endsection
