@extends('default.layout', ['title' => 'Nova NFe remessa'])
@section('content')
<div class="page-content">
    <div class="card">
        {!! Form::open()->post()->route('nferemessa.store')
        ->multipart() !!}
        <div class="pl-lg-4">
            @include('nfe_remessa._forms')
        </div>
        {!! Form::close() !!}
    </div>
</div>

@section('js')
<script>
    function salvar(t) {
        $("#type").val(t)
        $('#form-venda').submit()
    }

    function selectDiv2(ref) {
        $('.btn-outline-primary').removeClass('active')
        if (ref == 'transporte') {
            $('.div-transporte').removeClass('d-none')
            $('.div-itens').addClass('d-none')
            $('.div-pagamento').addClass('d-none')
            $('.btn-transporte').addClass('active')
        }
    }

    function salvar(t) {
        $("#type").val(t)
        $('#form-venda').submit()
    }

    function selectDiv2(ref) {
        $('.btn-outline-primary').removeClass('active')
        if (ref == 'transporte') {
            $('.div-transporte').removeClass('d-none')
            $('.div-itens').addClass('d-none')
            $('.div-pagamento').addClass('d-none')
            $('.btn-transporte').addClass('active')
        } else if (ref == 'itens') {
            $('.div-transporte').addClass('d-none')
            $('.div-itens').removeClass('d-none')
            $('.div-pagamento').addClass('d-none')
            $('.btn-itens').addClass('active')
        } else {
            $('.div-transporte').addClass('d-none')
            $('.div-itens').addClass('d-none')
            $('.div-pagamento').removeClass('d-none')
            $('.btn-pagamento').addClass('active')
        }
    }

</script>

<script type="text/javascript" src="/js/client.js"></script>
{{-- <script type="text/javascript" src="/js/vendas.js"></script> --}}
<script type="text/javascript" src="/js/product.js"></script>
<script type="text/javascript" src="/js/nfeRemessa.js"></script>
<script type="text/javascript" src="/js/transportadora.js"></script>


@endsection

@include('modals._produto', ['not_submit' => true])
@include('modals._client', ['not_submit' => true])
@include('modals._transportadora', ['not_submit' => true])
@include('modals._pagamento_personalizado', ['not_submit' => true])

@endsection
