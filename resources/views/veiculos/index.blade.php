@extends('default.layout',['title' => 'Veículos'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <a href="{{ route('veiculos.create')}}" type="button" class="btn btn-success">
                        <i class="bx bx-plus"></i> Novo veículo
                    </a>
                </div>
            </div>
            <div class="col">
                <h6 class="mb-0 text-uppercase">Veículos</h6>
                {!!Form::open()->fill(request()->all())
                ->get()
                !!}
                <div class="row">
                    <div class="col-md-3">
                        {!!Form::text('marca', 'Pesquisar por marca')
                        !!}
                    </div>
                    <div class="col-md-3">
                        {!!Form::text('proprietario_documento', 'Pesquisar por CPF/CNPJ')
                        ->attrs(['class' => 'cpf_cnpj'])
                        ->type('tel')
                        !!}
                    </div>
                    <div class="col-md-3 text-left ">
                        <br>
                        <button class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
                        <a id="clear-filter" class="btn btn-danger" href="{{ route('veiculos.index') }}"><i class="bx bx-eraser"></i> Limpar</a>
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
                                        <th>Marca</th>
                                        <th>Modelo</th>
                                        <th>Cor</th>
                                        <th>RNTRC</th>
                                        <th>Tara/Capacidade</th>
                                        <th>CPF/CNPJ Proprietário</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($data as $item)
                                    <tr>
                                        <td>{{ $item->marca }}</td>
                                        <td>{{ $item->modelo }}</td>
                                        <td>{{ $item->cor}}</td>
                                        <td>{{ $item->rntrc }}</td>
                                        <td>{{ $item->tara }}/{{ $item->capacidade }}</td>
                                        <td>{{ $item->proprietario_documento }}</td>
                                        <td>
                                            <form action="{{ route('veiculos.destroy', $item->id) }}" method="post" id="form-{{$item->id}}">
                                                @method('delete')
                                                <a href="{{ route('veiculos.edit', $item) }}" class="btn btn-warning btn-sm text-white">
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
