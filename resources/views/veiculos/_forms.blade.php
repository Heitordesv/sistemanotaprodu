<div class="row g-3">
    <div class="col-md-2">
        {!!Form::text('placa', 'Placa')->required() 
        ->attrs(['class' => 'placa'])
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::select('uf', 'UF', App\Models\Cidade::estados())
        ->attrs(['class' => 'select2'])
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::text('cor', 'Cor')->required() 
        ->attrs(['data-mask' => 'AAAAAAAAAA'])
        !!}
    </div>

    <div class="col-md-3">
        {!!Form::text('marca', 'Marca')->required() 
        ->attrs(['data-mask' => 'AAAAAAAAAAAAAAAAAAA'])
        !!}
    </div>

    <div class="col-md-3">
        {!!Form::text('modelo', 'Modelo')->required() 
        ->attrs(['data-mask' => 'AAAAAAAAAAAAAAAAAAA'])
        !!}
    </div>

    <div class="col-md-3">
        {!!Form::text('rntrc', 'RNTRC')->required()
        ->attrs(['data-mask' => '00000000'])
        !!}
    </div>

    <div class="col-md-3">
        {!!Form::text('renavam', 'Renavam')
        ->attrs(['data-mask' => '000000000'])
        !!}
    </div>

    <div class="col-md-3">
        {!!Form::text('TAF', 'TAF')
        ->attrs(['data-mask' => '00000000000000'])
        !!}
    </div>

    <div class="col-md-3">
        {!!Form::text('numero_registro_estadual', 'Nº Registro estadual')
        ->attrs(['data-mask' => '0000000000000000000000000'])
        !!}
    </div>

    <div class="col-md-3">
        {!!Form::select('tipo', 'Tipo', ['' => 'Selecione'] + App\Models\Veiculo::tipos())->required()
        ->attrs(['class' => 'select2'])
        !!}
    </div>

    <div class="col-md-3">
        {!!Form::select('tipo_carroceria', 'Tipo carroceria', ['' => 'Selecione'] + App\Models\Veiculo::tiposCarroceria())
        ->attrs(['class' => 'select2'])->required()
        !!}
    </div>

    <div class="col-md-3">
        {!!Form::select('tipo_rodado', 'Tipo rodado', ['' => 'Selecione'] + App\Models\Veiculo::tiposRodado())
        ->attrs(['class' => 'select2'])->required()
        !!}
    </div>

    <div class="col-md-3">
        {!!Form::tel('tara', 'Tara')->required() 
        ->attrs(['data-mask' => '0000000000'])
        !!}
    </div>

    <div class="col-md-3">
        {!!Form::tel('capacidade', 'Capacidade')->required() 
        ->attrs(['data-mask' => '0000000000'])
        !!}
    </div>

    <div class="col-md-4">
        {!!Form::tel('proprietario_nome', 'Nome do proprietário')->required() 
        !!}
    </div>

    <div class="col-md-3">
        {!!Form::tel('proprietario_documento', 'CPF/CNPJ')->required() 
        ->attrs(['class' => 'cpf_cnpj'])
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::tel('proprietario_ie', 'IE')
        ->attrs(['class' => 'ie_rg'])->required()
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::select('proprietario_uf', 'UF proprietário', App\Models\Cidade::estados())
        ->attrs(['class' => 'select2'])->required()
        !!}
    </div>

    <div class="col-md-3">
        {!!Form::select('proprietario_tp', 'Tipo do proprietário', App\Models\Veiculo::tiposProprietario())
        ->attrs(['class' => 'select2'])->required()
        !!}
    </div>

    <hr>

    <div class="col-12">
        <button type="submit" class="btn btn-primary px-5">Salvar</button>
    </div>
</div>
