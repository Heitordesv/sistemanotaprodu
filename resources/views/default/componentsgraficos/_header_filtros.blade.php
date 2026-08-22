<div class="dashboard-header mb-4">
    <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3 mb-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="dashboard-eyebrow">Visão gerencial</span>
                <span class="badge rounded-pill text-bg-light border" id="dashboard-period-label">Período atual</span>
            </div>
            <h2 class="dashboard-title mb-1">Desempenho do negócio</h2>
            <p class="dashboard-subtitle mb-0">Acompanhe vendas, financeiro, produtos e ordens de serviço em uma única tela.</p>
        </div>

        <div class="d-flex align-items-center gap-2 text-muted small">
            <span class="dashboard-live-dot"></span>
            <span id="dashboard-last-update">Carregando indicadores...</span>
        </div>
    </div>

    <div id="dashboard-alert" class="alert alert-danger border-0 shadow-sm d-none mb-3" role="alert">
        <div class="d-flex align-items-center gap-2">
            <i class="bx bx-error-circle fs-4"></i>
            <span id="dashboard-alert-text"></span>
        </div>
    </div>

    <div class="dashboard-filter-card">
        <div class="row g-3 align-items-end">
            <div class="col-12 col-lg-4">
                <label class="dashboard-label">Períodos rápidos</label>
                <div class="dashboard-period-buttons" role="group" aria-label="Selecionar período">
                    <button type="button" class="btn btn-sm js-periodo" data-periodo="hoje">Hoje</button>
                    <button type="button" class="btn btn-sm js-periodo" data-periodo="7d">7 dias</button>
                    <button type="button" class="btn btn-sm js-periodo active" data-periodo="mes">Este mês</button>
                    <button type="button" class="btn btn-sm js-periodo" data-periodo="30d">30 dias</button>
                </div>
            </div>

            <div class="col-6 col-md-3 col-lg-2">
                <label for="data_inicial" class="dashboard-label">Data inicial</label>
                <input type="date" id="data_inicial" class="form-control dashboard-input" value="{{ date('Y-m-01') }}">
            </div>

            <div class="col-6 col-md-3 col-lg-2">
                <label for="data_final" class="dashboard-label">Data final</label>
                <input type="date" id="data_final" class="form-control dashboard-input" value="{{ date('Y-m-d') }}">
            </div>

            @if(empresaComFilial() && sizeof(getLocaisUsarioLogado()) > 0)
                <div class="col-12 col-md-3 col-lg-2 dashboard-location-select">
                    <label class="dashboard-label">Local</label>
                    {!! __view_locais_select_home() !!}
                </div>
            @endif

            <div class="col-12 col-md-3 col-lg-2 ms-lg-auto">
                <button type="button" id="dashboard-filter" class="btn btn-primary w-100 dashboard-filter-button">
                    <span class="spinner-border spinner-border-sm me-1 d-none" role="status" aria-hidden="true"></span>
                    <i class="bx bx-filter-alt me-1"></i>
                    <span class="filter-label">Aplicar filtros</span>
                </button>
            </div>
        </div>

        @if(empresaComFilial() && sizeof(getLocaisUsarioLogado()) > 0)
            <div class="d-flex justify-content-end mt-2">
                <button id="set-location" type="button" class="btn btn-link btn-sm text-decoration-none px-0">
                    <i class="bx bx-map-pin me-1"></i>Salvar local selecionado como padrão
                </button>
            </div>
        @endif
    </div>
</div>