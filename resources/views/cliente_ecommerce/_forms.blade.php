<div class="row g-3">
    <div class="col-md-3">
        {!! Form::text('nome', 'Nome')->required()->attrs(['class' => '']) !!}
    </div>
    <div class="col-md-5">
        {!! Form::text('sobre_nome', 'Sobre Nome')->required()->attrs(['class' => '']) !!}
    </div>
    <div class="col-md-4">
        {!! Form::tel('cpf', 'CPF / CNPJ')->required()->attrs(['class' => 'cpf_cnpj']) !!}
    </div>
    <div class="col-md-3">
        {!! Form::tel('telefone', 'Telefone')->required()->attrs(['class' => 'fone']) !!}
    </div>  
    <div class="col-md-6">
        {!! Form::text('email', 'E-mail')->required()->attrs(['class' => '']) !!}
    </div>
    <div class="col-md-3">
        {!! Form::text('senha', 'Senha')->type('password')->required()->attrs(['']) !!}
    </div>
    <div class="col mt-5">
        <button type="submit" class="btn btn-primary px-5">Salvar</button>
    </div>
</div>
