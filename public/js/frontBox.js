
function listaPrecoPdvId() {
    return $('#lista-precos-pdv').val() || '';
}

function consultarPrecoPdv(produtoId) {
    return $.get(path_url + 'api/produtos/find/' + produtoId, {
        empresa_id: $('#empresa_id').val(),
        lista_preco_id: listaPrecoPdvId()
    });
}

function recalcularItensPorListaPreco() {
    var linhas = $('.table-itens tbody tr').filter(function () {
        return $(this).find('input[name="produto_id[]"]').length > 0;
    });

    if (!linhas.length) {
        return $.Deferred().resolve().promise();
    }

    var consultas = [];

    linhas.each(function () {
        var linha = $(this);
        var produtoId = linha.find('input[name="produto_id[]"]').val();

        consultas.push(
            consultarPrecoPdv(produtoId).then(function (produto) {
                var valor = Number(
                    produto.valor_venda_pdv !== undefined
                        ? produto.valor_venda_pdv
                        : produto.valor_venda
                );
                var quantidade = convertMoedaToFloat(
                    linha.find('input[name="quantidade[]"]').val()
                );

                linha.find('input[name="valor_unitario[]"]').val(
                    convertFloatToMoeda(valor)
                );
                linha.find('input[name="subtotal_item[]"]').val(
                    convertFloatToMoeda(valor * quantidade)
                );
            })
        );
    });

    return $.when.apply($, consultas).then(function () {
        calcTotal();
    });
}

$(document).on('change', '#lista-precos-pdv', function () {
    var campo = $(this);
    var valorAnterior = campo.data('valor-anterior');

    campo.prop('disabled', true);

    recalcularItensPorListaPreco()
        .done(function () {
            campo.data('valor-anterior', campo.val());

            if ($('.table-itens tbody input[name="produto_id[]"]').length) {
                swal(
                    'Lista de preços aplicada',
                    'Os preços dos produtos foram atualizados.',
                    'success'
                );
            }
        })
        .fail(function (xhr) {
            campo.val(valorAnterior || '');
            swal(
                'Não foi possível aplicar a lista',
                (xhr.responseJSON && xhr.responseJSON.message) ||
                    'Confira se a lista pertence à empresa e tente novamente.',
                'warning'
            );
        })
        .always(function () {
            campo.prop('disabled', false);
        });
});

$(function () {
    $('#lista-precos-pdv').data(
        'valor-anterior',
        $('#lista-precos-pdv').val() || ''
    );
});

var TOTAL = 0;
var caixaAberto = false;
var DESCONTO = 0;
var DESCONTO_TIPO = 'fixo';
var DESCONTO_VALOR_INFORMADO = 0;
var VALORACRESCIMO = 0;
var ACRESCIMO_TIPO = 'fixo';
var ACRESCIMO_VALOR_INFORMADO = 0;
var TAXA_ENTREGA = 0;
var ITENS = [];
var SENHADESBLOQUEADA = false;
var PERCENTUALMAXDESCONTO = false;
var CLIENTE = null;
var CLIENTES = [];

$(function (){
    // verificaCaixa()
    validateButtonSave()
    calcTotal()
    $('#codigo_comanda').val('0')
})

function apontarComanda(){
    let codigo = $('#inp-codigo').val()
    $.get(path_url + 'api/pedidos/comanda/'+codigo)
    .done((res) => {
        if(res.comanda){
            $('.h4-comanda').text('APONTANDO COMANDA ' + codigo)
            $('#codigo_comanda').val(codigo)
        }else{
            swal("Alerta", "Comanda não encontrada", "warning")
        }
    }).fail((err) => {
        fecharCarregamentoPdv();
        console.log(err)
    })

    $.get(path_url + 'api/pedidos/comandaHtml/'+codigo)
    .done((res) => {
        // console.log(res)
        $(".table-itens tbody").append(res);
        setTimeout(() => {
            calcTotal()
        }, 200)
    }).fail((err) => {
        console.log(err)
    })
}

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
    $("#inp-tipo_pagamento").val('').change()
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
        }
    });
  setTimeout(() => {

    $("#inp-produto_id").change(() => {

        let product_id = $("#inp-produto_id").val();

        if (product_id) {

            $.get(path_url + "api/produtos/find/" + product_id, {
                empresa_id: $('#empresa_id').val(),
                lista_preco_id: listaPrecoPdvId()
            })
            .done((e) => {

                $("#inp-quantidade").val("1,00");

                let valorFinal = parseFloat(
                    e.valor_venda_pdv !== undefined ? e.valor_venda_pdv : e.valor_venda
                );

                if (parseFloat(e.percentual_desconto_categoria_pdv || 0) > 0) {
                    swal(
                        "🎉 Produto com desconto!",
                        "Categoria " + e.categoria.nome + " com " +
                            e.percentual_desconto_categoria_pdv + "% de desconto aplicado.",
                        "success"
                    );
                }

                // 🔥 SETA VALORES
                $("#inp-valor_unitario").val(
                    convertFloatToMoeda(valorFinal)
                );

                $("#inp-subtotal").val(
                    convertFloatToMoeda(valorFinal)
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

    validaCaixa()
});


function validaCaixa() {
    let abertura = $('#abertura').val()
    if (!abertura) {
        $('#modal-abrir_caixa').modal('show')
        return
    }
}
var adicionado = false

$(".btn-add-item").click(() => {
    // $(".btn-add-item").attr('disabled', 1)
    if(adicionado == false){
        let qtd = $("#inp-quantidade").val();
        let value_unit = $("#inp-valor_unitario").val();
        value_unit = convertMoedaToFloat(value_unit);
        qtd = convertMoedaToFloat(qtd);
        $("#inp-subtotal").val(convertFloatToMoeda(qtd * value_unit));
        setTimeout(() => {

            let abertura = $('#abertura').val()
            if (abertura) {

                let qtd = $("#inp-quantidade").val();
                let value_unit = $("#inp-valor_unitario").val();
                let sub_total = $("#inp-subtotal").val();
                let product_id = $("#inp-produto_id").val();
                if (qtd && value_unit && product_id && sub_total) {
                    let dataRequest = {
                        qtd: qtd,
                        value_unit: value_unit,
                        sub_total: sub_total,
                        product_id: product_id,
                        empresa_id: $('#empresa_id').val(),
                        lista_preco_id: listaPrecoPdvId(),
                    };
                    $.get(path_url + "api/frenteCaixa/linhaProdutoVenda", dataRequest)
                    .done((e) => {
                        $(".table-itens tbody").append(e);

                        calcTotal();
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
                swal("Atenção", "Abra o caixa para continuar!","warning").then(() => {
                    validaCaixa()
                })
            }
        }, 100);
        adicionado = true
    }

    setTimeout(() => {
        adicionado = false
    }, 200)
});


var total_venda = 0;

function normalizarNumeroPdv(valor) {
    if (typeof valor === 'number') {
        return isFinite(valor) ? valor : 0;
    }

    var texto = String(valor || '').trim().replace(/[^0-9,.-]/g, '');
    if (!texto) {
        return 0;
    }

    if (texto.indexOf(',') >= 0) {
        texto = texto.replace(/\./g, '').replace(',', '.');
    }

    var numero = parseFloat(texto);
    return isFinite(numero) ? numero : 0;
}

function valorDoAjuste(tipo, valorInformado) {
    var valor = Math.max(0, normalizarNumeroPdv(valorInformado));
    return tipo === 'percentual'
        ? total_venda * (valor / 100)
        : valor;
}

function textoDoAjuste(tipo, valorInformado, valorCalculado) {
    if (tipo === 'percentual' && normalizarNumeroPdv(valorInformado) > 0) {
        return convertFloatToMoeda(normalizarNumeroPdv(valorInformado)) +
            '% (R$ ' + convertFloatToMoeda(valorCalculado) + ')';
    }

    return 'R$ ' + convertFloatToMoeda(valorCalculado);
}

function recalcularAjustesPdv() {
    DESCONTO = valorDoAjuste(DESCONTO_TIPO, DESCONTO_VALOR_INFORMADO);
    VALORACRESCIMO = valorDoAjuste(ACRESCIMO_TIPO, ACRESCIMO_VALOR_INFORMADO);

    $('#valor_desconto').text(
        textoDoAjuste(DESCONTO_TIPO, DESCONTO_VALOR_INFORMADO, DESCONTO)
    );
    $('#valor_acrescimo').text(
        textoDoAjuste(ACRESCIMO_TIPO, ACRESCIMO_VALOR_INFORMADO, VALORACRESCIMO)
    );
    $('#valor_taxa_entrega').text('R$ ' + convertFloatToMoeda(TAXA_ENTREGA));
}

function calcTotal() {
    var total = 0;

    $('.subtotal-item').each(function () {
        total += convertMoedaToFloat($(this).val());
    });

    setTimeout(function () {
        total_venda = total;
        TOTAL = total;
        recalcularAjustesPdv();

        var totalLiquido = totalLiquidoPdv();
        $('.total-venda').html(convertFloatToMoeda(totalLiquido));
        $('.total-venda-modal').html(convertFloatToMoeda(totalLiquido));
        $('#inp-valor_integral').val(convertFloatToMoeda(totalLiquido));

        $('#inp-quantidade').val('');
        $('#inp-valor_unitario').val('');
        $('#inp-produto_id').val('').change();
    }, 100);
}

$('#valor_recebido').on('keyup', function () {
    var valorRecebido = normalizarNumeroPdv($('#valor_recebido').val());
    var troco = valorRecebido - totalLiquidoPdv();
    $('#valor-troco').html(convertFloatToMoeda(Math.max(0, troco)));
});

$("body").on("click", "#btn-incrementa", function () {
    let inp = $(this).closest('div.input-group-append').prev()[0]
    if (inp.value) {
        let v = convertMoedaToFloat(inp.value)
        v += 1
        inp.value = convertFloatToMoeda(v)
        calcSubTotal()
    }
})

$("body").on("click", "#btn-subtrai", function () {
    let inp = $(this).closest('.input-group').find('input')[0]

    if (inp.value) {
        let v = convertMoedaToFloat(inp.value)
        v -= 1
        inp.value = convertFloatToMoeda(v)

        calcSubTotal()
    }
})

function calcSubTotal(e) {

    $(".line-product").each(function () {
        $qtd = $(this).find('.qtd')[0]
        $value = $(this).find('.value-unit')[0]
        $sub = $(this).find('.subtotal-item')[0]

        let qtd = convertMoedaToFloat($qtd.value)
        let value = convertMoedaToFloat($value.value)
        if (qtd <= 0) {
            $(this).remove()
        } else {
            $sub.value = convertFloatToMoeda(qtd * value)
        }
    })
    setTimeout(() => {
        calcTotal()
    }, 10)
}

function escolherTipoAjuste(titulo, callback) {
    swal({
        title: titulo,
        text: 'Escolha como deseja informar o valor.',
        icon: 'info',
        buttons: {
            cancelar: {
                text: 'Cancelar',
                value: null,
                visible: true,
            },
            fixo: {
                text: 'Valor em R$',
                value: 'fixo',
            },
            percentual: {
                text: 'Porcentagem %',
                value: 'percentual',
            },
        },
    }).then(function (tipo) {
        if (tipo === 'fixo' || tipo === 'percentual') {
            callback(tipo);
        }
    });
}

function solicitarValorAjuste(titulo, tipo, limitePercentual, callback) {
    swal({
        title: titulo,
        text: tipo === 'percentual'
            ? 'Digite a porcentagem. Exemplo: 10'
            : 'Digite o valor em reais. Exemplo: 15,50',
        content: 'input',
        buttons: ['Cancelar', 'Aplicar'],
    }).then(function (valor) {
        if (valor === null) {
            return;
        }

        var numero = normalizarNumeroPdv(valor);
        var limite = limitePercentual || 1000;
        if (numero < 0 || (tipo === 'percentual' && numero > limite)) {
            swal(
                'Valor inválido',
                tipo === 'percentual'
                    ? 'A porcentagem deve ficar entre 0% e ' + limite + '%.'
                    : 'Informe um valor igual ou maior que zero.',
                'warning'
            );
            return;
        }

        callback(numero);
        calcTotal();
        $('#codBarras').focus();
    });
}

function setaDesconto() {
    if (total_venda <= 0) {
        swal('Venda sem produtos', 'Adicione um produto antes de aplicar desconto.', 'warning');
        return;
    }

    escolherTipoAjuste('Aplicar desconto', function (tipo) {
        solicitarValorAjuste('Valor do desconto', tipo, 100, function (valor) {
            var limite = normalizarNumeroPdv($('#percentual_max_desconto').val());
            var percentualEquivalente = tipo === 'percentual'
                ? valor
                : (total_venda > 0 ? (valor / total_venda) * 100 : 0);

            if (limite > 0 && percentualEquivalente > limite + 0.0001) {
                swal(
                    'Desconto acima do permitido',
                    'O máximo configurado para esta empresa é ' +
                        convertFloatToMoeda(limite) + '%.',
                    'warning'
                );
                return;
            }

            DESCONTO_TIPO = tipo;
            DESCONTO_VALOR_INFORMADO = valor;
        });
    });
}

function setaAcrescimo() {
    if (total_venda <= 0) {
        swal('Venda sem produtos', 'Adicione um produto antes de aplicar acréscimo.', 'warning');
        return;
    }

    escolherTipoAjuste('Aplicar acréscimo', function (tipo) {
        solicitarValorAjuste('Valor do acréscimo', tipo, 1000, function (valor) {
            ACRESCIMO_TIPO = tipo;
            ACRESCIMO_VALOR_INFORMADO = valor;
        });
    });
}

function setaTaxaEntrega() {
    if (total_venda <= 0) {
        swal('Venda sem produtos', 'Adicione um produto antes de informar a taxa de entrega.', 'warning');
        return;
    }

    solicitarValorAjuste('Taxa de entrega', 'fixo', null, function (valor) {
        TAXA_ENTREGA = valor;
    });
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

var total_payment = 0;

function totalLiquidoPdv() {
    return Math.max(0, Number(total_venda || 0) + Number(TAXA_ENTREGA || 0) + Number(VALORACRESCIMO || 0) - Number(DESCONTO || 0));
}

function totalLinhasPagamento() {
    var total = 0;
    $('[name="valor_integral_row[]"]').each(function () {
        total += convertMoedaToFloat($(this).val());
    });
    return total;
}

function calcTotalPayment() {
    $('#btn-pag_row').attr('disabled', true);
    var total = totalLinhasPagamento();
    var diferenca = totalLiquidoPdv() - total;

    total_payment = total;
    $('.sum-payment').html('R$ ' + convertFloatToMoeda(total));
    $('.sum-restante').html('R$ ' + convertFloatToMoeda(diferenca));

    if (Math.abs(diferenca) <= 0.01 && total > 0) {
        $('#btn-pag_row').removeAttr('disabled');
    }

    validateButtonSave();
}


$(".table-payment").on("click", ".btn-delete-row", function () {
    $(this).closest("tr").remove();
    swal("Sucesso", "Parcela removida!", "success");
    calcTotalPayment();
});

$(".btn-add-payment").click(() => {
    let tipo_pagamento_row = $("#inp-tipo_pagamento_row").val();
    let vencimento = $("#inp-data_vencimento_row").val();
    let valor_integral_row = $("#inp-valor_integral_row").val();
    let obs_row = $("#inp-obs_row").val();

    validateButtonSave();

    let v = convertMoedaToFloat(valor_integral_row);

    if (v > 0 && v + total_payment <= totalLiquidoPdv() + 0.009) {
        if (vencimento && valor_integral_row && tipo_pagamento_row) {
            let dataRequest = {
                data_vencimento_row: vencimento,
                valor_integral_row: valor_integral_row,
                obs_row: obs_row,
                tipo_pagamento_row: tipo_pagamento_row,
            };

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


function selecionarCliente() {
    let cliente = $("#inp-cliente").val();
    if (c == cliente) {
        CLIENTE = c;
    }
    $("#conta_credito-btn").removeClass("disabled");
    $("#modal-selecionar_cliente").modal("hide");
}

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
                        console.clear();
                        var query = {
                            pesquisa: params.term,
                            empresa_id: $("#empresa_id").val(),
                        };
                        return query;
                    },
                    processResults: function (response) {
                        // console.log("response", response);
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

$("#inp-tipo_pagamento").change(() => {
    $("#valor_recebido").val();
    let tipo = $("#inp-tipo_pagamento").val();
    let cliente = $("#inp-cliente_id").val();
    if (tipo == '06') {
        if (cliente == null) {
            swal("Alerta", "Informe o cliente!", "warning")
            $('#inp-tipo_pagamento').val('').change()
        }
        $(".div-vencimento").removeClass('d-none');
    }

    if (tipo == "03" || tipo == "04") {
        $('#modal-dados_cartao').modal('show')
        $(".div-vencimento").addClass('d-none');

    }

    if (tipo == "99") {
        $("#modal-pag-outros").modal("show");
        $(".div-vencimento").addClass('d-none');

    }

    if (tipo == "01") {
        $("#valor_recebido").removeAttr("disabled");
        $("#finalizar-venda").attr("disabled", true);
        $("#finalizar-rascunho").attr("disabled", true);
        $("#finalizar-consignado").attr("disabled", true);
        $(".div-troco").removeClass('d-none');
        $(".div-vencimento").addClass('d-none');
    } else {
        $("#valor_recebido").attr("disabled", "true");
        $(".div-troco").addClass('d-none');
        $("#finalizar-venda").removeAttr("disabled");
        $("#finalizar-rascunho").removeAttr("disabled");
        $("#finalizar-consignado").removeAttr("disabled");
    }

    validateButtonSave()
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

$('#valor_recebido').blur(() => {
    validateButtonSave()
})

function validateButtonSave() {
    var botao = $('#salvar_venda');
    botao.attr('disabled', true);

    if (botao.attr('data-bloqueio-credito') === '1' || window.__pdvSubmissionInProgress === true) {
        return false;
    }

    var totalLiquido = totalLiquidoPdv();
    var tipoDireto = String($('#inp-tipo_pagamento').val() || '');
    var valorRecebido = convertMoedaToFloat($('#valor_recebido').val());
    var tiposMistos = $('[name="tipo_pagamento_row[]"]');
    var pagamentoDiretoValido = false;
    var pagamentoMultiploValido = false;

    if (totalLiquido <= 0) {
        return false;
    }

    if (tipoDireto) {
        pagamentoDiretoValido = tipoDireto !== '01' || valorRecebido + 0.009 >= totalLiquido;

        if (tipoDireto === '19') {
            var valorPixConfirmado = convertMoedaToFloat($('#pix_valor').val());
            pagamentoDiretoValido =
                window.__pdvPixQrConfirmado === true &&
                $('#pix_payment_id').val() !== '' &&
                Math.abs(valorPixConfirmado - totalLiquido) <= 0.01;
        }
    }

    if (tiposMistos.length > 0) {
        pagamentoMultiploValido =
            tiposMistos.length === $('[name="valor_integral_row[]"]').length &&
            Math.abs(totalLinhasPagamento() - totalLiquido) <= 0.01;
    }

    var pagamentoValido = tiposMistos.length > 0
        ? pagamentoMultiploValido
        : pagamentoDiretoValido;

    if (pagamentoValido) {
        botao.removeAttr('disabled');
        return true;
    }

    return false;
}


function verificaCaixa() {
    $.ajax({
        type: 'GET',
        url: path_url + "api/aberturaCaixa/verificaHoje",
        dataType: 'json',
        success: function (e) {
            data(e)
        }, error: function (e) {
            console.log(e)
        }
    });
}
// horário no pdv
setInterval(() => {
    let date = new Date().toLocaleTimeString();
    $('#timer').html(date)
}, 100);

$.fn.serializeFormJSON = function() {
    var o = {};
    var a = this.serializeArray();
    $.each(a, function() {
        if (o[this.name]) {
            if (!o[this.name].push) {
                o[this.name] = [o[this.name]];
            }
            o[this.name].push(this.value || '');
        } else {
            o[this.name] = this.value || '';
        }
    });
    return o;
};
// function convertFormToJSON(form) {
//     return $(form)
//     .serializeArray()
//     .reduce(function (json, { name, value }) {
//         json[name] = value;
//         return json;
//     }, {});
// }

var emitirNfce = false;
window.__pdvSubmissionInProgress = false;
window.__pdvUtf8RetryAttempted = false;
window.__pdvLoadingAlertOpen = false;
window.__pdvPixQrConfirmado = false;

function abrirCarregamentoPdv(titulo, mensagem) {
    window.__pdvLoadingAlertOpen = true;

    if (typeof window.swal !== 'function') {
        return;
    }

    var conteudo = document.createElement('div');
    conteudo.setAttribute('role', 'status');
    conteudo.setAttribute('aria-live', 'polite');
    conteudo.innerHTML =
        '<div class="spinner-border text-primary mb-3" aria-hidden="true"></div>' +
        '<p class="mb-0">' + mensagem + '</p>';

    window.swal({
        title: titulo,
        content: conteudo,
        icon: 'info',
        buttons: false,
        closeOnClickOutside: false,
        closeOnEsc: false
    });
}

function fecharCarregamentoPdv() {
    if (!window.__pdvLoadingAlertOpen) {
        return;
    }

    window.__pdvLoadingAlertOpen = false;
    if (typeof window.swal === 'function' && typeof window.swal.close === 'function') {
        window.swal.close();
    }
}

function mensagemValidacaoPdv(resposta) {
    if (resposta && resposta.errors) {
        var mensagens = [];
        Object.keys(resposta.errors).forEach(function (campo) {
            var errosCampo = resposta.errors[campo];
            if (Array.isArray(errosCampo)) {
                mensagens = mensagens.concat(errosCampo);
            }
        });
        if (mensagens.length) {
            return mensagens.join('\n');
        }
    }

    return resposta && resposta.message
        ? resposta.message
        : 'Confira os produtos e a forma de pagamento antes de finalizar.';
}

$('#form-pdv').on('submit', function (e) {
    e.preventDefault();

    if (window.__pdvSubmissionInProgress) {
        return;
    }

    if (!validateButtonSave()) {
        swal('Dados incompletos', 'Adicione produtos e informe uma forma de pagamento válida antes de finalizar.', 'warning');
        return;
    }

    var json = $(this).serializeFormJSON();
    json.empresa_id = $('#empresa_id').val();
    json.usuario_id = $('#usuario_id').val();
    json.lista_preco_id = listaPrecoPdvId();
    json.desconto = DESCONTO;
    json.desconto_tipo = DESCONTO_TIPO;
    json.desconto_valor = DESCONTO_VALOR_INFORMADO;
    json.acrescimo = VALORACRESCIMO;
    json.acrescimo_tipo = ACRESCIMO_TIPO;
    json.acrescimo_valor = ACRESCIMO_VALOR_INFORMADO;
    json.taxa_entrega = TAXA_ENTREGA;

    window.__pdvSubmissionInProgress = true;
    $('#salvar_venda').attr('disabled', true);
    abrirCarregamentoPdv(
        'Finalizando venda',
        'Aguarde enquanto os dados da venda são processados.'
    );

    $.post(path_url + 'api/pdv/store', json)
        .done((success) => {
            fecharCarregamentoPdv();
            if (emitirNfce === true) {
                gerarNfce(success);
                return;
            }

            swal({
                title: 'Sucesso',
                text: 'Venda finalizada com sucesso, deseja imprimir o comprovante?',
                icon: 'success',
                buttons: ['Não', 'Sim'],
                dangerMode: true,
            }).then((isConfirm) => {
                if (isConfirm) {
                    window.open(path_url + 'frenteCaixa/imprimir-nao-fiscal/' + success.id, '_blank');
                }

                if (success.is_os) {
                    window.open(path_url + 'ordemServico/completa/' + success.id_os);
                    return;
                }

                location.reload();
            });
        })
        .fail((xhr) => {
            fecharCarregamentoPdv();
            window.__pdvSubmissionInProgress = false;
            validateButtonSave();

            var resposta = xhr.responseJSON || {};
            var mensagem = mensagemValidacaoPdv(resposta);
            var erroUtf8 = /Malformed UTF-8/i.test(mensagem);

            if (erroUtf8 && !window.__pdvUtf8RetryAttempted) {
                window.__pdvUtf8RetryAttempted = true;
                window.setTimeout(function () {
                    $('#form-pdv').trigger('submit');
                }, 250);
                return;
            }

            if (erroUtf8) {
                resposta.message = 'O servidor não conseguiu confirmar a resposta da venda. Confira a listagem antes de tentar novamente.';
            }

            var titulo = resposta.credito
                ? 'Venda não autorizada'
                : (xhr.status === 422 ? 'Dados inválidos' : 'Não foi possível finalizar');

            swal(titulo, mensagemValidacaoPdv(resposta), 'error');

            if (resposta.credito && typeof window.atualizarCreditoClientePdv === 'function') {
                window.atualizarCreditoClientePdv(resposta.credito);
            }
        });
});


$('#btn-emitir-nfce').click(() => {
    emitirNfce = true
    // $("#form-pdv").submit()
})

function gerarNfce(venda) {
    // console.log("emitindo nfce ...")

    let empresa_id = $("#empresa_id").val();
    abrirCarregamentoPdv(
        'Emitindo NFC-e',
        'Aguarde a autorização da nota fiscal.'
    );

    $.post(path_url + 'api/nfce/transmitir', {
        id: venda.id,
        empresa_id: empresa_id,
    })
    .done((success) => {
        fecharCarregamentoPdv();

        swal("Sucesso", "NFCe emitida " + success, "success")
        .then(() => {
            window.open(path_url + 'nfce/imprimir/' + venda.id, "_blank")
            setTimeout(() => {
                location.reload()
            }, 100)
        })
    }).fail((err) => {
        console.log(err)
        if (err.status == 403) {
            try {
                let infProt = err.responseJSON.protNFe.infProt
                swal("Algo deu errado", infProt.cStat + " - " + infProt.xMotivo, "error")
            } catch {
                swal("Algo deu errado", err.responseJSON, "error")
            }
        } else {
            swal("Algo deu errado", err.responseJSON, "error")
        }
    })
}

var leituraCodigoTimer = null;
var leituraCodigoEmAndamento = false;
var codigoPendente = null;

function finalizarLeituraCodigo() {
    leituraCodigoEmAndamento = false;

    if (codigoPendente) {
        var proximoCodigo = codigoPendente;
        codigoPendente = null;
        processarCodigoBarras(proximoCodigo);
        return;
    }

    $('#codBarras').val('').focus();
}

function adicionarProdutoDoLeitor(produto) {
    var valor = Number(produto && (
        produto.valor_venda_pdv !== undefined
            ? produto.valor_venda_pdv
            : produto.valor_venda
    ));

    if (!produto || !produto.id || !Number.isFinite(valor)) {
        swal('Produto não encontrado', 'O código lido não pertence a um produto válido.', 'warning');
        finalizarLeituraCodigo();
        return;
    }

    $('#inp-produto_id').html('');
    $('#inp-produto_id')
        .append(new Option(produto.nome, produto.id, true, true))
        .trigger('change.select2');

    $('#inp-quantidade').val('1,00');
    $('#inp-valor_unitario').val(convertFloatToMoeda(valor));
    $('#inp-subtotal').val(convertFloatToMoeda(valor));

    enviarProdutoParaTabela(produto.id, '1,00', convertFloatToMoeda(valor), convertFloatToMoeda(valor));
}

function processarCodigoBarras(codigo) {
    codigo = String(codigo || '').trim();

    if (codigo.length < 8) {
        return;
    }

    if (leituraCodigoEmAndamento) {
        codigoPendente = codigo;
        return;
    }

    leituraCodigoEmAndamento = true;
    $('#codBarras').val('');

    $.get(path_url + 'api/produtos/findByBarcode', {
        barcode: codigo,
        empresa_id: $('#empresa_id').val(),
        lista_preco_id: listaPrecoPdvId()
    })
        .done(adicionarProdutoDoLeitor)
        .fail((xhr) => {
            if (xhr.status === 404) {
                buscarPorReferencia(codigo);
                return;
            }

            swal(
                'Erro de leitura',
                (xhr.responseJSON && xhr.responseJSON.message) || 'Não foi possível consultar o código de barras.',
                'error'
            );
            finalizarLeituraCodigo();
        });
}

$('#codBarras')
    .on('keydown', function (event) {
        if (event.key !== 'Enter' && event.keyCode !== 13) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();
        event.stopImmediatePropagation();
        window.clearTimeout(leituraCodigoTimer);
        processarCodigoBarras($(this).val());
    })
    .on('input', function () {
        var codigo = $(this).val();
        window.clearTimeout(leituraCodigoTimer);
        leituraCodigoTimer = window.setTimeout(function () {
            processarCodigoBarras(codigo);
        }, 300);
    });

function buscarPorReferencia(barcode) {
    $.get(path_url + 'api/produtos/findByBarcodeReference', {
        barcode: barcode,
        empresa_id: $('#empresa_id').val(),
        usuario_id: $('#usuario_id').val()
    })
        .done((produto) => {
            var quantidade = String(produto.quantidade).replace('.', ',');
            var valorUnitario = convertFloatToMoeda(produto.valor);
            var subtotal = convertFloatToMoeda(produto.valor_venda_calculado);

            $('#inp-produto_id').html('');
            $('#inp-produto_id')
                .append(new Option(produto.nome, produto.id, true, true))
                .trigger('change.select2');
            $('#inp-quantidade').val(quantidade);
            $('#inp-valor_unitario').val(valorUnitario);
            $('#inp-subtotal').val(subtotal);

            enviarProdutoParaTabela(produto.id, quantidade, valorUnitario, subtotal);
        })
        .fail((xhr) => {
            swal(
                'Produto não encontrado',
                (xhr.responseJSON && xhr.responseJSON.message) || 'Nenhum produto corresponde ao código lido.',
                'warning'
            );
            finalizarLeituraCodigo();
        });
}

function enviarProdutoParaTabela(productId, quantidade, valorUnitario, subtotal) {
    if (!$('#abertura').val()) {
        swal('Atenção', 'Abra o caixa para continuar!', 'warning');
        finalizarLeituraCodigo();
        return;
    }

    $.get(path_url + 'api/frenteCaixa/linhaProdutoVenda', {
        qtd: quantidade,
        value_unit: valorUnitario,
        sub_total: subtotal,
        product_id: productId,
        empresa_id: $('#empresa_id').val(),
        lista_preco_id: listaPrecoPdvId()
    })
        .done((linha) => {
            $('.table-itens tbody').append(linha);
            calcTotal();
        })
        .fail((xhr) => {
            swal(
                'Erro ao adicionar produto',
                (xhr.responseJSON && xhr.responseJSON.message) || 'Não foi possível adicionar o produto à venda.',
                'error'
            );
        })
        .always(finalizarLeituraCodigo);
}