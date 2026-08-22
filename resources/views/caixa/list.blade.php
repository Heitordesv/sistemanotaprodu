@extends('default.layout', ['title' => 'Lista de Caixas'])
@section('content')
<div class="page-content">
    <div class="card">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div>
                    <h4 class="mb-1">Caixas da empresa</h4>
                    <small class="text-muted">
                        Cada linha representa uma abertura independente, vinculada ao seu operador.
                    </small>
                </div>
                <div class="ms-auto">
                    <a href="{{ route('caixa.index') }}" type="button" class="btn btn-light btn-sm">
                        <i class="bx bx-arrow-back"></i> Voltar
                    </a>
                </div>
            </div>

            <hr>

            {!! Form::open()->fill(request()->all())->get() !!}
            <div class="row mt-3">
                <div class="col-md-3">
                    {!! Form::date('start_date', 'Data Inicial')->attrs(['class' => '']) !!}
                </div>
                <div class="col-md-3">
                    {!! Form::date('end_date', 'Data Final')->attrs(['class' => '']) !!}
                </div>
                <div class="col-md-3">
                    <br>
                    <button class="btn btn-info">
                        <i class="bx bx-search"></i> Pesquisar
                    </button>
                </div>
            </div>
            {!! Form::close() !!}

            <div class="table-responsive mt-4">
                <table class="table table-striped table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Caixa</th>
                            <th>Operador</th>
                            <th>Status</th>
                            <th>Abertura</th>
                            <th>Valor de abertura</th>
                            <th>Fechamento</th>
                            <th>Local</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($data as $item)
                            <tr @if((int) $item->status === 0) class="table-success" @endif>
                                <td>
                                    <strong>Caixa #{{ $item->id }}</strong>
                                </td>
                                <td>
                                    <i class="bx bx-user me-1"></i>
                                    <strong>{{ $item->usuario->nome ?? 'Operador não identificado' }}</strong>
                                </td>
                                <td>
                                    @if((int) $item->status === 0)
                                        <span class="badge bg-success">ABERTO</span>
                                    @else
                                        <span class="badge bg-secondary">FECHADO</span>
                                    @endif
                                </td>
                                <td>{{ $item->data_registro ?? $item->created_at }}</td>
                                <td>R$ {{ __moeda($item->valor) }}</td>
                                <td>
                                    {{ (int) $item->status === 0 ? '--' : $item->updated_at }}
                                </td>
                                <td>{{ $item->filial->descricao ?? 'Matriz' }}</td>
                                <td class="text-end">
                                    <a
                                        class="btn btn-primary btn-sm"
                                        href="{{ route('caixa.detalhes', $item->id) }}"
                                        title="Ver Caixa #{{ $item->id }} de {{ $item->usuario->nome ?? 'operador' }}"
                                    >
                                        <i class="bx bx-show"></i>
                                        Ver caixa
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    Nenhum caixa encontrado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(method_exists($data, 'links'))
                <div class="mt-3">
                    {{ $data->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
