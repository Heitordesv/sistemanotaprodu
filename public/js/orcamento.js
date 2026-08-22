$(function () {
    $('.btn-action').attr('disabled', 'disabled')
    // $('.checkbox').each(function(i, e){
    // 	e.checked = false
    // })

    validLineSelect()
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

function validLineSelect() {
    $('.btn-action').attr('disabled', 'disabled')
    $('.checkbox').each(function (i, e) {
        if ($(this).is(':checked')) {
            $('#btn-imprimir').removeAttr('disabled')
            $('#btn-simular').removeAttr('disabled')
            $('#btn-enviar_email').removeAttr('disabled')
        }
    })
}

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

$('#btn-imprimir').click(function () {
    getChecked((id) => {
        window.open(path_url + "orcamentoVenda/imprimir/" + id, "_blank");
    })
})

// function imprimir() {
//     let id = 0;
//     let cont = 0;
//     $('#body tr').each(function () {
//         if ($(this).find('#checkbox input').is(':checked')) {
//             id = $(this).find('#id').html();
//             cont++
//         }
//     })

//     if (cont > 1) {
//         Materialize.toast('Selecione apenas um documento para impressão!', 5000)
//     } else {
//         if (id > 0) {
//             window.open(path + "orcamentoVenda/imprimir/" + id, "_blank");
//         } else {
//             swal("Erro", "Escolha um orçamento na lista!!", "error")
//         }
//     }
// }


function enviarWhatsApp() {
    let celular = $('#inp-celular').val();
    let texto = $('#inp-texto').val();

    let mensagem = texto.split(" ").join("%20");

    let celularEnvia = '55' + celular.replace(' ', '');
    celularEnvia = celularEnvia.replace('-', '');
    let api = 'https://api.whatsapp.com/send?phone=' + celularEnvia
        + '&text=' + mensagem;
    window.open(api)
}