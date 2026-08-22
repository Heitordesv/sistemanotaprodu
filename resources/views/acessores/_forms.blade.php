<div class="row g-3">
    <div class="col-md-4">
        {!!Form::text('cpf_cnpj', 'CPF/CNPJ')->required()
        ->attrs(['class' => 'cpf_cnpj'])
        !!}
    </div>
    <div class="col-md-1 col-6"><br>
        <button class="btn btn-dark btn-block w-100" type="button" id="btn-consulta-cnpj">
            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span><i class="bx bx-search"></i>
        </button>
    </div>

    <div class="col-md-6">
        {!!Form::text('razao_social', 'Razão social')->required()
        !!}
    </div>
    <div class="col-md-3    ">
        {!!Form::text('ie_rg', 'IE/RG')->attrs(['class' => 'ie_rg'])
        !!}
    </div>
    <div class="col-md-4">
        {!!Form::text('email', 'Email')->required()
        ->type('email')
        !!}
    </div>
    <div class="col-md-2">
        {!!Form::tel('celular', 'Celular')->required()
        ->attrs(['class' => 'fone'])
        !!}
    </div>
    <div class="col-md-2">
        {!!Form::tel('telefone', 'Telefone')->required()
        ->attrs(['class' => 'fone'])
        !!}
    </div>
    <hr class="mt-4">
    <h5>Endereço</h5>
    <div class="col-md-6">
        {!!Form::text('rua', 'Rua')->required()
        !!}
    </div>
    <div class="col-md-2">
        {!!Form::text('numero', 'Número')->required()
        ->attrs(['data-mask' => '0000000000'])
        !!}
    </div>
    <div class="col-md-2">
        {!!Form::text('bairro', 'Bairro')->required()
        !!}
    </div>
    <div class="col-md-2">
        {!!Form::text('cep', 'CEP')->required()
        ->attrs(['class' => 'cep'])
        !!}
    </div>
    <div class="col-md-4">
        {!!Form::select('cidade_id', 'Cidade')->attrs(['class' => 'select2'])->options(isset($item) ? [$item->cidade_id => $item->cidade->info] : [])
        !!}
    </div>
    <hr class="mt-4">
    <h5>Dados</h5>
    <div class="col-md-3">
        {!!Form::date('data_registro', 'Data do registro')->required()
        !!}
    </div>
    <div class="col-md-2">
        {!!Form::tel('percentual_comissao', '% Comissão')
        ->attrs(['class' => 'moeda'])
        !!}
    </div>
    <div class="col-md-3">
        {!!Form::select('funcionario_id', 'Funcionário (opcional)', ['' => 'Selecione'] + $funcionarios->pluck('nome', 'id')
        ->all())
        !!}
    </div>
    <div class="col-md-3 form-switch">
        {!!Form::select('ativo', 'Ativo', [1 => 'Sim', 0 => 'Não'])->attrs(['class' => 'form-select'])
        !!}
    </div>
    <hr class="mt-4">
    <div class="col-12">
        <button type="submit" class="btn btn-primary px-5">Salvar</button>
    </div>
</div>

@section('js')

@endsection
