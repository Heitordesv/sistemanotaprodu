
$('#btn-store-cliente_delivery').click(() => {
    let valid = validaCamposModal('#modal-clienteRapido')
    if (valid.length > 0) {
        let msg = ""
        valid.map((x) => {
            msg += x + "\n"
        })
        swal("Ops, erro no formulário", msg, "error")
    } else {
        console.log("salvando...")
        let data = {}
        $(".modal input, .modal select").each(function() {
            let indice = $(this).attr('id')
            console.log("indice", indice)
            indice = indice.substring(4, indice.length)
            data[indice] = $(this).val()
        });
        data['empresa_id'] = $('#empresa_id').val()
        console.log(data)
        $.post(path_url + 'api/clienteDelivery/store', data)
        .done((success) => {
            console.log("success", success)
            swal("Sucesso", "Cliente cadastrado!", "success")
            .then(() => {
                var newOption = new Option(success.nome, success.id, false, false);
                $('#inp-cliente').append(newOption).trigger('change');
                $('#modal-clienteRapido').modal('hide')
            })

        }).fail((err) => {
            console.log(err)
            swal("Ops", "Algo deu errado ao salvar cliente!", "error")
        })
    }
})