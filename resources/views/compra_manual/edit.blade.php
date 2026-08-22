@extends('default.layout', ['title' => 'Editar Compra'])
@section('content')
<div class="page-content">
    <div class="card ">

        {!!Form::open()->fill($item)
        ->put()
        ->route('compraManual.update', [$item->id])
        !!}
        @include('compra_manual._forms')
        {!!Form::close()!!}
        
    </div>
</div>

@section('js')
<script>
    $(function() {
        $('[data-bs-toggle="popover"]').popover();
    });

    function selectDiv2(ref) {
        $('.btn-outline-primary').removeClass('active')
        if (ref == 'frete') {
            $('.div-frete').removeClass('d-none')
            $('.div-itens').addClass('d-none')
            $('.div-pagamento').addClass('d-none')
            $('.btn-frete').addClass('active')

        } else if (ref == 'itens') {
            $('.div-frete').addClass('d-none')
            $('.div-itens').removeClass('d-none')
            $('.div-pagamento').addClass('d-none')
            $('.btn-itens').addClass('active')

        } else {
            $('.div-frete').addClass('d-none')
            $('.div-itens').addClass('d-none')
            $('.div-pagamento').removeClass('d-none')
            $('.btn-pagamento').addClass('active')
        }

    }

</script>
<script type="text/javascript" src="/js/compra.js"></script>
<script src="/js/product.js"></script>

@endsection
@include('modals._produto', ['not_submit' => true])

@endsection

