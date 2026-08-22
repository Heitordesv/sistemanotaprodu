var SELECIONADOS = []
var SABORES = []
var SABORES2 = []
var VALORPRODUTO = 0
var PRODUTOID = 0
var NOMEPRODUTO = ''
$(function () {
    setTimeout(() => {
        $('#cat_todos').first().trigger('click')
    }, 100)
})

$('.sub_cat').addClass('d-none')

function selectCat(id) {
    $('#cat_todos').removeClass('btn-primary')
    $('.btn_cat').removeClass('btn-primary')
    $('.btn_cat_' + id).addClass('btn-primary')
    $('.sub_cat').addClass('d-none')
    $('.sub_cat_' + id).removeClass('d-none')
    //$('.cat_' + id).addClass('btn-primary')
    console.log(id)
}

function todos() {
    $('.sub_cat').removeClass('d-none')
    $('#cat_todos').addClass('btn-primary')
    $('.btn_cat').removeClass('btn-primary')
}

$('#inp-cliente').change(() => {
    $('#form-cliente').submit()
    let c = $('#inp-cliente').val()
    console.log(c)
})

$('#inp-endereco').change(() => {
    $('#form-endereco').submit()
})

function addSabor(id, valor, nome){
    let js = {
        id: id,
        nome: nome,
        valor: valor
    }
    let temp = SABORES2.find((x) => {
        return js.id != x.id
    })
    if(temp == null){
        SABORES2.push(js)
    }else{
        SABORES2 = SABORES2.filter((x) => {
            return x.id != js.id
        })
    }
    console.log(SABORES2)
}

function addItem(id, valor, tipo_pizza, nome) {
    NOMEPRODUTO = nome
    PRODUTOID = id

    buscarAdicionais(id)
    VALORPRODUTO = valor
    if (tipo_pizza == 1) {
        $('.unit').addClass('d-none')
        $('.tipo-pizza').removeClass('d-none')
        $('.quantidade').addClass('d-none')
        $('.tamanho-pizza').removeClass('d-none')
        $('#inp-tamanho_pizza').val('').change()
        $('.pizzas').html('')
    } else {
        $('.unit').removeClass('d-none')

        $('.tipo-pizza').addClass('d-none')
        $('.quantidade').removeClass('d-none')
        $('.tamanho-pizza').addClass('d-none')
    }
    let js = {
        id: id,
        valor: valor
    }

    $('#valor_produto').html(convertFloatToMoeda(valor))
    $('#inp-valor_com_add').val((valor))
    $('#nome_produto').html(nome)
    $('#valor_com_adicional').text(convertFloatToMoeda(valor))
    $('#inp-quantidade').val('1')
    $('#inp-prod_id').val(id)
    // console.log(id)
    let pedido = $('#ped_id').val()
    // console.log(pedido)
    $('#inp-pedido_nr').val(pedido)
    $('#modal-adicionais').modal('show')
}

function buscarAdicionais(id) {
    $.get(path_url + "api/produtosDelivery/filtroAdicionais", {
        produto_id: id
    })
    .done((e) => {
        console.log(e)
        $('.div-adicionais').html(e)
    })
    .fail((e) => {
        console.log(e);
    });
}

// Coloca a quantidade de sabores permitido para o tamanho selecionado
function tamPizza(id, maximo_sabores) {
    let t = {
        id: id,
        maximo_sabores: maximo_sabores,
    }
    $('#tamanhos_pizza').html(" " + maximo_sabores + " ")
    getSabores(id)
    getValorPizza(id)
}


// quando seleciono o tamanho das pizzas 
var MAXSABOR = 1;
var PIZZAS = []
// $('#inp-tamanho_pizza').change(() => {
//     console.clear()
//     let tamanho_id = $('#inp-tamanho_pizza').val()
//     let js = {
//         tamanho_id: $('#inp-tamanho_pizza').val()

//     }
// })

function getSabores(tamanho_id) {
    $.get(path_url + "api/produtosDelivery/sabores", {
        tamanho_id: tamanho_id,
        produto_id: PRODUTOID
    })
    .done((data) => {
        $('.pizzas').html(data)
    })
    .fail((err) => {
        console.log(err)
    })
}

function getValorPizza(tamanho_id) {
    $.get(path_url + "api/produtosDelivery/valorPizza", {
        tamanho_id: tamanho_id,
        produto_id: PRODUTOID
    })
    .done((data) => {
        console.log("getValorPizza", data)
        VALORPRODUTO = data.valor
        percorreArray()

    })
    .fail((err) => {
        console.log(err)
    })
}


// quando seleciono o tamanho da pizza na modal
function tamanho_pizza(id, nome) {
    let valorPizza = $('.valor_tamanho_'+id).val()

    let pizza = {
        id: id,
        nome: nome,
        valor: valorPizza
    }
    let temp = SABORES.find((x) => {
        return x.id == id
    })
    if (!temp) {
        SABORES.push(pizza)
    } else {
        SABORES = SABORES.filter((x) => {
            return x.id != id
        })
    }

    console.log(SABORES)
    setTimeout(() => {
        percorreArray()
    }, 200);
}


function adicionais(id, valor) {
    let adicionais = {
        id: id,
        valor: valor,
    }
    console.log(adicionais)
    let temp = SELECIONADOS.find((x) => {
        return x.id == id
    })
    if (!temp) {
        SELECIONADOS.push(adicionais)
    } else {
        SELECIONADOS = SELECIONADOS.filter((x) => {
            return x.id != id
        })
    }
    setTimeout(() => {
        percorreArray()
    }, 20);
}

function percorreArray() {
    let valor_produto = convertMoedaToFloat($('#valor_produto').text())
    // let valorp = $('#inp-tamanho_pizza').find(':selected').data('valor')

    $('.input_hidden').html('')
    $('.sabores_selecionados').html('')
    $('.sabores').removeClass('bg-light-success')
    $('.adicionais').removeClass('btn-success')
    let totalAdicionais = 0
    SELECIONADOS.map((x) => {
        console.log(x)
        totalAdicionais += parseFloat(x.valor)
        $('.adicionais_' + x.id).addClass('btn-success')
    })

    console.log(JSON.stringify(SABORES))
    $('#inp-adicionais').val(JSON.stringify(SELECIONADOS))
    $('#inp-sabores').val(JSON.stringify(SABORES))
    $('.lbl-sabores').html(NOMEPRODUTO)

    SABORES.map((x) => {
        console.log(" - " + x.nome)
        $('.sabores_pizza_' + x.id).addClass('bg-light-success')
        $('.lbl-sabores').append(" - " + x.nome)
    })

    $('#valor_com_adicional').text(convertFloatToMoeda(totalAdicionais + valor_produto))
    $('#inp-valor_com_add').val(convertFloatToMoeda(totalAdicionais + valor_produto))

    if(valor_produto == 0){
        let somaPizza = parseFloat(VALORPRODUTO);
        let valorMaior = parseFloat(VALORPRODUTO);
        let qtdSabores = SABORES.length+1
        if(SABORES.length > 0){
            SABORES.map((x) => {
                somaPizza += parseFloat(x.valor)

                if(x.valor > valorMaior){
                    valorMaior = x.valor
                }
            })
        }
        
        let tipo_divisao_pizza = $('#tipo_divisao_pizza').val()
        if(tipo_divisao_pizza == 0){
            VALORPRODUTO = somaPizza/qtdSabores
        }else{
            VALORPRODUTO = valorMaior
        }
        $('#valor_com_adicional').text(convertFloatToMoeda(VALORPRODUTO + totalAdicionais))
        $('#inp-valor_com_add').val(convertFloatToMoeda(VALORPRODUTO + totalAdicionais))
    }

    // $('#total').val(total)
    // $('.tempo_servico').text(tempo_servico + " min")
}

function calculaTotal(){
    // $('#valor_produto').html(convertFloatToMoeda(VALORPRODUTO))
}

$('#inp-forma_pagamento').change(() => {
    let forma_pagamento = $('#inp-forma_pagamento').val()
    if (forma_pagamento == '01') {
        $('.troco_para').removeClass('d-none')
    } else {
        $('.troco_para').addClass('d-none')
    }
})
