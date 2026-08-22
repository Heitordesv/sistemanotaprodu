(function ($) {
    'use strict';

    if (!$) {
        return;
    }

    function adicionarClasse(elemento, classe) {
        if (elemento && elemento.length) {
            elemento.addClass(classe);
        }
    }

    function montarEstruturaVisual() {
        var shell = $('#form-pdv > .card').first();
        if (!shell.length) {
            return;
        }

        adicionarClasse(shell, 'pdv-shell');

        var topbar = shell.children('.col-lg-12').first();
        adicionarClasse(topbar, 'pdv-topbar');

        var topbarRow = topbar.children('.row').first();
        if (topbarRow.length && !topbarRow.children('.pdv-brand').length) {
            topbarRow.prepend(
                '<div class="pdv-brand">' +
                    '<span class="pdv-brand-icon"><i class="bx bx-store-alt"></i></span>' +
                    '<span><strong>Frente de Caixa</strong><small>Venda rápida e segura</small></span>' +
                '</div>'
            );
        }

        if (topbarRow.length && !topbarRow.children('.pdv-shortcuts').length) {
            topbarRow.append(
                '<div class="pdv-shortcuts" aria-label="Atalhos do PDV">' +
                    '<kbd>F2</kbd><span>Produto</span>' +
                    '<kbd>F4</kbd><span>Cliente</span>' +
                    '<kbd>F8</kbd><span>Pagamento</span>' +
                    '<kbd>F10</kbd><span>Finalizar</span>' +
                '</div>'
            );
        }

        var workspace = shell.children('.row.dark-theme').first();
        adicionarClasse(workspace, 'pdv-workspace');

        var salePanel = workspace.children('.col-lg-8').first();
        var checkoutPanel = workspace.children('.col-lg-4').first();
        adicionarClasse(salePanel, 'pdv-sale-panel');
        adicionarClasse(checkoutPanel, 'pdv-checkout-panel');

        if (salePanel.length && !salePanel.children('.pdv-sale-card').length) {
            var saleContents = salePanel.contents();
            salePanel.append('<section class="pdv-panel-card pdv-sale-card"></section>');
            salePanel.children('.pdv-sale-card').append(saleContents);
        }

        var saleCard = salePanel.children('.pdv-sale-card');
        if (saleCard.length && !saleCard.children('.pdv-section-heading').length) {
            saleCard.prepend(
                '<header class="pdv-section-heading">' +
                    '<div><h1>Itens da venda</h1><p>Leia o código de barras ou pesquise o produto.</p></div>' +
                    '<span class="pdv-item-count">0 itens</span>' +
                '</header>'
            );
        }

        var itemsScroll = saleCard.find('.table-responsive').first();
        adicionarClasse(itemsScroll, 'pdv-items-scroll');
        if (itemsScroll.length && !itemsScroll.children('.pdv-empty-items').length) {
            itemsScroll.append(
                '<div class="pdv-empty-items">' +
                    '<i class="bx bx-cart"></i>' +
                    '<strong>Nenhum item na venda</strong><br>' +
                    '<span>Use o leitor ou pesquise um produto para começar.</span>' +
                '</div>'
            );
        }

        adicionarClasse(saleCard.find('.card').last(), 'pdv-adjustments');

        var checkoutCard = checkoutPanel.children('.card').first();
        adicionarClasse(checkoutCard, 'pdv-checkout-card');

        if (checkoutCard.length && !checkoutCard.children('.pdv-section-heading').length) {
            checkoutCard.prepend(
                '<header class="pdv-section-heading">' +
                    '<div><h2>Fechamento</h2><p>Cliente, pagamento e confirmação.</p></div>' +
                    '<i class="bx bx-receipt fs-4 text-primary"></i>' +
                '</header>'
            );
        }

        var totalCard = checkoutCard.children('.card').filter(function () {
            return $(this).find('.total-venda').length > 0;
        }).first();
        adicionarClasse(totalCard, 'pdv-total-card');

        var checkoutFields = checkoutCard.children('.row').filter(function () {
            return $(this).find('#inp-tipo_pagamento').length > 0;
        }).first();
        adicionarClasse(checkoutFields, 'pdv-checkout-fields');

        var changeCard = checkoutCard.find('#valor-troco').closest('.card');
        adicionarClasse(changeCard, 'pdv-change-card');

        var finalizeButton = checkoutCard.find('#salvar_venda');
        var finalizeColumn = finalizeButton.closest('.col-md-12');
        adicionarClasse(finalizeColumn, 'pdv-finalize-wrap');

        if (finalizeColumn.length && !finalizeColumn.children('.pdv-help-line').length) {
            finalizeColumn.append(
                '<div class="pdv-help-line"><kbd>F10</kbd> Finalizar venda após conferir os dados</div>'
            );
        }

        montarResumoCliente(checkoutCard);
        atualizarEstadoVisual();
    }

    function montarResumoCliente(checkoutCard) {
        if (!checkoutCard || !checkoutCard.length || checkoutCard.find('.pdv-client-summary').length) {
            return;
        }

        var actionButtons = checkoutCard.find('.btns-pdv').first();
        if (!actionButtons.length) {
            return;
        }

        actionButtons.after(
            '<div class="pdv-client-summary">' +
                '<i class="bx bx-user-circle"></i>' +
                '<div class="min-w-0"><span class="text-muted">Cliente da venda</span>' +
                    '<strong id="pdv-cliente-nome">Consumidor não identificado</strong>' +
                '</div>' +
            '</div>'
        );

        atualizarResumoCliente();
    }

    function atualizarResumoCliente() {
        var select = $('#inp-cliente_id');
        var texto = '';

        if (select.length) {
            texto = select.find('option:selected').text();

            if (!texto && select.data('select2')) {
                var dados = select.select2('data');
                texto = dados && dados[0] ? dados[0].text : '';
            }
        }

        texto = $.trim(texto || '');
        if (!texto || texto.toLowerCase().indexOf('selecione') !== -1) {
            texto = 'Consumidor não identificado';
        }

        $('#pdv-cliente-nome').text(texto);
    }

    function atualizarEstadoVisual() {
        var linhas = $('.table-itens tbody tr').filter(function () {
            return $(this).find('[name="produto_id[]"]').length > 0;
        }).length;

        $('.pdv-item-count').text(linhas + (linhas === 1 ? ' item' : ' itens'));
        $('.pdv-items-scroll').toggleClass('is-empty', linhas === 0);

        var total = $.trim($('.total-venda').first().text() || '0,00');
        $('.pdv-total-card').attr('aria-label', 'Total da venda: ' + total);
    }

    function focarProduto() {
        var barcode = $('#codBarras');
        if (barcode.length) {
            barcode.trigger('focus');
            return;
        }

        $('#inp-produto_id').select2('open');
    }

    function abrirModal(seletor) {
        var elemento = document.querySelector(seletor);
        if (!elemento || !window.bootstrap || !window.bootstrap.Modal) {
            return;
        }

        window.bootstrap.Modal.getOrCreateInstance(elemento).show();
    }

    function registrarAtalhos() {
        $(document).on('keydown.pdvUx', function (event) {
            var alvo = event.target;
            var editando = alvo && /INPUT|TEXTAREA|SELECT/.test(alvo.tagName);

            if (event.key === 'F2') {
                event.preventDefault();
                focarProduto();
                return;
            }

            if (event.key === 'F4') {
                event.preventDefault();
                abrirModal('#modal-selecionar_cliente');
                return;
            }

            if (event.key === 'F8') {
                event.preventDefault();
                abrirModal('#modal-pag_multi_pdv');
                return;
            }

            if (event.key === 'F10') {
                event.preventDefault();
                var botao = $('#salvar_venda');
                if (botao.length && !botao.prop('disabled')) {
                    botao.trigger('click');
                }
                return;
            }

            if (event.key === 'Escape' && !editando) {
                focarProduto();
            }
        });
    }

    $(function () {
        montarEstruturaVisual();
        registrarAtalhos();

        $(document).on('change select2:select select2:clear', '#inp-cliente_id', atualizarResumoCliente);
        $(document).on('click', '.btn-add-item, .btn-delete-row, #btn-incrementa, #btn-subtrai', function () {
            window.setTimeout(atualizarEstadoVisual, 180);
        });
        $(document).on('input change', '.subtotal-item, #valor_desconto, #valor_acrescimo', function () {
            window.setTimeout(atualizarEstadoVisual, 80);
        });

        var tabela = document.querySelector('.table-itens tbody');
        if (tabela && window.MutationObserver) {
            new MutationObserver(function () {
                atualizarEstadoVisual();
            }).observe(tabela, {childList: true, subtree: true});
        }

        window.setInterval(atualizarEstadoVisual, 800);
    });
})(window.jQuery);