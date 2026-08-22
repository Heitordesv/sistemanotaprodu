$('.modal .select2').each(function () {
    console.log($(this))
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
            indice = indice.substring(4, indice.length)
            data[indice] = $(this).val()
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

$("#btn-consulta").click(() => {
    let cnpj = $("#inp-cpf_cnpj").val();
    cnpj = cnpj.replace(/[^0-9]/g, '')

    if (cnpj.length == 14) {

        $.get('https://publica.cnpj.ws/cnpj/' + cnpj)
            .done((data) => {
                console.log(data);
                let ie = data.estabelecimento.inscricoes_estaduais[0].inscricao_estadual
                $('#inp-ie_rg').val(ie)
                $("#inp-razao_social").val(data.razao_social);
                $("#inp-nome_fantasia").val(data.estabelecimento.nome_fantasia);

                $("#inp-rua").val(data.estabelecimento.tipo_logradouro + " " + data.estabelecimento.logradouro);
                $("#inp-numero").val(data.estabelecimento.numero);
                $("#inp-bairro").val(data.estabelecimento.bairro);
                let cep = data.estabelecimento.cep.replace(/[^\d]+/g, '');
                $('#inp-cep').val(cep.substring(0, 5) + '-' + cep.substring(5, 9))
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

function cidadePorNome(nome, call) {
    $.get(path_url + "api/cidadePorNome/" + nome)
        .done((success) => {
            call(success);
        })
        .fail((err) => {
            call(err);
        });
}

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
