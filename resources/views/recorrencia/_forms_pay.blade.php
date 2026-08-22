<div class="row g-3">
   
    <div class="col-md-2">
        {!!Form::tel('valor_pago', 'Valor')
        ->attrs(['class' => 'moeda'])
        ->required()
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::date('data_recebimento', 'Data do Recebimento')
        ->required()
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::select('tipo_pagamento', 'Tipo de Pagamento', ['' => 'Selecione'] + App\Models\ContaReceber::tiposPagamento())
        ->attrs(['class' => 'form-select'])
        ->required()
        !!}
    </div>

    <div class="col-12">
        <button type="submit" class="btn btn-primary px-5">Receber</button>
    </div>
</div>