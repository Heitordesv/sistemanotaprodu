@extends('default.layout', ['title' => 'Lista de Caixa'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <a href="{{ route('caixa.index') }}" type="button" class="btn btn-light btn-sm">
                        <i class="bx bx-arrow-back"></i> Voltar
                    </a>
                </div>
            </div>
            <hr>
            <div class="col">
                <h5 class="mt-3">Lista de operações de caixa</h5>
                {!! Form::open()->fill(request()->all())->get() !!}
                <div class="row mt-3">
                    <div class="col-md-3">
                        {!! Form::date('star_date', 'Data Inicial')->attrs(['class' => '']) !!}
                    </div>
                    <div class="col-md-3">
                        {!! Form::date('end_date', 'Data Final')->attrs(['class' => '']) !!}
                    </div>
                    <div class="col-md-3">
                        <br>
                        <button class="btn btn-info"><i class="bx bx-search"></i> Pesquisa</button>
                    </div>
                </div>
                {!! Form::close() !!}
                <div class="table-responsive mt-3">
                    <table class="table mb-0 table-striped">
                        <thead class="">
                            <tr>
                                <th>Data Abertura</th>
                                <th>Valor de abertura</th>
                                <th>Data Fechamento</th>
                                <th>Usuário</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($data as $item)
                            <tr>
                                <td>{{ $item->data_registro }}</td>
                                <td>{{ __moeda($item->valor) }}</td>
                                <td>{{ ($item->updated_at == $item->created_at) ? 'Caixa aberto' :  $item->updated_at }} </td>
                                <td>{{ $item->usuario->nome }}</td>
                                <td>
                                    <a class="btn btn-primary btn-sm" href="{{ route('caixa.detalhes', $item->id) }}"><i class="bx bx-list-ol"></i></a>
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
</div>
@endsection
