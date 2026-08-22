<div class="row g-3">
    <!-- Referência -->
    <div class="col-md-2">
        {!! Form::text('referencia', 'Referência')->required() !!}
    </div>

    <!-- Cliente -->
    <div class="col-md-4">
        <div class="form-group">
            <label for="inp-cliente_id" class="required">Cliente</label>
            <div class="input-group">
                <select class="form-control" name="cliente_id" id="inp-cliente_id">
                    @isset($item)
                        <option value="{{ $item->cliente_id }}">{{ $item->cliente->razao_social }}</option>
                    @endif
                </select>
                <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#modal-cliente">
                    <i class="bx bx-plus"></i>
                </button>
            </div>
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

    <!-- Tabela de Recorrência (inicialmente oculta) -->
    <div class="row tbl-recorrencia d-none mt-2">
    </div>

    <!-- Botão de Salvar -->
    <div class="col-12">
        <button type="submit" class="btn btn-primary px-5 float-end">Salvar</button>
    </div>
</div>
