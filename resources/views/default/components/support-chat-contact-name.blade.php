<script>
document.addEventListener('DOMContentLoaded', function () {
    const title = document.getElementById('scv2-title');
    const ticketId = document.getElementById('scv2-ticket-id');
    const chatView = document.getElementById('scv2-chat');
    if (!title || !ticketId || !chatView) return;

    let lastTicketId = 0;
    let lastAtendente = '';

    async function atualizarNome() {
        const id = parseInt(ticketId.value || '0', 10);
        if (!id || chatView.classList.contains('d-none')) return;

        try {
            const response = await fetch('{{ url('/tickets') }}/' + id + '?ajax=1', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) return;
            const data = await response.json();
            const chat = data.chat || data;
            if (!chat || !chat.ticket) return;

            const atendente = chat.ticket.atendente || 'Suporte NFe Notas';
            if (id !== lastTicketId || atendente !== lastAtendente) {
                title.textContent = atendente;
                lastTicketId = id;
                lastAtendente = atendente;
            }
        } catch (e) {
            // Mantém o título padrão caso a atualização falhe.
        }
    }

    setInterval(atualizarNome, 1500);
});
</script>