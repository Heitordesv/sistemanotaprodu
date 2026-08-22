const Dashboard = {
    charts: {},
    requests: {},
    hasErrors: false,
    refreshId: 0,
    path: typeof path_url !== 'undefined' ? path_url : '/',
};

Apex.chart = {
    fontFamily: 'Inter, Arial, sans-serif',
    foreColor: '#667085',
    animations: { enabled: true, easing: 'easeinout', speed: 450 },
};
Apex.grid = { borderColor: '#eaecf0', strokeDashArray: 4 };
Apex.tooltip = { theme: 'light', style: { fontSize: '13px' } };
Apex.legend = { fontSize: '13px', labels: { colors: '#475467' } };

$(document).ready(() => Dashboard.init());

Dashboard.init = function () {
    this.bindEvents();
    this.updatePeriodLabel();
    this.updateAll();
};

Dashboard.bindEvents = function () {
    $('#dashboard-filter').on('click', () => this.updateAll());

    $('#data_inicial, #data_final').on('change', () => {
        $('.js-periodo').removeClass('active');
        this.updatePeriodLabel();
    });

    $('#locais').on('change', () => this.updateAll());

    $('.js-periodo').on('click', event => {
        const button = $(event.currentTarget);
        $('.js-periodo').removeClass('active');
        button.addClass('active');
        this.applyQuickPeriod(button.data('periodo'));
        this.updateAll();
    });

    $('#set-location').on('click', () => {
        const filial_id = $('#locais').val();
        $.get(this.url('usuarios/set-location'), { filial_id })
            .done(() => {
                this.notify('Sucesso', 'Local definido como padrão.', 'success');
                this.updateAll();
            })
            .fail(() => this.notify('Atenção', 'Não foi possível definir o local.', 'error'));
    });
};

Dashboard.url = function (path) {
    const base = String(this.path || '/').replace(/\/+$/, '');
    const endpoint = String(path || '').replace(/^\/+/, '');
    return `${base}/${endpoint}`;
};

Dashboard.params = function () {
    return {
        empresa_id: $('#empresa_id').val(),
        local_id: $('#locais').length ? ($('#locais').val() || 'todos') : 'todos',
        data_inicial: $('#data_inicial').val(),
        data_final: $('#data_final').val(),
    };
};

Dashboard.updateAll = function () {
    const params = this.params();

    if (!this.validatePeriod(params.data_inicial, params.data_final)) {
        return;
    }

    const refreshId = ++this.refreshId;
    this.hasErrors = false;
    this.clearError();
    this.setLoading(true);
    this.updatePeriodLabel();

    const requests = [
        this.cards(params),
        this.contaPagar(params),
        this.contaReceber(params),
        this.contaPagarCategorias(params),
        this.fluxoAnual(params),
        this.vendasAnual(params),
        this.curvaABC(params),
    ].filter(Boolean);

    if ($('#chart2').length) {
        requests.push(this.produtos(params));
    }

    $.when.apply($, requests).always(() => {
        if (refreshId !== this.refreshId) return;
        this.setLoading(false);
        if (!this.hasErrors) {
            $('#dashboard-last-update').text(`Atualizado às ${new Date().toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })}`);
        }
    });
};

Dashboard.ajaxGet = function (endpoint, data = {}, callback = () => {}) {
    if (this.requests[endpoint]) {
        this.requests[endpoint].abort();
    }

    const request = $.ajax({
        url: this.url(endpoint),
        method: 'GET',
        data,
        dataType: 'json',
        cache: false,
    });

    this.requests[endpoint] = request;

    request
        .done(callback)
        .fail(xhr => {
            if (xhr.statusText === 'abort') return;
            this.hasErrors = true;
            this.showError(this.responseMessage(xhr));
            console.error(`Erro ao carregar ${endpoint}:`, xhr);
        })
        .always(() => {
            if (this.requests[endpoint] === request) {
                delete this.requests[endpoint];
            }
        });

    return request;
};

Dashboard.responseMessage = function (xhr) {
    const json = xhr.responseJSON || {};
    if (json.message) return json.message;

    if (json.errors) {
        const first = Object.values(json.errors).flat()[0];
        if (first) return first;
    }

    if (xhr.status === 401 || xhr.status === 419) {
        return 'Sua sessão expirou. Atualize a página e faça login novamente.';
    }

    return 'Não foi possível carregar todos os indicadores. Revise os filtros e tente novamente.';
};

Dashboard.validatePeriod = function (inicio, fim) {
    if (!inicio || !fim) {
        this.showError('Informe a data inicial e a data final.');
        return false;
    }

    if (inicio > fim) {
        this.showError('A data inicial não pode ser posterior à data final.');
        return false;
    }

    return true;
};

Dashboard.setLoading = function (loading) {
    $('.dashboard-page').toggleClass('dashboard-loading', loading);
    $('#dashboard-filter').prop('disabled', loading);
    $('#dashboard-filter .filter-label').text(loading ? 'Carregando' : 'Aplicar filtros');
    $('#dashboard-filter .spinner-border').toggleClass('d-none', !loading);
};

Dashboard.showError = function (message) {
    $('#dashboard-alert-text').text(message);
    $('#dashboard-alert').removeClass('d-none');
};

Dashboard.clearError = function () {
    $('#dashboard-alert').addClass('d-none');
    $('#dashboard-alert-text').text('');
};

Dashboard.updatePeriodLabel = function () {
    const inicio = this.formatDateLabel($('#data_inicial').val());
    const fim = this.formatDateLabel($('#data_final').val());
    $('#dashboard-period-label').text(inicio && fim ? `${inicio} até ${fim}` : 'Período não definido');
};

Dashboard.applyQuickPeriod = function (periodo) {
    const hoje = new Date();
    const inicio = new Date(hoje);

    if (periodo === '7d') inicio.setDate(hoje.getDate() - 6);
    if (periodo === '30d') inicio.setDate(hoje.getDate() - 29);
    if (periodo === 'mes') inicio.setDate(1);

    $('#data_inicial').val(this.formatDateInput(inicio));
    $('#data_final').val(this.formatDateInput(hoje));
};

Dashboard.formatDateInput = function (date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
};

Dashboard.formatDateLabel = function (date) {
    if (!date) return '';
    const [year, month, day] = date.split('-');
    return `${day}/${month}/${year}`;
};

Dashboard.cards = function (params) {
    return this.ajaxGet('api/graficos/getDataCards', params, res => {
        $('.total_vendas').text(this.currency(res.vendas));
        $('.total_vendas_erp').text(this.currency(res.vendas_erp));
        $('.total_vendas_pdv').text(this.currency(res.vendas_pdv));
        $('.total_vendas_ecommerce').text(this.currency(res.vendas_ecommerce));
        $('.total_vendas_ecommerce_nao_integradas').text(this.currency(res.vendas_ecommerce_nao_integradas));
        $('.total_quantidade_vendas').text(this.integer(res.quantidade_vendas));
        $('.ticket_medio').text(this.currency(res.ticket_medio));
        $('.total_vendas_canceladas').text(this.integer(res.vendas_canceladas));
        $('.total_produtos').text(this.integer(res.produtos));
        $('.total_pagar').text(this.currency(res.conta_pagar_abertas));
        $('.total_pagars').text(this.currency(res.conta_pagar_pagas));
        $('.total_receber').text(this.currency(res.conta_receber_abertas));
        $('.total_recebido').text(this.currency(res.conta_receber_recebidas));
        $('.total_receber_vencido').text(this.currency(res.conta_receber_vencidas));

        const saldo = this.number(res.saldo_financeiro);
        $('.saldo_financeiro')
            .text(this.currency(saldo))
            .toggleClass('text-success', saldo >= 0)
            .toggleClass('text-danger', saldo < 0);
        $('.saldo-status').text(saldo >= 0 ? 'Entradas acima das saídas' : 'Saídas acima das entradas');

        $('.os_status_0_quantidade, .os_status_1_quantidade').text('0');
        $('.os_status_0_valor, .os_status_1_valor').text(this.currency(0));

        if (Number(res.perfil_id) === 2 && res.servico_os) {
            $('.card-os').removeClass('d-none');
            $('.total_os_valor').text(this.currency(res.servico_os.total_valor));
            $('.total_os_quantidade').text(this.integer(res.servico_os.quantidade));

            const statusItems = Array.isArray(res.servico_os.por_status)
                ? res.servico_os.por_status
                : Object.values(res.servico_os.por_status || {});

            statusItems.forEach(item => {
                $(`.os_status_${item.status}_quantidade`).text(this.integer(item.total));
                $(`.os_status_${item.status}_valor`).text(this.currency(item.valor_total));
            });
        } else {
            $('.card-os').addClass('d-none');
        }
    });
};

Dashboard.vendasAnual = function (params) {
    return this.ajaxGet('api/graficos/vendasAnual', params, res => {
        const labels = res.meses || [];
        const erp = this.numericArray(res.vendas_erp);
        const pdv = this.numericArray(res.vendas_pdv);
        const ecommerce = this.numericArray(res.vendas_ecommerce);
        const total = this.numericArray(res.somaVendas);

        this.renderChart('chart1', {
            series: [
                { name: 'NF-e / pedidos', type: 'column', data: erp },
                { name: 'PDV', type: 'column', data: pdv },
                { name: 'Online fora do ERP', type: 'column', data: ecommerce },
                { name: 'Total', type: 'line', data: total },
            ],
            chart: { type: 'line', height: 360, toolbar: { show: true }, zoom: { enabled: false } },
            stroke: { width: [0, 0, 0, 3], curve: 'smooth' },
            plotOptions: { bar: { columnWidth: '52%', borderRadius: 4 } },
            dataLabels: { enabled: false },
            xaxis: { categories: labels, labels: { hideOverlappingLabels: true } },
            yaxis: { labels: { formatter: value => this.currencyCompact(value) } },
            tooltip: { shared: true, y: { formatter: value => this.currency(value) } },
            legend: { position: 'top', horizontalAlign: 'left' },
            noData: this.noData(),
        });
    });
};

Dashboard.curvaABC = function (params) {
    return this.ajaxGet('api/graficos/curvaABC', params, res => {
        const produtos = res.curva_abc_produtos || [];
        const categorias = produtos.map(item => item.produto_nome);
        const faturamento = produtos.map(item => this.number(item.faturamento));
        const acumulado = produtos.map(item => this.number(item.porcentagem_acumulada));

        this.renderChart('chart_curva_abc', {
            series: produtos.length ? [
                { name: 'Faturamento', type: 'column', data: faturamento },
                { name: 'Acumulado', type: 'line', data: acumulado },
            ] : [],
            chart: { height: 410, type: 'line', toolbar: { show: true } },
            stroke: { width: [0, 3], curve: 'smooth' },
            plotOptions: { bar: { borderRadius: 4, columnWidth: '58%' } },
            dataLabels: { enabled: false },
            xaxis: {
                categories: categorias,
                labels: { rotate: -35, trim: true, maxHeight: 110 },
            },
            yaxis: [
                { title: { text: 'Faturamento' }, labels: { formatter: value => this.currencyCompact(value) } },
                { opposite: true, min: 0, max: 100, title: { text: 'Acumulado' }, labels: { formatter: value => `${Math.round(value)}%` } },
            ],
            tooltip: {
                shared: true,
                y: [
                    { formatter: value => this.currency(value) },
                    { formatter: value => `${this.number(value).toFixed(1)}%` },
                ],
            },
            legend: { position: 'top', horizontalAlign: 'left' },
            noData: this.noData('Nenhum produto vendido no período.'),
        });
    });
};

Dashboard.contaReceber = function (params) {
    return this.ajaxGet('api/graficos/contasReceber', params, res => {
        const recebido = this.number(res.recebidas);
        const receber = this.number(res.receber);
        $('.cr-recebido').text(this.currency(recebido));
        $('.cr-receber').text(this.currency(receber));
        this.renderChart('chart4', this.radialOptions(res.percentual, 'Recebido'));
    });
};

Dashboard.contaPagar = function (params) {
    return this.ajaxGet('api/graficos/contasPagar', params, res => {
        const pago = this.number(res.pagos);
        const pagar = this.number(res.pagar);
        $('.cp-pago').text(this.currency(pago));
        $('.cp-pagar').text(this.currency(pagar));
        this.renderChart('chart9', this.radialOptions(res.percentual, 'Pago'));
    });
};

Dashboard.contaPagarCategorias = function (params) {
    return this.ajaxGet('api/graficos/contasPagarCategorias', params, res => {
        const labels = res.labels || [];
        const series = this.numericArray(res.valores);

        this.renderChart('chartCategorias', {
            series,
            chart: { type: 'donut', height: 360 },
            labels,
            dataLabels: { enabled: series.length > 0 },
            legend: { position: 'bottom' },
            plotOptions: { pie: { donut: { size: '68%' } } },
            tooltip: { y: { formatter: value => this.currency(value) } },
            noData: this.noData('Nenhuma despesa encontrada no período.'),
        });
    });
};

Dashboard.fluxoAnual = function (params) {
    return this.ajaxGet('api/graficos/fluxoAnual', params, res => {
        const dados = res.dados || [];
        const categorias = dados.map(item => item.label);
        const entradas = dados.map(item => this.number(item.entrada));
        const saidas = dados.map(item => this.number(item.saida));
        const saldos = dados.map(item => this.number(item.saldo));

        this.renderChart('chartFluxoAnual', {
            series: dados.length ? [
                { name: 'Entradas realizadas', type: 'column', data: entradas },
                { name: 'Saídas realizadas', type: 'column', data: saidas },
                { name: 'Saldo', type: 'line', data: saldos },
            ] : [],
            chart: { type: 'line', height: 360, toolbar: { show: true }, zoom: { enabled: false } },
            stroke: { width: [0, 0, 3], curve: 'smooth' },
            plotOptions: { bar: { columnWidth: '54%', borderRadius: 4 } },
            dataLabels: { enabled: false },
            xaxis: { categories: categorias, labels: { hideOverlappingLabels: true } },
            yaxis: { labels: { formatter: value => this.currencyCompact(value) } },
            tooltip: { shared: true, y: { formatter: value => this.currency(value) } },
            legend: { position: 'top', horizontalAlign: 'left' },
            noData: this.noData('Nenhuma movimentação financeira realizada no período.'),
        });
    });
};

Dashboard.radialOptions = function (percentual, label) {
    const value = Math.max(0, Math.min(100, this.number(percentual)));

    return {
        series: [value],
        chart: { type: 'radialBar', height: 280, sparkline: { enabled: false } },
        plotOptions: {
            radialBar: {
                startAngle: -120,
                endAngle: 120,
                hollow: { size: '62%' },
                track: { background: '#f2f4f7', strokeWidth: '100%' },
                dataLabels: {
                    name: { fontSize: '14px', offsetY: 22 },
                    value: { fontSize: '28px', fontWeight: 700, offsetY: -16, formatter: val => `${Number(val).toFixed(1)}%` },
                },
            },
        },
        labels: [label],
    };
};

Dashboard.produtos = function (params) {
    return this.ajaxGet('api/graficos/produtos', params, res => {
        this.renderChart('chart2', {
            series: [
                { name: 'Cadastrados', data: this.numericArray(res.somaCadastradoMes) },
                { name: 'Vendidos', data: this.numericArray(res.somaVendidosNoDia) },
                { name: 'Sem venda', data: this.numericArray(res.somaNaoVendidos) },
            ],
            chart: { type: 'bar', height: 350 },
            plotOptions: { bar: { borderRadius: 4, columnWidth: '58%' } },
            dataLabels: { enabled: false },
            xaxis: { categories: res.meses || [] },
            legend: { position: 'top' },
            tooltip: { y: { formatter: value => `${this.integer(value)} produtos` } },
            noData: this.noData(),
        });
    });
};

Dashboard.renderChart = function (id, options) {
    const element = document.querySelector(`#${id}`);
    if (!element) return;

    if (this.charts[id]) {
        this.charts[id].destroy();
    }

    this.charts[id] = new ApexCharts(element, options);
    this.charts[id].render();
};

Dashboard.noData = function (text = 'Nenhum dado encontrado para o período selecionado.') {
    return {
        text,
        align: 'center',
        verticalAlign: 'middle',
        style: { color: '#98a2b3', fontSize: '14px' },
    };
};

Dashboard.number = function (value) {
    if (value === null || value === undefined || value === '') return 0;
    if (typeof value === 'number') return Number.isFinite(value) ? value : 0;

    const normalized = String(value)
        .replace(/R\$/g, '')
        .replace(/\s/g, '')
        .replace(/\.(?=\d{3}(?:\D|$))/g, '')
        .replace(',', '.');
    const parsed = Number(normalized);
    return Number.isFinite(parsed) ? parsed : 0;
};

Dashboard.numericArray = function (values) {
    return (values || []).map(value => this.number(value));
};

Dashboard.currency = function (value) {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
        minimumFractionDigits: 2,
    }).format(this.number(value));
};

Dashboard.currencyCompact = function (value) {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
        notation: 'compact',
        maximumFractionDigits: 1,
    }).format(this.number(value));
};

Dashboard.integer = function (value) {
    return new Intl.NumberFormat('pt-BR', { maximumFractionDigits: 0 }).format(this.number(value));
};

Dashboard.notify = function (title, message, type) {
    if (typeof swal === 'function') {
        swal(title, message, type);
        return;
    }

    window.alert(`${title}: ${message}`);
};