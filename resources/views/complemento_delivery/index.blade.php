@extends('default.layout', ['title' => 'Complemento Delivery'])
@section('content')
    <div class="page-content">
        <div class="card ">
            <div class="card-body p-4">
                <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                    <div class="ms-auto">
                        <a href="{{ route('deliveryComplemento.create') }}" type="button" class="btn btn-success">
                            <i class="bx bx-plus"></i> Novo adicional
                        </a>
                    </div>
                </div>
                <hr>
                <div class="col">
                    {!! Form::open()->fill(request()->all())->get() !!}
                    <div class="row">
                        <div class="col-md-4">
                            {!! Form::text('nome', 'Pesquisar') !!}
                        </div>
                        <div class="col-md-6 text-left ">
                            <br>
                            <button class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
                            <a id="clear-filter" class="btn btn-danger" href="{{ route('deliveryComplemento.index') }}"><i
                                    class="bx bx-eraser"></i> Limpar</a>
                        </div>
                    </div>
                    {!! Form::close() !!}
                    <hr>
                    <div class="mt-4">
                        <h5>Lista de adicionais </h5>
                        <p>Registros: {{sizeof($data)}}</p>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table mb-0 table-striped">
                                <thead>
                                    <tr>
                                        <th>Nome</th>
                                        <th>Valor</th>
                                        <th>Tipo</th>
                                        <th style="width: 150px">Ações</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach ($data as $item)
                                        <tr>
                                            <td>{{ $item->nome }}</td>
                                            <td>{{ __moeda($item->valor) }}</td>
                                            <td>{{ $item->tipo ? 'Borda' : 'Normal' }}</td>
                                            <td>
                                                <form action="{{ route('deliveryComplemento.destroy', $item->id) }}" method="post"
                                                    id="form-{{ $item->id }}">
                                                    @method('delete')
                                                    <a href="{{ route('deliveryComplemento.edit', $item) }}"
                                                        class="btn btn-warning btn-sm text-white">
                                                        <i class="bx bx-edit"></i>
                                                    </a>

                                                    @csrf
                                                    <button type="button" class="btn btn-delete btn-sm btn-danger">
                                                        <i class="bx bx-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                {{-- {!! $data->appends(request()->all())->links() !!} --}}
            </div>
        </div>
    </div>
@endsection
