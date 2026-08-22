<div class="collapse mb-4" id="filterCollapse">
    <div class="card shadow-sm border-0">
        <div class="card-body">

            {!! Form::open()->fill(request()->all())->get() !!}

            @php 
                $isSuper = isSuper(session('user_logged')['super']); 
            @endphp

            <div class="row g-3">

                {{-- EMPRESA / CLIENTE --}}
                <div class="col-lg-4 col-md-6">
                    <label class="form-label">Cliente / Empresa</label>

                    <select name="{{ $isSuper ? 'empresa_id_emp' : 'cliente_id' }}" class="form-select select2">
                        <option value="">Todos</option>

                        @foreach ($isSuper ? $empresas : $clientes as $item)
                            <option value="{{ $item->id }}"
                                {{ request($isSuper ? 'empresa_id_emp' : 'cliente_id') == $item->id ? 'selected' : '' }}>
                                
                                {{ $item->razao_social ?: $item->razao_social }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- GRUPO --}}
                <div class="col-lg-3 col-md-6">
                    <label class="form-label">Grupo</label>

                    <select name="grupo_id" class="form-select">
                        <option value="">Todos</option>

                        @foreach($grupos as $grupo)
                            <option value="{{ $grupo->id }}" {{ request('grupo_id') == $grupo->id ? 'selected' : '' }}>
                                {{ $grupo->nome }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- STATUS --}}
                <div class="col-lg-2 col-md-4">
                    {!! Form::select('status', 'Status', [
                        '' => 'Todos',
                        '1' => 'Recebido',
                        '0' => 'Pendente',
                    ])->attrs(['class' => 'form-select']) !!}
                </div>

                {{-- TIPO DATA --}}
                <div class="col-lg-3 col-md-4">
                    {!! Form::select('type_search', 'Tipo de Data', [
                        'created_at' => 'Cadastro',
                        'data_vencimento' => 'Vencimento',
                        'data_recebimento' => 'Recebimento',
                    ])->attrs(['class' => 'form-select']) !!}
                </div>

                {{-- DATAS --}}
                <div class="col-lg-2 col-md-4">
                    {!! Form::date('start_date', 'De') !!}
                </div>

                <div class="col-lg-2 col-md-4">
                    {!! Form::date('end_date', 'Até') !!}
                </div>

                {{-- FILIAL --}}
                @if(empresaComFilial())
                    <div class="col-lg-3 col-md-6">
                        {!! __view_locais_select_filtro("Filial", $filial_id ?? '') !!}
                    </div>
                @endif

                {{-- BOTÕES --}}
                <div class="col-12 d-flex gap-2 mt-2">

                    <button class="btn btn-primary">
                        <i class="bx bx-search me-1"></i> Filtrar
                    </button>

                    <a href="{{ route('conta-receber.index') }}" class="btn btn-light">
                        <i class="bx bx-eraser me-1"></i> Limpar
                    </a>

                </div>

            </div>

            {!! Form::close() !!}

        </div>
    </div>
</div>