<div class="modal fade" id="modalFunil" tabindex="-1" aria-labelledby="modalFunilLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-3 overflow-hidden" id="conteudoParaPrint">
            <div class="modal-header bg-dark text-white border-0 px-4 py-3">
                <div>
                    <h5 class="modal-title fw-bold" id="modalFunilLabel">
                        <i class="fas fa-chart-line me-2"></i>Diagnóstico do Funil
                    </h5>
                    <small class="text-white-50">Visão rápida da distribuição e conversão dos leads exibidos.</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>

            @php
                $status = $statusCounts ?? [];
                $novo = (int) ($status['Novo'] ?? 0);
                $contato = (int) ($status['Em Contato'] ?? 0);
                $qualificado = (int) ($status['Qualificado'] ?? 0);
                $convertido = (int) ($status['Convertido'] ?? 0);
                $descartado = (int) ($status['Descartado'] ?? 0);
                $total = max($novo + $contato + $qualificado + $convertido + $descartado, 1);
                $taxaConversao = ($convertido / $total) * 100;
                $taxaDescarte = ($descartado / $total) * 100;
            @endphp

            <div class="modal-body bg-light p-4">
                <div class="row g-3 mb-4">
                    @foreach([
                        ['label' => 'Novos', 'valor' => $novo, 'cor' => 'info', 'icone' => 'fas fa-user-plus'],
                        ['label' => 'Em contato', 'valor' => $contato, 'cor' => 'warning', 'icone' => 'fas fa-comments'],
                        ['label' => 'Qualificados', 'valor' => $qualificado, 'cor' => 'primary', 'icone' => 'fas fa-bullseye'],
                        ['label' => 'Convertidos', 'valor' => $convertido, 'cor' => 'success', 'icone' => 'fas fa-check-circle'],
                        ['label' => 'Descartados', 'valor' => $descartado, 'cor' => 'danger', 'icone' => 'fas fa-times-circle'],
                    ] as $item)
                        <div class="col-6 col-lg">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center justify-content-between gap-2">
                                        <div>
                                            <div class="small text-muted">{{ $item['label'] }}</div>
                                            <div class="h4 fw-bold mb-0">{{ $item['valor'] }}</div>
                                        </div>
                                        <span class="text-{{ $item['cor'] }} fs-4"><i class="{{ $item['icone'] }}"></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="row g-4">
                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body p-4">
                                <h6 class="fw-bold mb-4">Pipeline comercial</h6>

                                @foreach([
                                    ['label' => 'Topo — Novos', 'valor' => $novo, 'cor' => 'info'],
                                    ['label' => 'Atendimento — Em contato', 'valor' => $contato, 'cor' => 'warning'],
                                    ['label' => 'Oportunidade — Qualificados', 'valor' => $qualificado, 'cor' => 'primary'],
                                    ['label' => 'Fechamento — Convertidos', 'valor' => $convertido, 'cor' => 'success'],
                                ] as $etapa)
                                    @php $percentual = ($etapa['valor'] / $total) * 100; @endphp
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between small mb-1">
                                            <span class="fw-semibold">{{ $etapa['label'] }}</span>
                                            <span>{{ $etapa['valor'] }} lead(s)</span>
                                        </div>
                                        <div class="progress" style="height: 10px;">
                                            <div class="progress-bar bg-{{ $etapa['cor'] }}" role="progressbar" style="width: {{ min(100, $percentual) }}%;" aria-valuenow="{{ $percentual }}" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body p-4">
                                <h6 class="fw-bold mb-4">Indicadores</h6>
                                <div class="row g-3">
                                    <div class="col-sm-6">
                                        <div class="border rounded-3 p-3 h-100">
                                            <div class="small text-muted mb-1">Taxa de conversão</div>
                                            <div class="h3 text-success fw-bold mb-0">{{ number_format($taxaConversao, 1) }}%</div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="border rounded-3 p-3 h-100">
                                            <div class="small text-muted mb-1">Taxa de descarte</div>
                                            <div class="h3 text-danger fw-bold mb-0">{{ number_format($taxaDescarte, 1) }}%</div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="alert alert-light border mb-0">
                                            <div class="fw-semibold mb-1">Leitura rápida</div>
                                            <div class="small text-muted">
                                                O funil considera os leads disponíveis para o usuário atual. Use os cards e filtros da tela principal para aprofundar a análise.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-white border-0 px-4 py-3">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-outline-dark" onclick="window.print()">
                    <i class="fas fa-print me-1"></i> Imprimir
                </button>
            </div>
        </div>
    </div>
</div>