<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {

    const meses = ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'];

    /* ================= FLUXO MENSAL ================= */
    new Chart(document.getElementById('graficoFluxo'), {
        type: 'line',
        data: {
            labels: meses,
            datasets: [
                {
                    label: 'Receber',
                    data: @json($receberMensal),
                    borderColor: 'green',
                    tension: 0.4
                },
                {
                    label: 'Pagar',
                    data: @json($pagarMensal),
                    borderColor: 'red',
                    tension: 0.4
                }
            ]
        }
    });

    /* ================= CATEGORIA ================= */
    new Chart(document.getElementById('graficoCategoria'), {
        type: 'doughnut',
        data: {
            labels: @json(array_column($graficoCategoria->toArray(), 'label')),
            datasets: [{
                data: @json(array_column($graficoCategoria->toArray(), 'total'))
            }]
        }
    });

    /* ================= GRUPO ================= */
    new Chart(document.getElementById('graficoGrupo'), {
        type: 'bar',
        data: {
            labels: @json(array_column($graficoGrupo->toArray(), 'label')),
            datasets: [{
                data: @json(array_column($graficoGrupo->toArray(), 'total')),
                backgroundColor: '#11426f'
            }]
        }
    });

    /* ================= ACUMULADO ================= */
    new Chart(document.getElementById('fluxoAcumulado'), {
        type: 'line',
        data: {
            labels: meses,
            datasets: [{
                label: 'Fluxo Acumulado',
                data: @json($fluxoAcumulado),
                borderColor: '#28a745',
                fill: true,
                tension: 0.4
            }]
        }
    });

});
</script>