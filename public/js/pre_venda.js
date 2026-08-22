var TOTAL = 0;
// var caixaAberto = false;
var DESCONTO = 0;
var VALORACRESCIMO = 0;
var ITENS = [];
var SENHADESBLOQUEADA = false;
var PERCENTUALMAXDESCONTO = false;
var CLIENTE = null;
var CLIENTES = [];

$(function () {

    $('#mousetrapTitle').click(() => {
        $('#codBarras').focus()
    })
    $('#codBarras').focus(() => {
        $('#mousetrapTitle').css('display', 'none');
    });
    $('#codBarras').focusout(() => {
        $('#mousetrapTitle').css('display', 'flex');
    });
    $("#inp-cliente_id").val('').change()
    setTimeout(() => {
        validateButtonSave();
    }, 300);
    $(".modal .select2").each(function () {
        let id = $(this).prop("id");
        if (id == "inp-categoria_id") {
            $(this).select2({
                dropdownParent: $(this).parent(),
                theme: "bootstrap4",
            });
        }

        /*  select de marcas não estava funcionando, então coloquei mais essa condição para
        teste */

        if (id == "inp-marca_id") {
            $(this).select2({
                dropdownParent: $(this).parent(),
                theme: "bootstrap4",
            });
        }

        // verificaCaixa((v) => {
        //     console.log(v)
        //     caixaAberto = v >= 0 ? true : false;
        //     if (v < 0) {
        //         $('#modal1').modal('show');
        //     }
        //     $('#prods').css('visibility', 'visible')
        // })

        if (id == "inp-sub_categoria_id") {
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
                        // console.clear();
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
        }
    });

    setTimeout(() => {
        $("#inp-produto_id").change(() => {
            let product_id = $("#inp-produto_id").val();
            if (product_id) {
                $.get(path_url + "api/produtos/find/" + product_id)
                    .done((e) => {
                        $("#inp-quantidade").val("1,00");
                        $("#inp-valor_unitario").val(
                            convertFloatToMoeda(e.valor_venda)
                        );
                        $("#inp-subtotal").val(
                            convertFloatToMoeda(e.valor_venda)
                        );
                    })
                    .fail((e) => {
                        console.log(e);
                    });
            }
        });
    }, 100);

    $("body").on("blur", ".value_unit", function () {
        let qtd = $("#inp-quantidade").val();
        let value_unit = $(this).val();
        value_unit = convertMoedaToFloat(value_unit);
        qtd = convertMoedaToFloat(qtd);
        $("#inp-subtotal").val(convertFloatToMoeda(qtd * value_unit));
    });


});

$(".btn-add-item").click(() => {

    let qtd = $("#inp-quantidade").val();
    let value_unit = $("#inp-valor_unitario").val();
    let sub_total = $("#inp-subtotal").val();
    let product_id = $("#inp-produto_id").val();
    let testes = $(".table-itens tbody tr").length

    if (qtd && value_unit && product_id && sub_total) {
        let dataRequest = {
            qtd: qtd,
            value_unit: value_unit,
            sub_total: sub_total,
            product_id: product_id,
        };
        $.get(path_url + "api/frenteCaixa/linhaProdutoVenda", dataRequest)
            .done((e) => {
                $(".table-itens tbody").append(e);
                calcTotal();
                validateButtonSave();
            })
            .fail((e) => {
                console.log(e);
            });
    } else {
        swal(
            "Atenção",
            "Informe corretamente os campos para continuar!",
            "warning"
        );
    }
})

var total_venda = 0;
function calcTotal() {
    var total = 0;
    $(".subtotal-item").each(function () {
        total += convertMoedaToFloat($(this).val());
    });
    setTimeout(() => {
        total_venda = total;

        $(".total-venda").html(
            convertFloatToMoeda(total + VALORACRESCIMO - DESCONTO)
        );
        $(".total-venda-modal").html(
            convertFloatToMoeda(total + VALORACRESCIMO - DESCONTO)
        );
        $('#inp-valor_integral').val(convertFloatToMoeda(total_venda))

        $('#inp-quantidade').val('')
        $('#inp-valor_unitario').val('')
        $('#inp-produto_id').val('').change()
    }, 100);
}


$(".btn-desconto").keyup(() => {
    $(".btn-desconto").val("");
    let desconto = $(".btn-desconto").val();
    // if(!desconto){ $('#desconto').val('0'); desconto = 0}
    if (desconto) {
        desconto = parseFloat(desconto.replace(",", "."));
        DESCONTO = 0;
        if (desconto > TOTAL && $("btn-desconto").val().length > 2) {
            // Materialize.toast('ERRO, Valor desconto maior que o valor total', 4000)
            $("btn-desconto").val("");
        } else {
            DESCONTO = desconto;
            calcTotal();
        }
    }
});



$("body").on("click", "#btn-incrementa", function () {
    let inp = $(this).closest('div.input-group-append').prev()[0]
    if(inp.value){
        let v = convertMoedaToFloat(inp.value)
        v+= 1
        inp.value = convertFloatToMoeda(v)
        calcSubTotal()
    }
})

$("body").on("click", "#btn-subtrai", function () {
    let inp = $(this).closest('.input-group').find('input')[0]
    console.log(inp)
    if(inp.value){
        let v = convertMoedaToFloat(inp.value)
        v-= 1
        inp.value = convertFloatToMoeda(v)

        calcSubTotal()
    }
})

function calcSubTotal(e){

    $(".line-product").each(function () {
        $qtd = $(this).find('.qtd')[0]
        $value = $(this).find('.value-unit')[0]
        $sub = $(this).find('.subtotal-item')[0]

        let qtd = convertMoedaToFloat($qtd.value)
        let value = convertMoedaToFloat($value.value)
        if(qtd <= 0){
            $(this).remove()
        }else{
            $sub.value = convertFloatToMoeda(qtd * value)
        }
    })
    setTimeout(() => {
        calcTotal()
    }, 10)
}

function setaDesconto() {
    validaPass((sim) => {
        if (sim) {
            if (total_venda == 0) {
                swal("Erro", "Total da venda é igual a zero", "warning");
            } else {
                swal({
                    title: "Valor desconto?",
                    text: "Ultiliza ponto(.) ao invés de virgula!",
                    content: "input",
                    button: {
                        text: "Ok",
                        closeModal: false,
                        type: "error",
                    },
                }).then((v) => {
                    if (v) {
                        let desconto = v;
                        if (desconto.substring(0, 1) == "%") {
                            let perc = desconto.substring(1, desconto.length);
                            DESCONTO = TOTAL * (perc / 100);
                            if (PERCENTUALMAXDESCONTO > 0) {
                                if (perc > PERCENTUALMAXDESCONTO) {
                                    swal.close();
                                    setTimeout(() => {
                                        swal(
                                            "Erro",
                                            "Máximo de desconto permitido é de " +
                                            PERCENTUALMAXDESCONTO +
                                            "%",
                                            "error"
                                        );
                                        $("#valor_desconto").html("0,00");
                                    }, 500);
                                }
                            }
                            if (DESCONTO > 0) {
                                $("#valor_item").attr("disabled", "disabled");
                                $(".btn-mini-desconto").attr(
                                    "disabled",
                                    "disabled"
                                );
                            } else {
                                $("#valor_item").removeAttr("disabled");
                                $(".btn-mini-desconto").removeAttr("disabled");
                            }
                        } else {
                            desconto = desconto.replace(",", ".");
                            DESCONTO = parseFloat(desconto);
                            if (PERCENTUALMAXDESCONTO > 0) {
                                let tempDesc =
                                    (TOTAL * PERCENTUALMAXDESCONTO) / 100;
                                if (tempDesc < DESCONTO) {
                                    swal.close();

                                    setTimeout(() => {
                                        swal(
                                            "Erro",
                                            "Máximo de desconto permitido é de R$ " +
                                            parseFloat(tempDesc),
                                            "error"
                                        );
                                        $("#valor_desconto").html("0,00");
                                    }, 500);
                                }
                            }
                            if (DESCONTO > 0) {
                                $("#valor_item").attr("disabled", "disabled");
                                $(".btn-mini-desconto").attr(
                                    "disabled",
                                    "disabled"
                                );
                            } else {
                                $("#valor_item").removeAttr("disabled");
                                $(".btn-mini-desconto").removeAttr("disabled");
                            }
                        }
                        if (desconto.length == 0) DESCONTO = 0;
                        $("#valor_desconto").html(parseFloat(DESCONTO));
                        calcTotal();
                    }
                    swal.close();
                    $("#codBarras").focus();
                });
            }
        }
    });
}

function setaAcrescimo() {
    if (total_venda == 0) {
        swal("Erro", "Total da venda é igual a zero", "warning");
    } else {
        swal({
            title: "Valor acrescimo?",
            text: "Ultilize ponto(.) ao invés de virgula!",
            content: "input",
            button: {
                text: "Ok",
                closeModal: false,
                type: "error",
            },
        }).then((v) => {
            if (v) {
                let acrescimo = v;
                if (acrescimo > 0) {
                    DESCONTO = 0;
                    $("#valor_desconto").html(parseFloat(DESCONTO));
                }

                let total = total_venda;

                if (acrescimo.substring(0, 1) == "%") {
                    let perc = acrescimo.substring(1, acrescimo.length);
                    VALORACRESCIMO = total * (perc / 100);
                } else {
                    acrescimo = acrescimo.replace(",", ".");
                    VALORACRESCIMO = parseFloat(acrescimo);
                }

                if (acrescimo.length == 0) VALORACRESCIMO = 0;
                calcTotal();
                VALORACRESCIMO = parseFloat(VALORACRESCIMO);
                $("#valor_acrescimo").html(parseFloat(VALORACRESCIMO));

                calcTotal();
                $("#codBarras").focus();
            }
            swal.close();
        });
    }
}

function validaPass(call) {
    let senha = $("#pass").val();
    if (senha != "" && !SENHADESBLOQUEADA) {
        swal({
            title: "Desconto de item",
            text: "Informe a senha!",
            content: {
                element: "input",
                attributes: {
                    placeholder: "Digite a senha",
                    type: "password",
                },
            },
            button: {
                text: "Desbloquear!",
                closeModal: false,
                type: "error",
            },
            confirmButtonColor: "#DD6B55",
        }).then((v) => {
            if (v.length > 0) {
                $.get(path + "configNF/verificaSenha", { senha: v }).then(
                    (res) => {
                        SENHADESBLOQUEADA = true;
                        call(true);
                    },
                    (err) => {
                        swal.close();
                        swal("Erro", "Senha incorreta", "error").then(() => {
                            call(false);
                        });
                    }
                );
            } else {
                location.reload();
            }
        });
    } else {
        call(true);
    }
}

$('body').on('click', '#btn-seleciona-cliente', function () {
    validateButtonSave()
})

$(".btn-selecionar_cliente").click(() => {
    $(".modal .select2").each(function () {
        let id = $(this).prop("id");

        if (id == "inp-cliente_id") {
            $(this).select2({
                minimumInputLength: 2,
                language: "pt-BR",
                placeholder: "Digite para buscar o cliente",
                width: "100%",
                theme: "bootstrap4",
                dropdownParent: $(this).parent(),
                ajax: {
                    cache: true,
                    url: path_url + "api/cliente/pesquisa",
                    dataType: "json",
                    data: function (params) {
                        // console.clear();
                        var query = {
                            pesquisa: params.term,
                            empresa_id: $("#empresa_id").val(),
                        };
                        return query;
                    },
                    processResults: function (response) {
                        console.log("response", response);
                        var results = [];

                        $.each(response, function (i, v) {
                            var o = {};
                            o.id = v.id;

                            o.text = v.razao_social + " - " + v.cpf_cnpj;
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
    });
});

$('body').on('blur', '#inp-vendedor_id', function () {
    validateButtonSave()
})

$('body').on('click', '#btn-pag_row', function () {
    validateButtonSave()
})

function validateButtonSave() {
    $('.alerts').html('')

    let cliente = $('#inp-cliente_id').val()
    let vendedor = $('#inp-vendedor_id').val()
    let count_pay = $(".table-payment tbody tr").length
    let count_itens = $(".table-itens tbody tr").length

    console.log(count_itens)
    console.log(cliente)
    
    if(!vendedor){
        alertCreate("Selecione o vendedor!")
    }
    if(!cliente){
        alertCreate("Selecione o cliente!")
    }
    if(count_itens == 0){
        alertCreate("Adicione um produto na venda!")
    }
    // if(count_pay == 0){
    //     alertCreate("Informe o pagamento!")
    // }

    setTimeout(() => {
        if($('.alerts').html() == ""){
            $('#enviar_caixa').removeAttr("disabled")

        }else{
            $('#enviar_caixa').attr("disabled", true);

        }
    }, 100)

}

function alertCreate(msg){
    var div = '<div class="alert alert-danger border-0 bg-danger alert-dismissible fade show">'
    div += '<div class="text-white">'+msg+'</div>'
    div += '</div>'
    $('.alerts').append(div)
}


// horário no pre-venda
setInterval(() => {
    let date = new Date().toLocaleTimeString();
    $('#timer').html(date)
}, 100)

function convertFormToJSON(form) {
    return $(form)
        .serializeArray()
        .reduce(function (json, { name, value }) {
            json[name] = value;
            return json;
        }, {});
}

var total_payment = 0;
function calcTotalPayment() {
    $('#btn-pag_row').attr("disabled", true)

    var total = 0;
    $(".valor_integral").each(function () {
        total += convertMoedaToFloat($(this).val());
    });
    setTimeout(() => {
        total_payment = total;
        $(".sum-payment").html("R$ " + convertFloatToMoeda(total));

        $(".sum-restante").html("R$ " + convertFloatToMoeda(total_venda - total));
    }, 100);

    let diferenca = total_venda - total;

    if (diferenca == 0) {
        $("#btn-pag_row").removeAttr("disabled")
    }
    console.log(diferenca)
}


$(".table-payment").on("click", ".btn-delete-row", function () {
    $(this).closest("tr").remove();
    swal("Sucesso", "Parcela removida!", "success");
    calcTotalPayment();
});


$(".modal-pag_mult").click(() => {
    // let cliente = $("#inp-cliente_id").val();
    let count_itens = $(".table-itens tbody tr").length
    setTimeout(() => {
        if (count_itens == 0) {
            swal("Erro", "Adicione um produto!", "warning");
        }
        // if (cliente == null) {
        //     swal("Erro", "Adicione um cliente", "warning");
        // }
    }, 100)
})

$('body').on('click', '.btn-add-payment', function () {
    let tipo_pagamento_row = $("#inp-tipo_pagamento_row").val();
    let vencimento = $("#inp-data_vencimento_row").val();
    let valor_integral_row = $("#inp-valor_integral_row").val();
    let obs_row = $("#inp-obs_row").val();

    console.log(vencimento);
    validateButtonSave();

    let v = convertMoedaToFloat(valor_integral_row);

    if (v + total_payment <= total_venda) {
        if (vencimento && valor_integral_row && tipo_pagamento_row) {
            let dataRequest = {
                data_vencimento_row: vencimento,
                valor_integral_row: valor_integral_row,
                obs_row: obs_row,
                tipo_pagamento_row: tipo_pagamento_row,
            };

            console.log(dataRequest);

            $.get(path_url + "api/frenteCaixa/linhaParcelaVenda", dataRequest)
                .done((e) => {
                    $(".table-payment tbody").append(e);
                    calcTotalPayment();

                })
                .fail((e) => {
                    console.log(e);
                });
        } else {
            swal(
                "Atenção",
                "Informe corretamente os campos para continuar!",
                "warning"
            );
        }
    } else {
        swal(
            "Atenção",
            "A soma das parcelas não bate com o valor total da venda",
            "warning"
        );
    }
});