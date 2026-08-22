$('#modal-transportadora .select2').each(function() {
    setTimeout(() => {
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
                    data: function(params) {
                        console.clear()
                        var query = {
                            pesquisa: params.term,
                        };
                        return query;
                    },
                    processResults: function(response) {
                        console.log("response", response)
                        var results = [];


                        $.each(response, function(i, v) {
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
    }, 1000)
})



$('#btn-store-transportadora').click(() => {
    let valid = validaCamposModal('#modal-transportadora')
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
        $.post(path_url + 'api/transportadora/store', data)
            .done((success) => {
                console.log("success", success)
                swal("Sucesso", "Transportadora cadastrado!", "success")
                    .then(() => {
                        var newOption = new Option(success.razao_social, success.id, false, false);
                        $('#inp-transportadora_id').append(newOption).trigger('change');
                        $('#modal-transportadora').modal('hide')
                    })

            }).fail((err) => {
                console.log(err)
                swal("Ops", "Algo deu errado ao salvar transportadora!", "error")
            })
    }
})