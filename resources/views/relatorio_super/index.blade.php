@extends('default.layout',['title' => 'Planos'])
@section('content')
<div class="page-content">
    <div class="card">
        <div class="row m-2">
            <h5 class="mt-3 text-center">RELATÓRIOS</h5>
            <div class="col-6 mt-3">
                <div class="accordion-item">
                    <h3 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse1" aria-controls="collapse1">Relatório de Empresas</button>
                    </h3>
                    <div id="collapse1" class="accordion-collapse collapse" aria-labelledby="heading1" data-bs-parent="#accordionExample">
                        {!! Form::open()->route('relatorioSuper.empresas')->get() !!}
                        <div class="row m-3">
                            <div class="col-12 mt-3">
                                {!! Form::select(
                                'empresa',
                                'Empresa',
                                ['' => 'Selecione'] + $empresas->pluck('razao_social', 'id')->all())->attrs([
                                'class' => 'form-select']) !!}
                            </div>
                            <div class="col-6 mt-3">
                                {!! Form::select('status', 'Status', ['todos' => 'Todos', 'ativo' => 'Ativo', 'pedente' => 'Pendente', 'desativado' => 'Desativado'])->attrs([
                                'class' => 'form-select',
                                ]) !!}
                            </div>
                            <div class="col-6 mt-3">
                                {!! Form::select('plano', 'Plano', ['' => 'Selecione'] + $planos->pluck('nome', 'id')->all())->attrs([
                                'class' => 'form-select',
                                ]) !!}
                            </div>
                            <div class="mt-3">
                                <button style="width: 100%" class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
                                <br>
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
                <div class="accordion-item">
                    <h3 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse2" aria-controls="collapse2">
                            Extrato de Clientes
                        </button>
                    </h3>
                    <div id="collapse2" class="accordion-collapse collapse" aria-labelledby="heading2" data-bs-parent="#accordionExample">
                        {!! Form::open()->get()->route('relatorioSuper.extratoCliente') !!}
                        <div class="row m-3">
                            <div class="col-12 mt-3">
                                {!! Form::select(
                                'empresa',
                                'Empresa',
                                ['' => 'Selecione'] + $empresas->pluck('razao_social', 'id')->all())->attrs([
                                'class' => 'form-select']) !!}
                            </div>
                            <div class="col-6 mt-3">
                                {!! Form::date('start_date', 'Data Inicial') !!}
                            </div>
                            <div class="col-6 mt-3">
                                {!! Form::date('end_date', 'Data Final') !!}
                            </div>
                            <div class="col-6 mt-3">
                                {!! Form::select('status', 'Status', ['' => 'Todos'] + ['ativo' => 'Ativo', 'desativado' => 'Desativado'])->attrs([
                                'class' => 'form-select',
                                ]) !!}
                            </div>
                            <div class="mt-3">
                                <button style="width: 100%" class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse3" aria-expanded="false" aria-controls="collapse3">
                            Histórico de Acessos
                        </button>
                    </h2>
                    <div id="collapse3" class="accordion-collapse collapse" aria-labelledby="heading3" data-bs-parent="#accordionExample">
                        {!! Form::open()->get()->route('relatorioSuper.historico') !!}
                        <div class="row m-3">
                            <div class="col-6">
                                {!! Form::date('start_date', 'Data Inicial') !!}
                            </div>
                            <div class="col-6">
                                {!! Form::date('end_date', 'Data Final') !!}
                            </div>
                            <div class="mt-3">
                                <button style="width: 100%" class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>

            <div class="col-6 mt-3">
                <div class="accordion-item">
                    <h3 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse21" aria-controls="collapse21">
                            Certificado à Vencer
                        </button>
                    </h3>
                    <div id="collapse21" class="accordion-collapse collapse" aria-labelledby="heading21" data-bs-parent="#accordionExample">
                        {!! Form::open()->route('relatorioSuper.certificados')->get() !!}
                        <div class="row m-3">
                            <div class="col-6">
                                {!! Form::date('start_date', 'Data Inicial') !!}
                            </div>
                            <div class="col-6">
                                {!! Form::date('end_date', 'Data Final') !!}
                            </div>
                            <div class="col-6 mt-3">
                                {!! Form::select('status', 'Status', ['' => 'Todos'] + ['vencidos' => 'Vencidos', 'vencer' => 'Á Vencer'])->attrs([
                                'class' => 'form-select',
                                ]) !!}
                            </div>
                            <div class="mt-3">
                                <button style="width: 100%" class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
                <div class="accordion-item">
                    <h3 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse22" aria-controls="collapse22">
                            Empresas por Contador
                        </button>
                    </h3>
                    <div id="collapse22" class="accordion-collapse collapse" aria-labelledby="heading22" data-bs-parent="#accordionExample">
                        {!! Form::open()->get()->route('relatorioSuper.contador') !!}
                        <div class="row m-3">
                            <div class="col-12">
                                {!! Form::select('contador', 'Contador', $contador->pluck('razao_social', 'id'))->attrs([
                                'class' => 'form-select',
                                ]) !!}
                            </div>
                            <div class="mt-3">
                                <button style="width: 100%" class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
