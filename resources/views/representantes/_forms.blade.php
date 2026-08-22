<div class="row g-3">
    <div class="col-md-4">
        {!! Form::tel('cpf_cnpj', 'CPF/CNPJ')->attrs(['class' => 'cpf_cnpj'])->required() !!}
    </div>
    <div class="col-md-1 col-6"><br>
        <button class="btn btn-dark btn-block w-100" type="button" id="btn-consulta-cnpj">
            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span><i class="bx bx-search"></i>
        </button>
    </div>
    <div class="col-md-6">
        {!! Form::text('nome', 'Nome')->required() !!}
    </div>
    <div class="col-md-5">
        {!! Form::text('rua', 'Rua')->required() !!}
    </div>
    <div class="col-md-2">
        {!! Form::tel('numero', 'Número')->required()->attrs(['data-mask' => '00000']) !!}
    </div>
    <div class="col-md-4">
        {!! Form::text('bairro', 'Bairro')->required() !!}
    </div>
    <div class="col-md-4">
        {!! Form::select('cidade_id', 'Cidade')->required()->attrs(['class' => 'select2'])
        ->options(isset($item) ? [$item->cidade_id => $item->cidade->info] : [])
        !!}
    </div>
    <div class="col-md-5">
        {!! Form::text('email', 'E-mail')->attrs(['class' => 'e-mail'])->required() !!}
    </div>
    <div class="col-md-2">
        {!! Form::tel('telefone', 'Telefone')->attrs(['class' => 'fone'])->required() !!}
    </div>
    <hr class="mt-4">
    @if(!isset($item))
    <div class="col-md-5">
        {!! Form::text('login', 'Login')->required()
        ->value(isset($item) ? $item->usuario->login : '') !!}
    </div>
    <div class="col-md-3">
        {!! Form::text('senha', 'Senha')->required()->attrs(['minlength' => '8']) !!}
    </div>
    <div class="col-md-4">
        {!! Form::text('nome_usuario', 'Nome do usuário')->required()
        ->value(isset($item) ? $item->usuario->nome : '') !!}
    </div>
    @endif
 <div class="col-md-5">
    {!! Form::select('empresa', 'Empresa Representante', ['' => 'Selecione'] + $empresas)
        ->attrs(['class' => 'form-select select2'])
        ->required() 
    !!}
</div>
    <div class="col-md-2">
        {!! Form::tel('comissao', 'Comissão %')->attrs(['class' => ''])->required() !!}
    </div>
    <div class="col-md-2">
        {!! Form::select('acesso_xml', 'Acesso a XML', [0 => 'Não', 1 => 'Sim'])->attrs(['class' => 'form-select']) !!}
    </div>
    <div class="col-md-3">
        {!! Form::tel('limite_cadastros', 'Limite de cadastro de empresas')->attrs(['class' => ''])->required() !!}
    </div>
    <div class="col-12 mt-5">

        <button type="submit" class="btn btn-primary px-5">Salvar</button>
    </div>
</div>

