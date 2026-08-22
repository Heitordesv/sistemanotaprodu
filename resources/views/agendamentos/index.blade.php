@extends('default.layout', ['title' => 'Agendamentos'])
@section('content')

@section('css')
<link href='/fullcalendar/main.css' rel='stylesheet' />
@endsection

<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <a href="{{ route('agendamentos.create') }}" type="button" class="btn btn-info">
                        <i class="bx bx-plus"></i> Novo agendamento
                    </a>
                    <a href="{{ route('agendamentos.comissao') }}" type="button" class="btn btn-success">
                        <i class="bx bx"></i> % Comissão vendedor
                    </a>
                    <a href="{{ route('agendamentos.servicos') }}" type="button" class="btn btn-warning">
                        <i class="bx bx-list-ul"></i> Serviços executados
                    </a>
                </div>
            </div>
            <div class="col">
                <h6  class="mb-0 text-uppercase">Agendamentos</h6>
                {!! Form::open()->fill(request()->all())->get() !!}
                <div class="row mt-4">
                    <div class="col-md-6">
                        {!! Form::select('funcionario_id', 'Atendente', $funcionarios->pluck('nome', 'id')->all())->attrs(['class' => 'select2']) !!}
                    </div>
                    <div class="col-md-6">
                        {!! Form::select('cliente_id', 'Cliente') !!}
                    </div>
                    <div class="col-md-2 mt-3">
                        {!! Form::date('start_date', 'Data inicial') !!}
                    </div>
                    <div class="col-md-2 mt-3">
                        {!! Form::date('end_date', 'Data final') !!}
                    </div>
                    <div class="col-md-2 mt-3">
                        {!! Form::select('estado', 'Estado', ['todos' => 'Todos', 'finalizado' => 'Finalizado', 'pendente' => 'Pendente'])->attrs(['class' => 'select2']) !!}
                    </div>
                    <div class="col-md-3 text-left mt-3">
                        <br>
                        <button class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
                        <a id="clear-filter" class="btn btn-danger" href="{{ route('agendamentos.index') }}"><i class="bx bx-eraser"></i> Limpar</a>
                    </div>
                </div>
                {!! Form::close() !!}
                <hr />
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <div id='calendar'></div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')

<script src="/metronic/js/fullcalendar.bundle.js" type="text/javascript"></script>
<script src='/fullcalendar/main.js'></script>
<script src='/fullcalendar/locales/pt-br.js'></script>
<script src="/js/agendamento.js"></script>

@endsection
