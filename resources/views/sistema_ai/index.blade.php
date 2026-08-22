@extends('default.layout', ['title' => 'Pesquisa IA Financeira'])

@section('content')
<style>
    .finance-ai-page {
        --fa-border: rgba(15, 23, 42, .10);
        --fa-muted: #64748b;
        --fa-soft: rgba(15, 23, 42, .035);
        --fa-primary-soft: rgba(13, 110, 253, .08);
        --fa-success-soft: rgba(25, 135, 84, .08);
        --fa-radius: 18px;
    }

    .finance-ai-page .fa-card {
        border: 1px solid var(--fa-border);
        border-radius: var(--fa-radius);
        background: var(--bs-card-bg, #fff);
        box-shadow: 0 10px 32px rgba(15, 23, 42, .055);
    }

    .finance-ai-page .fa-hero {
        overflow: hidden;
        background:
            radial-gradient(circle at 90% 0%, rgba(13, 110, 253, .16), transparent 31%),
            radial-gradient(circle at 75% 110%, rgba(25, 135, 84, .11), transparent 30%),
            linear-gradient(135deg, rgba(13, 110, 253, .055), rgba(255,255,255,.96));
    }

    .finance-ai-page .fa-kicker {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        font-size: .76rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .075em;
        color: var(--bs-primary);
    }

    .finance-ai-page .fa-title {
        font-size: clamp(1.65rem, 3vw, 2.65rem);
        font-weight: 850;
        line-height: 1.08;
        letter-spacing: -.035em;
    }

    .finance-ai-page .fa-muted { color: var(--fa-muted); }

    .finance-ai-page .fa-icon {
        width: 44px;
        height: 44px;
        flex: 0 0 44px;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 23px;
    }

    .finance-ai-page .fa-filter-panel {
        border: 1px solid rgba(13,110,253,.16);
        background: linear-gradient(180deg, rgba(13,110,253,.035), transparent);
        border-radius: 17px;
    }

    .finance-ai-page .fa-filter-label {
        display: block;
        font-size: .73rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .045em;
        color: var(--fa-muted);
        margin-bottom: 6px;
    }

    .finance-ai-page .fa-filter-panel .form-select,
    .finance-ai-page .fa-filter-panel .form-control {
        min-height: 42px;
        border-radius: 11px;
        border-color: var(--fa-border);
        box-shadow: none;
    }

    .finance-ai-page .fa-filter-panel .form-select:focus,
    .finance-ai-page .fa-filter-panel .form-control:focus {
        border-color: rgba(13,110,253,.50);
        box-shadow: 0 0 0 .2rem rgba(13,110,253,.08);
    }

    .finance-ai-page .fa-period-btn {
        border: 1px solid var(--fa-border);
        background: var(--bs-card-bg, #fff);
        border-radius: 999px;
        padding: 7px 12px;
        font-size: .78rem;
        font-weight: 700;
        color: var(--bs-body-color);
        transition: .15s ease;
    }

    .finance-ai-page .fa-period-btn:hover,
    .finance-ai-page .fa-period-btn.active {
        color: var(--bs-primary);
        border-color: rgba(13,110,253,.45);
        background: rgba(13,110,253,.075);
    }

    .finance-ai-page .fa-active-filters {
        display: flex;
        flex-wrap: wrap;
        gap: 7px;
    }

    .finance-ai-page .fa-filter-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: .76rem;
        font-weight: 700;
        color: #0d6efd;
        background: rgba(13,110,253,.075);
        border: 1px solid rgba(13,110,253,.13);
    }

    .finance-ai-page .fa-question-box {
        border: 1px solid rgba(13,110,253,.24);
        border-radius: 17px;
        padding: 11px;
        background: var(--bs-body-bg, #fff);
        box-shadow: 0 12px 34px rgba(13,110,253,.07);
    }

    .finance-ai-page .fa-question-box textarea {
        min-height: 92px;
        border: 0;
        resize: vertical;
        background: transparent;
        box-shadow: none !important;
        font-size: 1rem;
        line-height: 1.55;
    }

    .finance-ai-page .fa-quick-action {
        width: 100%;
        height: 100%;
        text-align: left;
        border: 1px solid var(--fa-border);
        border-radius: 15px;
        background: var(--bs-card-bg, #fff);
        padding: 15px;
        cursor: pointer;
        transition: .16s ease;
    }

    .finance-ai-page .fa-quick-action:hover {
        transform: translateY(-2px);
        border-color: rgba(13,110,253,.30);
        box-shadow: 0 12px 28px rgba(15,23,42,.07);
    }

    .finance-ai-page .fa-quick-action strong {
        display: block;
        font-size: .91rem;
        margin-bottom: 3px;
    }

    .finance-ai-page .fa-quick-action small {
        color: var(--fa-muted);
        line-height: 1.4;
    }

    .finance-ai-page .fa-answer-shell {
        overflow: hidden;
        border-color: rgba(13,110,253,.17);
    }

    .finance-ai-page .fa-answer-header {
        background: linear-gradient(90deg, rgba(13,110,253,.07), transparent);
    }

    .finance-ai-page .fa-answer {
        min-height: 190px;
        font-size: .96rem;
        line-height: 1.72;
    }

    .finance-ai-page .fa-answer h4,
    .finance-ai-page .fa-answer h5,
    .finance-ai-page .fa-answer h6 {
        font-weight: 800;
        letter-spacing: -.015em;
    }

    .finance-ai-page .fa-answer h4 {
        font-size: 1.2rem;
        border-bottom: 1px solid var(--fa-border);
        padding-bottom: 9px;
    }

    .finance-ai-page .fa-answer h5 { font-size: 1.06rem; }
    .finance-ai-page .fa-answer h6 { font-size: .98rem; }

    .finance-ai-page .fa-answer-line {
        padding: 9px 12px;
        border-radius: 11px;
        background: var(--fa-soft);
        margin: 5px 0;
    }

    .finance-ai-page .fa-history-item {
        width: 100%;
        text-align: left;
        background: transparent;
        border: 0;
        border-bottom: 1px solid var(--fa-border);
        padding: 10px 0;
        font-size: .87rem;
        color: var(--bs-body-color);
    }

    .finance-ai-page .fa-history-item:last-child { border-bottom: 0; }
    .finance-ai-page .fa-history-item:hover { color: var(--bs-primary); }

    .finance-ai-page .fa-status-box {
        border: 1px dashed rgba(100,116,139,.28);
        border-radius: 16px;
        background: rgba(100,116,139,.03);
    }

    .finance-ai-page .fa-privacy {
        background: var(--fa-success-soft);
        border: 1px solid rgba(25,135,84,.15);
        border-radius: 15px;
    }

    .finance-ai-page .fa-module-link {
        border-radius: 10px;
    }

    @media (max-width: 767.98px) {
        .finance-ai-page .fa-hero .card-body { padding: 1.35rem !important; }
        .finance-ai-page .fa-submit-wrap,
        .finance-ai-page .fa-submit-wrap .btn { width: 100%; }
        .finance-ai-page .fa-period-btn { flex: 1 1 auto; }
    }
</style>

<div class="page-content finance-ai-page">
    <div class="container-fluid">
        <div class="fa-card fa-hero mb-4">
            <div class="card-body p-4 p-lg-5">
                <div class="row g-4 align-items-center">
                    <div class="col-xl-8">
                        <div class="fa-kicker mb-3"><i class="bx bx-line-chart"></i> Especialista Financeiro com IA</div>
                        <h1 class="fa-title mb-3">Analise o financeiro da empresa com o período e os filtros certos.</h1>
                        <p class="fa-muted mb-3" style="max-width:780px; line-height:1.7;">
                            Consulte caixas, vendas, entradas, saídas, contas a pagar, contas a receber, formas de pagamento, operadores, ticket médio, inadimplência e desempenho do período sem confundir faturamento com lucro.
                        </p>
                        <div class="d-flex flex-wrap gap-2">
                            <span class="badge bg-primary px-3 py-2"><i class="bx bx-user-check me-1"></i> Administrador</span>
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2"><i class="bx bx-shield-quarter me-1"></i> Empresa da sessão</span>
                            <span class="badge bg-light text-dark border px-3 py-2"><i class="bx bx-lock-alt me-1"></i> Somente leitura</span>
                        </div>
                    </div>
                    <div class="col-xl-4">
                        <div class="fa-card p-4 bg-white bg-opacity-75">
                            <div class="d-flex gap-3 align-items-start mb-3">
                                <span class="fa-icon bg-primary text-white"><i class="bx bx-file-find"></i></span>
                                <div>
                                    <strong>Relatório executivo</strong>
                                    <div class="fa-muted small">Usa automaticamente os filtros escolhidos abaixo.</div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-primary w-100 pergunta-rapida" data-pergunta="Gere um relatório financeiro executivo completo do período selecionado. Mostre vendas, quantidade de vendas, ticket médio, caixas e operadores, formas de pagamento, suprimentos, sangrias, recebimentos, pagamentos, contas vencidas, principais despesas, riscos, oportunidades e plano de ação. Diferencie faturamento, recebimento, fluxo de caixa e lucro.">
                                <i class="bx bx-bar-chart-square me-1"></i> Gerar relatório do período
                            </button>
                        </div>
                    </div>
                </div>

                <form id="form-pesquisa-ia" action="{{ route('sistema-ia.pesquisar') }}" method="POST" class="mt-4">
                    @csrf

                    <div class="fa-filter-panel p-3 p-lg-4 mb-3">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                            <div>
                                <div class="fw-bold"><i class="bx bx-filter-alt text-primary me-1"></i> Filtros da análise</div>
                                <small class="fa-muted">O período selecionado tem prioridade sobre qualquer pergunta rápida.</small>
                            </div>
                            <button type="button" class="btn btn-sm btn-light border" id="limpar-filtros"><i class="bx bx-reset me-1"></i> Limpar filtros</button>
                        </div>

                        <div class="d-flex flex-wrap gap-2 mb-3" id="atalhos-periodo">
                            <button type="button" class="fa-period-btn active" data-periodo="hoje"><i class="bx bx-calendar me-1"></i> Hoje</button>
                            <button type="button" class="fa-period-btn" data-periodo="ontem">Ontem</button>
                            <button type="button" class="fa-period-btn" data-periodo="ultimos_7_dias">Últimos 7 dias</button>
                            <button type="button" class="fa-period-btn" data-periodo="mes_atual">Mês atual</button>
                            <button type="button" class="fa-period-btn" data-periodo="data_especifica">Escolher dia</button>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-4 col-xl-3">
                                <label class="fa-filter-label" for="periodo_analise">Período</label>
                                <select class="form-select" id="periodo_analise">
                                    <option value="hoje" selected>Hoje</option>
                                    <option value="ontem">Ontem</option>
                                    <option value="ultimos_7_dias">Últimos 7 dias</option>
                                    <option value="mes_atual">Mês atual</option>
                                    <option value="data_especifica">Data específica</option>
                                </select>
                            </div>

                            <div class="col-md-4 col-xl-3 d-none" id="campo-data-especifica">
                                <label class="fa-filter-label" for="data_especifica">Dia do mês atual</label>
                                <input type="date" class="form-control" id="data_especifica" min="{{ date('Y-m-01') }}" max="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}">
                            </div>

                            <div class="col-md-4 col-xl-3">
                                <label class="fa-filter-label" for="foco_analise">Foco</label>
                                <select class="form-select" id="foco_analise">
                                    <option value="geral" selected>Visão geral</option>
                                    <option value="caixas">Caixas / PDV</option>
                                    <option value="fluxo">Fluxo de caixa</option>
                                    <option value="receber">Contas a receber</option>
                                    <option value="pagar">Contas a pagar</option>
                                    <option value="despesas">Despesas</option>
                                    <option value="dre">DRE / Resultado</option>
                                    <option value="vendas">Vendas / Ticket médio</option>
                                </select>
                            </div>

                            <div class="col-md-4 col-xl-3">
                                <label class="fa-filter-label" for="operador_filtro">Operador / Usuário</label>
                                <input type="text" class="form-control" id="operador_filtro" maxlength="80" placeholder="Todos ou nome do operador">
                            </div>

                            <div class="col-md-4 col-xl-3">
                                <label class="fa-filter-label" for="forma_pagamento_filtro">Forma de pagamento</label>
                                <select class="form-select" id="forma_pagamento_filtro">
                                    <option value="">Todas</option>
                                    <option value="01">Dinheiro</option>
                                    <option value="17">PIX</option>
                                    <option value="19">PIX QR Code</option>
                                    <option value="03">Cartão de crédito</option>
                                    <option value="04">Cartão de débito</option>
                                    <option value="06">Crediário</option>
                                    <option value="15">Boleto</option>
                                    <option value="16">Depósito bancário</option>
                                    <option value="99">Outros</option>
                                </select>
                            </div>

                            <div class="col-md-4 col-xl-3">
                                <label class="fa-filter-label" for="status_caixa_filtro">Status do caixa</label>
                                <select class="form-select" id="status_caixa_filtro">
                                    <option value="">Todos</option>
                                    <option value="aberto">Somente abertos</option>
                                    <option value="fechado">Somente fechados</option>
                                </select>
                            </div>

                            <div class="col-md-4 col-xl-3">
                                <label class="fa-filter-label" for="caixa_id_filtro">Caixa específico</label>
                                <input type="number" min="1" class="form-control" id="caixa_id_filtro" placeholder="Ex.: 1322">
                            </div>

                            <div class="col-md-4 col-xl-3">
                                <label class="fa-filter-label" for="comparacao_filtro">Comparação</label>
                                <select class="form-select" id="comparacao_filtro">
                                    <option value="">Sem comparação</option>
                                    <option value="periodo_anterior">Comparar com período anterior equivalente</option>
                                    <option value="dia_anterior">Comparar com dia anterior</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-3 pt-3 border-top">
                            <div class="fa-filter-label mb-2">Filtros ativos</div>
                            <div id="filtros-ativos" class="fa-active-filters"></div>
                        </div>
                    </div>

                    <div class="fa-question-box">
                        <textarea
                            id="pergunta"
                            name="pergunta"
                            class="form-control"
                            maxlength="500"
                            placeholder="Ex.: Analise meu caixa e diga os principais riscos financeiros deste período."
                            autocomplete="off"
                            required
                        ></textarea>
                        <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap px-2 pb-1">
                            <small class="fa-muted"><i class="bx bx-info-circle me-1"></i> A IA aplica os filtros acima antes de interpretar a pergunta.</small>
                            <div class="fa-submit-wrap">
                                <button class="btn btn-primary px-4" type="submit" id="btn-pesquisar-ia">
                                    <i class="bx bx-search-alt-2 me-1"></i> Analisar financeiro
                                </button>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="row g-2 mt-3">
                    <div class="col-6 col-lg-3">
                        <button type="button" class="fa-quick-action pergunta-rapida" data-pergunta="Faça o fechamento dos caixas do período selecionado. Compare operadores, vendas, ticket médio, formas de pagamento, suprimentos e sangrias.">
                            <strong><i class="bx bx-store-alt text-primary me-1"></i> Caixas</strong>
                            <small>Fechamento e operadores.</small>
                        </button>
                    </div>
                    <div class="col-6 col-lg-3">
                        <button type="button" class="fa-quick-action pergunta-rapida" data-pergunta="Analise o fluxo financeiro do período selecionado. Compare recebimentos e pagamentos, vencimentos e pressão de caixa, sem tratar faturamento como lucro.">
                            <strong><i class="bx bx-transfer-alt text-warning me-1"></i> Fluxo</strong>
                            <small>Entradas, saídas e pressão.</small>
                        </button>
                    </div>
                    <div class="col-6 col-lg-3">
                        <button type="button" class="fa-quick-action pergunta-rapida" data-pergunta="Analise as contas a receber do período selecionado. Mostre recebido, pendente, vencido, a vencer e possíveis riscos de inadimplência.">
                            <strong><i class="bx bx-down-arrow-circle text-success me-1"></i> Receber</strong>
                            <small>Recebimentos e inadimplência.</small>
                        </button>
                    </div>
                    <div class="col-6 col-lg-3">
                        <button type="button" class="fa-quick-action pergunta-rapida" data-pergunta="Analise as contas a pagar e despesas do período selecionado. Mostre pago, pendente, vencido, principais categorias, fornecedores e pontos de atenção.">
                            <strong><i class="bx bx-up-arrow-circle text-danger me-1"></i> Pagar</strong>
                            <small>Despesas e compromissos.</small>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-xl-8">
                <div id="resultado-ia" class="fa-card fa-answer-shell mb-4 d-none">
                    <div class="fa-answer-header border-bottom d-flex align-items-center justify-content-between gap-3 flex-wrap py-3 px-4">
                        <div class="d-flex align-items-center gap-3">
                            <span class="fa-icon bg-primary bg-opacity-10 text-primary"><i class="bx bx-bar-chart-alt-2"></i></span>
                            <div>
                                <div class="fw-bold">Análise do Especialista Financeiro</div>
                                <small class="fa-muted" id="pergunta-atual">Consulta financeira</small>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-light border" id="copiar-resposta"><i class="bx bx-copy me-1"></i> Copiar</button>
                            <button type="button" class="btn btn-sm btn-light border" id="limpar-resposta"><i class="bx bx-x me-1"></i> Limpar</button>
                        </div>
                    </div>
                    <div class="card-body p-4 p-lg-5">
                        <div id="texto-resultado-ia" class="fa-answer"></div>
                    </div>
                </div>

                <div id="estado-inicial-ia" class="fa-card mb-4">
                    <div class="card-body p-4">
                        <div class="fa-status-box p-4 text-center">
                            <span class="fa-icon bg-primary bg-opacity-10 text-primary mb-3"><i class="bx bx-pie-chart-alt-2"></i></span>
                            <h5 class="mb-2">Escolha os filtros e faça sua análise</h5>
                            <p class="fa-muted mb-0">Você pode analisar hoje, ontem, os últimos 7 dias, qualquer dia do mês atual ou o mês atual inteiro.</p>
                        </div>
                    </div>
                </div>

                <div class="fa-card mb-4">
                    <div class="card-body p-4">
                        <div class="fw-bold mb-1">Análises recomendadas para o dono da empresa</div>
                        <div class="fa-muted small mb-3">Os filtros escolhidos acima também são aplicados nestas perguntas.</div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <button type="button" class="fa-quick-action pergunta-rapida" data-pergunta="Faça um diagnóstico financeiro executivo do período selecionado. Mostre os indicadores principais, o que melhorou, o que piorou, riscos, oportunidades e ações prioritárias.">
                                    <strong>Diagnóstico financeiro</strong>
                                    <small>Visão de controller para decisão.</small>
                                </button>
                            </div>
                            <div class="col-md-6">
                                <button type="button" class="fa-quick-action pergunta-rapida" data-pergunta="Mostre as formas de pagamento do período selecionado, valor e participação percentual de cada uma. Destaque concentração e mudanças relevantes quando houver comparação.">
                                    <strong>Formas de pagamento</strong>
                                    <small>Dinheiro, PIX, cartões e outros.</small>
                                </button>
                            </div>
                            <div class="col-md-6">
                                <button type="button" class="fa-quick-action pergunta-rapida" data-pergunta="Compare o desempenho dos operadores no período selecionado. Mostre total vendido, quantidade de vendas, ticket médio e pontos de atenção por operador.">
                                    <strong>Ranking de operadores</strong>
                                    <small>Vendas e ticket médio por usuário.</small>
                                </button>
                            </div>
                            <div class="col-md-6">
                                <button type="button" class="fa-quick-action pergunta-rapida" data-pergunta="Analise a evolução das vendas por dia no período selecionado. Mostre melhor dia, pior dia, ticket médio, variações e possíveis sinais de tendência sem inventar causas.">
                                    <strong>Desempenho por dia</strong>
                                    <small>Evolução diária e melhores dias.</small>
                                </button>
                            </div>
                            <div class="col-md-6">
                                <button type="button" class="fa-quick-action pergunta-rapida" data-pergunta="Analise inadimplência e capital de giro com os dados disponíveis no período selecionado. Mostre vencidos, pendentes, a receber, a pagar e pressão financeira de curto prazo. Não invente saldo bancário.">
                                    <strong>Capital de giro e inadimplência</strong>
                                    <small>Pressão de curto prazo e vencidos.</small>
                                </button>
                            </div>
                            <div class="col-md-6">
                                <button type="button" class="fa-quick-action pergunta-rapida" data-pergunta="Analise DRE, margem e resultado do período selecionado somente se houver dados suficientes de receitas, custos e despesas. Se faltar informação, diga exatamente o que falta e apresente apenas os indicadores confiáveis.">
                                    <strong>DRE e resultado</strong>
                                    <small>Margens somente com base suficiente.</small>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="fa-card mb-4">
                    <div class="card-body p-4">
                        <div class="fw-bold mb-1">Conferir no sistema</div>
                        <div class="fa-muted small mb-3">Abra os módulos para validar ou aprofundar a análise.</div>
                        <div class="d-grid gap-2">
                            <a href="{{ route('caixa.index') }}" class="btn btn-sm btn-outline-primary text-start fa-module-link"><i class="bx bx-store-alt me-2"></i> Caixas</a>
                            <a href="{{ route('frenteCaixa.list') }}" class="btn btn-sm btn-outline-primary text-start fa-module-link"><i class="bx bx-cart me-2"></i> Vendas do PDV</a>
                            <a href="{{ route('conta-receber.index') }}" class="btn btn-sm btn-outline-primary text-start fa-module-link"><i class="bx bx-wallet me-2"></i> Contas a Receber</a>
                            <a href="{{ route('conta-pagar.index') }}" class="btn btn-sm btn-outline-primary text-start fa-module-link"><i class="bx bx-credit-card me-2"></i> Contas a Pagar</a>
                            <a href="{{ route('fluxoCaixa.index') }}" class="btn btn-sm btn-outline-primary text-start fa-module-link"><i class="bx bx-transfer me-2"></i> Fluxo de Caixa</a>
                            <a href="{{ route('dre.index') }}" class="btn btn-sm btn-outline-primary text-start fa-module-link"><i class="bx bx-bar-chart-alt-2 me-2"></i> DRE</a>
                        </div>
                    </div>
                </div>

                <div class="fa-card mb-4">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="fw-bold">Consultas recentes</div>
                            <button type="button" class="btn btn-sm btn-link text-decoration-none p-0" id="limpar-historico">Limpar</button>
                        </div>
                        <div id="historico-ia">
                            <div class="fa-muted small">Suas últimas perguntas aparecerão aqui neste navegador.</div>
                        </div>
                    </div>
                </div>

                <div class="fa-privacy p-4">
                    <div class="d-flex align-items-start gap-3">
                        <span class="fa-icon bg-success bg-opacity-10 text-success"><i class="bx bx-shield-quarter"></i></span>
                        <div>
                            <h6 class="mb-1">Privacidade e isolamento</h6>
                            <p class="fa-muted small mb-0">A análise é somente leitura e deve considerar exclusivamente os dados da empresa autenticada. Credenciais, tokens, senhas e certificados não entram na resposta.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
(function () {
    const form = document.getElementById('form-pesquisa-ia');
    const input = document.getElementById('pergunta');
    const button = document.getElementById('btn-pesquisar-ia');
    const box = document.getElementById('resultado-ia');
    const initial = document.getElementById('estado-inicial-ia');
    const result = document.getElementById('texto-resultado-ia');
    const currentQuestion = document.getElementById('pergunta-atual');
    const clear = document.getElementById('limpar-resposta');
    const copy = document.getElementById('copiar-resposta');
    const historyBox = document.getElementById('historico-ia');
    const clearHistory = document.getElementById('limpar-historico');
    const clearFilters = document.getElementById('limpar-filtros');
    const activeFilters = document.getElementById('filtros-ativos');

    const period = document.getElementById('periodo_analise');
    const specificDateWrap = document.getElementById('campo-data-especifica');
    const specificDate = document.getElementById('data_especifica');
    const focus = document.getElementById('foco_analise');
    const operator = document.getElementById('operador_filtro');
    const payment = document.getElementById('forma_pagamento_filtro');
    const cashStatus = document.getElementById('status_caixa_filtro');
    const cashId = document.getElementById('caixa_id_filtro');
    const comparison = document.getElementById('comparacao_filtro');

    const historyKey = 'nfenotas_pesquisa_ia_history';

    const periodLabels = {
        hoje: 'Hoje',
        ontem: 'Ontem',
        ultimos_7_dias: 'Últimos 7 dias',
        mes_atual: 'Mês atual',
        data_especifica: 'Data específica'
    };

    const focusLabels = {
        geral: 'Visão geral',
        caixas: 'Caixas / PDV',
        fluxo: 'Fluxo de caixa',
        receber: 'Contas a receber',
        pagar: 'Contas a pagar',
        despesas: 'Despesas',
        dre: 'DRE / Resultado',
        vendas: 'Vendas / Ticket médio'
    };

    function escapeHtml(value) {
        const div = document.createElement('div');
        div.textContent = value || '';
        return div.innerHTML;
    }

    function selectedText(select) {
        return select && select.selectedOptions.length ? select.selectedOptions[0].textContent.trim() : '';
    }

    function formatDateBr(value) {
        if (!value) return '';
        const parts = value.split('-');
        return parts.length === 3 ? parts[2] + '/' + parts[1] + '/' + parts[0] : value;
    }

    function filtersObject() {
        return {
            periodo: period.value,
            periodoLabel: period.value === 'data_especifica'
                ? 'Dia ' + formatDateBr(specificDate.value)
                : (periodLabels[period.value] || selectedText(period)),
            data: period.value === 'data_especifica' ? specificDate.value : '',
            foco: focus.value,
            focoLabel: focusLabels[focus.value] || selectedText(focus),
            operador: operator.value.trim(),
            forma: payment.value,
            formaLabel: payment.value ? selectedText(payment) : '',
            statusCaixa: cashStatus.value,
            statusCaixaLabel: cashStatus.value ? selectedText(cashStatus) : '',
            caixaId: cashId.value.trim(),
            comparacao: comparison.value,
            comparacaoLabel: comparison.value ? selectedText(comparison) : ''
        };
    }

    function updatePeriodUI() {
        specificDateWrap.classList.toggle('d-none', period.value !== 'data_especifica');
        document.querySelectorAll('.fa-period-btn').forEach(function (item) {
            item.classList.toggle('active', item.dataset.periodo === period.value);
        });
        renderActiveFilters();
    }

    function renderActiveFilters() {
        const filters = filtersObject();
        const badges = [];
        badges.push('<span class="fa-filter-badge"><i class="bx bx-calendar"></i>' + escapeHtml(filters.periodoLabel) + '</span>');

        if (filters.foco !== 'geral') {
            badges.push('<span class="fa-filter-badge"><i class="bx bx-target-lock"></i>' + escapeHtml(filters.focoLabel) + '</span>');
        }
        if (filters.operador) {
            badges.push('<span class="fa-filter-badge"><i class="bx bx-user"></i>' + escapeHtml(filters.operador) + '</span>');
        }
        if (filters.forma) {
            badges.push('<span class="fa-filter-badge"><i class="bx bx-credit-card"></i>' + escapeHtml(filters.formaLabel) + '</span>');
        }
        if (filters.statusCaixa) {
            badges.push('<span class="fa-filter-badge"><i class="bx bx-store"></i>' + escapeHtml(filters.statusCaixaLabel) + '</span>');
        }
        if (filters.caixaId) {
            badges.push('<span class="fa-filter-badge"><i class="bx bx-hash"></i>Caixa ' + escapeHtml(filters.caixaId) + '</span>');
        }
        if (filters.comparacao) {
            badges.push('<span class="fa-filter-badge"><i class="bx bx-git-compare"></i>' + escapeHtml(filters.comparacaoLabel) + '</span>');
        }

        activeFilters.innerHTML = badges.join('');
    }

    function buildFilteredQuestion(question) {
        const f = filtersObject();
        const lines = [
            'FILTROS OBRIGATÓRIOS SELECIONADOS PELO ADMINISTRADOR:',
            '- Período da análise: ' + f.periodoLabel,
            '- Foco principal: ' + f.focoLabel
        ];

        if (f.data) lines.push('- Data exata: ' + f.data);
        if (f.operador) lines.push('- Operador/usuário: ' + f.operador);
        if (f.forma) lines.push('- Forma de pagamento: ' + f.formaLabel + ' (código ' + f.forma + ')');
        if (f.statusCaixa) lines.push('- Status do caixa: ' + f.statusCaixaLabel);
        if (f.caixaId) lines.push('- Caixa ID: ' + f.caixaId);
        if (f.comparacao) lines.push('- Comparação solicitada: ' + f.comparacaoLabel);

        lines.push('- Estes filtros têm prioridade sobre referências genéricas de tempo existentes em perguntas rápidas, como “hoje”.');
        lines.push('- Use somente os dados compatíveis com o recorte selecionado. Se o contexto não possuir detalhe suficiente para algum filtro, informe claramente a limitação e não invente valores.');
        lines.push('- Mantenha o isolamento por empresa_id da sessão e não consulte outra empresa.');
        lines.push('');
        lines.push('PERGUNTA DO ADMINISTRADOR:');
        lines.push(question);

        return lines.join('\n');
    }

    function filterSummary() {
        const f = filtersObject();
        const parts = [f.periodoLabel];
        if (f.foco !== 'geral') parts.push(f.focoLabel);
        if (f.operador) parts.push('Operador: ' + f.operador);
        if (f.forma) parts.push(f.formaLabel);
        if (f.statusCaixa) parts.push(f.statusCaixaLabel);
        if (f.caixaId) parts.push('Caixa ' + f.caixaId);
        return parts.join(' · ');
    }

    function renderAnswer(text) {
        let safe = escapeHtml(text || 'Nenhuma informação encontrada.');

        safe = safe
            .replace(/^###\s+(.+)$/gm, '<h6 class="mt-4 mb-2">$1</h6>')
            .replace(/^##\s+(.+)$/gm, '<h5 class="mt-4 mb-2">$1</h5>')
            .replace(/^#\s+(.+)$/gm, '<h4 class="mt-4 mb-3">$1</h4>')
            .replace(/^(Resumo executivo|Resumo|Indicadores|Diagnóstico|Pontos de atenção|Oportunidades|Plano de ação|Próxima ação|Caixas|Caixas do dia|Entradas|Saídas|Despesas|Despesas por categoria|Formas de pagamento|Contas a receber|Contas a pagar|Capital de giro|Inadimplência|DRE|Resultado)\s*:\s*$/gmi, '<h5 class="mt-4 mb-2">$1</h5>')
            .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
            .replace(/^[-•]\s+(.+)$/gm, '<div class="fa-answer-line d-flex gap-2"><span class="text-primary">•</span><span>$1</span></div>');

        safe = safe.replace(
            /(https?:\/\/[^\s<]+)/g,
            '<div class="mt-2 mb-2"><a href="$1" class="btn btn-sm btn-outline-primary" target="_self"><i class="bx bx-link-external me-1"></i>Abrir tela</a></div>'
        );

        result.innerHTML = safe.replace(/\n/g, '<br>');
    }

    function getHistory() {
        try {
            return JSON.parse(localStorage.getItem(historyKey) || '[]');
        } catch (e) {
            return [];
        }
    }

    function saveHistory(question) {
        let items = getHistory().filter(item => item !== question);
        items.unshift(question);
        items = items.slice(0, 7);
        localStorage.setItem(historyKey, JSON.stringify(items));
        renderHistory();
    }

    function renderHistory() {
        const items = getHistory();
        historyBox.innerHTML = '';

        if (!items.length) {
            historyBox.innerHTML = '<div class="fa-muted small">Suas últimas perguntas aparecerão aqui neste navegador.</div>';
            return;
        }

        items.forEach(function (question) {
            const item = document.createElement('button');
            item.type = 'button';
            item.className = 'fa-history-item';
            item.innerHTML = '<i class="bx bx-history text-muted me-2"></i>' + escapeHtml(question);
            item.addEventListener('click', function () {
                pesquisar(question);
            });
            historyBox.appendChild(item);
        });
    }

    async function pesquisar(question) {
        question = (question || '').trim();
        if (question.length < 3) {
            input.focus();
            return;
        }

        input.value = question;
        currentQuestion.textContent = filterSummary() + ' — ' + question;
        saveHistory(question);

        const original = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<i class="bx bx-loader-alt bx-spin me-1"></i> Cruzando dados...';
        box.classList.remove('d-none');
        initial.classList.add('d-none');
        result.innerHTML = '<div class="d-flex align-items-center gap-3 text-muted py-3"><span class="spinner-border spinner-border-sm text-primary"></span><span>Analisando o financeiro com os filtros selecionados...</span></div>';
        box.scrollIntoView({ behavior: 'smooth', block: 'start' });

        const formData = new FormData(form);
        formData.set('pergunta', buildFilteredQuestion(question));

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value
                },
                body: formData
            });

            const data = await response.json().catch(() => ({}));
            if (!response.ok) {
                throw new Error(data.mensagem || data.message || 'Não foi possível realizar a consulta.');
            }

            renderAnswer(data.resposta || 'Nenhuma informação encontrada.');
        } catch (error) {
            result.innerHTML = '<div class="alert alert-danger mb-0"><i class="bx bx-error-circle me-1"></i>' + escapeHtml(error.message || 'Não foi possível realizar a consulta.') + '</div>';
        } finally {
            button.disabled = false;
            button.innerHTML = original;
        }
    }

    document.querySelectorAll('.fa-period-btn').forEach(function (item) {
        item.addEventListener('click', function () {
            period.value = this.dataset.periodo;
            updatePeriodUI();
        });
    });

    [period, specificDate, focus, payment, cashStatus, comparison].forEach(function (field) {
        field.addEventListener('change', function () {
            if (field === period) updatePeriodUI();
            else renderActiveFilters();
        });
    });

    [operator, cashId].forEach(function (field) {
        field.addEventListener('input', renderActiveFilters);
    });

    clearFilters.addEventListener('click', function () {
        period.value = 'hoje';
        focus.value = 'geral';
        operator.value = '';
        payment.value = '';
        cashStatus.value = '';
        cashId.value = '';
        comparison.value = '';
        specificDate.value = '{{ date('Y-m-d') }}';
        updatePeriodUI();
    });

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        pesquisar(input.value);
    });

    input.addEventListener('keydown', function (event) {
        if (event.ctrlKey && event.key === 'Enter') {
            event.preventDefault();
            pesquisar(input.value);
        }
    });

    document.querySelectorAll('.pergunta-rapida').forEach(function (item) {
        item.addEventListener('click', function () {
            pesquisar(this.dataset.pergunta || this.textContent);
        });
    });

    clear.addEventListener('click', function () {
        result.innerHTML = '';
        box.classList.add('d-none');
        initial.classList.remove('d-none');
        input.value = '';
        input.focus();
    });

    copy.addEventListener('click', async function () {
        const text = result.innerText.trim();
        if (!text) return;

        try {
            await navigator.clipboard.writeText(text);
            const original = copy.innerHTML;
            copy.innerHTML = '<i class="bx bx-check me-1"></i> Copiado';
            setTimeout(() => copy.innerHTML = original, 1400);
        } catch (e) {
            // Alguns navegadores bloqueiam clipboard fora de HTTPS.
        }
    });

    clearHistory.addEventListener('click', function () {
        localStorage.removeItem(historyKey);
        renderHistory();
    });

    updatePeriodUI();
    renderHistory();
})();
</script>
@endsection