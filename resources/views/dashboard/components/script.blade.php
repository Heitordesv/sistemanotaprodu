@push('styles')
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

    body {
        font-family: 'Plus Jakarta Sans', sans-serif;
        background: #f0f2f5;
        color: #0f172a;
    }

    .card-saas {
        background: #ffffff;
        border-radius: 24px;
        border: 1px solid rgba(226, 232, 240, 0.7);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.04);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .card-saas:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.08);
    }

    .metric-vivid {
        position: relative;
        overflow: hidden;
        border: none;
    }

    .metric-vivid::before {
        content: '';
        position: absolute;
        top: -20px; right: -20px;
        width: 100px; height: 100px;
        background: rgba(255, 255, 255, 0.15);
        border-radius: 50%;
    }

    .icon-glass {
        width: 56px; height: 56px;
        border-radius: 18px;
        display: flex; align-items: center; justify-content: center;
        font-size: 28px;
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        color: white;
    }

    .bg-grad-indigo { background: linear-gradient(135deg, #6366f1 0%, #4338ca 100%); }
    .bg-grad-emerald { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
    .bg-grad-rose { background: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%); }
    .bg-grad-amber { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
    .bg-grad-cyan { background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%); }
    .bg-grad-dark { background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); }

    .input-clean {
        background: #f8fafc;
        border: 2px solid transparent;
        transition: 0.3s;
    }

    .input-clean:focus {
        background: #fff;
        border-color: #6366f1;
        outline: none;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Gráfico de Categorias (Pie)
    const ctx = document.getElementById('chartCategorias')?.getContext('2d');
    if(ctx) {
        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: {!! json_encode($labelsCategorias) !!},
                datasets: [{
                    data: {!! json_encode($valoresCategorias) !!},
                    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#C9CBCF']
                }]
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
        });
    }

    // 2. Gráfico de Recebimentos (Bar)
    const ctxReceber = document.getElementById('chartRecebimentos')?.getContext('2d');
    if(ctxReceber) {
        new Chart(ctxReceber, {
            type: 'bar',
            data: {
                labels: {!! json_encode($labelsReceber) !!},
                datasets: [{
                    label: 'Total Recebido (R$)',
                    data: {!! json_encode($valoresReceber) !!},
                    backgroundColor: '#10b981',
                    borderRadius: 8
                }]
            },
            options: { indexAxis: 'y', responsive: true }
        });
    }

    // 3. Fluxo de Caixa (ApexCharts)
    let fluxoData = @json($fluxo['mensal'] ?? []);
    if(document.querySelector("#graficoFluxo")) {
        const realParaNumero = (v) => v ? Number(v.replace(/[R$\s\.]/g, '').replace(',', '.')) : 0;
        
        var options = {
            chart: { type: 'area', height: 350, toolbar: { show: false }, fontFamily: 'Plus Jakarta Sans' },
            colors: ['#6366f1', '#06b6d4', '#10b981', '#f43f5e', '#1e293b'],
            series: [
                { name: 'Recebido', data: fluxoData.map(i => realParaNumero(i.recebido)) },
                { name: 'A Receber', data: fluxoData.map(i => realParaNumero(i.a_receber)) },
                { name: 'Pago', data: fluxoData.map(i => realParaNumero(i.pago)) },
                { name: 'A Pagar', data: fluxoData.map(i => realParaNumero(i.a_pagar)) },
                { name: 'Saldo', data: fluxoData.map(i => realParaNumero(i.saldo)) }
            ],
            xaxis: { categories: fluxoData.map(i => i.mes + '/' + i.ano) },
            stroke: { curve: 'smooth', width: 3 },
            fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.45, opacityTo: 0.05 } },
            tooltip: { y: { formatter: (v) => v.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }) } }
        };
        new ApexCharts(document.querySelector("#graficoFluxo"), options).render();
    }
});
</script>
@endpush