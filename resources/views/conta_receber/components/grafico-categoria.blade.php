<div class="card p-3 shadow-sm">
    <h6>Receitas por Categoria</h6>
    <canvas id="graficoCategoria"></canvas>
</div>

<script>
new Chart(document.getElementById('graficoCategoria'), {
    type: 'doughnut',
    data: {
        labels: @json($graficoCategoria->pluck('label') ?? []),
        datasets: [{
            data: @json($graficoCategoria->pluck('total') ?? []),
            backgroundColor: ['#11426f','#61bd4f','#ffc107','#dc3545','#6f42c1']
        }]
    }
});
</script>