var TOTAL = 0;

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
})

$(document).on("change", ".produto_id", function () {
    let product_id = $(this).val()
    if (product_id) {
        $qtd = $(this).closest('td').next().find('input');
        $vlUnit = $qtd.closest('td').next().find('input');
        $sub = $vlUnit.closest('td').next().find('input');
        $perc_icms = $sub.closest('td').next().find('input');
        $valor_icms = $perc_icms.closest('td').next().find('input');
        $perc_pis = $valor_icms.closest('td').next().find('input');
        $valor_pis = $perc_pis.closest('td').next().find('input');
        $perc_cofins = $valor_pis.closest('td').next().find('input');
        $valor_cofins = $perc_cofins.closest('td').next().find('input');
        $perc_ipi = $valor_cofins.closest('td').next().find('input');
        $valor_ipi = $perc_ipi.closest('td').next().find('input');
        $perc_red_bc = $valor_ipi.closest('td').next().find('input');
        $CFOP_saida_estadual = $perc_red_bc.closest('td').next().find('input');
        $NCM = $CFOP_saida_estadual.closest('td').next().find('input');
        $CEST = $NCM.closest('td').next().find('input');
        $modBCST = $CEST.closest('td').next().find('select');
        $vbc_icms = $modBCST.closest('td').next().find('input');
        $vbc_pis = $vbc_icms.closest('td').next().find('input'); 
        $vbc_cofins = $vbc_pis.closest('td').next().find('input');
        $vbc_ipi = $vbc_cofins.closest('td').next().find('input');
        $VBCSTRET = $vbc_ipi.closest('td').next().find('input');
        $vFrete = $VBCSTRET.closest('td').next().find('input');
        $vBCST = $vFrete.closest('td').next().find('input');
        $pICMSST = $vBCST.closest('td').next().find('input');
        $vICMSST = $pICMSST.closest('td').next().find('input');
        $pMVAST = $vICMSST.closest('td').next().find('input');
        $x_pedido = $pMVAST.closest('td').next().find('input');
        $num_item_pedido = $x_pedido.closest('td').next().find('input');
        $CST_CSOSN = $num_item_pedido.closest('td').next().find('select');
        $CST_PIS = $CST_CSOSN.closest('td').next().find('select');
        $CST_COFINS = $CST_PIS.closest('td').next().find('select');
        $CST_IPI = $CST_COFINS.closest('td').next().find('select');

        $.get(path_url + "api/produtos/findProdRemessa", {produto_id: product_id, cliente_id: $('#inp-cliente_id').val()})
        .done((e) => {
            $qtd.val('1,00')
            $vlUnit.val(convertFloatToMoeda(e.valor_venda))
            $sub.val(convertFloatToMoeda(e.valor_venda))
            $perc_icms.val(e.perc_icms)
            $valor_icms.val(convertFloatToMoeda((e.perc_icms) * (e.valor_venda)))
            $perc_pis.val(e.perc_pis)
            $valor_pis.val(convertFloatToMoeda((e.perc_pis) * (e.valor_venda)))
            $perc_cofins.val(e.perc_cofins)
            $valor_cofins.val(convertFloatToMoeda((e.perc_cofins) * (e.valor_venda)))
            $perc_ipi.val(e.perc_ipi)
            $valor_ipi.val(convertFloatToMoeda((e.perc_ipi) * (e.valor_venda)))
            $perc_red_bc.val(e.pRedBC)
            $CFOP_saida_estadual.val(e.CFOP_saida_estadual)
            $NCM.val(e.NCM)
            $CEST.val(e.CEST)
            $modBCST.val(e.modBCST).change()
            $vbc_icms.val(convertFloatToMoeda((e.perc_icms) * (e.valor_venda)))
            $vbc_pis.val(convertFloatToMoeda((e.perc_pis) * (e.valor_venda)))
            $vbc_cofins.val(convertFloatToMoeda((e.perc_cofins) * (e.valor_venda)))
            $vbc_ipi.val(convertFloatToMoeda((e.perc_ipi) * (e.valor_venda)))
            $VBCSTRET.val(convertFloatToMoeda((e.pRedBC) * (e.valor_venda)))
            $vFrete.val(e.vFrete)
            $vBCST.val(convertFloatToMoeda((e.pRedBC) * (e.valor_venda)))
            $pICMSST.val(e.pICMSST)
            $vICMSST.val(convertFloatToMoeda((e.pRedBC) * (e.valor_venda)))
            $pMVAST.val(convertFloatToMoeda((e.pRedBC) * (e.valor_venda)))
            $x_pedido.val(e.x_pedido)
            $num_item_pedido.val(e.num_item_pedido)
            $CST_CSOSN.val(e.CST_CSOSN).change()
            $CST_PIS.val(e.CST_PIS).change()
            $CST_COFINS.val(e.CST_COFINS).change()
            $CST_IPI.val(e.CST_IPI).change()
            calcTotal()
            limpaFatura()
            calTotalNfe()
        })
        .fail((e) => {
            console.log(e)
        })
    }
})

function limpaFatura() {
    console.clear()
    $('#body-pagamento tr').each(function (e, x) {
        if (e == 0) {
            setTimeout(() => {
                total = 0
                $(".sub_total").each(function () {
                    total += convertMoedaToFloat($(this).val())
                })

                $('.valor_fatura').first().val(convertFloatToMoeda(total))
                $('.tipo_pagamento').first().val('').change()
                let data = new Date
                let dataFormatada = (data.getFullYear() + "-" + adicionaZero((data.getMonth() + 1)) + "-" + adicionaZero(data.getDate()));
                $('.date_atual').first().val(dataFormatada)
                calcTotalFatura()
            }, 500)

        } else {
            x.remove();
        }
    })
}

$('body').on('blur', '.valor_unit', function () {
    $qtd = $(this).closest('td').prev().find('input');
    $sub = $(this).closest('td').next().find('input');

    let value_unit = $(this).val();
    value_unit = convertMoedaToFloat(value_unit)
    let qtd = convertMoedaToFloat($qtd.val())
    $sub.val(convertFloatToMoeda(qtd * value_unit))
})

$('.btn-add-tr').on("click", function () {
    console.clear()
    var $table = $(this)
    .closest(".row")
    .prev()
    .find(".table-dynamic");
    var hasEmpty = false;
    $table.find("input, select").each(function () {
        if (($(this).val() == "" || $(this).val() == null) && $(this).attr("type") != "hidden" && $(this).attr("type") != "file" && !$(this).hasClass("ignore")) {
            hasEmpty = true;
        }
    });
    if (hasEmpty) {
        swal(
            "Atenção",
            "Preencha todos os campos antes de adicionar novos.",
            "warning"
            );
        return;
    }
    // $table.find("select.select2").select2("destroy");
    var $tr = $table.find(".dynamic-form").first();
    $tr.find("select.select2").select2("destroy");
    var $clone = $tr.clone();
    $clone.show();
    $clone.find("input,select").val("");
    $table.append($clone);
    setTimeout(function () {
        $("tbody select.select2").select2({
            language: "pt-BR",
            width: "100%",
            theme: "bootstrap4"
        });
    }, 100);
})

$(document).delegate(".btn-remove-tr", "click", function (e) {
    e.preventDefault();
    swal({
        title: "Você esta certo?",
        text: "Deseja remover esse item mesmo?",
        icon: "warning",
        buttons: true
    }).then(willDelete => {
        if (willDelete) {
            var trLength = $(this)
            .closest("tr")
            .closest("tbody")
            .find("tr")
            .not(".dynamic-form-document").length;
            if (!trLength || trLength > 1) {
                $(this)
                .closest("tr")
                .remove();
            } else {
                swal(
                    "Atenção",
                    "Você deve ter ao menos um item na lista",
                    "warning"
                    );
            }
        }
    });
});


$(function () {
    calcTotal()
    $('body').on('blur', '.produto_id', function () {
        calcTotal()
        // validateButtonSave()
    })
})

$(function () {
    calcTotal()
    $('body').on('blur', '.sub_total', function () {
        calcTotal()
        limpaFatura()
        // validateButtonSave()
    })
})

$(function () {
    $('.btn-action').attr('disabled', 'disabled')
    // $('.checkbox').each(function(i, e){
    //  e.checked = false
    // })

    validLineSelect()
})

function validLineSelect() {
    $('.btn-action').attr('disabled', 'disabled')

    $('.checkbox').each(function (i, e) {
        if ($(this).is(':checked')) {
            let status = $(this).data('status')
            if (status == 'novo' || status == 'rejeitado') {
                $('#btn-enviar').removeAttr('disabled')
                $('#btn-danfe-temp').removeAttr('disabled')
            } else if (status == 'aprovado') {
                $('#btn-imprimir').removeAttr('disabled')
                $('#btn-imprimir-cce').removeAttr('disabled')
                $('#btn-consultar').removeAttr('disabled')
                $('#btn-cancelar').removeAttr('disabled')
                $('#btn-corrigir').removeAttr('disabled')
                $('#btn-baixar-xml').removeAttr('disabled')
                $('#btn-enviar-email').removeAttr('disabled')
            } else if (status == 'cancelado') {
                $('#btn-imprimir-cancela').removeAttr('disabled')
            }
        }
    })
}

$('.checkbox').click(function () {
    $value = $(this).val()

    $('.checkbox').each(function (i, e) {
        if (e.value != $value) {
            e.checked = false
        }

        validLineSelect()
    })
})


// CÁLCULO TOTAL DE PRODUTOS
var total_venda = 0
var total_prod = 0
function calcTotal() {
    var total = 0
    $(".sub_total").each(function () {
        total += convertMoedaToFloat($(this).val())
    })
    setTimeout(() => {
        total_venda = total
        $('.total_prod').html("R$ " + convertFloatToMoeda(total))
        $('.total_prod').val(total)
        total_prod = total
        calTotalNfe()
    }, 100)
}


var total_frete = 0
var total_nfe = 0
function calTotalNfe() {
    let acrescimo = convertMoedaToFloat($('#inp-acrescimo').val())
    let desconto = convertMoedaToFloat($('#inp-desconto').val())
    let total_fr = convertMoedaToFloat($("#inp-valor_frete").val())

    setTimeout(() => {
        total_frete = total_fr
        total_nfe = total_prod + total_fr + acrescimo - desconto
        $('.total-venda').html(("R$ " + convertFloatToMoeda(total_nfe)))
        $('.total-geral').val(convertFloatToMoeda(total_nfe))
    }, 200)
}

$(function () {
    $('body').on('blur', '.acrescimo, .desconto, .valor_frete', function () {
        calTotalNfe()
        calcTotalFatura()
    })
})


// CÁLCULO TOTAL DA FATURA
$(function () {
    calcTotalFatura()
    $('body').on('blur', '.valor_fatura', function () {
        calcTotalFatura()
    })
})

var total_fatura = 0
function calcTotalFatura() {
    var total = 0
    $(".valor_fatura").each(function () {
        total += convertMoedaToFloat($(this).val())
    })
    setTimeout(() => {
        total_fatura = total
        $('.total_fatura').html("R$ " + convertFloatToMoeda(total))
    }, 100)
}


// CALCULO TOTAL DA NFCE
$(function () {
    $('body').on('blur', '.sub_total', function () {
        calTotalNfe()
    })
})

// $(function () {
//     $('body').on('blur', '.acrescimo, .desconto', function () {
//         calTotalNfe()
//         calcTotalFatura()
//     })
// })

$('.btn-salvar-nfe').click(() => {
    addClassRequired()
})

function addClassRequired() {
    let infMsg = ""
    $("body").find('input, select').each(function () {
        if ($(this).prop('required')) {
            if ($(this).val() == "") {
                try{
                    infMsg += $(this).prev()[0].textContent + "\n"
                }catch{}
                $(this).addClass('is-invalid')
            } else {
                $(this).removeClass('is-invalid')
            }
        } else {
            $(this).removeClass('is-invalid')
        }
    })
    if(!$('.produto_id').val()){
        infMsg += "Produto\n"
    }
    if(infMsg != ""){
        swal("Campos pendentes", infMsg, "warning")
    }
}

$(function(){
    setTimeout(() => {
        validateButtonSave();
    }, 300)
})

$('#inp-cliente_id').change(() => {
    validateButtonSave()
})

function validateButtonSave(){
    $('.alerts').html('')
    let cliente_id = $('#inp-cliente_id').val()
    // let count_pay = $(".table-payment tbody tr").length
    // let count_itens = $(".table-itens tbody tr").length

    if(!cliente_id){
        alertCreate("Selecione o cliente!")
    }
    // if(count_itens == 0){
    //     alertCreate("Adicione um produto na venda!")
    // }
    // if(count_pay == 0){
    //     alertCreate("Informe o pagamento!")
    // }

    setTimeout(() => {
        if($('.alerts').html() == ""){
            $('.btn-venda').removeAttr("disabled")

        }else{
            $('.btn-venda').attr("disabled", true);

        }
    }, 100)
}

function alertCreate(msg){
    var div = '<div class="alert alert-danger border-0 bg-danger alert-dismissible fade show">'
    div += '<div class="text-white">'+msg+'</div>'
    div += '</div>'
    $('.alerts').append(div)
}


$('#inp-forma_pagamento').change(() => {
    let fp = $('#inp-forma_pagamento').val()

    $("#inp-qtd_parcelas").attr("disabled", true);
    $("#inp-data_vencimento").attr("disabled", true);
    $("#inp-valor_integral").attr("disabled", true);
    if(fp == 'a_vista'){
        let now = new Date();
        let data = now.getFullYear() + "-" + ((now.getMonth() + 1) < 10 ? "0" + (now.getMonth() + 1) : (now.getMonth() + 1))
        + "-" + (now.getDate() < 10 ? "0" + now.getDate() : now.getDate())
        $('#inp-qtd_parcelas').val('1')
        $('#inp-data_vencimento').val(data)
        $('#inp-valor_integral').val(convertFloatToMoeda(total_nfe))
    }else if(fp == '30_dias'){
        var date = new Date(new Date().setDate(new Date().getDate() + 30));
        let data = date.getFullYear() + "-" + ((date.getMonth() + 1) < 10 ? "0" + (date.getMonth() + 1) : (date.getMonth() + 1))
        + "-" + (date.getDate() < 10 ? "0" + date.getDate() : date.getDate())
        $('#inp-qtd_parcelas').val('1')
        $('#inp-data_vencimento').val(data)
        $('#inp-valor_integral').val(convertFloatToMoeda(total_nfe))
    }else if(fp == 'personalizado'){
        $("#inp-qtd_parcelas").removeAttr("disabled");
        $("#inp-data_vencimento").removeAttr("disabled");
        $("#inp-valor_integral").removeAttr("disabled");

        $('#inp-qtd_parcelas').val('1')
        $('#inp-data_vencimento').val('')
        $('#inp-valor_integral').val(convertFloatToMoeda(total_nfe))
    }else{

    }
})

// $('#inp-qtd_parcelas').blur(() => {
//     clearPayment()
//     let qtd = $('#inp-qtd_parcelas')
//     $('#inp-valor_integral').val(convertFloatToMoeda(total_nfe / qtd));
// })

function clearPayment() {
    $('#table-payment tbody').html('')
    $("#inp-data_vencimento").val("");
    $("#inp-valor_integral").val("");
    $('.valor_integral').html('')
    $('.btn-add-payment').removeClass("disabled");
}

$('#remover_parcelas').click (() => {
    $('.table-payment tbody').html('')
    $('.sum-payment').html('')

})

$('.btn-add-payment').click(() => {
    let tipo_pagamento = $('#inp-tipo_pagamento').val();
    let vencimento = $('#inp-data_vencimento').val();
    let valor_integral = $('#inp-valor_integral').val();

    let v = convertMoedaToFloat(valor_integral)
    if((v + total_payment) <= total_nfe){
        if(vencimento && valor_integral && tipo_pagamento){
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
        }else{
            swal("Atenção", "Informe corretamente os campos para continuar!", "warning")
        }
    }else{
        swal("Atenção", "A soma das parcelas ultrapassa o valor total da venda", "warning")
    }
})

var total_payment = 0
function calcTotalPayment(){
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


function getChecked(call) {
	let id = null
	$('.checkbox').each(function (i, e) {
		if (e.checked) {
			id = e.value
		}
	})
	call(id)
}



$('#inp-forma_pagamento').change(() => {
    let fp = $('#inp-forma_pagamento').val()
    let tp = $('#inp-tipo_pagamento').val()
    if (fp == 'personalizado') {
        $('#btn-personalizado').removeClass('disabled')
        $('#inp-qtd_parcelas').on('keyup', () => {
            let parcelas = $('#inp-qtd_parcelas').val()
            $('#inp-valor_integral').val(convertFloatToMoeda(total_nfe / parcelas));
            $('#valor_integral_personalizado').val(total_nfe)
            // console.log(divisao)
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
    let totalTemp = total_nfe.toFixed(2)
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
    total_geral = convertFloatToMoeda(total_nfe)
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





//btn-enviar antigo
/*
$('#btn-enviar').click(() => {
	console.clear()
	getChecked((id) => {
		$("#btn-consulta-cnpj span").removeClass("d-none");
		let empresa_id = $("#empresa_id").val();
		$.post(path_url + "api/nferemessa/transmitir", {
			id: id,
			empresa_id: empresa_id,
		})
		.done((success) => {

			swal("Sucesso", "NFe remessa emitida " + success, "success")
			.then(() => {
				window.open(path_url + 'nferemessa/imprimir/' + id, "_blank")
				setTimeout(() => {
					location.reload()
				}, 100)
			})
		})
		.fail((err) => {
			console.log(err)
			try{
				if (err.status == 403) {
					let infProt = err.responseJSON.protNFe.infProt
					swal("Algo deu errado", infProt.cStat + " - " + infProt.xMotivo, "error")
				} else {
					swal("Algo deu errado", err.responseJSON, "error")
				}
			}catch{
				swal("Algo deu errado", err.responseJSON, "error")
			}
		})
	})
})
*/

//btn-enviar(transmitir)
$('#btn-enviar').click(() => {
    console.clear()

    getChecked((id) => {

        swal({
            title: "Atenção – Emissão com data retroativa",
            text:
                "A SEFAZ permite emissão de NF-e com data retroativa apenas dentro de um limite aproximado de 23 horas.\n\n" +
                "Caso a data informada ultrapasse esse prazo, a NF-e poderá ser rejeitada pela SEFAZ, sem possibilidade de intervenção do sistema.\n\n" +
                "Ao prosseguir, você confirma que está ciente dessa regra e assume total responsabilidade pela data informada.",
            icon: "warning",
            buttons: {
                cancel: {
                    text: "Cancelar",
                    visible: true
                },
                confirm: {
                    text: "Confirmar envio",
                    closeModal: true
                }
            },
            dangerMode: true
        }).then((confirmou) => {

            if (!confirmou) return

            $("#btn-consulta-cnpj span").removeClass("d-none")
            let empresa_id = $("#empresa_id").val()

            $.post(path_url + "api/nferemessa/transmitir", {
                id: id,
                empresa_id: empresa_id,
            })
            .done((resp) => {
                console.log(resp)

                swal(
                    "Sucesso",
                    "NF-e de remessa enviada com sucesso.",
                    "success"
                ).then(() => {
                    window.open(
                        path_url + 'nferemessa/imprimir/' + id,
                        "_blank"
                    )
                    setTimeout(() => location.reload(), 200)
                })
            })
            .fail((err) => {
                console.error(err)

                let msg = "Erro ao transmitir a NF-e."

                if (err.responseJSON?.mensagem) {
                    msg = err.responseJSON.mensagem
                } else if (err.responseJSON?.error) {
                    msg = err.responseJSON.error
                }

                swal(
                    "Algo deu errado",
                    msg,
                    "error"
                )
            })
        })
    })
})


$('#btn-imprimir').click(function () {
	getChecked((id) => {
		window.open(path_url + 'nferemessa/imprimir/' + id, "_blank")
	})
})

$('#btn-imprimir-cce').click(function () {
	getChecked((id) => {
		window.open(path_url + 'nferemessa/imprimir-cce/' + id, "_blank")
	})
})

$('#btn-imprimir-cancela').click(function () {
	getChecked((id) => {
		window.open(path_url + 'nferemessa/imprimir-cancela/' + id, "_blank")
	})
})

$('#btn-baixar-xml').click(function () {
	getChecked((id) => {
		window.open(path_url + 'nferemessa/baixar-xml/' + id, "_blank")
	})
})

$('#btn-danfe-temp').click(function () {
	getChecked((id) => {
		let href = $(this).data('href')
		window.open(href + "/" + id, "_blank")
	})
})

$('#btn-consultar').click(() => {
	console.clear()
	getChecked((id) => {
		if (id) {
			let empresa_id = $("#empresa_id").val();
			$.post(path_url + "api/nferemessa/consulta-nfe", {
				id: id,
				empresa_id: empresa_id,
			})
			.done((success) => {

				let infProt = success.protNFe.infProt   
				swal("Sucesso", "[" + infProt.chNFe + "] " + infProt.xMotivo, "success")
			})
			.fail((err) => {
				console.log(err)
				swal("Algo deu errado", err.responseJSON, "error")
			})
		} else {
			swal("Alerta", "Selecione uma venda!", "warning")
		}
	})
})

$('#btn-cancelar').click(() => {
	console.clear()
	getCheckedElement((el) => {
		let numero_nfe = el.data('numero_nfe')
		if (numero_nfe > 0) {
			$('.numero_nfe').text(numero_nfe)
			$('#modal-cancelar').modal('show')
		}
	})
})


function getCheckedElement(call) {
	$el = null
	$('.checkbox').each(function (i, e) {
		if (e.checked) {
			$el = $(this)
		}
	})
	call($el)
}


$('#btn-inutilizar').click(() => {
	console.clear()

	$('#modal-inutilizar').modal('show')
})

$('#btn-cancelar-send').click(() => {
	getChecked((id) => {
		if (id) {
			let empresa_id = $("#empresa_id").val();
			let motivo = $('#inp-motivo-cancela').val()
			if (motivo.length >= 15) {
				$.post(path_url + "api/nferemessa/cancelar-nfe", {
					id: id,
					empresa_id: empresa_id,
					motivo: motivo
				})
				.done((success) => {

					let infEvento = success.retEvento.infEvento
					swal("Sucesso", "[" + infEvento.cStat + "] " + infEvento.xMotivo, "success")
					.then(() => {
						window.open(path_url + 'nferemessa/imprimir-cancela/' + id, "_blank")
						setTimeout(() => {
							location.reload()
						}, 100)
					})

				})
				.fail((err) => {
					console.log(err)
					try {
						swal("Algo deu errado", err.responseJSON.retEvento.infEvento.xMotivo, "error")
					} catch {
						swal("Algo deu errado", err.responseJSON, "error")
					}
				})
			} else {
				swal("Alerta", "Informe no mínimo 15 caracteres", "warning")
			}
		} else {
			swal("Alerta", "Selecione uma venda!", "warning")
		}
	})
})

$('#btn-corrigir').click(() => {
	console.clear()
	getCheckedElement((el) => {
		let numero_nfe = el.data('numero_nfe')
		if (numero_nfe > 0) {
			$('.numero_nfe').text(numero_nfe)
			$('#modal-corrigir').modal('show')
		}
	})
})

$('#btn-corrige-send').click(() => {
	getChecked((id) => {
		if (id) {
			let empresa_id = $("#empresa_id").val();
			let motivo = $('#inp-motivo-corrige').val()
			if (motivo.length >= 15) {
				$.post(path_url + "api/nferemessa/corrigir-nfe", {
					id: id,
					empresa_id: empresa_id,
					motivo: motivo
				})
				.done((success) => {
					let infEvento = success.retEvento.infEvento
					swal("Sucesso", "[" + infEvento.cStat + "] " + infEvento.xMotivo, "success")
					.then(() => {
						window.open(path_url + 'nferemessa/imprimir-cce/' + id, "_blank")
						$('#modal-corrigir').modal('hide')
					})
				})
				.fail((err) => {
					console.log(err)
					try {
						swal("Algo deu errado", err.responseJSON.retEvento.infEvento.xMotivo, "error")
					} catch {
						swal("Algo deu errado", err.responseJSON, "error")
					}
				})
			} else {
				swal("Alerta", "Informe no mínimo 15 caracteres", "warning")
			}
		} else {
			swal("Alerta", "Selecione uma venda!", "warning")
		}
	})
})

$('#btn-inutiliza-send').click(() => {
	let empresa_id = $("#empresa_id").val();
	let motivo = $('#inp-motivo-inutiliza').val()
	let numero_inicial = $('#inp-numero_inicial').val()
	let numero_final = $('#inp-numero_final').val()
	if (motivo.length >= 15) {
		$.post(path_url + "api/nferemessa/inutiliza-nfe", {
			empresa_id: empresa_id,
			motivo: motivo,
			numero_inicial: numero_inicial,
			numero_final: numero_final
		})
		.done((success) => {
			let infInut = success.infInut
			if (infInut.cStat == "102") {
				$('#modal-inutilizar').modal('hide')
				swal("Sucesso", "[" + infInut.nProt + "] " + infInut.xMotivo, "success")
			} else {
				swal("Erro", "[" + infInut.cStat + "] " + infInut.xMotivo, "error")
			}
		})
		.fail((err) => {
			console.log(err)
			swal("Algo deu errado", err.responseJSON, "error")
		})
	} else {
		swal("Alerta", "Informe no mínimo 15 caracteres", "warning")
	}
})

$('.checkbox').click(function () {
	let email = $(this).data('email')
	$('#inp-email').val(email)
})