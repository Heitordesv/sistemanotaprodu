var codigo = "";
var nome = "";
var ncm = "";
var cfop = "";
var unidade = "";
var valor = "";
var quantidade = "";
var codBarras = "";
var cfopEntrda = "";
var TOTAL = 0;
var fatura = [];
var semRegistro;
var PRODUTO = null;

$(function () {
    let faturaInput = $('#fatura').val();
    if (faturaInput) {
        fatura = JSON.parse(faturaInput);
    }

    TOTAL = parseFloat($('#total').val() || 0);
    verificaProdutoSemRegistro();
});

/* ================= SELECT2 ================= */

setTimeout(() => {
    $(".modal .select2").each(function () {
        let id = $(this).prop("id");

        if (id === "inp-categoria_id") {
            $(this).select2({
                dropdownParent: $('#modal-produto'),
                theme: "bootstrap4",
                width: "100%"
            });
        }

        if (id === "inp-marca_id") {
            $(this).select2({
                dropdownParent: $(this).parent(),
                theme: "bootstrap4"
            });
        }

        if (id === "inp-sub_categoria_id") {
            $(this).select2({
                minimumInputLength: 2,
                language: "pt-BR",
                placeholder: "Digite para buscar a subcategoria",
                width: "100%",
                dropdownParent: $(this).parent(),
                theme: "bootstrap4",
                ajax: {
                    cache: true,
                    url: path_url + "api/categorias/buscarSubCategoria",
                    dataType: "json",
                    data: function (params) {
                        let categoria_id = $("#inp-categoria_id").val();
                        let empresa_id = $("#empresa_id").val();

                        if (!categoria_id) {
                            swal("Erro", "Selecione uma categoria!", "warning");
                            return false;
                        }

                        return {
                            pesquisa: params.term,
                            empresa_id: empresa_id,
                            categoria_id: categoria_id
                        };
                    },
                    processResults: function (response) {
                        let results = [];
                        $.each(response, function (i, v) {
                            results.push({
                                id: v.id,
                                text: v.nome
                            });
                        });
                        return { results: results };
                    }
                }
            });
        }
    });
}, 1000);

/* ================= UI ================= */

function selectDiv(ref) {
    $('button').removeClass('link-active');

    if (ref === 'aliquotas') {
        $('.div-aliquotas').removeClass('d-none');
        $('.div-identificacao').addClass('d-none');
        $('.btn-aliquotas').addClass('link-active');
    } else {
        $('.div-aliquotas').addClass('d-none');
        $('.div-identificacao').removeClass('d-none');
        $('.btn-identificacao').addClass('link-active');
    }
}

/* ================= PRODUTO ================= */

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

function cadProd(codigo, nome, codBarras, ncm, cfop, unidade, valor, quantidade, cfop_entrada, cest) {

    _construct(codigo, nome, codBarras, ncm, cfop, unidade, valor, quantidade, cfop_entrada);

    $('#inp-nome').val(nome).focus();
    $('#inp-CEST').val(cest || '');
    $('#inp-NCM').val(ncm);

    /* CFOP seguro */
    if (cfop && typeof cfop === 'string') {
        let dig2Cfop = cfop.substring(1, 2);
        if (dig2Cfop == 4) {
            cfop = '5405';
        }
    }

    if (cfop == '5405') {
        $('#inp-CST_CSOSN').val(500).change();
    }

    $('#inp-cfop').val(cfop);
    $('#inp-unidade_compra').val(unidade);
    $('#inp-unidade_venda').val(unidade);
    $('#inp-valor_compra').val(valor);

    let percentualLucro = ($('#inp-percentual_lucro').val() || '0').replace(",", ".");
    let valorVenda = parseFloat(valor || 0) + (parseFloat(valor || 0) * (percentualLucro / 100));
    $('#inp-valor_venda').val(convertFloatToMoeda(valorVenda));

    $('#inp-estoque_inicial').val(quantidade);
    $('#conv_estoque').val('1');
    $('#inp-CFOP_entrada_estadual').val(cfop_entrada);
    $('#inp-codBarras').val(codBarras);

    $('#modal-produto').modal('show');
}

/* ================= SALVAR PRODUTO ================= */

$('#btn-store-produto').click(() => {

    let valid = validaCamposModal("#modal-produto");

    if (valid.length > 0) {
        let msg = valid.join("\n");
        swal("Ops, erro no formulário", msg, "error");
        return;
    }

    let data = {};

    $(".modal input, .modal select").each(function () {
        let id = $(this).attr('id');

        if (!id) return; // 🔒 evita substring em undefined
        if (!id.startsWith('inp-')) return;

        let indice = id.substring(4);
        data[indice] = $(this).val();
    });

    data['empresa_id'] = $('#empresa_id').val();

    $.post(path_url + 'api/produtos/store', data)
        .done((success) => {

            swal("Sucesso", "Produto cadastrado!", "success")
                .then(() => {
                    $('.inp-novo-' + this.codigo).val('0');
                    $('.btn-cad-' + this.codigo).addClass('d-none');
                    $('#n_' + this.codigo).removeClass('text-danger');
                    $('.produto_id_' + this.codigo).val(success.id);
                    $('#modal-produto').modal('hide');
                    verificaProdutoSemRegistro();
                });

        }).fail(() => {
            swal("Ops", "Algo deu errado ao salvar produto!", "error");
        });
});

/* ================= VERIFICA PRODUTO SEM REGISTRO ================= */

function verificaProdutoSemRegistro() {
    let cont = 0;
    $('#btn-salvar').attr('disabled', true);

    $('.inp-check').each(function () {
        if (this.value == 1) {
            cont++;
        }
    });

    setTimeout(() => {
        if (cont === 0) {
            $('#btn-salvar').removeAttr('disabled');
        }
    }, 50);
}
