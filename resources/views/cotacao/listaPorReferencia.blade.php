@extends('default.layout', ['title' => 'Lista por referência'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="col">
                <h6 class="mb-0 text-uppercase">Lista Por Referência</h6>

                {!! Form::open()->fill(request()->all())->get() !!}

                <div class="row mt-3">
                    <div class="col-md-4">
                        {!! Form::select('fornecedor_id', 'Pesquisar por fornecedor')->attrs(['class' => 'select2']) !!}
                    </div>

                    <div class="col-md-2">
                        {!! Form::date('start_date', 'Data Inicial')->attrs(['class' => '']) !!}
                    </div>
                    <div class="col-md-2">
                        {!! Form::date('end_date', 'Data Final')->attrs(['class' => '']) !!}
                    </div>
                    <div class="col-md-3 text-left ">
                        <br>
                        <button class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisa</button>
                    </div>

                </div>

                {!! Form::close() !!}

                <hr />

                <label style="color: royalblue">Total de registros: {{count($cotacoes)}}</label>
                <div class="row">
                    @foreach($cotacoes as $c)
                    <div class="col-4 mt-3">
                        <div class="card card radius-10 border-top border-0 border-4 border-info">
                            <div class="card-header">
                                <div class="d-flex align-items-center">
                                    <h3 style=" font-size: 18px;" class="card-title">{{$c->referencia}}</h3>
                                    <div class="dropdown ms-auto">
                                        <div class="dropdown-toggle dropdown-toggle-nocaret cursor-pointer" data-bs-toggle="dropdown"> <i class='bx bx-dots-horizontal-rounded font-22'></i></div>
                                        <ul class="dropdown-menu">
                                            <li class="dropdown-item">
                                                <a href="/cotacao/referenciaView/{{ $c->referencia }}" class="dropdown-item">Ver cotações</a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="kt-widget__info">
                                    <span class="kt-widget__label">Total de itens:</span>
                                    <a class="kt-widget__data text-success">{{$c->contaItens()}}</a>
                                </div>
                                <div class="kt-widget__info">
                                    <span class="kt-widget__label">Total de fornecedores:</span>
                                    <a class="kt-widget__data text-success">{{$c->contaFornecedores()}}</a>
                                </div>
                                <div class="kt-widget__info">
                                    <span class="kt-widget__label">Maior valor:</span>
                                    <a class="kt-widget__data text-success">{{__moeda($c->getValores(true), 2)}}</a>
                                </div>
                                <div class="kt-widget__info">
                                    <span class="kt-widget__label">Menor valor:</span>
                                    <a class="kt-widget__data text-success">{{__moeda($c->getValores(), 2, ',', '.')}}</a>
                                </div>
                                <div class="kt-widget__info">
                                    <span class="kt-widget__label">Data de criação:</span>
                                    <a class="kt-widget__data text-success">{{ \Carbon\Carbon::parse($c->created_at)->format('d/m/Y H:i:s')}}</a>
                                </div>
                                <div class="kt-widget__info">
                                    <span class="kt-widget__label">Escolhida:</span>
                                    @if(!$c->escolhida())
                                    <a class="kt-widget__data text-danger">Não</a>
                                    @else
                                    <a class="kt-widget__data text-success">Sim</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
