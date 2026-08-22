$(function () {
    $('[data-bs-toggle="popover"]').popover();
});

function selectDiv(ref) {
    $('button').removeClass('link-active')
    if (ref == 'aliquotas') {
        $('.div-aliquotas').removeClass('d-none')
        $('.div-identificacao').addClass('d-none')
        $('.btn-aliquotas').addClass('link-active')

    } else {
        $('.div-aliquotas').addClass('d-none')
        $('.div-identificacao').removeClass('d-none')
        $('.btn-identificacao').addClass('link-active')

    }
}

$('#inp-derivado_petroleo').change(() => {
    isPetroleo()
})

function isPetroleo() {
    let is = $('#inp-derivado_petroleo').val()
    if (is == 1) {
        $('.d-pet').removeClass('d-none')
    } else {
        $('.d-pet').addClass('d-none')

    }
}

$('#inp-composto').change(() => {
    isComposto()
})

function isComposto() {
    let is = $('#inp-composto').val()
    if (is == 1) {
        $('.d-comp').removeClass('d-none')
    } else {
        $('.d-comp').addClass('d-none')

    }
}

$('#inp-ecommerce').change(() => {
    isEcommerce()
})

function isEcommerce() {
    let is = $('#inp-ecommerce').val()
    if (is == 1) {
        $('.d-ecommerce').removeClass('d-none')
    } else {
        $('.d-ecommerce').addClass('d-none')
    }
}

$('#inp-locacao').change(() => {
    isLocacao()
})

function isLocacao() {
    let is = $('#inp-locacao').val()
    if (is == 1) {
        $('.d-locacao').removeClass('d-none')
    } else {
        $('.d-locacao').addClass('d-none')
    }
}


$('#inp-lote-vencimento').change(() => {
    isLoteVencimento()
})

function isLoteVencimento() {
    let is = $('#inp-lote-vencimento').val()
    if (is == 1) {
        $('.d-lote').removeClass('d-none')
    } else {
        $('.d-lote').addClass('d-none')
    }
}

$('#inp-dados-veiculo').change(() => {
    isDadosVeiculo()
})

function isDadosVeiculo() {
    let is = $('#inp-dados-veiculo').val()
    if (is == 1) {
        $('.d-dados').removeClass('d-none')
    } else {
        $('.d-dados').addClass('d-none')
    }
}

// $('#btn-store').click(() => {
//     let valid = validaCamposModal()
//     if (valid.length > 0) {
//         let msg = ""
//         valid.map((x) => {
//             msg += x + "\n"
//         })
//         swal("Ops, erro no formulário", msg, "error")
//     } else {
//         console.log("salvando...")

//         let data = {}
//         $(".modal input, .modal select").each(function () {

//             let indice = $(this).attr('id')
//             indice = indice.substring(4, indice.length)
//             data[indice] = $(this).val()
//         });
//         data['empresa_id'] = $('#empresa_id').val()

//         console.log(data)
//         $.post(path_url + 'api/categoria/store', data)
//             .done((success) => {
//                 console.log("success", success)
//                 swal("Sucesso", "Categoria cadastrado!", "success")
//                     .then(() => {
//                         var newOption = new Option(success.nome, success.id, false, false);
//                         $('#inp-categoria_id').append(newOption).trigger('change');
//                         $('#modal-categoria').modal('hide')
//                     })

//             }).fail((err) => {
//                 console.log(err)
//                 swal("Ops", "Algo deu errado ao salvar categoria!", "error")
//             })
//     }
// })

$('#btn-codBarras').click(() => {
    $.get(path_url + 'api/produtos/getBarcode')
        .done((success) => {
            $('#inp-codBarras').val(success)
        }).fail((err) => {
            console.log(err)
        })
})

$('#btn-store-categoria').click(() => {
    let nome = $('#inp-nome_categoria').val()
    if (nome) {
        let js = {
            empresa_id: $('#empresa_id').val(),
            nome: nome,
            _token: '{{ csrf_token() }}'
        }
        $.post(path_url + 'api/categorias/storeCategoria', js)
            .done((data) => {
                $('#inp-categoria_id')
                var newOption = new Option(data.nome, data.id, false, false);
                $('#inp-categoria_id').append(newOption).trigger('change');
                $('#modal-categoria').modal('hide')
            }).fail((err) => {
                console.log(err)
            })
    } else {
        swal("Erro", "Informe o nome da categoria", "warning")
    }
})


$('#inp-percentual_lucro').keyup(() => {
    let valorCompra = parseFloat($('#inp-valor_compra').val().replace(',', '.'));
    let percentualLucro = parseFloat($('#inp-percentual_lucro').val().replace(',', '.'));

    if (valorCompra > 0 && percentualLucro > 0) {
        let valorVenda = valorCompra + (valorCompra * (percentualLucro / 100));
        valorVenda = formatReal(valorVenda);
        valorVenda = valorVenda.replace('.', '')
        valorVenda = valorVenda.substring(3, valorVenda.length)

        $('#inp-valor_venda').val(valorVenda)
    } else {
        $('#inp-valor_venda').val('0')
    }
})


$('#inp-valor_venda').keyup(() => {
    let valorCompra = parseFloat($('#inp-valor_compra').val().replace(',', '.'));
    let valorVenda = parseFloat($('#inp-valor_venda').val().replace(',', '.'));

    if (valorCompra > 0 && valorVenda > 0) {
        let dif = (valorVenda - valorCompra) / valorCompra * 100;
        // valorVenda = formatReal(valorVenda);
        // valorVenda = valorVenda.replace('.', '')
        // valorVenda = valorVenda.substring(3, valorVenda.length)

        $('#inp-percentual_lucro').val(dif)
    } else {
        $('#inp-percentual_lucro').val('0')
    }
})

function formatReal(v) {
    return v.toLocaleString('pt-br', { style: 'currency', currency: 'BRL', minimumFractionDigits: casas_decimais });
}


$('#btn-store-sub_categoria').click(() => {
    let nome = $('#inp-nome_sub_categoria').val()
    let categoria_id = $('#inp-categoria_id').val()

    if (!categoria_id) {
        swal("Erro", "Informe a categoria", "warning")
    } else if (!nome) {
        swal("Erro", "Informe nome", "warning")
    } else {
        $.post(path_url + 'api/categorias/storesubCategoria',
            {
                _token: $('#token').val(),
                nome: nome,
                categoria_id: categoria_id
            })
            .done((res) => {

                $('#inp-sub_categoria_id').append('<option value="' + res.id + '">' +
                    res.nome + '</option>').change();
                $('#inp-sub_categoria_id').val(res.id).change();
                swal("Sucesso", "Sub Categoria adicionada!!", 'success')
                    .then(() => {
                        $('#modal-sub_categoria').modal('hide')
                    })
            })
            .fail((err) => {
                console.log(err)
                swal("Erro", "Algo deu errado!!", 'error')

            })
    }
})

$('#btn-store-marca').click(() => {
    let nome = $('#inp-nome_marca').val()
    if (nome) {
        let js = {
            empresa_id: $('#empresa_id').val(),
            nome: nome,
            _token: '{{ csrf_token() }}'
        }
        $.post(path_url + 'api/marcas/store', js)
            .done((data) => {
                $('#inp-marca_id')
                var newOption = new Option(data.nome, data.id, false, false);
                $('#inp-marca_id').append(newOption).trigger('change');
                $('#modal-marca').modal('hide')
                swal("Sucesso", "Marca adicionada!!", 'success')
                    .then(() => {
                        $('#modal-marca').modal('hide')
                    })
            }).fail((err) => {
                console.log(err)
            })
    } else {
        swal("Erro", "Informe o nome da marca", "warning")
    }
})
