(function ($) {
    'use strict';

    var TIPO_CREDIARIO = '06';

    function moedaParaFloat(valor) {
        valor = String(valor || '').replace('R$', '').trim();

        if (!valor) {
            return 0;
        }

        if (valor.indexOf(',') !== -1) {
            valor = valor.replace(/\./g, '').replace(',', '.');
        }

        valor = valor.replace(/[^0-9.-]/g, '');
        return parseFloat(valor) || 0;
    }

    function moeda(valor) {
        return Number(valor || 0).toLocaleString('pt-BR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function avisar(titulo, texto, tipo) {
        if (typeof window.swal === 'function') {
            window.swal(titulo, texto, tipo || 'warning');
            return;
        }

        window.alert(texto);
    }

    function totalLiquidoVenda() {
        var bruto = Number(window.total_venda || 0);
        var acrescimo = Number(window.VALORACRESCIMO || 0);
        var desconto = Number(window.DESCONTO || 0);

        return Math.max(0, bruto + acrescimo - desconto);
    }

    function totalPagamentosAdicionados() {
        var total = 0;

        $('.table-payment [name="valor_integral_row[]"]').each(function () {
            total += moedaParaFloat($(this).val());
        });

        return total;
    }

    function adicionarMesesSemEstourar(dataIso, meses) {
        var partes = String(dataIso || '').split('-');

        if (partes.length !== 3) {
            return dataIso;
        }

        var ano = parseInt(partes[0], 10);
        var mes = parseInt(partes[1], 10) - 1;
        var dia = parseInt(partes[2], 10);
        var primeiroDiaDestino = new Date(ano, mes + meses, 1);
        var ultimoDiaDestino = new Date(
            primeiroDiaDestino.getFullYear(),
            primeiroDiaDestino.getMonth() + 1,
            0
        ).getDate();

        var dataDestino = new Date(
            primeiroDiaDestino.getFullYear(),
            primeiroDiaDestino.getMonth(),
            Math.min(dia, ultimoDiaDestino)
        );

        var anoDestino = dataDestino.getFullYear();
        var mesDestino = String(dataDestino.getMonth() + 1).padStart(2, '0');
        var diaDestino = String(dataDestino.getDate()).padStart(2, '0');

        return anoDestino + '-' + mesDestino + '-' + diaDestino;
    }

    function parcelasEmCentavos(valorTotal, quantidade) {
        var totalCentavos = Math.round(valorTotal * 100);
        var base = Math.floor(totalCentavos / quantidade);
        var resto = totalCentavos - (base * quantidade);
        var parcelas = [];

        for (var indice = 0; indice < quantidade; indice++) {
            parcelas.push(base + (indice === quantidade - 1 ? resto : 0));
        }

        return parcelas;
    }

    function criarCampo(nome, valor, tipo, classes) {
        return $('<input>', {
            type: tipo || 'text',
            name: nome,
            value: valor,
            readonly: true,
            class: 'form-control ' + (classes || '')
        });
    }

    function criarLinhaParcela(configuracao) {
        var linha = $('<tr>');

        linha.append(
            $('<td>').append(
                criarCampo('nome_pagamento[]', configuracao.nomePagamento),
                criarCampo('tipo_pagamento_row[]', configuracao.tipoPagamento, 'hidden')
            )
        );

        linha.append(
            $('<td>').append(
                criarCampo('data_vencimento_row[]', configuracao.vencimento, 'date')
            )
        );

        linha.append(
            $('<td>').append(
                criarCampo('valor_integral_row[]', moeda(configuracao.valor), 'text', 'valor_integral')
            )
        );

        linha.append(
            $('<td>').append(
                criarCampo('obs_row[]', configuracao.observacao)
            )
        );

        linha.append(
            $('<td>').append(
                $('<button>', {
                    type: 'button',
                    class: 'btn btn-sm btn-danger btn-delete-row',
                    title: 'Remover parcela'
                }).append($('<i>', {class: 'bx bx-trash'}))
            )
        );

        return linha;
    }

    function tipoSelecionado() {
        return String($('#inp-tipo_pagamento_row').val() || '');
    }

    function quantidadeParcelas() {
        var quantidade = parseInt($('#quantidade_parcelas_row').val(), 10) || 1;
        return Math.min(60, Math.max(1, quantidade));
    }

    function atualizarConfiguracaoCrediario() {
        var crediario = tipoSelecionado() === TIPO_CREDIARIO;
        var painel = $('#configuracao-parcelas-crediario');
        var ajuda = $('#ajuda-valor-crediario');

        painel.toggleClass('d-none', !crediario);
        ajuda.toggleClass('d-none', !crediario);

        if (!crediario) {
            $('#quantidade_parcelas_row').val(1);
            $('#resumo-parcelamento-crediario').text(
                'Informe o valor e a quantidade para calcular as parcelas.'
            );
            return;
        }

        atualizarResumoParcelamento();
    }

    function atualizarResumoParcelamento() {
        if (tipoSelecionado() !== TIPO_CREDIARIO) {
            return;
        }

        var valor = moedaParaFloat($('#inp-valor_integral_row').val());
        var quantidade = quantidadeParcelas();
        var primeiroVencimento = $('#inp-data_vencimento_row').val();
        var intervalo = parseInt($('#intervalo_parcelas_row').val(), 10) || 1;
        var resumo = $('#resumo-parcelamento-crediario');

        if (valor <= 0 || !primeiroVencimento) {
            resumo
                .removeClass('alert-success')
                .addClass('alert-info')
                .text('Informe o valor e o primeiro vencimento para calcular as parcelas.');
            return;
        }

        var valores = parcelasEmCentavos(valor, quantidade);
        var primeira = valores[0] / 100;
        var ultima = valores[valores.length - 1] / 100;
        var ultimoVencimento = adicionarMesesSemEstourar(
            primeiroVencimento,
            (quantidade - 1) * intervalo
        );
        var textoValor = quantidade === 1
            ? '1 parcela de R$ ' + moeda(primeira)
            : quantidade + ' parcelas: ' + (quantidade - 1) + 'x de R$ ' + moeda(primeira)
                + ' e a última de R$ ' + moeda(ultima);

        resumo
            .removeClass('alert-info')
            .addClass('alert-success')
            .html(
                '<strong>' + textoValor + '</strong><br>' +
                'Primeiro vencimento: ' + primeiroVencimento.split('-').reverse().join('/') +
                ' | Último: ' + ultimoVencimento.split('-').reverse().join('/')
            );
    }

    function adicionarParcelasCrediario(evento) {
        if (tipoSelecionado() !== TIPO_CREDIARIO) {
            return;
        }

        evento.preventDefault();
        evento.stopPropagation();
        evento.stopImmediatePropagation();

        var clienteId = parseInt($('#inp-cliente_id').val(), 10) || 0;
        var valorTotal = moedaParaFloat($('#inp-valor_integral_row').val());
        var primeiroVencimento = $('#inp-data_vencimento_row').val();
        var quantidade = quantidadeParcelas();
        var intervalo = parseInt($('#intervalo_parcelas_row').val(), 10) || 1;
        var observacaoBase = String($('#inp-obs_row').val() || '').trim();
        var nomePagamento = $('#inp-tipo_pagamento_row option:selected').text().trim() || 'Crediário';

        if (!clienteId) {
            avisar('Cliente obrigatório', 'Selecione um cliente antes de adicionar parcelas no crediário.', 'warning');
            return;
        }

        if (valorTotal <= 0 || !primeiroVencimento) {
            avisar('Dados incompletos', 'Informe o valor total e o primeiro vencimento.', 'warning');
            return;
        }

        var totalDepois = totalPagamentosAdicionados() + valorTotal;
        var totalVenda = totalLiquidoVenda();

        if (totalDepois > totalVenda + 0.009) {
            avisar(
                'Valor acima da venda',
                'Os pagamentos ultrapassam o total líquido da venda em R$ ' + moeda(totalDepois - totalVenda) + '.',
                'warning'
            );
            return;
        }

        var valores = parcelasEmCentavos(valorTotal, quantidade);
        var fragmento = $(document.createDocumentFragment());

        valores.forEach(function (valorCentavos, indice) {
            var numero = indice + 1;
            var vencimento = adicionarMesesSemEstourar(primeiroVencimento, indice * intervalo);
            var identificacao = 'Parcela ' + numero + '/' + quantidade;
            var observacao = observacaoBase
                ? observacaoBase + ' - ' + identificacao
                : identificacao;

            fragmento.append(criarLinhaParcela({
                nomePagamento: nomePagamento,
                tipoPagamento: TIPO_CREDIARIO,
                vencimento: vencimento,
                valor: valorCentavos / 100,
                observacao: observacao
            }));
        });

        $('.table-payment tbody').append(fragmento);

        if (typeof window.calcTotalPayment === 'function') {
            window.calcTotalPayment();
        }

        $('.table-payment [name="tipo_pagamento_row[]"], .table-payment [name="valor_integral_row[]"]')
            .trigger('change');

        $('#inp-valor_integral_row').val('');
        $('#inp-obs_row').val('');
        $('#quantidade_parcelas_row').val(1);
        atualizarResumoParcelamento();

        avisar(
            'Parcelas calculadas',
            quantidade + (quantidade === 1 ? ' parcela adicionada.' : ' parcelas adicionadas ao pagamento múltiplo.'),
            'success'
        );
    }

    $(document).on('change', '#inp-tipo_pagamento_row', atualizarConfiguracaoCrediario);
    $(document).on(
        'input change',
        '#inp-valor_integral_row, #inp-data_vencimento_row, #quantidade_parcelas_row, #intervalo_parcelas_row',
        atualizarResumoParcelamento
    );

    document.addEventListener('click', function (evento) {
        var botao = evento.target.closest('.btn-add-payment');

        if (!botao || tipoSelecionado() !== TIPO_CREDIARIO) {
            return;
        }

        adicionarParcelasCrediario(evento);
    }, true);

    $(function () {
        atualizarConfiguracaoCrediario();
    });
})(window.jQuery);