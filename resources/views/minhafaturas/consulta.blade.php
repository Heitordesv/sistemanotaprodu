<!-- Chosen Palette: Minimalist Blue & Gray (Tons de Cinza, Branco, e Azul Escuro para Ação) -->
<!-- Application Structure Plan: Dashboard/Storefront Hybrid. A navegação superior permite alternar entre as três principais áreas de interação: 1. Catálogo de Serviços (Ação de Compra), 2. Gestão de Faturas Pendentes (Ação de Pagamento), e 3. Painel de Métricas (Visão Geral da Conta). A estrutura é dividida em cards temáticos para facilitar a exploração e a tomada de decisão. -->
<!-- Visualization & Content Choices: 1. Catálogo de Serviços (Cards HTML/CSS/JS): Permite seleção interativa para simular o início da compra. 2. Faturas Pendentes (Tabela HTML/JS): Filtragem por status (Vencido/Aberto) para ação imediata. 3. Histórico de Pagamentos (Line Chart - Chart.js/Canvas): Visualiza a tendência de faturamento mensal. 4. Distribuição de Uso de Serviços (Doughnut Chart - Chart.js/Canvas): Mostra a proporção de uso dos serviços contratados. NO SVG/Mermaid. -->
<!-- CONFIRMATION: NO SVG graphics used. NO Mermaid JS used. -->
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loja de Serviços e Faturas - NFeNotas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap');
        body { font-family: 'Inter', sans-serif; }
        .chart-container {
            position: relative;
            width: 100%;
            height: 350px;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
            padding: 1rem;
            background-color: #ffffff;
            border-radius: 0.75rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
        @media (max-width: 640px) {
             .chart-container {
                height: 300px;
             }
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-800">

    <div id="app-container" class="max-w-7xl mx-auto p-4 sm:p-6 lg:p-8">

        <!-- Header e Navegação Principal -->
        <header class="bg-white rounded-xl shadow-md mb-8 p-4">
            <h1 class="text-3xl font-extrabold text-blue-800 tracking-tight">
                Portal Financeiro e de Serviços
            </h1>
            <p class="text-gray-500 mt-1">
                Explore, gerencie faturas e faça upgrades de serviços em um só lugar.
            </p>
            <nav class="mt-4 flex space-x-4 border-t pt-3">
                <button data-target="catalog-section" class="nav-btn text-sm font-medium px-4 py-2 rounded-lg bg-blue-600 text-white shadow-md transition duration-150 hover:bg-blue-700">
                    &#x1F4E6; Catálogo
                </button>
                <button data-target="invoices-section" class="nav-btn text-sm font-medium px-4 py-2 rounded-lg text-gray-600 hover:bg-gray-100 transition duration-150">
                    &#x1F4B3; Faturas
                </button>
                <button data-target="metrics-section" class="nav-btn text-sm font-medium px-4 py-2 rounded-lg text-gray-600 hover:bg-gray-100 transition duration-150">
                    &#x1F4CA; Métricas
                </button>
            </nav>
        </header>

        <!-- 1. Catálogo de Serviços (Seção Interativa) -->
        <section id="catalog-section" class="app-section">
            <h2 class="text-2xl font-bold mb-4 text-gray-700">Catálogo de Serviços Disponíveis</h2>
            <p class="text-gray-600 mb-6">
                Explore os planos de emissão de NF-e e encontre o que melhor se adapta ao volume de transações da sua empresa. Clique em um plano para ver os detalhes e iniciar o upgrade.
            </p>

            <div id="service-catalog" class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Cards de Serviço (Preenchidos via JS para simular interatividade) -->
            </div>
        </section>

        <!-- 2. Gestão de Faturas (Seção de Ação) -->
        <section id="invoices-section" class="app-section hidden mt-10">
            <h2 class="text-2xl font-bold mb-4 text-gray-700">Faturas Pendentes e Histórico</h2>
            <p class="text-gray-600 mb-6">
                Visualize e gerencie todas as suas contas a pagar. Use o filtro para identificar rapidamente as faturas que necessitam de atenção imediata.
            </p>

            <div class="flex space-x-4 mb-4">
                <button id="filter-all" class="filter-btn px-4 py-2 text-sm font-medium rounded-lg bg-blue-600 text-white shadow-md transition duration-150 hover:bg-blue-700">
                    Todas as Faturas
                </button>
                <button id="filter-overdue" class="filter-btn px-4 py-2 text-sm font-medium rounded-lg text-red-600 border border-red-300 hover:bg-red-50 transition duration-150">
                    Somente Vencidas
                </button>
            </div>
            
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID Fatura</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vencimento</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor (R$)</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ação</th>
                        </tr>
                    </thead>
                    <tbody id="invoice-table-body" class="bg-white divide-y divide-gray-200">
                        <!-- Linhas de Fatura preenchidas via JS -->
                    </tbody>
                </table>
            </div>
        </section>

        <!-- 3. Painel de Métricas (Seção de Análise) -->
        <section id="metrics-section" class="app-section hidden mt-10">
            <h2 class="text-2xl font-bold mb-4 text-gray-700">Métricas e Histórico da Conta</h2>
            <p class="text-gray-600 mb-8">
                Esta seção oferece uma visão analítica do seu relacionamento com o sistema, mostrando a evolução dos seus pagamentos e a distribuição de uso dos serviços contratados.
            </p>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Gráfico de Tendência de Pagamento -->
                <div class="chart-container">
                    <h3 class="text-lg font-semibold text-gray-700 mb-2">Tendência de Pagamento Mensal</h3>
                    <canvas id="paymentTrendChart"></canvas>
                </div>

                <!-- Gráfico de Distribuição de Serviços -->
                <div class="chart-container">
                    <h3 class="text-lg font-semibold text-gray-700 mb-2">Distribuição de Uso de Serviços</h3>
                    <canvas id="serviceDistributionChart"></canvas>
                </div>
            </div>
        </section>

    </div>

    <script>
        const appState = {
            currentSection: 'catalog-section',
            invoiceFilter: 'all'
        };

        const serviceData = [
            { id: 1, name: "Plano Bronze", price: 99.90, features: ["500 NF-e/mês", "Suporte Padrão"], popular: false },
            { id: 2, name: "Plano Prata", price: 199.90, features: ["2.000 NF-e/mês", "Suporte Prioritário", "APIs"], popular: true },
            { id: 3, name: "Plano Ouro", price: 499.90, features: ["Ilimitado NF-e", "Suporte 24/7", "Certificado Digital Grátis"], popular: false }
        ];

        const invoiceData = [
            { id: 'FAT001', date: '2025-10-01', due: '2025-10-15', value: 199.90, status: 'Aberto' },
            { id: 'FAT002', date: '2025-09-01', due: '2025-09-15', value: 99.90, status: 'Vencido' },
            { id: 'FAT003', date: '2025-08-01', due: '2025-08-15', value: 199.90, status: 'Pago' },
            { id: 'FAT004', date: '2025-11-01', due: '2025-11-15', value: 499.90, status: 'Aberto' },
            { id: 'FAT005', date: '2025-07-01', due: '2025-07-15', value: 99.90, status: 'Pago' }
        ];

        let selectedServiceId = null;
        let paymentTrendChart, serviceDistributionChart;

        function formatCurrency(value) {
            return value.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
        }

        function truncateLabel(label) {
            if (label.length > 16) {
                return label.substring(0, 14) + '...';
            }
            return label;
        }

        function renderServices() {
            const container = document.getElementById('service-catalog');
            container.innerHTML = serviceData.map(service => `
                <div data-id="${service.id}" class="service-card bg-white p-6 rounded-xl border border-gray-200 shadow-md transition duration-200 hover:shadow-lg cursor-pointer ${service.popular ? 'ring-2 ring-blue-500' : ''}">
                    ${service.popular ? '<span class="absolute top-0 right-0 bg-blue-500 text-white text-xs font-semibold px-3 py-1 rounded-bl-lg">Popular</span>' : ''}
                    <h3 class="text-xl font-bold ${service.popular ? 'text-blue-600' : 'text-gray-800'} mb-2">${service.name}</h3>
                    <p class="text-3xl font-extrabold mb-4">${formatCurrency(service.price)}<span class="text-base font-normal text-gray-500">/mês</span></p>
                    <ul class="space-y-2 mb-6 text-gray-600">
                        ${service.features.map(f => `<li class="flex items-center text-sm">
                            <span class="text-green-500 mr-2">&#x2714;</span> ${f}
                        </li>`).join('')}
                    </ul>
                    <button data-service-id="${service.id}" class="select-service-btn w-full py-2 rounded-lg text-white font-semibold transition duration-150 ${service.id === selectedServiceId ? 'bg-green-600 hover:bg-green-700' : 'bg-blue-500 hover:bg-blue-600'}">
                        ${service.id === selectedServiceId ? 'Selecionado' : 'Selecionar Plano'}
                    </button>
                </div>
            `).join('');

            document.querySelectorAll('.select-service-btn').forEach(button => {
                button.addEventListener('click', function() {
                    selectedServiceId = parseInt(this.getAttribute('data-service-id'));
                    renderServices(); 
                    console.log('Plano selecionado:', selectedServiceId);
                });
            });
        }

        function renderInvoices() {
            const tbody = document.getElementById('invoice-table-body');
            tbody.innerHTML = ''; 

            const filteredInvoices = invoiceData.filter(invoice => 
                appState.invoiceFilter === 'all' || invoice.status === appState.invoiceFilter
            ).sort((a, b) => new Date(b.due) - new Date(a.due));

            filteredInvoices.forEach(invoice => {
                const isOverdue = invoice.status === 'Vencido';
                const row = tbody.insertRow();
                row.className = isOverdue ? 'bg-red-50 hover:bg-red-100' : 'hover:bg-gray-50';

                row.insertCell().textContent = invoice.id;
                row.insertCell().textContent = new Date(invoice.due).toLocaleDateString('pt-BR');
                row.insertCell().textContent = formatCurrency(invoice.value);
                
                const statusCell = row.insertCell();
                statusCell.className = 'px-6 py-4 whitespace-nowrap';
                statusCell.innerHTML = `<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${isOverdue ? 'bg-red-200 text-red-800' : (invoice.status === 'Aberto' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800')}">
                    ${invoice.status}
                </span>`;

                const actionCell = row.insertCell();
                actionCell.className = 'px-6 py-4 whitespace-nowrap';
                if (invoice.status !== 'Pago') {
                    actionCell.innerHTML = `<button class="text-sm font-medium text-blue-600 hover:text-blue-800 transition duration-150" onclick="alert('Simulando pagamento da ${invoice.id}')">Pagar Agora</button>`;
                } else {
                    actionCell.textContent = 'Ver Detalhes';
                }
            });

            document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('bg-blue-600', 'text-white', 'border-red-300', 'text-red-600', 'hover:bg-red-50', 'shadow-md'));
            document.getElementById('filter-all').classList.add('text-gray-600', 'hover:bg-gray-100');
            document.getElementById('filter-overdue').classList.add('text-gray-600', 'hover:bg-gray-100', 'border-gray-200');

            if (appState.invoiceFilter === 'all') {
                document.getElementById('filter-all').classList.replace('text-gray-600', 'text-white');
                document.getElementById('filter-all').classList.add('bg-blue-600', 'shadow-md');
                document.getElementById('filter-all').classList.remove('hover:bg-gray-100');
            } else if (appState.invoiceFilter === 'Vencido') {
                document.getElementById('filter-overdue').classList.replace('text-gray-600', 'text-white');
                document.getElementById('filter-overdue').classList.add('bg-red-600', 'shadow-md');
                document.getElementById('filter-overdue').classList.remove('hover:bg-gray-100');
            }
        }

        function initCharts() {
            const trendCtx = document.getElementById('paymentTrendChart').getContext('2d');
            const distributionCtx = document.getElementById('serviceDistributionChart').getContext('2d');

            const trendData = {
                labels: ['Mai/25', 'Jun/25', 'Jul/25', 'Ago/25', 'Set/25', 'Out/25'],
                datasets: [
                    {
                        label: 'Valor Faturado',
                        data: [150, 150, 99, 199, 199, 300],
                        borderColor: 'rgb(59, 130, 246)',
                        tension: 0.3,
                        fill: true,
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    },
                ]
            };

            paymentTrendChart = new Chart(trendCtx, {
                type: 'line',
                data: trendData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: { callbacks: { label: (context) => ` ${context.dataset.label}: ${formatCurrency(context.parsed.y)}` } }
                    },
                    scales: {
                        y: { beginAtZero: true, ticks: { callback: (value) => formatCurrency(value) } }
                    }
                }
            });

            const distributionData = {
                labels: ['Plano Prata', 'Plano Bronze', 'Adicionais API'],
                datasets: [
                    {
                        data: [45, 30, 25], // Porcentagem de uso/custo
                        backgroundColor: ['#3b82f6', '#93c5fd', '#1d4ed8'],
                        hoverOffset: 4
                    }
                ]
            };

            serviceDistributionChart = new Chart(distributionCtx, {
                type: 'doughnut',
                data: distributionData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        tooltip: { callbacks: { label: (context) => `${truncateLabel(context.label)}: ${context.parsed}%` } }
                    }
                }
            });
        }

        function setupEventListeners() {
            document.querySelectorAll('.nav-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const targetId = this.getAttribute('data-target');
                    
                    document.querySelectorAll('.app-section').forEach(section => {
                        section.classList.add('hidden');
                    });
                    document.getElementById(targetId).classList.remove('hidden');
                    appState.currentSection = targetId;

                    document.querySelectorAll('.nav-btn').forEach(btn => {
                        btn.classList.remove('bg-blue-600', 'text-white', 'shadow-md');
                        btn.classList.add('text-gray-600', 'hover:bg-gray-100');
                    });
                    this.classList.add('bg-blue-600', 'text-white', 'shadow-md');
                    this.classList.remove('text-gray-600', 'hover:bg-gray-100');
                });
            });

            document.getElementById('filter-all').addEventListener('click', () => {
                appState.invoiceFilter = 'all';
                renderInvoices();
            });

            document.getElementById('filter-overdue').addEventListener('click', () => {
                appState.invoiceFilter = 'Vencido';
                renderInvoices();
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            renderServices();
            renderInvoices();
            initCharts();
            setupEventListeners();
            
            // Inicializa a navegação para a primeira seção
            document.querySelector('.nav-btn[data-target="catalog-section"]').click();
        });
    </script>
</body>
</html>
