<div class="card mt-4 p-3 shadow-sm">
    <h5>Fluxo Mensal</h5>
    <canvas id="graficoFluxo"></canvas>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
new Chart(document.getElementById('graficoFluxo'), {
    type: 'line',
    data: {
        labels: @json($meses ?? []),
        datasets: [
            {
                label: 'Receber',
                data: @json($graficoReceber ?? []),
                borderColor: 'green',
                tension: 0.4
            },
            {
                label: 'Pagar',
                data: @json($graficoPagar ?? []),
                borderColor: 'red',
                tension: 0.4
            }
        ]
    }
});
</script>