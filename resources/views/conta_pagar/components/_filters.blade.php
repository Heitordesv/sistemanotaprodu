    <div class="card shadow-sm border-0">
        <div class="card-body">
            <h6 class="mb-3 text-primary">Opções de Pesquisa</h6>
            {!! Form::open()->fill(request()->all())->get() !!}
            <div class="row g-3">
                <div class="col-md-4 space-y-2">
                    @php
                        $fornecedorOptions = ['' => 'Todos'] + $fornecedores->pluck('nome', 'id')->toArray();
                        $funcionarioOptions = ['' => 'Todos'] + $funcionarios->pluck('nome', 'id')->toArray();
                    @endphp

                    {!! Form::select('fornecedor_id', 'Fornecedor', $fornecedorOptions)->attrs(['class' => 'select2 form-control-sm']) !!}
                    {!! Form::select('funcionario_id', 'Funcionário', $funcionarioOptions)->attrs(['class' => 'select2 form-control-sm']) !!}
                </div>

                <div class="col-md-2">
                    {!! Form::select('type_search', 'Período por', [
                        'created_at' => 'Data de Cadastro',
                        'data_vencimento' => 'Data de Vencimento',
                        'data_pagamento' => 'Data de Pagamento',
                    ])->attrs(['class' => 'form-select form-select-sm']) !!}
                </div>
                <div class="col-md-2">{!! Form::date('start_date', 'De')->attrs(['class' => 'form-control-sm']) !!}</div>
                <div class="col-md-2">{!! Form::date('end_date', 'Até')->attrs(['class' => 'form-control-sm']) !!}</div>
                <div class="col-md-2">
                    {!! Form::select('status', 'Status', ['' => 'Todos', '1' => 'Pago', '0' => 'Pendente'])->attrs(['class' => 'form-select form-select-sm']) !!}
                </div>
                
                @if(empresaComFilial())
                    <div class="col-md-4 mt-3">
                        {!! __view_locais_select_filtro("Local", $filial_id ?? '')->attrs(['class' => 'select2 form-control-sm']) !!}
                    </div>
                @endif

                <div class="col-md-12 text-end mt-3">
                    <button class="btn btn-primary btn-sm px-4" type="submit"><i class="bx bx-search"></i> Pesquisar</button>
                    <a class="btn btn-danger btn-sm px-4" href="{{ route('conta-pagar.index') }}"><i class="bx bx-eraser"></i> Limpar</a>
                </div>
            </div>
            {!! Form::close() !!}
        </div>
    </div>
