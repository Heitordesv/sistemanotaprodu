var ADICIONAISESCOLHIDOS = [];
var ADICIONAIS = [];
var maiorValorPizza = 0;

$(function () {
    ADICIONAIS = $('#adicionais-inp').val()
});

$('.modal .select2').each(function () {
    console.log($(this))
    let id = $(this).prop('id')
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

$("#inp-produto").change(() => {
    let product_id = $("#inp-produto").val()
    if (product_id) {
        $.get(path_url + "api/produtos/find/" + product_id)
            .done((e) => {
                $('#inp-quantidade').val('1,00')
                $('#inp-valor').val(convertFloatToMoeda(e.valor_venda))
                console.log(e)
            })
            .fail((e) => {
                console.log(e)
            })
    }
})

$('#btn-adicional').click(() => {
    let adicional = $('#inp-adicionais').val();
    validaAdicionalNaoAdicionado(adicional, (res) => {
        if (!res) {
            ADICIONAISESCOLHIDOS.push(adicional)
            $('#adicionais_escolhidos').val(ADICIONAISESCOLHIDOS)
            montaAdicionais((html) => {
                $('#div-adicionais').html(html)
                $('#div-adicionais').css('display', 'block')
            })
            // somaValor();
        } else {
            swal("Alerta", "Esta adicional já esta escolhido!!", "warning")
        }
    })
})

function validaAdicionalNaoAdicionado(adicional, call) {
    let retorno = false;
    ADICIONAISESCOLHIDOS.map((a) => {
        if (a == adicional) {
            retorno = true;
        }
    })
    call(retorno)
}

function montaAdicionais(call) {
    let html = '';
    html += '<div class="row">'
    console.log(ADICIONAIS)
    ADICIONAISESCOLHIDOS.map((s) => {
        ADICIONAIS.map((a) => {
            if (s == a.id) {
                html += '<div class="col-sm-4 col-lg-4 col-6">';
                html += '<div class="card card-custom bg-info">';
                html += '<div class="d-flex align-items-center">'
                html += '<div class="card-title">'
                html += '<h5 class="card-label m-2">' + a.nome + ' - ' + ' R$ ' + a.valor + '</h5></div>'
                html += '<a class="btn btn-danger btn-sm ms-auto m-3" onclick="deleteAdicional(' + a.id + ')">'
                html += '<i class="bx bx-x"></i></a>'
                html += '</div></div></div>';
            }
        })
    })
    html += '</div>'
    console.log(html)
    call(html)
}

function deleteAdicional(id) {
    percorreDeleteAdicional(id, (adicionais) => {
        ADICIONAISESCOLHIDOS = adicionais;
        montaAdicionais((html) => {
            $('#div-adicionais').html(html)
            $('#div-adicionais').css('display', 'block')
        })
    })
}

function percorreDeleteAdicional(id, call) {
    let temp = []
    ADICIONAISESCOLHIDOS.map((s) => {
        if (s != id) {
            temp.push(s)
        }
    })
    call(temp)
}

function imprimirItens() {
    let ids = "";
    $('#body tr').each(function () {
        if ($(this).find('#checkbox input').is(':checked')) {
            id = $(this).find('#item_id').html();
            ids += id + ",";
        }
    })
    window.open(path_url + 'pedidos/imprimirItens?ids=' + ids);
    location.href = window.location.href;
}