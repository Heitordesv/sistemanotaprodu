@extends('default.layout', ['title' => 'Clientes'])
@section('content')
<div class="page-content">
    <div class="card">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <a href="{{ route('clientes.create') }}" type="button" class="btn btn-success">
                        <i class="bx bx-plus"></i> Novo cliente
                    </a>
                </div>
            </div>

            <div class="col">
                {!! Form::open()->fill(request()->all())->get() !!}
                <div class="row">
                    <div class="col-md-4">
                        {!! Form::text('nome', 'Pesquisar') !!}
                    </div>
                    <div class="col-md-6 text-left ">
                        <br>
                        <button class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
                        <a id="clear-filter" class="btn btn-danger" href="{{ route('clientes.index') }}"><i class="bx bx-eraser"></i> Limpar</a>
                    </div>
                </div>
                {!! Form::close() !!}
                <hr>

                <div class="mt-4">
                    <h5>Lista de Clientes</h5>
                </div>

                <!-- Cards de Clientes -->
                <div class="row">
                    @foreach ($data as $item)
                    <div class="col-md-4 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">{{ $item->nome_fantasia }}</h5>
                                <p class="card-text">
                                    <strong>Telefone:</strong> {{ $item->celular }}<br>
                                    <strong>Data Aniversário:</strong> {{ \Carbon\Carbon::parse($item->data_aniversario)->format('d/m/Y') }}
                                </p>
                               
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                {!! $data->appends(request()->all())->links() !!}
            </div>
        </div>
    </div>
</div>
@endsection
