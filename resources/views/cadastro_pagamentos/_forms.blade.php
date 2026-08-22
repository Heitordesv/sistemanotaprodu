<div class="row g-3">
    <div class="col-md-4">
        {!!Form::text('f_pagamento', 'Nome')->required()
        ->attrs(['class' => 'form-control'])
        !!}
    </div>

    <div class="col-12">
    <button type="submit" class="btn btn-primary px-5" id="btn-store">Salvar</button>

    </div>
</div>
