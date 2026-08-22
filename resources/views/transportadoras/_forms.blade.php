<div class="row g-3">
    <div class="col-md-4">
        {!! Form::tel('cnpj_cpf', 'CPF/CNPJ')->required()->attrs(['class' => 'cpf_cnpj']) !!}
    </div>
    
    <!-- <div class="col-md-1 col-6"><br>
        <button class="btn btn-dark btn-block w-100" type="button" id="btn-consulta-cnpj">
            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span><i class="bx bx-search"></i>
        </button>
    </div> -->

    <div class="col-12"></div>

    <div class="col-md-6">
        {!! Form::text('razao_social', 'Razão social')->required() !!}
    </div>

    <div class="col-md-3">
        {!! Form::text('email', 'Email')->type('email')->required() !!}
    </div>

    <div class="col-md-2">
        {!! Form::tel('telefone', 'Telefone')->attrs(['class' => 'fone'])->required() !!}
    </div>

    <div class="col-md-8">
        {!! Form::text('logradouro', 'Logradouro')->required() !!}
    </div>
    <div class="col-md-4">
        {!! Form::select('cidade_id', 'Cidade')->attrs(['class' => 'select2'])->required() !!}
    </div>
    <hr>
    <div class="col-12">
        @isset($not_submit)
        <button type="button" class="btn btn-primary px-5" id="btn-store-transportadora">Salvar</button>
        @else
        <button type="submit" class="btn btn-primary px-5">Salvar</button>
        @endif
    </div>
</div>

