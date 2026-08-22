<div class="row g-3">
    <!-- Referência -->
    <div class="col-md-2">
        {!! Form::text('referencia', 'Referência')->required() !!}
    </div>

<!-- Empresa -->
           <div class="col-md-4">
    <div class="form-group">
        @if(isSuper(session('user_logged')['super']))
            <label class="required">Empresa</label>
            <select class="form-control" name="empresa_id_emp" required>
                <option value="">Selecione uma empresa</option>

                @foreach($empresas as $empresa)
                    <option value="{{ $empresa->id }}"
                        {{ (isset($item) && $item->empresa_id_emp == $empresa->id) ? 'selected' : '' }}>
                        {{ $empresa->razao_social }}
                    </option>
                @endforeach
            </select>
        @else
            <label>Cliente</label>
            <select class="form-control" name="cliente_id">
                <option value="">Selecione um cliente</option>

                @foreach($clientes as $cliente)
                    <option value="{{ $cliente->id }}"
                        {{ (isset($item) && $item->cliente_id == $cliente->id) ? 'selected' : '' }}>
                        {{ $cliente->razao_social }}
                    </option>
                @endforeach
            </select>
        @endif
    </div>
</div>

    <!-- Categoria -->
    <div class="col-md-2">
        {!! Form::select('categoria_id', 'Categoria', $categorias->pluck('nome', 'id')->all())
        ->attrs(['class' => 'form-select'])->required() !!}
    </div>

    <!-- Valor -->
    <div class="col-md-2">
        {!! Form::tel('valor_integral', 'Valor')->required()
        ->attrs(['class' => 'moeda'])
        ->value(isset($item) ? __moeda($item->valor_integral) : '') !!}
    </div>

    <!-- Data de Vencimento -->
    <div class="col-md-2">
        {!! Form::date('data_vencimento', 'Vencimento')->required() !!}
    </div>

    <!-- Tipo de Pagamento -->
    <div class="col-md-2">
        {!! Form::select('tipo_pagamento', 'Tipo de pagamento', App\Models\ContaReceber::tiposPagamento())
        ->attrs(['class' => 'form-select']) !!}
    </div>

    <!-- Status -->
    <div class="col-md-2">
        {!! Form::select('status', 'Conta recebida', ['0' => 'Não', '1' => 'Sim'])
        ->attrs(['class' => 'form-select']) !!}
    </div>

    <!-- Quantidade de Parcelas -->
    <div class="col-md-2">
        <label for="quantidade_parcelas">Quantidade de Parcelas</label>
        <input type="number" name="quantidade_parcelas" class="form-control" step="any">
    </div>

    <!-- Local (Filial) -->
    @isset($item)
        {!! __view_locais_select_edit("Local", $item->filial_id) !!}
    @else
        {!! __view_locais_select() !!}
    @endif

    <hr>

    <!-- Recorrência (somente para novos itens) -->
    @if(!isset($item))
        <p class="text-danger">
            *Campo abaixo deve ser preenchido se houver recorrência para este registro
        </p>
        <div class="col-md-2">
            {!! Form::tel('recorrencia', 'Data de Recorrência')
            ->attrs(['data-mask' => '00/00'])
            ->placeholder('mm/aa') !!}
        </div>
    @endif
<input type="hidden" name="previous_url" value="{{ url()->previous() }}">

    <!-- Tabela de Recorrência (inicialmente oculta) -->
    <div class="row tbl-recorrencia d-none mt-2">
    </div>

    <!-- Botão de Salvar -->
    <div class="col-12">
        <button type="submit" class="btn btn-primary px-5 float-end">Salvar</button>
    </div>
</div>
