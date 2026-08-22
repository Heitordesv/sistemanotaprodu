$(".btn-delete").on("click", function (e) {
    e.preventDefault();
    var form = $(this).parents("form").attr("id");
    swal({
        title: "Você está certo?",
        text: "Uma vez deletado, você não poderá recuperar esse item novamente!",
        icon: "warning",
        buttons: true,
        buttons: ["Cancelar", "Excluir"],
        dangerMode: true,
    }).then((isConfirm) => {
        if (isConfirm) {
            document.getElementById(form).submit();
        } else {
            swal("", "Este item está salvo!", "info");
        }
    });
});

$(function () {
    $body = $("body");

    $(document).on({
        ajaxStart: function () {
            $body.addClass("loading");
        },
        ajaxStop: function () {
            $body.removeClass("loading");
        }
    });

    $("input[required], select[required], textarea[required]")
    .siblings("label")
    .addClass("required");
});

// $(function(){
//     $(".select2").select2({
//         language: "pt-BR",
//         width: "100%",
//         theme: "bootstrap4"
//     });
// })

$(".select2").select2({
    theme: "bootstrap4",
    width: $(this).data("width") ?
    $(this).data("width") :
    $(this).hasClass("w-100") ?
    "100%" :
    "style",
    placeholder: $(this).data("placeholder"),
    allowClear: Boolean($(this).data("allow-clear")),
});

//mascaras

var cpfMascara = function (val) {
    return val.replace(/\D/g, "").length > 11 ?
    "00.000.000/0000-00" :
    "000.000.000-009";
},
cpfOptions = {
    onKeyPress: function (val, e, field, options) {
        field.mask(cpfMascara.apply({}, arguments), options);
    },
};

$(document).on("focus", ".cpf_cnpj", function () {
    $(this).mask(cpfMascara, cpfOptions);
});

var SPMaskBehavior = function (val) {
    return val.replace(/\D/g, "").length === 11 ?
    "(00) 00000-0000" :
    "(00) 0000-00009";
},
spOptions = {
    onKeyPress: function (val, e, field, options) {
        field.mask(SPMaskBehavior.apply({}, arguments), options);
    },
};
$(".fone").mask(SPMaskBehavior, spOptions);

$('.inp-number').mask('#,##0', {
    reverse: true,
    translation: {
        '#': {
            pattern: /-|\d/,
            recursive: true
        }
    },
    onChange: function (value, e) {
        var target = e.target,
            position = target.selectionStart; // Capture initial position

            target.value = value.replace(/(?!^)-/g, '').replace(/^,/, '').replace(/^-,/, '-');

        target.selectionEnd = position; // Set the cursor back to the initial position.
    }
});

$("#btn-consulta-cnpj").click(() => {
    let cnpj = $("#inp-cpf_cnpj").val();
    cnpj = cnpj.replace(/[^0-9]/g, '')

    let empresa_id = $("#empresa_id").val();
    if (cnpj.length == 14) {

        $.get('https://publica.cnpj.ws/cnpj/' + cnpj)
        .done((data) => {
            console.log(data);

            let ie = ''
            if (data.estabelecimento.inscricoes_estaduais.length > 0) {
                ie = data.estabelecimento.inscricoes_estaduais[0].inscricao_estadual
            }
            $('#inp-ie_rg').val(ie)

            if (ie != "") {
                $('#inp-contribuinte').val(1).change()
            }

            $("#inp-razao_social").val(data.razao_social);
            $("#inp-nome_fantasia").val(data.estabelecimento.nome_fantasia);

            let cep = data.estabelecimento.cep.replace(/[^\d]+/g, '');
            cep = cep.substring(0, 5) + '-' + cep.substring(5, 9)

            $("#inp-cep").val(cep);

            $("#inp-rua").val(data.estabelecimento.tipo_logradouro + " " + data.estabelecimento.logradouro);
            $("#inp-numero").val(data.estabelecimento.numero);
            $("#inp-bairro").val(data.estabelecimento.bairro);

            findCidade(data.estabelecimento.cidade.ibge_id)

        })
        .fail((err) => {
            console.log(err)
            swal(
                "Alerta",
                err.responseJSON.titulo,
                "warning"
                );
        })

    } else {
        swal("Alerta", "Informe o CNPJ corretamente", "warning");
    }
});


function findCidade(codigo_ibge) {

    $.get(path_url + "api/cidadePorCodigoIbge/" + codigo_ibge)
    .done((res) => {

        var newOption = new Option(
            res.nome + " (" + res.uf + ")",
            res.id,
            false,
            false
            );
        $("#inp-cidade_id")
        .html(newOption)
        .trigger("change");
    })
    .fail((err) => {
        console.log(err)
    })
}

function cidadePorNome(nome, call) {
    $.get(path_url + "api/cidadePorNome/" + nome)
    .done((success) => {
        call(success);
    })
    .fail((err) => {
        call(err);
    });
}

function consultaAlternativa(cnpj, call) {
    cnpj = cnpj.replace(/[^0-9]/g, "");
    let res = null;
    $.ajax({
        url: "https://www.receitaws.com.br/v1/cnpj/" + cnpj,
        type: "GET",
        crossDomain: true,
        dataType: "jsonp",
        success: function (data) {
            if (data.status == "ERROR") {
                swal("Erro", data.message, "error");
                call(false);
            } else {
                call(data);
            }
        },
        error: function (e) {
            $("#consulta").removeClass("spinner");
            console.log(e);

            call(false);
        },
    });
}

var mask = "00";


$(function () {
    if (casas_decimais == 2) mask = "00";
    else if (casas_decimais == 3) mask = "000";
    else if (casas_decimais == 4) mask = "0000";
    else if (casas_decimais == 5) mask = "00000";
    else if (casas_decimais == 6) mask = "000000";
    else if (casas_decimais == 7) mask = "0000000";

    // $(".moeda").mask("00000000," + mask, { reverse: true });

    $(document).on("focus", ".moeda", function () {
        $(this).mask("00000000," + mask, {
            reverse: true
        })
    });
    //$(".qtd_carga").mask("00000000,00" + mask, { reverse: true });

    $(".qtd").mask("00000000,00", {
        reverse: true
    });
    $(".card_number").mask("0000000000000000", {
        reverse: true
    });
    $(".ie_rg").mask("0000000000000000000", {
        reverse: true
    });
    $(".qtd_carga").mask("00000,0000", {
        reverse: true
    });
    $(".data").mask("00/00/0000", {
        reverse: true
    });
    $(".cep").mask("00000-000", {
        reverse: true
    });
    $(".cfop").mask("0000", {
        reverse: true
    });
    $(".perc").mask("000.00", {
        reverse: true
    });
    $(".peso").mask("000000.000", {
        reverse: true
    });
    $(".ncm").mask("0000.00.00", {
        reverse: true
    });
    $(".placa").mask("AAA-AAAA", {
        reverse: true
    });
    $(".input_lacres").mask("0000000000000000000000000000000000", {
        reverse: true
    });
    $(".agencia").mask("AAAA-A", {
        reverse: true
    });
    $(".conta_corrente").mask("AAAAAA", {
        reverse: true
    });


    $(document).on("focus", ".cpf", function () {
        $(this).mask("000.000.000-00", {
            reverse: true
        });
    });

    $(document).on("focus", ".cnpj", function () {
        $(this).mask("00.000.000/0000-00", {
            reverse: true
        });
    });

    $(document).on("focus", ".chave_nfe", function () {
        $(this).mask("0000 0000 0000 0000 0000 0000 0000 0000 0000 0000 0000", {
            reverse: true
        });
    });

    $(document).on("focus", ".chave_nfe2", function () {
        $(this).mask("00000000000000000000000000000000000000000000", {
            reverse: true
        });
    });

    $(document).on("focus", "#chave_nfe", function () {
        $(this).mask("0000 0000 0000 0000 0000 0000 0000 0000 0000 0000 0000", {
            reverse: true
        });
    });

    $(".coordenadas").mask("AAAAAAAAAAAAA", {
        reverse: true
    });

});


// $('.coordenadas').mask('A9Z.9999999', {
//     translation: {
//       'Z': {
//         pattern: /[0-9]/,
//         optional: false
//       }
//     }
// });

$("#inp-fornecedor_id").select2({
    minimumInputLength: 2,
    language: "pt-BR",
    placeholder: "Digite para buscar o fornecedor",
    // width: "85%",
    theme: "bootstrap4",

    ajax: {
        cache: true,
        url: path_url + "api/fornecedor/pesquisa",
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

$("#inp-cliente_id").select2({
    minimumInputLength: 2,
    language: "pt-BR",
    placeholder: "Digite para buscar o cliente",
    theme: "bootstrap4",

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


$("#inp-cidade_id, #inp-cidade_cobranca_id").select2({
    minimumInputLength: 2,
    language: "pt-BR",
    placeholder: "Digite para buscar a cidade",
    width: "100%",
    theme: "bootstrap4",
    ajax: {
        cache: true,
        url: path_url + "api/buscaCidades",
        dataType: "json",
        data: function (params) {
            console.clear();
            var query = {
                pesquisa: params.term,
            };
            return query;
        },
        processResults: function (response) {
            console.log("response", response);
            var results = [];

            $.each(response, function (i, v) {
                var o = {};
                o.id = v.id;

                o.text = v.nome + "(" + v.uf + ")";
                o.value = v.id;
                results.push(o);
            });
            return {
                results: results,
            };
        },
    },
});

function validaCamposModal(parentModal = "") {
    // console.clear();
    var hasEmpty = false;
    msg = [];
    if (parentModal == "") {
        $(".modal input, .modal select").each(function () {
            if (
                ($(this).val() == "" ||
                    $(this).val() == null ||
                    ($(this).attr("vl") == 1 && $(this).val() == "0,00")) &&
                $(this).attr("type") != "hidden" &&
                $(this).attr("type") != "file" &&
                !$(this).hasClass("ignore")
                ) {
                hasEmpty = true;
            let lbl = $("label[for='" + $(this).attr("id") + "']").text();
            if (lbl) {
                $(this).addClass('is-invalid')
                msg.push("Campo " + lbl + " é obrigatório!");
            }
        }
    });
    } else {

        $(parentModal + " input, " + parentModal + " select").each(function () {
            if (
                ($(this).val() == "" ||
                    $(this).val() == null ||
                    ($(this).attr("vl") == 1 && $(this).val() == "0,00")) &&
                $(this).attr("type") != "hidden" &&
                $(this).attr("type") != "file" &&
                !$(this).hasClass("ignore")
                ) {
                hasEmpty = true;
            let lbl = $(parentModal + " label[for='" + $(this).attr("id") + "']").text();
            if (lbl) {
                $(this).addClass('is-invalid')
                msg.push("Campo " + lbl + " é obrigatório!");
            } else {
                $(this).removeClass('is-invalid')
            }
        }
    });
    }
    return msg;
}

$("#inp-sub_categoria_id").select2({
    minimumInputLength: 2,
    language: "pt-BR",
    placeholder: "Digite para buscar a subcategoria",
    width: "80%",
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

var input_product = document.querySelector('.produto_id');
$("#inp-produto_id").select2({
    minimumInputLength: 2,
    language: "pt-BR",
    placeholder: "Digite para buscar o produto",
    width: "80%",
    theme: "bootstrap4",

    ajax: {
        cache: true,
        url: path_url + "api/produtos/pesquisa",
        dataType: "json",
        data: function (params) {
            // console.clear();
            var query = {
                pesquisa: params.term,
                empresa_id: $("#empresa_id").val(),
                usuario_id: $("#usuario_id").val(),
                filial_id: $(".filial_id") ? $(".filial_id").val() : null
            };
            return query;
        },
        processResults: function (response) {
            console.log("response", response);
            var results = [];

            $.each(response, function (i, v) {
                var o = {};
                o.id = v.id;

                o.text = v.nome + " " + v.str_grade
                o.text += " | R$ " + parseFloat(v.valor_venda).toFixed(casas_decimais).replace(".", ",")
                if(v.estoque){
                    o.text += " | estoque: " + v.estoque.quantidade
                }
                o.value = v.id;

                results.push(o);
            });
            return {
                results: results,
            };
        },
    },
});

$(".produto_id").select2({
    minimumInputLength: 2,
    language: "pt-BR",
    placeholder: "Digite para buscar o produto",
    width: "100%",
    theme: "bootstrap4",
    ajax: {
        cache: true,
        url: path_url + "api/produtos/pesquisa",
        dataType: "json",
        data: function (params) {
            // console.clear();
            var query = {
                pesquisa: params.term,
                empresa_id: $("#empresa_id").val(),
                usuario_id: $("#usuario_id").val(),
                filial_id: $(".filial_id") ? $(".filial_id").val() : null
            };
            return query;
        },
        processResults: function (response) {
            console.log("response", response);
            var results = [];

            $.each(response, function (i, v) {
                var o = {};
                o.id = v.id;

                o.text = v.nome + " " + v.str_grade
                o.text += " | R$ " + parseFloat(v.valor_venda).toFixed(casas_decimais).replace(".", ",")
                if(v.estoque){
                    o.text += " | estoque: " + v.estoque.quantidade
                }
                o.value = v.id;

                results.push(o);
            });
            return {
                results: results,
            };
        },
    },
});

function removeOptions(selectElement) {
    var i, L = selectElement.options.length - 1;
    for (i = L; i >= 1; i--) {
        selectElement.remove(i);
    }
}

function convertMoedaToFloat(value) {
    if (!value) {
        return 0;
    }

    var number_without_mask = value.replaceAll(".", "").replaceAll(",", ".");
    return parseFloat(number_without_mask.replace(/[^0-9\.]+/g, ""));
}

function convertFloatToMoeda(value) {
    value = parseFloat(value)
    return value.toLocaleString("pt-BR", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

$('input[type=file]').change(() => {
    var filename = $('input[type=file]').val().replace(/.*(\/|\\)/, '');
    $('#filename').html(filename)
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

$('.multiple-select').select2({
    theme: 'bootstrap4'
    , width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style'
    , placeholder: $(this).data('placeholder')
    , allowClear: Boolean($(this).data('allow-clear'))
    ,
});
