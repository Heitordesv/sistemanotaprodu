$('.modal .select2').each(function () {
    let id = $(this).prop('id')
    if (id == 'inp-uf') {
        $(this).select2({
            dropdownParent: $(this).parent(),
            theme: 'bootstrap4',
        });
    }
    if (id == 'inp-cidade_id') {
        $(this).select2({
            minimumInputLength: 2,
            language: "pt-BR",
            placeholder: "Digite para buscar a cidade",
            width: "100%",
            theme: 'bootstrap4',
            dropdownParent: $(this).parent(),
            ajax: {
                cache: true,
                url: path_url + 'api/buscaCidades',
                dataType: "json",
                data: function (params) {
                    console.clear()
                    var query = {
                        pesquisa: params.term,
                    };
                    return query;
                },
                processResults: function (response) {
                    console.log("response", response)
                    var results = [];

                    $.each(response, function (i, v) {
                        var o = {};
                        o.id = v.id;

                        o.text = v.nome + "(" + v.uf + ")";
                        o.value = v.id;
                        results.push(o);
                    });
                    return {
                        results: results
                    };
                }
            }
        });
    }
})

$('#btn-store-produto').click(() => {

    let valid = validaCamposModal("#modal-produto");

    if (valid.length > 0) {
        let msg = valid.join("\n");
        swal("Ops, erro no formulário", msg, "error");
        return;
    }

    let data = {};

    $("#modal-produto input, #modal-produto select").each(function () {
        let indice = $(this).attr('id');

        if (indice) {
            indice = indice.substring(4);
            data[indice] = $(this).val();
        }
    });

    data['empresa_id'] = $('#empresa_id').val();

    $.post(path_url + 'api/produtos/store', data)
        .done((success) => {
            swal("Sucesso", "Produto cadastrado!", "success")
                .then(() => {
                    let option = new Option(success.nome, success.id, true, true);
                    $('#inp-produto_id').append(option).trigger('change');
                    $('#modal-produto').modal('hide');
                });
        })
        .fail(() => swal("Erro", "Falha ao salvar produto!", "error"));
});

$('#btn-store-fornecedor').click(() => {
    let valid = validaCamposModal('#modal-fornecedor')
    if (valid.length > 0) {
        let msg = ""
        valid.map((x) => {
            msg += x + "\n"
        })
        swal("Ops, erro no formulário", msg, "error")
    } else {
        console.log("salvando...")
        let data = {}
        $(".modal input, .modal select").each(function () {

            let indice = $(this).attr('id')
            if (indice) {
                indice = indice.substring(4, indice.length)
                data[indice] = $(this).val()
            }
        });
        data['empresa_id'] = $('#empresa_id').val()
        console.log(data)
        $.post(path_url + 'api/fornecedor/store', data)
            .done((success) => {
                console.log("success", success)
                swal("Sucesso", "Fornecedor cadastrado!", "success")
                    .then(() => {
                        var newOption = new Option(success.razao_social, success.id, false, false);
                        $('#inp-fornecedor_id').append(newOption).trigger('change');
                        $('#modal-fornecedor').modal('hide')
                    })
            }).fail((err) => {
                console.log(err)
                swal("Ops", "Algo deu errado ao salvar fornecedor!", "error")
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

        if (id == 'inp-marca_id') {
            $(this).select2({
                dropdownParent: $(this).parent(),
                theme: 'bootstrap4',
            });
        }
        if (id == 'inp-sub_categoria_id') {
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
                        console.log("response", response);
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
        }
    })

    setTimeout(() => {
        $("#inp-produto_id").change(() => {
            let product_id = $("#inp-produto_id").val()
            if (product_id) {
                $.get(path_url + "api/produtos/find/" + product_id)
                    .done((e) => {
                        $('#inp-quantidade').val('1,00')
                        $('#inp-valor_unitario').val(convertFloatToMoeda(e.valor_compra))
                        $('#inp-subtotal').val(convertFloatToMoeda(e.valor_compra))
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
    $('#inp-fornecedor_id').change(() => {
        validateButtonSave()
    })
})

$('.btn-add-item').click(() => {
    let qtd = $('#inp-quantidade').val();
    let value_unit = $('#inp-valor_unitario').val();
    let sub_total = $('#inp-subtotal').val();
    let product_id = $("#inp-produto_id").val()
    if (qtd && value_unit && sub_total && product_id) {
        let dataRequest = {
            qtd: qtd,
            value_unit: value_unit,
            sub_total: sub_total,
            product_id: product_id,
        }
        $.get(path_url + "api/produtos/linhaProdutoCompra", dataRequest)
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


var total_compra = 0
function calcTotal() {
    var total = 0
    let desconto =  convertMoedaToFloat($('#inp-desconto').val())
    $(".subtotal-item").each(function () {
        total += convertMoedaToFloat($(this).val())
    })
    setTimeout(() => {
        total_compra = total - desconto
        validateButtonSave()
        $('.total-compra').html("R$ " + convertFloatToMoeda(total_compra))
    }, 100)
}

$('#inp-desconto').on('keyup', () => {
    calcTotal()
})

$(".table-itens").on('click', '.btn-delete-row', function () {
    $(this).closest('tr').remove();
    swal("Sucesso", "Produto removido!", "success")
    calcTotal()
});

$('#inp-forma_pagamento').change(() => {
    let fp = $('#inp-forma_pagamento').val()
    $("#inp-qtd_parcelas").attr("disabled", true);
    $("#inp-vencimento_parcela").attr("disabled", true);
    $("#inp-valor_parcela").attr("disabled", true);
    if (fp == 'a_vista') {
        let now = new Date();
        let data = now.getFullYear() + "-" + ((now.getMonth() + 1) < 10 ? "0" + (now.getMonth() + 1) : (now.getMonth() + 1)) +
            "-" + (now.getDate() < 10 ? "0" + now.getDate() : now.getDate())
        $('#inp-qtd_parcelas').val('1')
        $('#inp-vencimento_parcela').val(data)
        $('#inp-valor_parcela').val(convertFloatToMoeda(total_compra))
    } else if (fp == '30_dias') {
        var date = new Date(new Date().setDate(new Date().getDate() + 30));
        let data = date.getFullYear() + "-" + ((date.getMonth() + 1) < 10 ? "0" + (date.getMonth() + 1) : (date.getMonth() + 1)) +
            "-" + (date.getDate() < 10 ? "0" + date.getDate() : date.getDate())
        $('#inp-qtd_parcelas').val('1')
        $('#inp-vencimento_parcela').val(data)
        $('#inp-valor_parcela').val(convertFloatToMoeda(total_compra))
    } else if (fp == 'personalizado') {
        $("#inp-qtd_parcelas").removeAttr("disabled");
        $("#inp-vencimento_parcela").removeAttr("disabled");
        $("#inp-valor_parcela").removeAttr("disabled");
        $('#inp-qtd_parcelas').val('1')
        $('#inp-vencimento_parcela').val('')
        $('#inp-valor_parcela').val(convertFloatToMoeda(total_compra))
    } else {

    }
})

$('#inp-qtd_parcelas').blur(() => {
    clearPayment()
    let qtd = $('#inp-qtd_parcelas')
    $('#inp-valor_parcela').val(convertFloatToMoeda(TOTAL / qtd));

})

function clearPayment() {
    $('#table-payment tbody').html('')
    $("#inp-vencimento_parcela").val("");
    $("#inp-valor_parcela").val("");
    $('.btn-add-payment').removeClass("disabled");
}

$('.btn-add-payment').click(() => {
    let vencimento = $('#inp-vencimento_parcela').val();
    let valor_parcela = $('#inp-valor_parcela').val();
    let v = convertMoedaToFloat(valor_parcela)
    if ((v + total_payment) <= total_compra) {
        if (vencimento && valor_parcela) {
            let dataRequest = {
                vencimento: vencimento,
                valor_parcela: valor_parcela,
            }
            $.get(path_url + "api/produtos/linhaParcelaCompra", dataRequest)
                .done((e) => {
                    console.log(e);
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
        swal("Atenção", "A soma das parcelas ultrapassa o valor total da compra", "warning")
    }
})

var total_payment = 0

function calcTotalPayment() {
    var total = 0
    $(".valor-parcela").each(function () {
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
    let fornecedor_id = $('#inp-fornecedor_id').val()
    let count_pay = $(".table-payment tbody tr").length
    let count_itens = $(".table-itens tbody tr").length
    if (!fornecedor_id) {
        alertCreate("Selecione o fornecedor")
    }
    if (count_itens == 0) {
        alertCreate("Adicione um produto na compra!")
    }
    if (count_pay == 0) {
        alertCreate("Informe a fatura da compra!")
    }
    setTimeout(() => {
        if ($('.alerts').html() == "") {
            $('.btn-finalizar').removeAttr("disabled")
        } else {
            $('.btn-finalizar').attr("disabled", true);
        }
    }, 100)
}

function alertCreate(msg) {
    var div = '<div class="alert alert-danger border-0 bg-danger alert-dismissible fade show">'
    div += '<div class="text-white">' + msg + '</div>'
    div += '</div>'
    $('.alerts').append(div)
}
