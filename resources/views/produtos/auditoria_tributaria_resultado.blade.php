@extends('default.layout', ['title' => 'Resultado da auditoria tributária'])

@section('content')
@php
    $total = count($results);
    $corretos = collect($results)->filter(fn ($item) => !$item['erro'] && data_get($item, 'analise.status') === 'correto')->count();
    $revisoes = collect($results)->filter(fn ($item) => !$item['erro'] && data_get($item, 'analise.status') === 'revisao')->count();
    $incorretos = collect($results)->filter(fn ($item) => !$item['erro'] && data_get($item, 'analise.status') === 'incorreto')->count();
    $falhas = collect($results)->filter(fn ($item) => !empty($item['erro']))->count();
@endphp

<style>
.tax-audit-page {
    --audit-primary: #2563eb;
    --audit-success: #16a34a;
    --audit-warning: #d97706;
    --audit-danger: #dc2626;
    --audit-muted: #64748b;
    --audit-border: #e2e8f0;
    --audit-surface: #ffffff;
    --audit-bg: #f8fafc;
}

.tax-audit-page .audit-hero {
    background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 58%, #2563eb 100%);
    border-radius: 18px;
    padding: 26px;
    color: #fff;
    box-shadow: 0 14px 35px rgba(15, 23, 42, .18);
    margin-bottom: 22px;
    position: relative;
    overflow: hidden;
}

.tax-audit-page .audit-hero::after {
    content: '';
    width: 230px;
    height: 230px;
    border-radius: 50%;
    position: absolute;
    right: -80px;
    top: -110px;
    background: rgba(255, 255, 255, .08);
}

.tax-audit-page .audit-hero h2 {
    color: #fff;
    font-weight: 800;
    margin: 0 0 7px;
}

.tax-audit-page .audit-hero p {
    margin: 0;
    color: rgba(255, 255, 255, .82);
    max-width: 720px;
}

.tax-audit-page .hero-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    position: relative;
    z-index: 2;
}

.tax-audit-page .hero-actions .btn {
    border-radius: 10px;
    font-weight: 600;
}

.tax-audit-page .btn-hero-light {
    background: #fff;
    color: #1d4ed8;
    border: 1px solid #fff;
}

.tax-audit-page .btn-hero-outline {
    color: #fff;
    border: 1px solid rgba(255,255,255,.45);
    background: rgba(255,255,255,.06);
}

.tax-audit-page .audit-summary-grid {
    display: grid;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap: 14px;
    margin-bottom: 22px;
}

.tax-audit-page .summary-card {
    background: var(--audit-surface);
    border: 1px solid var(--audit-border);
    border-radius: 15px;
    padding: 17px;
    box-shadow: 0 5px 18px rgba(15, 23, 42, .05);
    display: flex;
    align-items: center;
    gap: 12px;
}

.tax-audit-page .summary-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    flex: 0 0 44px;
}

.tax-audit-page .summary-card strong {
    display: block;
    font-size: 24px;
    line-height: 1;
    color: #0f172a;
}

.tax-audit-page .summary-card small {
    color: var(--audit-muted);
    font-weight: 600;
}

.tax-audit-page .summary-total .summary-icon { background: #dbeafe; color: #1d4ed8; }
.tax-audit-page .summary-success .summary-icon { background: #dcfce7; color: #15803d; }
.tax-audit-page .summary-warning .summary-icon { background: #fef3c7; color: #b45309; }
.tax-audit-page .summary-danger .summary-icon { background: #fee2e2; color: #b91c1c; }
.tax-audit-page .summary-error .summary-icon { background: #f1f5f9; color: #475569; }

.tax-audit-page .human-warning {
    border: 1px solid #fde68a;
    background: #fffbeb;
    color: #92400e;
    border-radius: 14px;
    padding: 15px 17px;
    display: flex;
    gap: 12px;
    align-items: flex-start;
    margin-bottom: 22px;
}

.tax-audit-page .human-warning i {
    font-size: 23px;
    margin-top: 1px;
}

.tax-audit-page .product-audit-card {
    background: #fff;
    border: 1px solid var(--audit-border);
    border-radius: 17px;
    overflow: hidden;
    margin-bottom: 18px;
    box-shadow: 0 7px 24px rgba(15, 23, 42, .06);
}

.tax-audit-page .product-audit-card.status-correto { border-left: 5px solid var(--audit-success); }
.tax-audit-page .product-audit-card.status-revisao { border-left: 5px solid var(--audit-warning); }
.tax-audit-page .product-audit-card.status-incorreto { border-left: 5px solid var(--audit-danger); }
.tax-audit-page .product-audit-card.status-erro { border-left: 5px solid #64748b; }

.tax-audit-page .product-head {
    padding: 18px 20px;
    background: linear-gradient(180deg, #fff 0%, #f8fafc 100%);
    border-bottom: 1px solid #eef2f7;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 16px;
}

.tax-audit-page .product-title {
    min-width: 0;
}

.tax-audit-page .product-title h4 {
    font-size: 18px;
    font-weight: 800;
    color: #0f172a;
    margin: 0 0 5px;
}

.tax-audit-page .product-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 7px 14px;
    color: #64748b;
    font-size: 12px;
}

.tax-audit-page .audit-status {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border-radius: 999px;
    padding: 7px 11px;
    font-size: 12px;
    font-weight: 800;
    white-space: nowrap;
}

.tax-audit-page .audit-status.correto { background: #dcfce7; color: #166534; }
.tax-audit-page .audit-status.revisao { background: #fef3c7; color: #92400e; }
.tax-audit-page .audit-status.incorreto { background: #fee2e2; color: #991b1b; }
.tax-audit-page .audit-status.erro { background: #e2e8f0; color: #334155; }

.tax-audit-page .product-body {
    padding: 20px;
}

.tax-audit-page .analysis-lead {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 13px;
    padding: 15px 16px;
    color: #334155;
    line-height: 1.55;
    margin-bottom: 18px;
}

.tax-audit-page .confidence-wrap {
    min-width: 220px;
    max-width: 300px;
}

.tax-audit-page .confidence-label {
    display: flex;
    justify-content: space-between;
    font-size: 11px;
    font-weight: 700;
    color: #64748b;
    margin-bottom: 5px;
}

.tax-audit-page .confidence-bar {
    height: 7px;
    background: #e2e8f0;
    border-radius: 999px;
    overflow: hidden;
}

.tax-audit-page .confidence-fill {
    height: 100%;
    border-radius: 999px;
    background: linear-gradient(90deg, #3b82f6, #2563eb);
}

.tax-audit-page .section-title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: .04em;
    color: #475569;
    font-weight: 800;
    margin: 0 0 10px;
}

.tax-audit-page .problem-list {
    list-style: none;
    padding: 0;
    margin: 0 0 18px;
}

.tax-audit-page .problem-list li {
    display: flex;
    gap: 9px;
    align-items: flex-start;
    padding: 10px 12px;
    background: #fff7ed;
    border: 1px solid #fed7aa;
    border-radius: 10px;
    color: #9a3412;
    margin-bottom: 8px;
    font-size: 13px;
}

.tax-audit-page .no-problems {
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    color: #166534;
    border-radius: 10px;
    padding: 11px 13px;
    margin-bottom: 18px;
    font-size: 13px;
}

.tax-audit-page .comparison-table {
    border-collapse: separate;
    border-spacing: 0;
    overflow: hidden;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    margin-bottom: 0;
}

.tax-audit-page .comparison-table thead th {
    background: #f8fafc;
    color: #475569;
    border-bottom: 1px solid #e2e8f0;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: .04em;
    font-weight: 800;
    padding: 11px 13px;
}

.tax-audit-page .comparison-table td {
    padding: 11px 13px;
    vertical-align: middle;
    border-top: 1px solid #f1f5f9;
    font-size: 13px;
}

.tax-audit-page .comparison-table tbody tr:first-child td { border-top: none; }
.tax-audit-page .comparison-table .field-name { font-weight: 700; color: #334155; }
.tax-audit-page .value-current {
    display: inline-block;
    background: #f1f5f9;
    color: #475569;
    padding: 4px 8px;
    border-radius: 7px;
    font-family: monospace;
}

.tax-audit-page .value-suggested {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: #eff6ff;
    color: #1d4ed8;
    padding: 4px 8px;
    border-radius: 7px;
    font-weight: 700;
    font-family: monospace;
}

.tax-audit-page .value-same {
    color: #64748b;
    font-size: 12px;
}

.tax-audit-page .card-actions {
    display: flex;
    justify-content: flex-end;
    gap: 8px;
    padding-top: 16px;
}

.tax-audit-page .card-actions .btn {
    border-radius: 9px;
    font-weight: 600;
}

.tax-audit-page .error-box {
    background: #fff7ed;
    border: 1px solid #fed7aa;
    color: #9a3412;
    border-radius: 13px;
    padding: 16px;
    display: flex;
    align-items: flex-start;
    gap: 12px;
}

.tax-audit-page .error-box i {
    font-size: 24px;
    flex: 0 0 auto;
}

.tax-audit-page .error-box strong {
    display: block;
    margin-bottom: 3px;
}

@media (max-width: 1100px) {
    .tax-audit-page .audit-summary-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
}

@media (max-width: 768px) {
    .tax-audit-page .audit-hero { padding: 20px; border-radius: 14px; }
    .tax-audit-page .audit-summary-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .tax-audit-page .product-head { flex-direction: column; }
    .tax-audit-page .confidence-wrap { min-width: 100%; max-width: 100%; }
    .tax-audit-page .card-actions { justify-content: stretch; }
    .tax-audit-page .card-actions .btn { width: 100%; }
}

@media (max-width: 480px) {
    .tax-audit-page .audit-summary-grid { grid-template-columns: 1fr; }
    .tax-audit-page .summary-card { padding: 14px; }
}
</style>

<div class="container-fluid py-4 tax-audit-page">
    <section class="audit-hero">
        <div class="d-flex flex-wrap justify-content-between align-items-center" style="gap: 18px; position:relative; z-index:2;">
            <div>
                <div class="mb-2" style="font-size:12px; text-transform:uppercase; letter-spacing:.12em; font-weight:800; opacity:.75;">
                    <i class="bx bx-bot"></i> Auditoria fiscal assistida por IA
                </div>
                <h2>Resultado da análise tributária</h2>
                <p>Compare o cadastro atual dos produtos com as sugestões encontradas e revise somente o que realmente precisa de atenção.</p>
            </div>
            <div class="hero-actions">
                <a href="{{ route('produtos.auditoria-tributaria') }}" class="btn btn-hero-light">
                    <i class="bx bx-refresh"></i> Nova análise
                </a>
                <a href="{{ route('produtos.index') }}" class="btn btn-hero-outline">
                    <i class="bx bx-package"></i> Produtos
                </a>
            </div>
        </div>
    </section>

    <div class="audit-summary-grid">
        <div class="summary-card summary-total">
            <div class="summary-icon"><i class="bx bx-scan"></i></div>
            <div><strong>{{ $total }}</strong><small>Analisados</small></div>
        </div>
        <div class="summary-card summary-success">
            <div class="summary-icon"><i class="bx bx-check-circle"></i></div>
            <div><strong>{{ $corretos }}</strong><small>Corretos</small></div>
        </div>
        <div class="summary-card summary-warning">
            <div class="summary-icon"><i class="bx bx-search-alt"></i></div>
            <div><strong>{{ $revisoes }}</strong><small>Para revisar</small></div>
        </div>
        <div class="summary-card summary-danger">
            <div class="summary-icon"><i class="bx bx-error-circle"></i></div>
            <div><strong>{{ $incorretos }}</strong><small>Inconsistentes</small></div>
        </div>
        <div class="summary-card summary-error">
            <div class="summary-icon"><i class="bx bx-cloud-off"></i></div>
            <div><strong>{{ $falhas }}</strong><small>Não analisados</small></div>
        </div>
    </div>

    <div class="human-warning">
        <i class="bx bx-info-circle"></i>
        <div>
            <strong>Conferência humana obrigatória</strong>
            <div style="font-size:13px; margin-top:2px;">A IA apenas aponta possíveis inconsistências. Nenhuma sugestão abaixo altera automaticamente o cadastro do produto. Confirme regime tributário, UF, operação e legislação antes de salvar qualquer mudança.</div>
        </div>
    </div>

    @foreach($results as $result)
        @php
            $produto = $result['produto'];
            $analysis = $result['analise'] ?? null;
            $status = $result['erro'] ? 'erro' : ($analysis['status'] ?? 'revisao');
            $confianca = max(0, min(100, (int) ($analysis['confianca'] ?? 0)));
            $statusLabel = [
                'correto' => 'Cadastro coerente',
                'revisao' => 'Revisão recomendada',
                'incorreto' => 'Possível inconsistência',
                'erro' => 'Não analisado',
            ][$status] ?? 'Revisão recomendada';

            $statusIcon = [
                'correto' => 'bx-check-circle',
                'revisao' => 'bx-search-alt',
                'incorreto' => 'bx-error-circle',
                'erro' => 'bx-cloud-off',
            ][$status] ?? 'bx-search-alt';

            $campos = [
                'ncm' => ['NCM', $produto->NCM],
                'cest' => ['CEST', $produto->CEST],
                'cst_csosn' => ['CST / CSOSN', $produto->CST_CSOSN],
                'cfop_saida_interna' => ['CFOP saída interna', $produto->CFOP_saida_estadual],
                'cfop_saida_interestadual' => ['CFOP saída interestadual', $produto->CFOP_saida_inter_estadual],
                'cfop_entrada_interna' => ['CFOP entrada interna', $produto->CFOP_entrada_estadual],
                'cfop_entrada_interestadual' => ['CFOP entrada interestadual', $produto->CFOP_entrada_inter_estadual],
            ];
        @endphp

        <article class="product-audit-card status-{{ $status }}">
            <header class="product-head">
                <div class="product-title">
                    <h4>{{ $produto->nome }}</h4>
                    <div class="product-meta">
                        <span><i class="bx bx-hash"></i> ID {{ $produto->id }}</span>
                        @if(optional($produto->categoria)->nome)
                            <span><i class="bx bx-category"></i> {{ optional($produto->categoria)->nome }}</span>
                        @endif
                        @if($produto->codBarras)
                            <span><i class="bx bx-barcode"></i> {{ $produto->codBarras }}</span>
                        @endif
                    </div>
                </div>

                <div class="d-flex flex-column align-items-end" style="gap:10px; min-width:220px;">
                    <span class="audit-status {{ $status }}">
                        <i class="bx {{ $statusIcon }}"></i> {{ $statusLabel }}
                    </span>

                    @if(!$result['erro'] && $analysis)
                        <div class="confidence-wrap">
                            <div class="confidence-label">
                                <span>Confiança da análise</span>
                                <strong>{{ $confianca }}%</strong>
                            </div>
                            <div class="confidence-bar">
                                <div class="confidence-fill" style="width: {{ $confianca }}%;"></div>
                            </div>
                        </div>
                    @endif
                </div>
            </header>

            <div class="product-body">
                @if($result['erro'])
                    <div class="error-box">
                        <i class="bx bx-error-alt"></i>
                        <div>
                            <strong>Não foi possível concluir a análise deste produto.</strong>
                            <div>{{ $result['erro'] }}</div>
                            <small style="display:block; margin-top:7px; opacity:.8;">Você pode tentar novamente sem perder nenhuma informação do cadastro.</small>
                        </div>
                    </div>
                @else
                    <div class="analysis-lead">
                        <strong style="display:block; color:#0f172a; margin-bottom:4px;">Resumo da IA</strong>
                        {{ $analysis['resumo'] ?? 'A análise foi concluída, mas não retornou um resumo.' }}
                    </div>

                    <h6 class="section-title"><i class="bx bx-list-check"></i> Pontos encontrados</h6>
                    @if(!empty($analysis['problemas']))
                        <ul class="problem-list">
                            @foreach($analysis['problemas'] as $problema)
                                <li><i class="bx bx-error-circle" style="margin-top:1px;"></i><span>{{ $problema }}</span></li>
                            @endforeach
                        </ul>
                    @else
                        <div class="no-problems"><i class="bx bx-check-circle"></i> Nenhuma inconsistência relevante foi apontada nos campos analisados.</div>
                    @endif

                    <h6 class="section-title"><i class="bx bx-git-compare"></i> Cadastro atual x sugestão</h6>
                    <div class="table-responsive">
                        <table class="table comparison-table">
                            <thead>
                                <tr>
                                    <th style="width:31%;">Campo fiscal</th>
                                    <th style="width:29%;">Cadastro atual</th>
                                    <th style="width:40%;">Sugestão da IA</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($campos as $key => [$label, $current])
                                    @php
                                        $suggested = data_get($analysis, 'sugestoes.' . $key);
                                        $currentNormalized = trim((string) $current);
                                        $suggestedNormalized = trim((string) $suggested);
                                        $changed = $suggestedNormalized !== '' && $suggestedNormalized !== $currentNormalized;
                                    @endphp
                                    <tr>
                                        <td class="field-name">{{ $label }}</td>
                                        <td><span class="value-current">{{ $currentNormalized !== '' ? $currentNormalized : 'Não informado' }}</span></td>
                                        <td>
                                            @if($changed)
                                                <span class="value-suggested"><i class="bx bx-right-arrow-alt"></i> {{ $suggestedNormalized }}</span>
                                            @elseif($suggestedNormalized !== '')
                                                <span class="value-same"><i class="bx bx-check"></i> Manter {{ $suggestedNormalized }}</span>
                                            @else
                                                <span class="value-same"><i class="bx bx-minus"></i> Sem alteração sugerida</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                <div class="card-actions">
                    <a href="{{ route('produtos.edit', $produto->id) }}" class="btn btn-primary btn-sm">
                        <i class="bx bx-edit-alt"></i> Revisar cadastro do produto
                    </a>
                </div>
            </div>
        </article>
    @endforeach

    @if($total === 0)
        <div class="text-center py-5 text-muted">
            <i class="bx bx-search-alt" style="font-size:48px;"></i>
            <h5 class="mt-2">Nenhum resultado para exibir</h5>
            <p>Volte para a auditoria e selecione os produtos que deseja analisar.</p>
            <a href="{{ route('produtos.auditoria-tributaria') }}" class="btn btn-primary">Selecionar produtos</a>
        </div>
    @endif
</div>
@endsection