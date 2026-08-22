<script>
document.addEventListener('DOMContentLoaded', function () {
    const listUrl = @json(route('tickets.index'));

    async function atualizarParticipantes() {
        const container = document.getElementById('scv2-conversations');
        if (!container) return;

        try {
            const response = await fetch(listUrl, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) return;
            const data = await response.json();
            if (!data || !data.success || !Array.isArray(data.conversations)) return;

            data.conversations.forEach(function (conversation) {
                const button = Array.from(container.querySelectorAll('.scv2-conv')).find(function (item) {
                    return (item.dataset.url || '') === (conversation.show_url || '');
                });

                if (!button) return;

                const title = button.querySelector('.scv2-conv-top strong');
                const meta = button.querySelector('.scv2-conv-meta');

                if (title) {
                    title.textContent = conversation.participantes || ('Usuário ↔ ' + (conversation.atendente || 'Aguardando atendente'));
                }

                if (meta) {
                    meta.textContent = '#' + conversation.id + ' · ' + conversation.assunto + ' · ' + conversation.estado_label;
                }
            });
        } catch (e) {
            // Mantém o conteúdo original caso a atualização falhe.
        }
    }

    atualizarParticipantes();
    setInterval(atualizarParticipantes, 2000);
});
</script>