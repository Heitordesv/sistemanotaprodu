(function () {
    'use strict';

    const data = window.SaasDashboardData || {};

    function currency(value) {
        return new Intl.NumberFormat('pt-BR', {
            style: 'currency',
            currency: 'BRL',
            maximumFractionDigits: 2,
        }).format(Number(value) || 0);
    }

    function render(selector, options) {
        const element = document.querySelector(selector);
        if (!element || typeof ApexCharts === 'undefined') return;
        new ApexCharts(element, options).render();
    }

    function financialChart(selector, source, labels) {
        const chartData = source || { labels: [], series: {} };

        render(selector, {
            series: [
                { name: labels.realized, type: 'column', data: chartData.series?.[labels.realizedKey] || [] },
                { name: labels.pending, type: 'line', data: chartData.series?.pendente || [] },
                { name: labels.overdue, type: 'line', data: chartData.series?.vencida || [] },
            ],
            chart: { type: 'line', height: 330, toolbar: { show: true }, zoom: { enabled: false } },
            stroke: { width: [0, 3, 3], curve: 'smooth' },
            plotOptions: { bar: { borderRadius: 5, columnWidth: '45%' } },
            dataLabels: { enabled: false },
            xaxis: { categories: chartData.labels || [] },
            yaxis: { labels: { formatter: currency } },
            tooltip: { shared: true, y: { formatter: currency } },
            legend: { position: 'top', horizontalAlign: 'left' },
            noData: { text: labels.noData },
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        financialChart('#saasRevenueChart', data.receita, {
            realized: 'Recebido',
            realizedKey: 'recebida',
            pending: 'A receber',
            overdue: 'Receita vencida',
            noData: 'Sem contas a receber no período.',
        });

        financialChart('#saasExpenseChart', data.despesa, {
            realized: 'Pago',
            realizedKey: 'paga',
            pending: 'A pagar',
            overdue: 'Despesa vencida',
            noData: 'Sem contas a pagar no período.',
        });

        const growth = data.crescimento || { labels: [], series: {} };
        render('#saasGrowthChart', {
            series: [
                { name: 'Novas empresas', data: growth.series?.empresas || [] },
                { name: 'Novas pagantes', data: growth.series?.pagantes || [] },
                { name: 'Leads', data: growth.series?.leads || [] },
                { name: 'Leads convertidos', data: growth.series?.convertidos || [] },
            ],
            chart: { type: 'line', height: 340, toolbar: { show: false }, zoom: { enabled: false } },
            stroke: { curve: 'smooth', width: 3 },
            markers: { size: 3 },
            dataLabels: { enabled: false },
            xaxis: { categories: growth.labels || [] },
            yaxis: { min: 0, forceNiceScale: true, labels: { formatter: value => Math.round(value) } },
            tooltip: { shared: true, y: { formatter: value => `${Math.round(value)} registro(s)` } },
            legend: { position: 'top', horizontalAlign: 'left' },
            noData: { text: 'Sem crescimento registrado no período.' },
        });
    });
})();