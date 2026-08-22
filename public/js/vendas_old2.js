var REFERENCIASNFE = [];
var TOTAL = 0;

$('#btn-store-produto').click(() => {
    let valid = validaCamposModal("#modal-produto")
    if (valid.length > 0) {
        let msg = ""
        valid.map((x) => {
            msg += x + "\n"
        })
        swal("Ops, erro no formulário", msg, "error")
    } else {
        console.clear()

        let data = {}
        $(".modal input, .modal select").each(function () {

            let indice = $(this).attr('id')
            indice = indice.substring(4, indice.length)
            data[indice] = $(this).val()
        });
        data['empresa_id'] = $('#empresa_id').val()

        $.post(path_url + 'api/produtos/store', data)
            .done((success) => {
                swal("Sucesso", "Produto cadastrado!", "success")
                    .then(() => {
                        var newOption = new Option(success.nome, success.id, false, false);
                        $('#inp-produto_id').append(newOption).trigger('change');
                        $('#modal-produto').modal('hide')
                    })

            }).fail((err) => {
                swal("Ops", "Algo deu errado ao salvar produto!", "error")
            })
    }
})


$(function () {
    setTimeout(() => {
        validateButtonSave();
    }, 300)
    $('.modal .select2').each(function () {
        let id = $(this).prop('id')

        if (id == 'inp-categoria_id') {
            $(this).select2({
                dropdownParent: $(this).parent(),
                theme: 'bootstrap4',
            });
        }
        /*  select de marcas não estava funcionando, então coloquei mais essa condição para
        teste */

        else if (id == 'inp-marca_id') {
            $(this).select2({
                dropdownParent: $(this).parent(),
                theme: 'bootstrap4',
            });
        }

        else if (id == 'inp-sub_categoria_id') {

            $(this).select2({
                minimumInputLength: 2,
                language: "pt-BR",
                placeholder: "Digite para buscar a subcategoria",
                width: "100%",
                dropdownParent: $(this).parent(),
                theme: "bootstrap4",
                ajax: {
                    cache: true,
                    url: path_url + "api/categorias/buscarSubCategoria",
                    dataType: "json",
                    data: function (params) {
                        console.clear();

                        let empresa_id = $("#empresa_id").val();
                        let categoria_id = $("#inp-categoria_id").val();

                        if (categoria_id) {
                            var query = {
                                pesquisa: params.term,
                                empresa_id: empresa_id,
                                categoria_id: categoria_id,
                            };
                            return query;
                        } else {
                            swal("Erro", "Selecione uma categoria!", "warning");
                        }
                    },
                    processResults: function (response) {
                        var results = [];

                        $.each(response, function (i, v) {
                            var o = {};
                            o.id = v.id;

                            o.text = v.nome;
                            o.value = v.id;
                            results.push(o);
                        });
                        return {
                            results: results,
                        };
                    },
                },

            });
        } else {
            $(this).select2({
                dropdownParent: $(this).parent(),
                theme: 'bootstrap4',
            });
        }
    })

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

    $('body').on('blur', '.value_unit', function () {
        let qtd = $('#inp-quantidade').val();
        let value_unit = $(this).val();
        value_unit = convertMoedaToFloat(value_unit)
        qtd = convertMoedaToFloat(qtd)
        $('#inp-subtotal').val(convertFloatToMoeda(qtd * value_unit))
    })

    $('#inp-cliente_id').change(() => {
        validateButtonSave()
    })
})

$('.btn-add-item').click(() => {
    let qtd = $("#inp-quantidade").val();
    let value_unit = $("#inp-valor_unitario").val();
    let sub_total = $("#inp-subtotal").val();
    let product_id = $("#inp-produto_id").val()

    if (qtd && value_unit && sub_total && product_id) {
        let dataRequest = {
            qtd: qtd,
            value_unit: value_unit,
            sub_total: sub_total,
            product_id: product_id,
        }

        $.get(path_url + "api/vendas/linhaProdutoVenda", dataRequest)
            .done((e) => {
                $('.table-itens tbody').append(e)
                calcTotalProdutos()
            })
            .fail((e) => {
                console.log(e)
            })
    } else {
        swal("Atenção", "Informe corretamente os campos para continuar!", "warning")
    }
})

$(function () {
    $('body').on('blur', '.acrescimo, .desconto', function () {
        setTimeout(() => {
            calcTotal()
            limparCampos()
            clearPayment()
        }, 200)

    })
})

$(function () {
    calcTotalProdutos()
})

var total_produto = 0
var total_venda = 0
function calcTotalProdutos() {
    var total = 0
    $(".subtotal-item").each(function () {
        total += convertMoedaToFloat($(this).val())
    })
    setTimeout(() => {
        total_venda = total
        $('.total_produtos').html("R$ " + convertFloatToMoeda(total))
        $('.total_produtos').val(total)
        total_produto = total
        calcTotal()
        console.log(total_produto)
    }, 100)
}
var total_frete = 0
var total_geral = 0
function calcTotal() {
    var total = 0
    let acrescimo = convertMoedaToFloat($('#inp-acrescimo').val())
    let desconto = convertMoedaToFloat($('#inp-desconto').val())
    let total_fr = convertMoedaToFloat($("#inp-valor_frete").val())

    setTimeout(() => {
        total_frete = total_fr
        total_venda = total
        total_geral = total_produto + total_fr + acrescimo - desconto
        validateButtonSave()
        // $('.total_produtos').html("R$ " + convertFloatToMoeda(total))
        $('.total-venda').html("R$ " + convertFloatToMoeda(total_geral))
    }, 200)
}



$(".table-itens").on('click', '.btn-delete-row', function () {
    $(this).closest('tr').remove();
    swal("Sucesso", "Produto removido!", "success")
    calcTotal()
});

$('#inp-forma_pagamento').change(() => {
    let fp = $('#inp-forma_pagamento').val()
    $("#inp-qtd_parcelas").attr("disabled", true);
    $("#inp-data_vencimento").attr("disabled", true);
    $("#inp-valor_integral").attr("disabled", true);
    if (fp == 'a_vista') {
        let now = new Date();
        let data = now.getFullYear() + "-" + ((now.getMonth() + 1) < 10 ? "0" + (now.getMonth() + 1) : (now.getMonth() + 1))
            + "-" + (now.getDate() < 10 ? "0" + now.getDate() : now.getDate())
        $('#inp-qtd_parcelas').val('1')
        $('#inp-data_vencimento').val(data)
        $('#inp-valor_integral').val(convertFloatToMoeda(total_geral))
    } else if (fp == '30_dias') {
        var date = new Date(new Date().setDate(new Date().getDate() + 30));
        let data = date.getFullYear() + "-" + ((date.getMonth() + 1) < 10 ? "0" + (date.getMonth() + 1) : (date.getMonth() + 1))
            + "-" + (date.getDate() < 10 ? "0" + date.getDate() : date.getDate())
        $('#inp-qtd_parcelas').val('1')
        $('#inp-data_vencimento').val(data)
        $('#inp-valor_integral').val(convertFloatToMoeda(total_geral))
    } else if (fp == 'personalizado') {
        $("#inp-qtd_parcelas").removeAttr("disabled");
        $("#inp-data_vencimento").removeAttr("disabled");
        $("#inp-valor_integral").removeAttr("disabled");
        $('#inp-qtd_parcelas').val('1')
        $('#inp-data_vencimento').val('')
        $('#inp-valor_integral').val(convertFloatToMoeda(total_geral))
    } else {

    }
})

// $('#inp-qtd_parcelas').blur(() => {
//     clearPayment()
//     let qtd = $('#inp-qtd_parcelas')
//     $('#inp-valor_integral').val(convertFloatToMoeda(TOTAL / qtd));
// })

function clearPayment() {
    $('#table-payment tbody').html('')
    $("#inp-data_vencimento").val("");
    $("#inp-valor_integral").val("");
    $('.btn-add-payment').removeClass("disabled");
}

$('.btn-add-payment').click(() => {
    let tipo_pagamento = $('#inp-tipo_pagamento').val();
    let vencimento = $('#inp-data_vencimento').val();
    let valor_integral = $('#inp-valor_integral').val();

    let v = convertMoedaToFloat(valor_integral)
    if ((v + total_payment) <= total_geral) {
        if (vencimento && valor_integral && tipo_pagamento) {
            let dataRequest = {
                tipo_pagamento: tipo_pagamento,
                data_vencimento: vencimento,
                valor_integral: valor_integral,
            }

            $.get(path_url + "api/vendas/linhaParcelaVenda", dataRequest)
                .done((e) => {
                    $('.table-payment tbody').append(e)
                    calcTotalPayment()
                })
                .fail((e) => {
                    console.log(e)
                })
        } else {
            swal("Atenção", "Informe corretamente os campos para continuar!", "warning")
        }
    } else {
        swal("Atenção", "A soma das parcelas ultrapassa o valor total da venda", "warning")
    }
})

var total_payment = 0
function calcTotalPayment() {
    var total = 0
    $(".valor_integral").each(function () {
        total += convertMoedaToFloat($(this).val())
    })
    setTimeout(() => {
        total_payment = total
        $('.sum-payment').html("R$ " + convertFloatToMoeda(total))
        validateButtonSave()
    }, 100)
}

$(".table-payment").on('click', '.btn-delete-row', function () {
    $(this).closest('tr').remove();
    swal("Sucesso", "Parcela removida!", "success")
    calcTotal()
});


function validateButtonSave() {
    $('.alerts').html('')
    let cliente_id = $('#inp-cliente_id').val()
    let count_pay = $(".table-payment tbody tr").length
    let count_itens = $(".table-itens tbody tr").length

    if (!cliente_id) {
        alertCreate("Selecione o cliente!")
    }
    if (count_itens == 0) {
        alertCreate("Adicione um produto na venda!")
    }
    if (count_pay == 0) {
        alertCreate("Informe o pagamento!")
    }

    setTimeout(() => {
        if ($('.alerts').html() == "") {
            $('.btn-venda').removeAttr("disabled")

        } else {
            $('.btn-venda').attr("disabled", true);

        }
    }, 100)
}

function alertCreate(msg) {
    var div = '<div class="alert alert-danger border-0 bg-danger alert-dismissible fade show">'
    div += '<div class="text-white">' + msg + '</div>'
    div += '</div>'
    $('.alerts').append(div)
}


// REFERENCIA DE CHAVE NFE


function addChave() {
    let chave = $('#inp-chave').val()
    if (chave.length != 44) {
        swal("Erro", "Informe uma chave com 44 nÃºmeros", "error")
    } else {
        REFERENCIASNFE.push(chave)
        let t = montaTabelaChave(); // para remover
        $('.table-chaves tbody').html(t)
    }
}

function montaTabelaChave() {
    let t = "";
    REFERENCIASNFE.map((v) => {

        t += '<tr class="datatable-row">'
        t += '<td class="datatable-cell">'
        t += v
        t += '</td>'
        t += "<td class='datatable-cell'><span class='codigo' style='width: 100px;'><a class='btn btn-danger' onclick='deleteChave(\"" + v + "\")'>"
        t += "<i class='bx bx-trash'></i></a></span></td>";
        t += "</tr>";
    });
    return t
}

function deleteChave(chave) {
    let n = []

    REFERENCIASNFE.map((c) => {
        if (c != chave) n.push(c)
    })
    REFERENCIASNFE = n
    let t = montaTabelaChave(); // para remover
    $('.table-chaves tbody').html(t)
}

let newOption = " "
$('body').on('change', '#inp-filial_id_create', function () {
    $("#inp-produto_id").html(newOption).trigger("change");
    $('#inp-quantidade').val('0,00')
    $('#inp-valor_unitario').val('0,00')
    $('#inp-subtotal').val('0,00')
})


$('#inp-forma_pagamento').change(() => {
    let fp = $('#inp-forma_pagamento').val()
    let tp = $('#inp-tipo_pagamento').val()
    if (fp == 'personalizado') {
        $('#btn-personalizado').removeClass('disabled')
        $('#inp-qtd_parcelas').on('keyup', () => {
            let parcelas = $('#inp-qtd_parcelas').val()
            $('#inp-valor_integral').val(convertFloatToMoeda(total_geral / parcelas));
            $('#valor_integral_personalizado').val(total_geral)
            $('#tipo_pagamento_personalizado').val(tp)
            $('#qtd-parcela_personalizado').val(parcelas)
        })
    }
    else {
        $('#btn-personalizado').addClass('disabled')
    }
})

function renderizarPagamento() {
    simulaParcelas((res) => {
        let html = '';
        res.map((rs) => {
            html += '<option value="' + rs.indice + '">';
            html += rs.indice + 'x R$' + ' ' + convertFloatToMoeda(rs.valor);
            html += '</option>';
        })
        $('#qtd_parcelas').html(html)
    });
}

function simulaParcelas(call) {
    let parcelamento_maximo = parseInt($('#parcelamento_maximo').val())
    let desconto = $('.desconto').val();
    if (desconto.length == 0) desconto = 0;
    else desconto = desconto.replace(",", ".");

    let acrescimo = $('.acrescimo').val();
    if (acrescimo.length == 0) acrescimo = 0;
    else acrescimo = acrescimo.replace(",", ".");

    let temp = [];
    let totalTemp = total_geral.toFixed(2)
    totalTemp = totalTemp.replace(",", ".")

    for (let i = 1; i <= parcelamento_maximo; i++) {
        let vp = totalTemp / i;
        js = {
            'indice': i,
            'valor': vp.toFixed(2)
        }
        temp.push(js)
    }
    call(temp)
}

$('.btn-pag_personalizado').click(() => {
    let parcelas = $('#qtd_parcelas').val()

    let intervalo = $('#inp-intervalo').val()
    let tipo_pagamento = $('#inp-tipo_pagamento').val()
    total_geral = convertFloatToMoeda(total_geral)
    let data = {
        total_geral: total_geral,
        parcelas: parcelas,
        intervalo: intervalo,
        tipo_pagamento: tipo_pagamento
    }
    console.log(data)
    $.get(path_url + 'api/vendas/linhaParcelaVendaPersonalizado', data)
        .done((success) => {
            console.log(success)
            $('.table-payment tbody').append(success)
            calcTotalPayment()
        }).fail((err) => {
            console.log(err)
        })
})

$('#remover_parcelas').click(() => {
    $('.table-payment tbody').html('')
    $('.sum-payment').html('')

})

function limparCampos() {
    $('#inp-qtd_parcelas').val('')
    $('#inp-valor_integral').val('')
    $('#inp-data_vencimento').val('')
    $('#inp-forma_pagamento').first().val('').change()
    $('.table-payment tbody').html('')
    $('.sum-payment').html('')


}
// function limpaFatura() {

//     $('.table-payment tbody tr').each(function (e, x) {
//         if (e == 0) {
//             setTimeout(() => {
//                 // let total = ($('.total_prod').val())
//                 // $('.valor_fatura').first().val(convertFloatToMoeda(total))
//                 // $('.tipo_pagamento').first().val('').change()
//                 let data = new Date
//                 let dataFormatada = (data.getFullYear() + "-" + adicionaZero((data.getMonth() + 1)) + "-" + adicionaZero(data.getDate()));
//                 $('.date_atual').first().val(dataFormatada)
//                 calcTotalFatura()
//             }, 500)

//         } else {
//             console.log('sim')
//             x.remove();
//         }
//     })
// }
// $(".table-payment").on('click', '.btn-delete-row', function () {
//     $(this).closest('tr').remove();
//     swal("Sucesso", "Parcela removida!", "success")
//     calcTotal()
// });