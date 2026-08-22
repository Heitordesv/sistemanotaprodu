MUNIPIOSCARREGAMENTO = [];
NUMEROS = [];


$(function () {
    validateButtonSave()
})

function selectDiv2(ref) {
    $('.btn-outline-primary').removeClass('active')
    if (ref == 'gerais') {
        $('.div-gerais').removeClass('d-none')
        $('.div-transporte').addClass('d-none')
        $('.div-descarregamento').addClass('d-none')
        $('.btn-gerais').addClass('active')

    } else if (ref == 'transporte') {
        $('.div-gerais').addClass('d-none')
        $('.div-transporte').removeClass('d-none')
        $('.div-descarregamento').addClass('d-none')
        $('.btn-transporte').addClass('active')

    } else {
        $('.div-transporte').addClass('d-none')
        $('.div-gerais').addClass('d-none')
        $('.div-descarregamento').removeClass('d-none')
        $('.btn-descarregamento').addClass('active')
    }
}

$('#btn-add-municipio-carregamento').click(() => {
    let cidade = JSON.parse($('#inp-cidade_id').val());
    if (cidade != 'null') {
        validaMunipioNaoInserido(cidade.id, (res) => {
            if (!res) {
                MUNIPIOSCARREGAMENTO.push({
                    id: cidade.id,
                    nome: cidade.nome
                });
                montaTabelaMunicipioCarregamento();
            } else {
                swal("Erro", "Este municipio já esta incluido", "error")
            }
            console.log(MUNIPIOSCARREGAMENTO)
        })
    } else {
        alert("Escolha uma cidade")
    }
})

function montaTabelaMunicipioCarregamento() {
    let html = "";
    MUNIPIOSCARREGAMENTO.map((res) => {

        html += '<tr class="datatable-row">'
        html += '<td class="datatable-cell"><span class="codigo" style="width: 60px;" id="id">'
        html += res.id
        html += '</span></td>'

        html += '<td class="datatable-cell"><span class="codigo" style="width: 200px;" id="id">'
        html += res.nome
        html += '</span></td>'

        html += '<td class="datatable-cell"><span class="codigo" style="width: 100px;" id="id">'
        html += '<a onclick="deleteMunicipioCarregamento(' + res.id +
        ')" class="btn btn-sm btn-danger"><i class="la la-trash"></i></a>'
        html += '</span></td>'

        html += "</tr>";
    })

    $('#tbody-municipio-carregamento').html(html);
}

function deleteMunicipioCarregamento(cId) {
    let temp = [];
    MUNIPIOSCARREGAMENTO.map((m) => {
        if (m.id != cId) temp.push(m);
    })
    MUNIPIOSCARREGAMENTO = temp;
    montaTabelaMunicipioCarregamento()
}

function validaMunipioNaoInserido(cId, call) {
    let retorno = false;
    MUNIPIOSCARREGAMENTO.map((m) => {
        if (m.id == cId) retorno = true;
    })
    call(retorno)
}

$('#inp-chave_nfe').on('keyup', () => {
    if ($('#inp-chave_nfe').val().length > 0) {
        $('#inp-chave_cte').attr('disabled', true)
        $('#inp-seg_cod_cte').attr('disabled', true)

        $('#inp-chave_cte').val("")
        $('#inp-seg_cod_cte').val("")
    } else {
        $('#inp-chave_cte').attr('disabled', false)
        $('#inp-seg_cod_cte').attr('disabled', false)
    }
})

$('#inp-chave_cte').on('keyup', () => {
    if ($('#inp-chave_cte').val().length > 0) {
        $('#inp-chave_nfe').attr('disabled', true)
        $('#inp-seg_cod_nfe').attr('disabled', true)

        $('#inp-chave_nfe').val("")
        $('#inp-seg_cod_nfe').val("")
    } else {
        $('#inp-chave_nfe').attr('disabled', false)
        $('#inp-seg_cod_nfe').attr('disabled', false)
    }
})


$('.btn_info_desc').click(() => {
    let hasEmpty = false;
    $('.form-descarregamento').find('input, select').each(function () {
        if (($(this).val() == '') && !$(this).hasClass('ignore')) {
            $(this).addClass('is-invalid')
            hasEmpty = true
        }
    })
    if (hasEmpty) {
        swal("Atenção", "Informe corretamente os campos para continuar!", "warning")
        return;
    }
    let tp_und_transp = $("#inp-tp_unid_transp").val();
    let id_und_transp = $("#inp-id_unid_transp").val();
    let quantidade_rateio = $("#inp-quantidade_rateio").val();
    let quantidade_rateio_carga = $("#inp-quantidade_rateio_carga").val();
    let chave_nfe = $("#inp-chave_nfe").val();
    let chave_cte = $("#inp-chave_cte").val();
    let municipio_descarregamento = $("#inp-municipio_descarregamento").val();
    let lacres_transporte = [];
    let lacres_unidade = [];

    $(".numero_transporte").each(function () {
        lacres_transporte.push($(this).val())

    });
    $(".numero_carga").each(function () {
        lacres_unidade.push($(this).val())
    });

    console.log(lacres_transporte)

    console.log(lacres_unidade)

    if (tp_und_transp || id_und_transp || quantidade_rateio || quantidade_rateio_carga || chave_nfe ||
        chave_cte || municipio_descarregamento || lacres_transporte || lacres_unidade) {
        let dataRequest = {
            tp_und_transp: tp_und_transp,
            id_und_transp: id_und_transp,
            quantidade_rateio: quantidade_rateio,
            quantidade_rateio_carga: quantidade_rateio_carga,
            chave_nfe: chave_nfe,
            chave_cte: chave_cte,
            municipio_descarregamento: municipio_descarregamento,
            lacres_transporte: lacres_transporte,
            lacres_unidade: lacres_unidade
        }

        console.log(dataRequest);

        $.get(path_url + "api/mdfe/linhaInfoDescarregamento", dataRequest)
        .done((e) => {
            $('.table-descarregamento tbody').append(e)
                // calcTotal()
            })
        .fail((e) => {
            console.log(e)
        })

        setTimeout(() => {
            limparInput()
            limparLinhas()
            validateButtonSave()
        }, 1000)

    } else {
        swal("Atenção", "Informe corretamente os campos para continuar!", "warning")
    }
})

$(".table-descarregamento").on('click', '.btn-delete-row', function () {
    $(this).closest('tr').remove();
    swal("Sucesso", "Item removido!", "success")
    calcTotal()
});

function limparInput() {
    $("#inp-tp_unid_transp").val('').change();
    $("#inp-id_unid_transp").val('');
    $("#inp-quantidade_rateio").val('');
    $("#inp-quantidade_rateio_carga").val('');
    $("#inp-chave_nfe").val('');
    $("#inp-chave_cte").val('');
    $("#inp-seg_cod_nfe").val('');
    $("#inp-seg_cod_cte").val('');
    $("#inp-municipio_descarregamento").val('').change();
    $("#inp-id_unidade_carga").val('');
    $(".numero_carga").val('');
    $(".numero_transporte").val('');
}

function limparLinhas() {
    var $tr = $('.table-lacres').find(".dynamic-form").first();
    var $trc = $('.table-lacres-carga').find(".dynamic-form").first();

    console.log($tr)
    console.log($trc)

    $('.table-lacres tbody').html('')
    $('.table-lacres-carga tbody').html('')

    var $clone = $tr.clone();
    var $clonec = $trc.clone();

    $clone.show();
    $clonec.show();

    console.log($clone)
    console.log($clonec)

    $clone.find("input,select").val("");
    $clonec.find("input,select").val("");

    $('.table-lacres').append($clone);
    $('.table-lacres-carga').append($clonec);

    $('.form-descarregamento').find('input, select').each(function () {
        $(this).removeClass('is-invalid')
    })
}

$("body").on("blur", ".class-required", function () {
    validateButtonSave()
})

$("body").on("blur", "input", function () {
    if ($(this).prop('required')) {
        if ($(this).val() != "") {
            $(this).removeClass('is-invalid')
        }
    }
})

$("body").on("change", ".class-required", function () {
    validateButtonSave()
})

function validateButtonSave() {
    $('.alerts').html('')

    let tp_emit = $('#inp-tp_emit').val()
    let veiculo_tracao_id = $('#inp-veiculo_tracao_id').val()
    let municipio = $('.class-municipio').val()
    let descarregamento = $(".table-descarregamento tbody tr").length
    console.log(descarregamento)

    if (!tp_emit) {
        alertCreate("Selecione um emitente!")
    }
    if (!veiculo_tracao_id) {
        alertCreate("Informe o veículo de tração!")
    }
    if (municipio == '') {
        alertCreate("Selecione um município de carregamento!")
    }
    let condutor = true
    $(".class-condutor").each(function () {
        if ($(this).val() == '') {
            condutor = false
        }
    });
    if (!condutor) {
        alertCreate("Informe um condutor!")
    }
    if (descarregamento == 0) {
        alertCreate("Informe dados do descarregamento!")
    }
    
    // let outros = true
    // $(".class-outros").each(function () {
    //     if ($(this).val() == '') {
    //         outros = false
    //     }
    // });
    // if (!chave_nfe && !outros) {
    //     alertCreate("Adicione referência de documento para CTe!")
    // }

    setTimeout(() => {
        if ($('.alerts').html() == "") {
            $('.btn-salvarMdfe').removeAttr("disabled")
        } else {
            $('.btn-salvarMdfe').attr("disabled", true);
        }

    }, 100)

}


function alertCreate(msg) {
    var div = '<div class="alert alert-danger border-0 bg-danger alert-dismissible fade show">'
    div += '<div class="text-white">' + msg + '</div>'
    div += '</div>'
    $('.alerts').append(div)
}

$(function () {
    validLineSelect()
})


function getChecked(call) {
    let id = null
    $('.checkbox').each(function (i, e) {
        if (e.checked) {
            id = e.value
        }
    })
    call(id)
}

function getCheckedElement(call) {
    $el = null
    $('.checkbox').each(function (i, e) {
        if (e.checked) {
            $el = $(this)
        }
    })
    call($el)
}

$('#btn-cancelar').click(() => {
    console.clear()
    getCheckedElement((el) => {
        console.log(el)

        let numero_mdfe = el.data('numero_mdfe')
        if (numero_mdfe > 0) {
            $('.numero_mdfe').text(numero_mdfe)
            $('#modal-cancelar').modal('show')
        }
    })
})

function validLineSelect() {

    $('.btn-action').attr('disabled', 'disabled')

    $('.checkbox').each(function (i, e) {
        if ($(this).is(':checked')) {
            $('#btn-xml-temp').attr('disabled', 1)
            $('#btn-enviar-xml').attr('disabled', 1)
            $('#btn-transmitir').attr('disabled', 1)
            $('#btn-imprimir').attr('disabled', 1)
            $('#btn-consultar').attr('disabled', 1)
            $('#btn-cancelar').attr('disabled', 1)
           
            let status = $(this).data('status')
            if (status == 'novo' || status == 'rejeitado') {
                $('#btn-enviar').removeAttr('disabled')
                $('#btn-xml-temp').removeAttr('disabled')
            } else if (status == 'aprovado') {
                $('#btn-imprimir').removeAttr('disabled')
                $('#btn-consultar').removeAttr('disabled')
                $('#btn-cancelar').removeAttr('disabled')
                $('#btn-enviar-email').removeAttr('disabled')
                $('#btn-baixar-xml').removeAttr('disabled')

            } else if (status == 'cancelado') {
                $('#btn-imprimir-cancela').removeAttr('disabled')
            }
        }
    })
}

$('#btn-baixar-xml').click(function () {
	getChecked((id) => {
		window.open(path_url + 'mdfe/baixar-xml/' + id, "_blank")
	})
})


$('#btn-consultar').click(() => {
    console.clear()
    getChecked((id) => {
        if (id) {
            let empresa_id = $("#empresa_id").val();

            $.post(path_url + "api/mdfe/consultar", {
                id: id,
                empresa_id: empresa_id,
            })
            .done((success) => {
                console.log(success)
                let infProt = success.protMDFe.infProt
                swal("Sucesso", "[" + infProt.chMDFe + "] " + infProt.xMotivo, "success")

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

$('#btn-imprimir').click(function () {
    getChecked((id) => {
        window.open(path_url + 'mdfe/imprimir/' + id, "_blank")
    })
})

$('.checkbox').click(function () {
    $value = $(this).val()
    console.log($value)
    $('.checkbox').each(function (i, e) {
        if (e.value != $value) {
            e.checked = false
        }

        validLineSelect()
    })
})

$('#btn-xml-temp').click(function () {
    getChecked((id) => {
        let href = $(this).data('href')
        window.open(path_url + 'mdfe-xml-temp/' + id, "_blank")
    })
})


$('#btn-enviar').click(() => {
    console.clear()
    getChecked((id) => {
        $("#btn-consulta-cnpj span").removeClass("d-none");
        let empresa_id = $("#empresa_id").val();

        $.post(path_url + "api/mdfe/transmitir", {
            id: id,
            empresa_id: empresa_id,
        })
        .done((success) => {
            console.log(success)
                // let infProt = err.responseJSON.protNFe.infProt
                swal("Sucesso", "MDFe emitida " + success, "success")
                .then(() => {
                    window.open(path_url + 'mdfe/imprimir/' + id, "_blank")
                    setTimeout(() => {
                        location.reload()
                    }, 100)
                })
            })
        .fail((err) => {
            console.log(err)
            if (err.status == 403) {
                swal("Algo deu errado", err.responseJSON, "error")
            } else {
                try {
                    swal("Algo deu errado", err.responseJSON, "error")
                } catch {
                    swal("Algo deu errado", err.responseText, "error")
                }
            }
        })
    })
})


$('#btn-cancelar-send').click(() => {
    getChecked((id) => {
        if (id) {
            let empresa_id = $("#empresa_id").val();
            let motivo = $('#inp-motivo-cancela').val()
            if (motivo.length >= 15) {
                $.post(path_url + "api/mdfe/cancelar", {
                    id: id,
                    empresa_id: empresa_id,
                    motivo: motivo
                })
                .done((success) => {
                    console.log(success)
                    let infEvento = success.infEvento
                    swal("Sucesso", "[" + infEvento.cStat + "] " + infEvento.xMotivo, "success")
                    .then(() => {
                        // window.open(path_url + 'mdfe/imprimir-cancela/' + id, "_blank")
                        setTimeout(() => {
                            location.reload()
                        }, 100)
                    })

                })
                .fail((err) => {
                    console.log(err)
                    try {
                        swal("Algo deu errado", err.responseJSON.infEvento.xMotivo, "error")
                    } catch {
                        swal("Algo deu errado", err.responseJSON, "error")
                    }
                })
            } else {
                swal("Alerta", "Informe no mínimo 15 caracteres", "warning")
            }
        } else {
            swal("Alerta", "Selecione uma mdfe!", "warning")
        }
    })
})

