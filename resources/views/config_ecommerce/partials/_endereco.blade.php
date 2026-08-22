<div class="card border-0 shadow-sm mb-4" id="secao-endereco">
    <div class="card-header bg-white border-bottom py-3">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle bg-light-info text-info d-flex align-items-center justify-content-center" style="width:42px;height:42px;">
                <i class="bx bx-map fs-4"></i>
            </div>
            <div>
                <h5 class="mb-0">Endereço e Contato</h5>
                <small class="text-muted">Dados usados na loja, retirada e cálculo de frete.</small>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-8">
                {!! Form::text('rua', 'Rua')->required() !!}
            </div>
            <div class="col-md-4">
                {!! Form::tel('numero', 'Número')->required() !!}
            </div>
            <div class="col-md-5">
                {!! Form::text('bairro', 'Bairro')->required() !!}
            </div>
            <div class="col-md-5">
                {!! Form::select('cidade_id', 'Cidade')
                    ->required()
                    ->options(isset($item) ? [$item->cidade_id => optional($item->cidade)->info] : []) !!}
            </div>
            <div class="col-md-2">
                {!! Form::select('uf', 'UF', App\Models\Veiculo::cUF())->attrs(['class' => 'select2']) !!}
            </div>
            <div class="col-md-4">
                {!! Form::tel('cep', 'CEP')->required()->attrs(['class' => 'cep']) !!}
                <small class="text-muted">Este CEP também é usado como origem no cálculo do frete.</small>
            </div>
            <div class="col-md-4">
                {!! Form::tel('telefone', 'Telefone')->required()->attrs(['class' => 'fone']) !!}
            </div>
            <div class="col-md-4">
                {!! Form::text('email', 'E-mail')->required()->type('email') !!}
            </div>
            <div class="col-md-6">
                {!! Form::text('latitude', 'Latitude')->required()->attrs(['class' => 'coordenadas']) !!}
            </div>
            <div class="col-md-6">
                {!! Form::text('longitude', 'Longitude')->required()->attrs(['class' => 'coordenadas']) !!}
            </div>
        </div>
    </div>
</div>