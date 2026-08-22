<div class="card p-3 shadow-sm">
    <h6>Receitas por Grupo</h6>
    <canvas id="graficoGrupo"></canvas>
</div>

<script>
new Chart(document.getElementById('graficoGrupo'), {
    type: 'bar',
    data: {
        labels: @json($graficoGrupo->pluck('label') ?? []),
        datasets: [{
            label: 'Grupos',
            data: @json($graficoGrupo->pluck('total') ?? []),
            backgroundColor: '#11426f'
        }]
    }
});
</script>