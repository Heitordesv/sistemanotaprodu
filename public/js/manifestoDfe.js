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
$(function(){
    verificaProdutoSemRegistro()
})

$(".modal .select2").each(function () {
    let id = $(this).prop("id");
    if (id == "inp-categoria_id") {
        $(this).select2({
            dropdownParent: $(this).parent(),
            theme: "bootstrap4",
        });
    }
    if (id == "inp-marca_id") {
        $(this).select2({
            dropdownParent: $(this).parent(),
            theme: "bootstrap4",
        });
    }
    if (id == "inp-sub_categoria_id") {
        $(this).select2({
            minimumInputLength: 2,
            language: "pt-BR",
            placeholder: "Digite para buscar a subcategoria",
            width: "100%",
            dropdownParent: $(this).parent(),
            theme: "bootstrap4",
            ajax: {
                cache: true,
                url: window.dfeEndpoints && window.dfeEndpoints.subcategorias,
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
    }
})


function cadProd(codigo, nome, codBarras, ncm, cfop, unidade, valor, quantidade, cfop_entrada, cest) {
    _construct(codigo, nome, codBarras, ncm, cfop, unidade, valor, quantidade, cfop_entrada, cest);
    $('#inp-nome').val(nome);
    $("#inp-nome").focus();

    // getUnidadeMedida((data) => {
    //     let achouUnidade = false;
    //     data.map((v) => {
    //         if (v == unidade) {
    //             achouUnidade = true;
    //         }
    //     })
    // })
    $('#inp-NCM').val(ncm);
    $('#inp-CEST').val(cest);
    $("#inp-NCM").trigger("click");
    let dig2Cfop = cfop.substring(1, 2);
    if (dig2Cfop == 4) {
        cfop = '5405';
    }
    if (cfop == 5405) {
        $('#inp-CST_CSOSN').val(500).change()
    }
    $('#inp-cfop').val(cfop);
    $('#inp-cfop').val(cfop);
    $('#inp-unidade_compra').val(unidade);
    $('#inp-unidade_venda option[value="' + unidade + '"]').prop("selected", true);
    $('#inp-valor_compra').val(valor);
    let percentualLucro = $('#inp-percentual_lucro').val()
    percentualLucro = percentualLucro.replace(",", ".");
    // percentualLucro = parseFloat(percentualLucro)
    let valorVenda = parseFloat(valor) + (parseFloat(valor) * (percentualLucro / 100));
    valorVenda = convertFloatToMoeda(valorVenda);
    // valorVenda = valorVenda
    // valorVenda = valorVenda.substring(3, valorVenda.length)
    $('#inp-valor_venda').val(valorVenda)
    $('#inp-estoque_inicial').val(quantidade);
    $('#conv_estoque').val('1');
    $('#inp-CFOP_entrada_estadual').val(cfop);
    $('#inp-codBarras').val(codBarras);
    $('#inp-referencia').val(codigo);
    $("#quantidade").trigger("click");
    $('#produto').modal('toggle');
}

function _construct(codigo, nome, codBarras, ncm, cfop, unidade, valor, quantidade, cfop_entrada) {
    this.codigo = codigo;
    this.nome = nome;
    this.ncm = ncm;
    this.cfop = cfop;
    this.unidade = unidade;
    this.valor = valor;
    this.quantidade = quantidade;
    this.codBarras = codBarras;
    this.cfopEntrda = cfop_entrada;
}


$('#btn-store-produto').click(() => {
    let valid = validaCamposModal("#modal-produto")
    if (valid.length > 0) {
        // ... (código de validação)
    } else {
        console.clear()
        console.log("salvando...")
        let data = {}
        
        // CORREÇÃO: Adicionando a verificação 'if (indice)'
        $(".modal input, .modal select").each(function () {
            let indice = $(this).attr('id')
            
            // Só executa a manipulação da string se o ID existir
            if (indice) { 
                // Essa linha falhava: manifestoDFe.js:151
                indice = indice.substring(4, indice.length) 
                data[indice] = $(this).val()
            }
        });

        const endpoint = window.dfeEndpoints && window.dfeEndpoints.salvarProduto;
        if (!endpoint) {
            swal("Erro", "A rota de cadastro de produto não foi configurada.", "error");
            return;
        }

        $.post(endpoint, data)
            .done((success) => {
                console.log("success", success)
                swal("Sucesso", "Produto cadastrado!", "success")
                    .then(() => {
                        $('.inp-novo-' + this.codigo).val('0')
                        $('.btn-cad-' + this.codigo).addClass('d-none')
                        $('#n_' + this.codigo).removeClass('text-danger')
                        $('.produto_id_' + this.codigo).val(success.id)
                        var newOption = new Option(success.nome, success.id, false, false);
                        $('#inp-produto_id').append(newOption).trigger('change');
                        $('#modal-produto').modal('hide')
                        verificaProdutoSemRegistro()
                    })
            }).fail((err) => {
                console.log(err)
                swal("Ops", "Algo deu errado ao salvar produto!", "error")
            })
    }
})


function verificaProdutoSemRegistro() {
    let cont = 0
    $('#btn-salvar').attr('disabled', 1)
    $('.inp-check').each(function (i, e) {
        if (e.value == 1) {
            cont++
        }
    })

    setTimeout(() => {
        console.log(cont)
        if (cont == 0) {
            $('#btn-salvar').removeAttr('disabled')
        }
    }, 50)
}
