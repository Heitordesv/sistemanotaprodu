@extends('default.layout', ['title' => 'Comissões'])
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
            <div>
                <h5>Comissões</h5>
            </div>
            <div class="col">
                {!! Form::open()->fill(request()->all())->get() !!}
                <div class="row mt-5">
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
                    </div>
                </div>
                {!! Form::close() !!}
                <hr />
            </div>
        </div>
    </div>
</div>
@endsection
