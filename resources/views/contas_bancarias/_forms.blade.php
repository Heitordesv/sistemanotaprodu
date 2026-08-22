<div class="row g-3">
    <div class="col-md-4">
        {!!Form::select('banco', 'Banco', App\Models\ContaBancaria::bancos())
        ->attrs(['class' => 'select2'])
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::text('agencia', 'Agência')->required()
        ->attrs(['class' => 'agencia'])
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::text('conta', 'Conta')
        ->attrs(['class' => 'conta_corrente'])->required()
        !!}
    </div>

    <div class="col-md-4">
        {!!Form::text('titular', 'Titular')->required()
        !!}
    </div>

    <div class="col-md-3">
        {!!Form::text('cnpj', 'CNPJ')->required()
        ->attrs(['class' => 'cnpj'])
        !!}
    </div>

    <div class="col-md-4">
        {!!Form::text('endereco', 'Endereço')->required()
        !!}
    </div>

    <div class="col-md-3">
        {!!Form::text('bairro', 'Bairro')->required()
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::text('cep', 'CEP')->required()
        ->attrs(['class' => 'cep'])
        !!}
    </div>

    <div class="col-md-6">
        {!!Form::select('cidade_id', 'Cidade')->required()
        ->options(isset($item) ? [$item->cidade_id => $item->cidade->info] : [])
        ->attrs(['class' => 'select2'])
        !!}
    </div>

    <h2>Padrão</h2>

    <div class="col-md-3">
        {!!Form::select('padrao', 'Padrão para emissão', [0 => 'Não', 1 => 'Sim'])
        ->attrs(['class' => 'select2'])
        !!}
    </div>

    <div class="col-md-3">
        {!!Form::select('usar_logo', 'Usar logo', [0 => 'Não', 1 => 'Sim'])
        ->attrs(['class' => 'select2'])
        !!}
    </div>

    <div class="col-md-3">
        {!!Form::text('carteira', 'Carteira')->required()
        ->attrs(['data-mask' => '000000000'])
        !!}
    </div>

    <div class="col-md-3">
        {!!Form::text('convenio', 'Convênio')->required()
        ->attrs(['data-mask' => '000000000'])
        !!}
    </div>

    <div class="col-md-3">
        {!!Form::text('juros', 'Juros')
        ->attrs(['class' => 'moeda'])
        !!}
    </div>

    <div class="col-md-3">
        {!!Form::text('multa', 'Multa')
        ->attrs(['class' => 'moeda'])
        !!}
    </div>

    <div class="col-md-3">
        {!!Form::text('juros_apos', 'Juros após (dias)')
        ->attrs(['data-mask' => '000'])
        !!}
    </div>

    <div class="col-md-3">
        {!!Form::select('tipo', 'Tipo', [ 'Cnab400' => 'Cnab400', 'Cnab240' => 'Cnab240'])
        ->attrs(['class' => 'select2'])
        !!}
    </div>

    <div class="col-12">
        <button type="submit" class="btn btn-primary px-5">Salvar</button>
    </div>
</div>
