<div class="row g-3">

    <div class="col-md-5">
        {!!Form::text('nome', 'Nome')->required()
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::tel('valor', 'Valor')->required()
        ->attrs(['class' => 'moeda'])
        !!}
    </div>

    <div class="col-md-3">
        {!!Form::text('tempo_servico', 'Tempo de serviço (min)')->required()
        ->attrs(['data-mask' => '0000'])
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::tel('comissao', 'Comissão (opcional)')
        ->attrs(['class' => 'moeda'])
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::select('categoria_id', 'Categoria', $categorias->pluck('nome', 'id')->all())->required()
        ->attrs(['class' => 'form-select'])
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::select('unidade_cobranca', 'Unidade de cobrança', ['UND' => 'UND', 'HORAS' => 'HORAS', 'MIN' => 'MIN'])
        ->attrs(['class' => 'select2'])
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::text('tempo_adicional', 'Tempo adicional')
        ->attrs(['data-mask' => '00:00'])
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::tel('valor_adicional', 'Valor adicional')->attrs(['class' => 'moeda'])
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::text('tempo_tolerancia', 'Tempo de tolerância')
        ->attrs(['data-mask' => '00:00'])
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::tel('codigo_servico', 'Código do serviço')->attrs(['class' => ''])
        !!}
    </div>

    <div class="card mt-4">
        <div class="row m-5">
            <h6>Tributação</h6>
            <div class="col-md-3">
                {!!Form::tel('aliquota_iss', '% ISS')->attrs(['data-mask' => '000,00'])
                !!}
            </div>
            <div class="col-md-3">
                {!!Form::tel('aliquota_pis', '% PIS')->attrs(['data-mask' => '000,00'])
                !!}
            </div>
            <div class="col-md-3">
                {!!Form::tel('aliquota_cofins', '% COFINS')->attrs(['data-mask' => '000,00'])
                !!}
            </div>
            <div class="col-md-3">
                {!!Form::tel('aliquota_inss', '% INSS')->attrs(['data-mask' => '000,00'])
                !!}
            </div>
        </div>
    </div>

</div>

<hr>

<div class="col-12">
    <button type="submit" class="btn btn-primary px-5">Salvar</button>
</div>

@section('js')
@endsection
