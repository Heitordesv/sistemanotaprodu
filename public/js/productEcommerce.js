$('#btn-store-produtored').click(() => {

    let valid = validaCamposModal("#modal-produtored")
    if (valid.length > 0) {
        let msg = ""
        valid.map((x) => {
            msg += x + "\n"
        })
        swal("Ops, erro no formulário", msg, "error")
    } else {
        console.clear()
        console.log("salvando...")

        let data = {}
        $(".modal input, .modal select").each(function() {

            let indice = $(this).attr('id')
            if (indice) {
                indice = indice.substring(4, indice.length)
                data[indice] = $(this).val()
            }

        });
        data['empresa_id'] = $('#empresa_id').val()

        console.log(data)
        $.post(path_url + 'api/produtos/storeProdutoRapido', data)
            .done((success) => {
                console.log("success", success)
                swal("Sucesso", "Produto cadastrado!", "success")
                    .then(() => {
                        var newOption = new Option(success.nome, success.id, false, false);
                        $('#inp-produto_id').append(newOption).trigger('change');
                        $('#modal-produtoRapido').modal('hide')
                    })

            }).fail((err) => {
                console.log(err)
                swal("Ops", "Algo deu errado ao salvar produto!", "error")
            })
    }
});

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
    return v.toLocaleString('pt-br', {
        style: 'currency'
        , currency: 'BRL'
        , minimumFractionDigits: casas_decimais
    });
}

$('#btn-store-categoriaEcommerce').click(() => {
    let nome = $('#inp-nome_categoriaEcommerce').val()
    if (nome) {
        let js = {
            empresa_id: $('#empresa_id').val()
            , nome: nome
            , _token: '{{ csrf_token() }}'
        }
        $.post(path_url + 'api/categoriaEcommerce/storeCategoria', js)
            .done((data) => {
                console.log(data)
                $('#inp-categoriaEcommerce_id')
                var newOption = new Option(data.nome, data.id, false, false);
                $('#inp-categoriaEcommerce_id').append(newOption).trigger('change');
                $('#modal-categoriaEcommerce').modal('hide')
            }).fail((err) => {
                console.log(err)
            })
    } else {
        swal("Erro", "Informe o nome da categoria", "warning")
    }
})

$("#inp-sub_categoriaEcommerce_id").select2({
    minimumInputLength: 2
    , language: "pt-BR"
    , placeholder: "Digite para buscar a subcategoria"
    , width: "80%"
    , theme: "bootstrap4"
    , ajax: {
        cache: true
        , url: path_url + "api/categoriaEcommerce/buscarSubCategoria"
        , dataType: "json"
        , data: function(params) {
            console.clear();

            let empresa_id = $("#empresa_id").val();
            let categoria_id = $("#inp-categoriaEcommerce_id").val();

            if (categoria_id) {
                var query = {
                    pesquisa: params.term
                    , empresa_id: empresa_id
                    , categoria_id: categoria_id
                , };
                return query;
            } else {
                swal("Erro", "Selecione uma categoria!", "warning");
            }
        }
        , processResults: function(response) {
            console.log("response", response);
            var results = [];

            $.each(response, function(i, v) {
                var o = {};
                o.id = v.id;

                o.text = v.nome;
                o.value = v.id;
                results.push(o);
            });
            return {
                results: results
            , };
        }
    , }
, });
