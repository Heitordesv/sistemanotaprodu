@extends('default.layout', ['title' => ''])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
            </div>
            <div class="pl-lg-4">
                <div class="pl-lg-4">
                    @include('orcamento._forms')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
    setTimeout(() => {
        $("#inp-produto_id").change(() => {
            let product_id = $("#inp-produto_id").val()
            if (product_id) {
                $.get(path_url + "api/produtos/find/" + product_id)
                    .done((e) => {
                        $('#inp-quantidade').val('1,00')
                        $('#inp-valor_unitario').val(convertFloatToMoeda(e.valor_venda))
                        $('#inp-subtotal').val(convertFloatToMoeda(e.valor_venda))
                    })
                    .fail((e) => {
                        console.log(e)
                    })
            }
        })
    }, 100)


    $('body').on('blur', '.value_unit', function() {
        let qtd = $('#inp-quantidade').val();
        let value_unit = $(this).val();
        value_unit = convertMoedaToFloat(value_unit)
        qtd = convertMoedaToFloat(qtd)
        $('#inp-subtotal').val(convertFloatToMoeda(qtd * value_unit))
    })
    var total_venda = 0

    function calcTotal() {
        var total = 0
        $("#inp-subtotal").each(function() {
            total += convertMoedaToFloat($(this).val())
        })
        setTimeout(() => {
            total_venda = total
            $('.total-venda').html("R$ " + convertFloatToMoeda(total))
        }, 100)
    }
    $('.btn-add-item').click(() => {
        let qtd = $("#inp-quantidade").val();
        let value_unit = $("#inp-valor_unitario").val();
        let sub_total = $("#inp-subtotal").val();
        let product_id = $("#inp-produto_id").val();
        let orcamento_id = $('.orc_id').val()
        if (qtd && value_unit && sub_total && product_id && orcamento_id) {
            let dataRequest = {
                qtd: qtd
                , value_unit: value_unit
                , sub_total: sub_total
                , product_id: product_id
                , orcamento_id: orcamento_id
            }
            $.get(path_url + "api/vendas/linhaProdutoOrcamento", dataRequest)
                .done((e) => {
                    console.log(e);
                    $('.table-itens tbody').append(e)
                    calcTotal()
                })
                .fail((e) => {
                    console.log(e)
                })
        } else {
            swal("Atenção", "Informe corretamente os campos para continuar!", "warning")
        }
    })
    $(".table-itens").on('click', '.btn-delete-row', function() {
        $(this).closest('tr').remove();
        swal("Sucesso", "Produto removido!", "success")
        calcTotal()
    });
</script>

@include('modals._pagamentos_orcamento')
@endsection
