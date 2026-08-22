@extends('default.layout', ['title' => 'Nova Cotação'])
@section('content')
<div class="page-content">
    <div class="card border-top border-0 border-4 border-primary">
        <div class="card-body p-5">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <a href="{{ route('cotacao.index') }}" type="button" class="btn btn-light btn-sm">
                        <i class="bx bx-arrow-back"></i> Voltar
                    </a>
                </div>
            </div>
            <div class="card-title d-flex align-items-center">
                <h5 class="mb-0 text-primary">Nova Cotação</h5>
            </div>
            <hr>

            {!! Form::open()->post()->route('cotacao.store')->multipart() !!}
            <div class="pl-lg-4">
                @include('cotacao._forms')
            </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>

@section('js')
<script>
    $('.btn-add-item').click(() => {
        let qtd = $('#inp-quantidade').val();
        let product_id = $("#inp-produto_id").val()
        if (qtd && product_id) {
            let dataRequest = {
                qtd: qtd
                , product_id: product_id
            , }
            $.get(path_url + "api/cotacao/linhaProduto", dataRequest)
                .done((e) => {
                    console.log(e);
                    $('.table-itens tbody').append(e)
                })
                .fail((e) => {
                    console.log(e)
                })
        } else {
            swal("Atenção", "Informe corretamente os campos para continuar!", "warning")
        }
    })
</script>
<script type="text/javascript" src="/js/fornecedor.js"></script>
@endsection
@include('modals._fornecedor', ['not_submit' => true])
@endsection
