<section class="mb-5" id="resumo-gerencial">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <div>
            <h4 class="fw-bold mb-1">Resumo executivo</h4>
            <p class="text-muted mb-0">
                Indicadores do período comparados com o intervalo anterior equivalente.
            </p>
        </div>
        <span class="badge bg-light text-dark border px-3 py-2 periodo-comparacao">
            Analisando dados...
        </span>
    </div>

    <div class="alert alert-danger d-none dashboard-gerencial-erro" role="alert"></div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-md-6 col-xl-2">
            <div class="card card-soft h-100 bg-white">
                <div class="card-body p-3">
                    <p class="text-muted small mb-1">Saúde do negócio</p>
                    <div class="d-flex align-items-end justify-content-between gap-2">
                        <h3 class="fw-bold mb-0 saude-score">--</h3>
                        <span class="badge saude-classificacao bg-secondary">Calculando</span>
                    </div>
                    <div class="progress mt-3" style="height: 6px;">
                        <div class="progress-bar saude-progress" role="progressbar" style="width: 0%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-2">
            <div class="card card-soft h-100 bg-white">
                <div class="card-body p-3">
                    <p class="text-muted small mb-1">Faturamento</p>
                    <h5 class="fw-bold mb-1 gerencial-faturamento">R$ 0,00</h5>
                    <span class="small gerencial-faturamento-variacao text-muted">Sem comparação</span>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-2">
            <div class="card card-soft h-100 bg-white">
                <div class="card-body p-3">
                    <p class="text-muted small mb-1">Ticket médio</p>
                    <h5 class="fw-bold mb-1 gerencial-ticket">R$ 0,00</h5>
                    <span class="small gerencial-ticket-variacao text-muted">Sem comparação</span>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-2">
            <div class="card card-soft h-100 bg-white">
                <div class="card-body p-3">
                    <p class="text-muted small mb-1">Margem bruta estimada</p>
                    <h5 class="fw-bold mb-1 gerencial-margem">0,0%</h5>
                    <span class="small text-muted gerencial-lucro">Lucro bruto: R$ 0,00</span>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-2">
            <div class="card card-soft h-100 bg-white">
                <div class="card-body p-3">
                    <p class="text-muted small mb-1">Resultado estimado</p>
                    <h5 class="fw-bold mb-1 gerencial-resultado">R$ 0,00</h5>
                    <span class="small text-muted">Lucro bruto menos despesas pagas</span>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-2">
            <div class="card card-soft h-100 bg-white">
                <div class="card-body p-3">
                    <p class="text-muted small mb-1">Inadimplência</p>
                    <h5 class="fw-bold mb-1 gerencial-inadimplencia">0,0%</h5>
                    <span class="small text-muted gerencial-vencido">Vencido: R$ 0,00</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12 col-xl-7">
            <div class="card card-soft bg-white h-100">
                <div class="card-header bg-transparent border-0 pb-0">
                    <h5 class="fw-bold mb-1">Decisões recomendadas</h5>
                    <p class="text-muted small mb-0">Alertas gerados pelas métricas do período.</p>
                </div>
                <div class="card-body">
                    <div class="recomendacoes-gerenciais">
                        <div class="text-center text-muted py-4">
                            <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                            Analisando indicadores...
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-5">
            <div class="card card-soft bg-white h-100">
                <div class="card-header bg-transparent border-0 pb-0">
                    <h5 class="fw-bold mb-1">Produtos que mais geram receita</h5>
                    <p class="text-muted small mb-0">Participação no faturamento do período.</p>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Produto</th>
                                    <th class="text-end">Receita</th>
                                    <th class="text-end pe-3">Participação</th>
                                </tr>
                            </thead>
                            <tbody class="produtos-top-gerencial">
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">Carregando...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
