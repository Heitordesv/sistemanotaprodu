<div class="row g-3">
    <div class="col-md-6">
        {!!Form::text('razao_social', 'Razão social')->required()
        !!}
    </div>
    <div class="col-md-5">
        {!!Form::text('nome_fantasia', 'Nome fantasia')->required()
        !!}
    </div>

    <div class="col-md-4">
        {!!Form::tel('cnpj', 'CNPJ')->required()
        ->attrs(['class' => 'cpf_cnpj'])
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::tel('ie', 'I.E')->attrs(['class' => 'ie_rg'])->required()
        !!}
    </div>
    <div class="col-md-5">
        {!!Form::text('logradouro', 'Rua')->required()
        !!}
    </div>
    <div class="col-md-2">
        {!!Form::tel('numero', 'Número')->required()
        !!}
    </div>
    <div class="col-md-4">
        {!!Form::text('bairro', 'Bairro')->required()
        !!}
    </div>
    <div class="col-md-2">
        {!!Form::tel('fone', 'Telefone')->required()
        ->attrs(['class' => 'form-control fone'])
        !!}
    </div>
    <div class="col-md-3">
        {!!Form::tel('cep', 'CEP')->required()
        ->attrs(['class' => 'form-control cep'])
        !!}
    </div>
    <div class="col-md-4">
        {!!Form::text('email', 'Email')->required()
        !!}
    </div>
    <div class="col-md-3">
        {!!Form::tel('token_sieg', 'Token SIEG')
        !!}
    </div>
    <div class="col-md-4">
        {!!Form::select('cidade_id', 'Cidade', ['' => 'Selecione a cidade'] + $cidades->pluck('nome', 'id')->all())->required()
        ->attrs(['class' => 'form-control select2'])
        !!}
    </div>

    <div class="col-md-3">
        {!!Form::select('envio_automatico_xml_contador', 'Envio automático de XML',
        ['0' => 'Não', '1' => 'Sim'])
        ->attrs(['class' => 'form-select'])
        !!}
    </div>

    <div class="col-12">
        <button type="submit" class="btn btn-primary px-5">Salvar</button>
    </div>
</div>


