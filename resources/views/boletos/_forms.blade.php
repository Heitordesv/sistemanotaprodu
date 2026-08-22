<div class="row g-3">
    <div class="col-md-3">
        {!!Form::select('conta_bancaria_id', 'Conta bancária', ['' => 'Selecione'] + $contasBancarias->pluck('info', 'id')->all())
        ->attrs(['class' => 'form-select'])
        ->required()
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::text('carteira', 'Carteira')->required()
        ->attrs(['data-mask' => '000000000'])
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::text('convenio', 'Convênio')->required()
        ->attrs(['data-mask' => '000000000'])
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::select('usar_logo', 'Usar logo', [0 => 'Não', 1 => 'Sim'])
        ->attrs(['class' => 'select2'])
        !!}
    </div>

    <div class="col-md-3">
        {!!Form::select('tipo', 'Tipo', [ 'Cnab400' => 'Cnab400', 'Cnab240' => 'Cnab240'])
        ->attrs(['class' => 'select2'])
        !!}
    </div>

    <div class="col-md-2 div-aux">
        {!!Form::text('posto', 'Posto')->required()
        !!}
    </div>

    <div class="col-md-2 div-aux">
        {!!Form::text('codigo_cliente', 'Código do cliente')->required()
        !!}
    </div>

    @foreach($contas as $c)
    <input type="hidden" name="conta_id" value="{{ $c->id }}">
    <div class="card">
        <div class="card-header">
            <div class="row">
                <div class="form-group col-12 col-lg-6">
                    <h5>Razão Social: <strong class="text-primary">{{$c->getCliente()->razao_social}}</strong></h5>
                    <h5>CPF/CNPJ: <strong class="text-primary">{{$c->getCliente()->cpf_cnpj}}</strong></h5>
                    <h5>Cidade: <strong class="text-primary">{{$c->getCliente()->cidade->nome}} ({{$c->getCliente()->cidade->uf}})</strong></h5>
                </div>

                <div class="form-group col-12 col-lg-6">
                    <h5>Valor: <strong class="text-primary">R$ {{number_format($c->valor_integral, 2, ',', '.')}}</strong></h5>
                    <h5>Vencimento: <strong class="text-primary">{{ \Carbon\Carbon::parse($c->data_vencimento)->format('d/m/Y')}}</strong></h5>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-2">
                    {!!Form::text('numero_boleto[]', 'Nº do boleto')->required()
                    ->attrs(['class' => 'numero_boleto'])
                    !!}
                </div>
                <div class="col-md-2">
                    {!!Form::text('numero_documento[]', 'Nº do documento')->required()
                    ->attrs(['class' => 'numero_documento'])
                    !!}
                </div>

                <div class="col-md-2">
                    {!!Form::text('juros[]', 'Juros')->required()
                    ->attrs(['class' => 'juros moeda'])
                    !!}
                </div>
                <div class="col-md-2">
                    {!!Form::text('multa[]', 'Multa')->required()
                    ->attrs(['class' => 'multa moeda'])
                    !!}
                </div>
                <div class="col-md-2">
                    {!!Form::text('juros_apos[]', 'Juros após (dias)')->required()
                    ->attrs(['class' => 'juros_apos'])
                    !!}
                </div>
            </div>
        </div>
    </div>
    @endforeach

    <div class="col-12">
        <button type="submit" class="btn btn-primary px-5">Salvar</button>
    </div>

</div>

@section('js')
<script type="text/javascript">
    $(function(){
        getConta()
    })

    $('#inp-conta_bancaria_id').change(() => {
        getConta()
    })

    function getConta(){
        $('#inp-posto').val('')
        $('#inp-posto').removeAttr('required')
        $('#inp-codigo_cliente').val('')
        $('#inp-codigo_cliente').removeAttr('required')

        $('.div-aux').addClass('d-none')
        let conta = $('#inp-conta_bancaria_id').val()
        if(conta){
            $.get(path_url + 'api/conta-bancaria-get/'+conta)
            .done((c) => {
                console.log(c)
                $('#inp-carteira').val(c.carteira)
                $('#inp-convenio').val(c.convenio)
                $('#inp-tipo').val(c.tipo).change()
                $('.juros').val(c.juros)
                $('.multa').val(c.multa)
                $('.juros_apos').val(c.juros_apos)
                if(c.banco == 'Sicredi' || c.banco == 'Caixa Econônica Federal' || c.banco == 'Santander'){
                    $('.div-aux').removeClass('d-none')
                    $('#inp-posto').attr('required', 1)
                    $('#inp-codigo_cliente').attr('required', 1)

                }
            }).fail((err) => {
                console.log(err)
            })
        }
    }
</script>
@endsection