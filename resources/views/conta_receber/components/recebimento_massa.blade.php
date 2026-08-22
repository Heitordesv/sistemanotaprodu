<script>
$(function () {
    const formasRecebimentoMassa = @json(collect(\App\Models\ContaReceber::tiposPagamento())->except(['06', '90']));

    $('#btn-pay-selected').on('click.recebimentoCaixa', function (event) {
        // Este handler é registrado antes do handler legado e assume totalmente
        // a confirmação para impedir baixa sem uma forma de pagamento real.
        event.preventDefault();
        event.stopImmediatePropagation();

        const count = $('.check-item:checked').length;
        if (count === 0) {
            return;
        }

        Swal.fire({
            title: 'Confirmar recebimento',
            text: `Baixar ${count} conta(s) selecionada(s)?`,
            icon: 'question',
            input: 'select',
            inputOptions: formasRecebimentoMassa,
            inputPlaceholder: 'Selecione a forma de pagamento',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            confirmButtonText: 'Receber contas',
            cancelButtonText: 'Cancelar',
            inputValidator: value => !value ? 'Informe a forma de pagamento.' : undefined
        }).then(result => {
            if (!result.isConfirmed) {
                return;
            }

            const form = document.getElementById('form-pay-mass');
            if (!form) {
                Swal.fire('Erro', 'Formulário de recebimento não encontrado.', 'error');
                return;
            }

            let tipo = form.querySelector('input[name="tipo_pagamento"]');
            if (!tipo) {
                tipo = document.createElement('input');
                tipo.type = 'hidden';
                tipo.name = 'tipo_pagamento';
                form.appendChild(tipo);
            }
            tipo.value = result.value;

            let data = form.querySelector('input[name="data_recebimento"]');
            if (!data) {
                data = document.createElement('input');
                data.type = 'hidden';
                data.name = 'data_recebimento';
                form.appendChild(data);
            }
            data.value = new Date().toISOString().slice(0, 10);

            form.submit();
        });
    });
});
</script>
