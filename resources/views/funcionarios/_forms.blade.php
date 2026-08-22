<div class="row g-3">

    <div class="col-md-4">
        {!! Form::text('nome', 'Nome')->required() !!}
    </div>

    <div class="col-md-3">
        {!! Form::tel('cpf', 'CPF')->attrs(['class' => 'cpf'])->required()  !!}
    </div>

    <div class="col-md-2">
        {!! Form::tel('rg', 'RG')->required()->attrs(['class' => 'ie_rg']) !!}
    </div>

    <div class="col-md-3">
        {!! Form::text('email', 'Email')->type('email') !!}
    </div>

    <div class="col-md-2">
        {!! Form::tel('celular', 'Celular')->required() ->attrs(['class' => 'fone']) !!}
    </div>

    <div class="col-md-2">
        {!! Form::tel('telefone', 'Telefone')->required() ->attrs(['class' => 'fone']) !!}
    </div>

    <div class="col-md-2">
        {!! Form::date('data_registro', 'Data do registro')->required()  !!}
    </div>
    
      <div class="col-md-2">
        {!! Form::date('data_nasci', 'Data do Nascimento')->required()  !!}
    </div>


    <div class="col-md-2">
        {!! Form::tel('percentual_comissao', '% Comissão')->attrs(['class' => 'perc']) !!}
    </div>

    <div class="col-md-2">
        {!! Form::tel('salario', 'Salário')->required() ->attrs(['class' => 'moeda']) !!}
    </div>

    <div class="col-md-3">
        {!! Form::select('usuario_id', 'Usuário (opcional)', [null => 'Selecione' ] + $usuarios->pluck('nome', 'id')->all())->attrs([
            'class' => 'select2',
        ]) !!}
    </div>

    <hr class="mt-4">

    <h5>Endereço</h5>

    <div class="col-md-4">
        {!! Form::text('rua', 'Rua')->required()  !!}
    </div>
    <div class="col-md-2">
        {!! Form::tel('numero', 'Número')->required()->attrs(['data-mask' => '00000000']) !!}
    </div>

    <div class="col-md-2">
        {!! Form::text('bairro', 'Bairro')->required()  !!}
    </div>

    <div class="col-md-3">
        {!! Form::select('cidade_id', 'Cidade')->required()->attrs(['class' => 'select2'])->options(isset($item) ? [$item->cidade_id => $item->cidade->info] : []) !!}
    </div>

    <hr>

    <div class="col-12">
        <button type="submit" class="btn btn-primary px-5">Salvar</button>
    </div>
</div>

@section('js')
@endsection
