@extends('default.layout', ['title' => 'Agendamento Serviços'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <a href="{{ route('agendamentos.index') }}" type="button" class="btn btn-light btn-sm">
                        <i class="bx bx-arrow-back"></i> Voltar
                    </a>
                </div>
            </div>
            <hr>
            <div>
                <h5>Agendamento de Serviços</h5>
            </div>
            <div class="col">
                {!! Form::open()->fill(request()->all())->get() !!}
                <div class="row mt-3">
                    <div class="col-md-6">
                        <label for="">Vendedor</label>
                        <select class="select2" name="" id="">
                            @foreach ($funcionarios as $item)
                            <option value="">Selecione</option>
                            <option value="">{{ $item->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        {!! Form::date('data_inicial', 'Data Inicial') !!}
                    </div>
                    <div class="col-md-2">
                        {!! Form::date('data_final', 'Data Final') !!}
                    </div>
                    <div class="col-md-2">
                        {!! Form::select('estado', 'Estado', [
                        'todos' => 'Todos',
                        'finalizado' => 'Finalizado',
                        'pendente' => 'Pendente',
                        ])->attrs(['class' => 'select2']) !!}
                    </div>
                    <div class="col-md-3 text-left mt-3">
                        <br>
                        <button class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
                        <a id="clear-filter" class="btn btn-danger" href="{{ route('agendamentos.servicos') }}"><i class="bx bx-eraser"></i> Limpar</a>
                    </div>
                </div>
                {!! Form::close() !!}
                <hr/>
            </div>
        </div>
    </div>
</div>
@endsection
