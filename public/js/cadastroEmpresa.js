$(document).ready(function () {
    $("#show_hide_password a").on('click', function (event) {
        event.preventDefault();
        if ($('#show_hide_password input').attr("type") == "usuario") {
            $('#show_hide_password input').attr('type', 'password');
            $('#show_hide_password i').addClass("bx-hide");
            $('#show_hide_password i').removeClass("bx-show");
        } else if ($('#show_hide_password input').attr("type") == "password") {
            $('#show_hide_password input').attr('type', 'usuario');
            $('#show_hide_password i').removeClass("bx-hide");
            $('#show_hide_password i').addClass("bx-show");
        }
    });
});

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

$("#btn-consulta").click(() => {
    let cnpj = $("#inp-cpf_cnpj").val();
    if (cnpj.length == 18) {
        $("#btn-consulta span").removeClass("d-none");

        consultaAlternativa(cnpj, (data) => {
            $("#btn-consulta span").addClass("d-none");

            if (data == false) {
                swal(
                    "Alerta", "Nenhum retorno encontrado para este CNPJ, informe manualmente por gentileza", "warning"
                    );
            } else {
                console.log(data);
                $("#inp-razao_social").val(data.nome);
                $("#inp-telefone").val(data.telefone);
                cidadePorNome(data.municipio, (res) => {
                    if (res) {
                        var newOption = new Option(
                            res.nome + " (" + res.uf + ")",
                            res.id,
                            false,
                            false
                            );
                        $("#inp-cidade_id")
                        .html(newOption)
                        .trigger("change");
                    }
                });
                // $("#inp-numero").val(data.numero);
                // $("#inp-bairro").val(data.bairro);
                // $("#inp-cep").val(data.cep.replace(".", ""));

            }
        });

    } else {
        swal("Alerta", "Informe o CNPJ corretamente", "warning");
    }
});


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

$(".select2").select2({
    theme: "bootstrap4",
    width: $(this).data("width") ?
    $(this).data("width") : $(this).hasClass("w-100") ?
    "100%" : "style",
    placeholder: $(this).data("placeholder"),
    allowClear: Boolean($(this).data("allow-clear")),
});

function cidadePorNome(nome, call) {
    $.get(path_url + "api/cidadePorNome/" + nome)
    .done((success) => {
        call(success);
    })
    .fail((err) => {
        call(err);
    });
}

$("#inp-cidade_id").select2({
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
