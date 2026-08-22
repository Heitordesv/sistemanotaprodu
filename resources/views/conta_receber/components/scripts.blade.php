<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(function () {
    const intervalos = {};
    const csrfToken = '{{ csrf_token() }}';
    const mpRoutes = {
        pix: @json(route('conta-receber.mp.pix', ['id' => '__ID__'])),
        boleto: @json(route('conta-receber.mp.boleto', ['id' => '__ID__'])),
        cartao: @json(route('conta-receber.mp.cartao', ['id' => '__ID__'])),
        checkout: @json(route('conta-receber.mp.checkout', ['id' => '__ID__'])),
        status: @json(route('conta-receber.mp.status', ['id' => '__ID__'])),
    };

    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' } });

    function mpUrl(tipo, id) {
        return mpRoutes[tipo].replace('__ID__', id);
    }

    function erroAjax(xhr, fallback) {
        return xhr?.responseJSON?.erro
            || xhr?.responseJSON?.message
            || xhr?.responseJSON?.errors?.cep?.[0]
            || fallback;
    }

    function statusLabel(status) {
        const labels = {
            approved: 'Pagamento aprovado',
            pending: 'Aguardando pagamento',
            in_process: 'Pagamento em processamento',
            action_required: 'Aguardando ação do cliente',
            checkout_created: 'Link de pagamento criado',
            rejected: 'Pagamento recusado',
            cancelled: 'Pagamento cancelado',
            refunded: 'Pagamento estornado',
            charged_back: 'Pagamento contestado',
        };
        return labels[status] || status || 'Sem pagamento ativo';
    }

    $(document).on('click', '.show-referencia', function () {
        Swal.fire({
            title: 'Detalhes da Conta',
            text: $(this).data('referencia') || 'Sem descrição',
            icon: 'info',
            confirmButtonColor: '#3085d6'
        });
    });

    // PIX Mercado Pago
    $(document).on('click', '.gerarPixBtn', function () {
        const id = $(this).data('id');
        const btn = $(this);
        const original = btn.html();

        btn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-2"></i>Gerando PIX...');

        $.post(mpUrl('pix', id))
            .done(function (res) {
                if (res.pago) {
                    Swal.fire('Pagamento identificado', 'Esta conta já foi paga no Mercado Pago.', 'success')
                        .then(() => location.reload());
                    return;
                }

                if (!res.qr_code_base64 || !res.pix_copia_cola) {
                    Swal.fire('Mercado Pago', 'O PIX foi criado, mas o QR Code ainda não foi disponibilizado. Consulte o status novamente.', 'info');
                    return;
                }

                $('#pixQrCodeImg').attr('src', 'data:image/png;base64,' + res.qr_code_base64);
                $('#pixCodigo').val(res.pix_copia_cola);
                $('#pixStatus').text(statusLabel(res.status)).removeClass('text-success text-danger').addClass('text-warning');
                $('#pixPaymentId').text(res.payment_id || '');

                bootstrap.Modal.getOrCreateInstance(document.getElementById('pixModal')).show();
                iniciarConsultaPix(id);
            })
            .fail(xhr => Swal.fire('Erro ao gerar PIX', erroAjax(xhr, 'Não foi possível gerar o PIX.'), 'error'))
            .always(() => btn.prop('disabled', false).html(original));
    });

    function iniciarConsultaPix(id) {
        if (intervalos[id]) clearInterval(intervalos[id]);

        intervalos[id] = setInterval(function () {
            $.get(mpUrl('status', id))
                .done(function (res) {
                    $('#pixStatus').text(statusLabel(res.status));

                    if (res.pago || res.status === 'approved') {
                        clearInterval(intervalos[id]);
                        $('#pixStatus').removeClass('text-warning text-danger').addClass('text-success').text('Pagamento aprovado');
                        Swal.fire('Sucesso', 'Pagamento PIX identificado e conta baixada.', 'success')
                            .then(() => location.reload());
                    }

                    if (['rejected', 'cancelled', 'refunded', 'charged_back'].includes(res.status)) {
                        clearInterval(intervalos[id]);
                        $('#pixStatus').removeClass('text-warning text-success').addClass('text-danger');
                    }
                });
        }, 5000);
    }

    // Boleto Mercado Pago
    $(document).on('click', '.gerarBoletoBtn', function () {
        const id = $(this).data('id');
        const btn = $(this);
        const original = btn.html();
        btn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-2"></i>Gerando boleto...');

        $.post(mpUrl('boleto', id))
            .done(function (res) {
                if (res.pago) {
                    Swal.fire('Pagamento identificado', 'Esta conta já foi paga.', 'success').then(() => location.reload());
                    return;
                }

                if (res.boleto_link) {
                    window.open(res.boleto_link, '_blank', 'noopener');
                }

                Swal.fire({
                    icon: 'success',
                    title: 'Boleto gerado',
                    html: res.linha_digitavel
                        ? '<div class="text-start"><small class="text-muted">Linha digitável</small><div class="mt-1 p-2 bg-light rounded text-break">' + res.linha_digitavel + '</div></div>'
                        : 'O boleto foi criado no Mercado Pago.',
                    confirmButtonText: 'OK'
                }).then(() => location.reload());
            })
            .fail(xhr => Swal.fire('Erro ao gerar boleto', erroAjax(xhr, 'Não foi possível gerar o boleto.'), 'error'))
            .always(() => btn.prop('disabled', false).html(original));
    });

    // Cartão via Checkout Pro: nenhum dado do cartão passa pelo ERP.
    $(document).on('click', '.gerarCartaoMpBtn', function () {
        gerarLinkMercadoPago($(this), 'cartao', 'Link para cartão');
    });

    // Checkout completo: Mercado Pago pode oferecer os meios disponíveis na conta.
    $(document).on('click', '.gerarCheckoutMpBtn', function () {
        gerarLinkMercadoPago($(this), 'checkout', 'Link Mercado Pago');
    });

    function gerarLinkMercadoPago(btn, tipo, titulo) {
        const id = btn.data('id');
        const original = btn.html();
        btn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-2"></i>Criando link...');

        $.post(mpUrl(tipo, id))
            .done(function (res) {
                if (res.pago) {
                    Swal.fire('Pagamento identificado', 'Esta conta já foi paga.', 'success').then(() => location.reload());
                    return;
                }

                if (!res.checkout_url) {
                    Swal.fire('Mercado Pago', 'O link não foi retornado pelo Mercado Pago.', 'error');
                    return;
                }

                Swal.fire({
                    icon: 'success',
                    title: titulo + ' criado',
                    html: '<p class="mb-2">Abra o checkout ou copie o link para enviar ao cliente.</p>' +
                          '<input id="mpCheckoutLinkSwal" class="swal2-input" value="' + res.checkout_url.replace(/"/g, '&quot;') + '" readonly>',
                    showCancelButton: true,
                    confirmButtonText: 'Abrir Mercado Pago',
                    cancelButtonText: 'Fechar',
                    footer: '<button type="button" class="btn btn-sm btn-outline-primary" onclick="navigator.clipboard.writeText(document.getElementById(\'mpCheckoutLinkSwal\').value)"><i class="bx bx-copy"></i> Copiar link</button>'
                }).then(result => {
                    if (result.isConfirmed) window.open(res.checkout_url, '_blank', 'noopener');
                    location.reload();
                });
            })
            .fail(xhr => Swal.fire('Erro no Mercado Pago', erroAjax(xhr, 'Não foi possível criar o link.'), 'error'))
            .always(() => btn.prop('disabled', false).html(original));
    }

    // Consulta manual de status
    $(document).on('click', '.consultarMpBtn', function () {
        const id = $(this).data('id');
        const btn = $(this);
        const original = btn.html();
        btn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-2"></i>Consultando...');

        $.get(mpUrl('status', id))
            .done(function (res) {
                Swal.fire({
                    icon: res.pago ? 'success' : 'info',
                    title: statusLabel(res.status),
                    html: '<div class="text-start small">' +
                        (res.payment_id ? '<div><b>Payment ID:</b> ' + res.payment_id + '</div>' : '') +
                        (res.payment_method ? '<div><b>Meio:</b> ' + res.payment_method + '</div>' : '') +
                        (res.status_detail ? '<div><b>Detalhe:</b> ' + res.status_detail + '</div>' : '') +
                        '</div>'
                }).then(() => { if (res.pago) location.reload(); });
            })
            .fail(xhr => Swal.fire('Erro', erroAjax(xhr, 'Não foi possível consultar o pagamento.'), 'error'))
            .always(() => btn.prop('disabled', false).html(original));
    });

    // Cora continua isolado do Mercado Pago.
    $(document).on('click', '.gerarBoletoCoraBtn', function () {
        const id = $(this).data('id');
        const btn = $(this);
        const original = btn.html();
        btn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-2"></i>Aguarde...');

        $.post('/conta_receber/gerar-boleto-cora/' + id)
            .done(function (response) {
                if (response.boleto_link) {
                    window.open(response.boleto_link, '_blank', 'noopener');
                    Swal.fire('Boleto Cora gerado', '', 'success');
                } else {
                    Swal.fire('Erro', 'Link do boleto Cora não retornado.', 'error');
                }
            })
            .fail(xhr => Swal.fire('Erro', erroAjax(xhr, 'Erro ao gerar boleto Cora.'), 'error'))
            .always(() => btn.prop('disabled', false).html(original));
    });

    $(document).on('click', '.btn-delete', function (e) {
        e.preventDefault();
        const formId = $(this).data('form-id');
        Swal.fire({
            title: 'Excluir registro?',
            text: 'Esta ação não poderá ser revertida!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Sim, excluir!',
            cancelButtonText: 'Cancelar'
        }).then(r => { if (r.isConfirmed) $('#' + formId).submit(); });
    });

    function updateMassSelection() {
        const selected = $('.check-item:checked');
        const count = selected.length;
        const ids = selected.map(function () { return this.value; }).get().join(',');
        $('#selected-count, #selected-count-pay').text(count);
        $('#input-delete-ids, #input-pay-ids').val(ids);
        $('#btn-delete-selected, #btn-pay-selected').toggleClass('d-none', count === 0);
    }

    $(document).on('change', '#check-all', function () {
        $('.check-item').prop('checked', this.checked);
        updateMassSelection();
    });
    $(document).on('change', '.check-item', updateMassSelection);

    $('#btn-delete-selected').on('click', function () {
        Swal.fire({
            title: 'Excluir selecionados?',
            text: 'Você removerá todos os itens marcados!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Sim, excluir tudo!'
        }).then(r => { if (r.isConfirmed) $('#form-delete-mass').submit(); });
    });

    $('#btn-pay-selected').on('click', function () {
        const count = $('.check-item:checked').length;
        Swal.fire({
            title: 'Confirmar Recebimento?',
            text: `Baixar ${count} conta(s) selecionada(s)?`,
            icon: 'success',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            confirmButtonText: 'Sim, receber!'
        }).then(r => { if (r.isConfirmed) $('#form-pay-mass').submit(); });
    });

    $(document).on('click', '.btn-enviar-cobranca', function (e) {
        e.preventDefault();
        const id = $(this).data('id');
        $.post('/conta_receber/enviar-cobranca/' + id)
            .done(() => Swal.fire('Enviado!', 'Cobrança enviada com sucesso.', 'success'))
            .fail(xhr => Swal.fire('Erro', erroAjax(xhr, 'Falha ao enviar cobrança.'), 'error'));
    });

    window.copiarChavePix = function () {
        const chavePix = $('#pixCodigo').val();
        if (!chavePix) return;
        navigator.clipboard.writeText(chavePix).then(() => {
            Swal.fire({ icon: 'success', title: 'PIX copiado!', showConfirmButton: false, timer: 1000 });
        });
    };
});

function atualizarLimiteRapido(clienteId, valor, acao) {
    const valorFormatado = valor.toLocaleString('pt-br', { style: 'currency', currency: 'BRL' });
    Swal.fire({
        title: 'Confirmar Alteração?',
        text: `Deseja realmente ${acao.toLowerCase()} o limite para ${valorFormatado}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sim, aplicar!'
    }).then((result) => {
        if (!result.isConfirmed) return;

        const btnAlvo = window.event.target.closest('button');
        const originalHtml = btnAlvo.innerHTML;
        btnAlvo.disabled = true;
        btnAlvo.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i>';

        fetch("{{ route('clientes.atualizarLimite') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ cliente_id: clienteId, novo_limite: valor })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                Swal.fire('Sucesso!', data.message, 'success').then(() => window.location.reload());
            } else {
                throw new Error(data.message);
            }
        })
        .catch(err => {
            Swal.fire('Erro!', err.message, 'error');
            btnAlvo.disabled = false;
            btnAlvo.innerHTML = originalHtml;
        });
    });
}
</script>
@endsection