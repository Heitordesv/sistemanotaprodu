<div class="row g-3">
    <div class="col-md-6">
        {!!Form::text('natureza', 'Descrição')
        ->attrs(['class' => 'form-control'])
        ->required()
        !!}
    </div>

    <div class="col-md-3">
        {!!Form::select('sobrescreve_cfop', 'Sobrescrever CFOP do produto', [0 => 'Não', 1 => 'Sim'])
        ->attrs(['class' => 'form-select'])
        ->required()
        !!}
    </div>

    <div class="col-md-3">
        {!!Form::select('finNFe', 'Finalidade da NFe', App\Models\NaturezaOperacao::finalidades())
        ->attrs(['class' => 'form-select'])
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::text('CFOP_saida_estadual', 'CFOP de saída estadual')
        ->attrs(['class' => 'form-control cfop'])
        ->required()
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::text('CFOP_saida_inter_estadual', 'CFOP de saída outro estado')
        ->attrs(['class' => 'form-control cfop'])
        ->required()
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::text('CFOP_entrada_estadual', 'CFOP de entrada estadual')
        ->attrs(['class' => 'form-control cfop'])
        ->required()
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::text('CFOP_entrada_inter_estadual', 'CFOP de entrada outro estado')
        ->attrs(['class' => 'form-control cfop'])
        ->required()
        !!}
    </div>

    <div class="col-md-4">
        {!!Form::select('nao_movimenta_estoque', 'Não movimentar estoque <button type="button" class="btn btn-secondary btn-sm btn-outline btn-primary" data-bs-container="body" data-bs-toggle="popover" data-bs-placement="right" data-bs-content="Se marcado a ação de venda com esta natureza de operação, será sem movimentação de estoque." data-bs-original-title="" title="" aria-describedby="popover187810"><i class="bx bx-info-circle"></i></button>', [0 => 'Não', 1 => 'Sim'])
        ->attrs(['class' => 'form-select'])
        !!}
    </div>

    <div class="col-12">
        <button type="submit" class="btn btn-primary px-5">Salvar</button>
        
    </div>
</div>

@section('js')
<script type="text/javascript">
    $(function () {
        $('[data-bs-toggle="popover"]').popover();
    })

    $('#inp-CFOP_saida_estadual').blur(() => {
        let cfop = $('#inp-CFOP_saida_estadual').val()
        let temp = cfop.substring(1,4)
        if(cfop.length == 4){
            $('#inp-CFOP_saida_inter_estadual').val('6'+temp)
            $('#inp-CFOP_entrada_inter_estadual').val('2'+temp)
            $('#inp-CFOP_entrada_estadual').val('1'+temp)
        }
    })
</script>
@endsection
