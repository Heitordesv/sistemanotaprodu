<div class="row g-3">
    <div class="col-md-5">
        {!!Form::tel('nome', 'Nome')->required()
        !!}
    </div>
    <div class="col-md-3">
        {!!Form::tel('alerta_amarelo', 'Alerta amarelo(min)')->required()->attrs(['data-mask' => '00:00'])
        !!}
    </div>
    <div class="col-md-3">
        {!!Form::tel('alerta_vermelho', 'Alerta vermelho(min)')->required()->attrs(['data-mask' => '00:00'])
        !!}
    </div>
    <div class="col-12">
        <button type="submit" class="btn btn-primary px-5">Salvar</button>
    </div>

</div>
