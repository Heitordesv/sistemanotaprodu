@extends('default.layout')

@section('content')
<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

<style>
    .saas-dashboard { --primary:#4f46e5; --primary-soft:#eef2ff; --success:#059669; --warning:#d97706; --danger:#e11d48; --muted:#64748b; --text:#0f172a; --border:#e2e8f0; --surface:#ffffff; --bg:#f8fafc; background:var(--bg); min-height:100vh; padding:24px; }
    .saas-dashboard * { box-sizing:border-box; }
    .saas-shell { max-width:1600px; margin:0 auto; }
    .saas-header { display:flex; align-items:flex-end; justify-content:space-between; gap:18px; margin-bottom:22px; }
    .saas-kicker { display:flex; align-items:center; gap:7px; color:var(--primary); font-size:11px; font-weight:800; letter-spacing:.12em; text-transform:uppercase; margin-bottom:7px; }
    .saas-title { color:var(--text); font-size:30px; line-height:1.15; font-weight:800; margin:0; }
    .saas-subtitle { color:var(--muted); margin:7px 0 0; font-size:13px; }
    .saas-filter { display:flex; align-items:flex-end; gap:9px; flex-wrap:wrap; background:var(--surface); border:1px solid var(--border); padding:10px; border-radius:16px; box-shadow:0 8px 24px rgba(15,23,42,.05); }
    .saas-filter label { display:block; font-size:9px; color:var(--muted); font-weight:800; text-transform:uppercase; margin:0 0 4px 4px; letter-spacing:.08em; }
    .saas-filter input { height:38px; border:1px solid var(--border); border-radius:10px; padding:0 10px; color:#334155; font-size:12px; background:#f8fafc; }
    .saas-btn { height:38px; border:0; border-radius:10px; padding:0 15px; background:var(--text); color:#fff; font-size:11px; font-weight:800; display:inline-flex; align-items:center; gap:6px; cursor:pointer; text-decoration:none; }
    .saas-btn:hover { background:var(--primary); color:#fff; }
    .saas-alert { display:flex; align-items:flex-start; gap:12px; border:1px solid #fde68a; background:#fffbeb; color:#92400e; padding:13px 15px; border-radius:14px; margin-bottom:18px; font-size:12px; }
    .saas-alert i { font-size:20px; margin-top:1px; }
    .kpi-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:14px; margin-bottom:16px; }
    .kpi-card { position:relative; overflow:hidden; background:var(--surface); border:1px solid var(--border); border-radius:18px; padding:18px; box-shadow:0 8px 28px rgba(15,23,42,.05); min-height:132px; }
    .kpi-card::after { content:''; position:absolute; width:72px; height:72px; border-radius:999px; right:-20px; top:-20px; background:var(--soft,#eef2ff); }
    .kpi-top { display:flex; align-items:flex-start; justify-content:space-between; gap:10px; position:relative; z-index:1; }
    .kpi-label { color:var(--muted); font-size:10px; font-weight:800; letter-spacing:.08em; text-transform:uppercase; }
    .kpi-icon { width:38px; height:38px; border-radius:11px; display:flex; align-items:center; justify-content:center; background:var(--soft,#eef2ff); color:var(--accent,var(--primary)); font-size:20px; }
    .kpi-value { color:var(--text); font-size:25px; line-height:1.1; font-weight:850; margin-top:14px; letter-spacing:-.02em; }
    .kpi-foot { margin-top:7px; color:var(--muted); font-size:10px; }
    .kpi-foot strong { color:var(--accent,var(--primary)); }
    .status-grid { display:grid; grid-template-columns:repeat(5,minmax(0,1fr)); gap:12px; margin-bottom:16px; }
    .status-card { background:var(--surface); border:1px solid var(--border); border-radius:15px; padding:14px 15px; display:flex; align-items:center; justify-content:space-between; gap:10px; }
    .status-card span { display:block; color:var(--muted); font-size:10px; font-weight:800; text-transform:uppercase; letter-spacing:.06em; }
    .status-card strong { display:block; color:var(--text); font-size:22px; line-height:1; margin-top:5px; }
    .status-dot { width:10px; height:10px; border-radius:999px; background:var(--dot,#94a3b8); box-shadow:0 0 0 6px var(--dot-soft,#f1f5f9); }
    .dashboard-grid { display:grid; grid-template-columns:minmax(0,1.65fr) minmax(320px,.85fr); gap:16px; margin-bottom:16px; }
    .panel { background:var(--surface); border:1px solid var(--border); border-radius:18px; box-shadow:0 8px 28px rgba(15,23,42,.04); overflow:hidden; }
    .panel-head { padding:17px 18px 13px; display:flex; align-items:center; justify-content:space-between; gap:12px; border-bottom:1px solid #f1f5f9; }
    .panel-title { margin:0; color:var(--text); font-size:13px; font-weight:800; }
    .panel-subtitle { color:var(--muted); font-size:10px; margin-top:3px; }
    .panel-body { padding:18px; }
    .badge-soft { display:inline-flex; align-items:center; gap:5px; padding:6px 9px; border-radius:999px; background:var(--primary-soft); color:var(--primary); font-size:9px; font-weight:800; text-transform:uppercase; letter-spacing:.05em; }
    .mini-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:10px; }
    .mini-card { border:1px solid var(--border); border-radius:14px; padding:13px; background:#fbfdff; }
    .mini-card .label { font-size:9px; color:var(--muted); font-weight:800; text-transform:uppercase; }
    .mini-card .value { margin-top:6px; color:var(--text); font-size:18px; font-weight:800; }
    .mini-card .hint { margin-top:4px; color:var(--muted); font-size:9px; }
    .progress-row { margin-bottom:14px; }
    .progress-row:last-child { margin-bottom:0; }
    .progress-meta { display:flex; justify-content:space-between; gap:10px; margin-bottom:6px; font-size:10px; color:var(--muted); }
    .progress-meta strong { color:#334155; }
    .progress-track { height:7px; background:#f1f5f9; border-radius:999px; overflow:hidden; }
    .progress-bar-saas { height:100%; border-radius:999px; background:var(--primary); }
    .table-wrap { overflow-x:auto; }
    .saas-table { width:100%; border-collapse:collapse; }
    .saas-table th { background:#f8fafc; color:#94a3b8; font-size:9px; text-transform:uppercase; letter-spacing:.06em; text-align:left; padding:11px 13px; white-space:nowrap; }
    .saas-table td { color:#475569; font-size:11px; padding:12px 13px; border-top:1px solid #f1f5f9; vertical-align:middle; }
    .saas-table td strong { color:#1e293b; }
    .status-badge { display:inline-flex; align-items:center; padding:5px 8px; border-radius:999px; font-size:9px; font-weight:800; text-transform:uppercase; }
    .status-authorized { background:#ecfdf5; color:#047857; }
    .status-paused { background:#fff7ed; color:#c2410c; }
    .status-cancelled { background:#fff1f2; color:#be123c; }
    .status-other { background:#f1f5f9; color:#475569; }
    .empty-state { padding:22px; text-align:center; color:#94a3b8; font-size:11px; }
    .finance-strip { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:12px; margin-bottom:16px; }
    .finance-card { border-radius:16px; padding:15px; color:#fff; background:linear-gradient(135deg,#334155,#0f172a); }
    .finance-card.green { background:linear-gradient(135deg,#10b981,#047857); }
    .finance-card.blue { background:linear-gradient(135deg,#6366f1,#4338ca); }
    .finance-card.red { background:linear-gradient(135deg,#f43f5e,#be123c); }
    .finance-card .label { font-size:9px; text-transform:uppercase; letter-spacing:.07em; opacity:.72; font-weight:800; }
    .finance-card .value { font-size:20px; margin-top:7px; font-weight:850; }
    .finance-card .hint { font-size:9px; margin-top:5px; opacity:.72; }
    @media (max-width:1199px) { .kpi-grid{grid-template-columns:repeat(2,1fr)} .status-grid{grid-template-columns:repeat(3,1fr)} .dashboard-grid{grid-template-columns:1fr} .finance-strip{grid-template-columns:repeat(2,1fr)} }
    @media (max-width:767px) { .saas-dashboard{padding:14px 10px} .saas-header{align-items:stretch; flex-direction:column} .saas-title{font-size:24px} .saas-filter{width:100%} .saas-filter>div{flex:1; min-width:130px} .saas-filter input{width:100%} .kpi-grid,.status-grid,.finance-strip,.mini-grid{grid-template-columns:1fr} .kpi-card{min-height:auto} }
</style>

<div class="saas-dashboard">
    <div class="saas-shell">
        <div class="saas-header">
            <div>
                <div class="saas-kicker"><i class='bx bxs-dashboard'></i> Administração SaaS</div>
                <h1 class="saas-title">Dashboard de Assinaturas</h1>
                <p class="saas-subtitle">Receita recorrente, clientes, trials, Mercado Pago e saúde financeira em um único painel.</p>
            </div>

            <form method="GET" class="saas-filter">
                <div>
                    <label>Início</label>
                    <input type="date" name="data_inicio" value="{{ request('data_inicio', $inicio->format('Y-m-d')) }}">
                </div>
                <div>
                    <label>Fim</label>
                    <input type="date" name="data_fim" value="{{ request('data_fim', $fim->format('Y-m-d')) }}">
                </div>
                <button class="saas-btn" type="submit"><i class='bx bx-filter-alt'></i> Aplicar</button>
            </form>
        </div>

        @if(!$assinaturasDisponiveis)
            <div class="saas-alert">
                <i class='bx bx-info-circle'></i>
                <div><strong>Subscriptions ainda não está disponível neste banco.</strong><br>O painel está usando os planos locais como fallback. Assim que a migration <code>subscriptions</code> for executada, os indicadores do Mercado Pago aparecem automaticamente.</div>
            </div>
        @endif

        <div class="kpi-grid">
            <div class="kpi-card" style="--accent:#4f46e5;--soft:#eef2ff">
                <div class="kpi-top"><div class="kpi-label">MRR</div><div class="kpi-icon"><i class='bx bx-dollar-circle'></i></div></div>
                <div class="kpi-value">R$ {{ number_format($mrr, 2, ',', '.') }}</div>
                <div class="kpi-foot">Receita recorrente mensal estimada</div>
            </div>
            <div class="kpi-card" style="--accent:#059669;--soft:#ecfdf5">
                <div class="kpi-top"><div class="kpi-label">ARR</div><div class="kpi-icon"><i class='bx bx-line-chart'></i></div></div>
                <div class="kpi-value">R$ {{ number_format($arr, 2, ',', '.') }}</div>
                <div class="kpi-foot">Projeção anual da base atual</div>
            </div>
            <div class="kpi-card" style="--accent:#0284c7;--soft:#f0f9ff">
                <div class="kpi-top"><div class="kpi-label">Clientes Pagantes</div><div class="kpi-icon"><i class='bx bxs-user-check'></i></div></div>
                <div class="kpi-value">{{ number_format($empresasPagas, 0, ',', '.') }}</div>
                <div class="kpi-foot"><strong>+{{ $novosPagantesPeriodo }}</strong> novos no período</div>
            </div>
            <div class="kpi-card" style="--accent:#d97706;--soft:#fffbeb">
                <div class="kpi-top"><div class="kpi-label">Teste Grátis</div><div class="kpi-icon"><i class='bx bx-time-five'></i></div></div>
                <div class="kpi-value">{{ number_format($empresasTesteGratis, 0, ',', '.') }}</div>
                <div class="kpi-foot">Trials ativos aguardando conversão</div>
            </div>
        </div>

        <div class="status-grid">
            <div class="status-card"><div><span>Autorizadas</span><strong>{{ $assinaturasAutorizadas }}</strong></div><div class="status-dot" style="--dot:#10b981;--dot-soft:#d1fae5"></div></div>
            <div class="status-card"><div><span>Pausadas</span><strong>{{ $assinaturasPausadas }}</strong></div><div class="status-dot" style="--dot:#f59e0b;--dot-soft:#fef3c7"></div></div>
            <div class="status-card"><div><span>Canceladas</span><strong>{{ $assinaturasCanceladas }}</strong></div><div class="status-dot" style="--dot:#f43f5e;--dot-soft:#ffe4e6"></div></div>
            <div class="status-card"><div><span>Outros status</span><strong>{{ $assinaturasPendentes }}</strong></div><div class="status-dot" style="--dot:#64748b;--dot-soft:#e2e8f0"></div></div>
            <div class="status-card"><div><span>Cobranças 7 dias</span><strong>{{ $proximasCobrancas }}</strong></div><div class="status-dot" style="--dot:#6366f1;--dot-soft:#e0e7ff"></div></div>
        </div>

        <div class="dashboard-grid">
            <div class="panel">
                <div class="panel-head">
                    <div>
                        <h3 class="panel-title">Aquisição de clientes — 12 meses</h3>
                        <div class="panel-subtitle">Novas empresas em planos pagos, sem contar trial</div>
                    </div>
                    <span class="badge-soft"><i class='bx bx-trending-up'></i> Crescimento</span>
                </div>
                <div class="panel-body"><canvas id="chartCrescimento" height="105"></canvas></div>
            </div>

            <div class="panel">
                <div class="panel-head">
                    <div>
                        <h3 class="panel-title">Indicadores comerciais</h3>
                        <div class="panel-subtitle">Eficiência de aquisição e retenção</div>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="mini-grid">
                        <div class="mini-card"><div class="label">Conversão</div><div class="value">{{ number_format($taxaConversao, 1, ',', '.') }}%</div><div class="hint">Pagantes novos x trials ativos</div></div>
                        <div class="mini-card"><div class="label">Ticket MRR</div><div class="value">R$ {{ number_format($ticketMedio, 2, ',', '.') }}</div><div class="hint">Média por cliente pagante</div></div>
                        <div class="mini-card"><div class="label">CAC</div><div class="value">R$ {{ number_format($cac, 2, ',', '.') }}</div><div class="hint">Marketing ÷ novos pagantes</div></div>
                        <div class="mini-card"><div class="label">LTV / CAC</div><div class="value">{{ number_format($ltvCac, 1, ',', '.') }}x</div><div class="hint">LTV projetado em 12 meses</div></div>
                    </div>
                    <div style="margin-top:14px">
                        <div class="progress-row">
                            <div class="progress-meta"><span>Clientes em risco de renovação</span><strong>{{ $clientesRisco }} ({{ number_format($churnPrevisto,1,',','.') }}%)</strong></div>
                            <div class="progress-track"><div class="progress-bar-saas" style="width:{{ min($churnPrevisto,100) }}%;background:#f59e0b"></div></div>
                        </div>
                        <div class="progress-row">
                            <div class="progress-meta"><span>Empresas bloqueadas</span><strong>{{ $totalBloqueadas }}</strong></div>
                            @php $percBloqueadas = ($totalAtivas + $totalBloqueadas) > 0 ? ($totalBloqueadas / ($totalAtivas + $totalBloqueadas)) * 100 : 0; @endphp
                            <div class="progress-track"><div class="progress-bar-saas" style="width:{{ min($percBloqueadas,100) }}%;background:#e11d48"></div></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="dashboard-grid">
            <div class="panel">
                <div class="panel-head">
                    <div>
                        <h3 class="panel-title">MRR por plano</h3>
                        <div class="panel-subtitle">Distribuição da receita recorrente da base ativa</div>
                    </div>
                    <span class="badge-soft">{{ $empresasPagas }} clientes</span>
                </div>
                <div class="panel-body"><canvas id="chartPlanos" height="105"></canvas></div>
            </div>

            <div class="panel">
                <div class="panel-head">
                    <div>
                        <h3 class="panel-title">Pagamentos aprovados</h3>
                        <div class="panel-subtitle">Pix, boleto, cartão e demais meios no período</div>
                    </div>
                </div>
                <div class="panel-body">
                    @forelse($pagamentosPorForma as $forma)
                        @php $maxPagamento = max((float) $pagamentosPorForma->max('total'), 1); $pct = ((float)$forma->total / $maxPagamento) * 100; @endphp
                        <div class="progress-row">
                            <div class="progress-meta"><span>{{ ucfirst(str_replace('_',' ', $forma->forma)) }} · {{ $forma->quantidade }} transações</span><strong>R$ {{ number_format($forma->total,2,',','.') }}</strong></div>
                            <div class="progress-track"><div class="progress-bar-saas" style="width:{{ $pct }}%"></div></div>
                        </div>
                    @empty
                        <div class="empty-state">Nenhum pagamento aprovado no período.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="finance-strip">
            <div class="finance-card blue"><div class="label">Pagamentos MP aprovados</div><div class="value">R$ {{ number_format($pagamentosAprovados,2,',','.') }}</div><div class="hint">Período filtrado</div></div>
            <div class="finance-card"><div class="label">Pagamentos MP pendentes</div><div class="value">R$ {{ number_format($pagamentosPendentes,2,',','.') }}</div><div class="hint">Aguardando confirmação</div></div>
            <div class="finance-card green"><div class="label">Recebido financeiro</div><div class="value">R$ {{ number_format($recebidoMes,2,',','.') }}</div><div class="hint">Contas recebidas no período</div></div>
            <div class="finance-card {{ $saldoMes >= 0 ? 'green' : 'red' }}"><div class="label">Saldo realizado</div><div class="value">R$ {{ number_format($saldoMes,2,',','.') }}</div><div class="hint">Recebido menos contas pagas</div></div>
        </div>

        <div class="dashboard-grid">
            <div class="panel">
                <div class="panel-head">
                    <div>
                        <h3 class="panel-title">Renovações próximas</h3>
                        <div class="panel-subtitle">Clientes com plano vencendo nos próximos 7 dias</div>
                    </div>
                    <span class="badge-soft">{{ $clientesRisco }} alertas</span>
                </div>
                <div class="table-wrap">
                    <table class="saas-table">
                        <thead><tr><th>Empresa</th><th>Plano</th><th>Vencimento</th><th>Prazo</th></tr></thead>
                        <tbody>
                        @forelse($empresasAlerta as $empresa)
                            @php $vencimento = \Carbon\Carbon::parse($empresa->expiracao); $dias = now()->startOfDay()->diffInDays($vencimento->startOfDay(), false); @endphp
                            <tr>
                                <td><strong>{{ $empresa->nome_fantasia ?: $empresa->razao_social ?: 'Empresa #'.$empresa->empresa_id }}</strong></td>
                                <td>{{ $empresa->plano_nome ?: 'Plano' }}</td>
                                <td>{{ $vencimento->format('d/m/Y') }}</td>
                                <td><span class="status-badge {{ $dias <= 2 ? 'status-cancelled' : 'status-paused' }}">{{ $dias <= 0 ? 'Hoje' : $dias.' dias' }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="4"><div class="empty-state">Nenhuma renovação crítica nos próximos 7 dias.</div></td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="panel">
                <div class="panel-head">
                    <div>
                        <h3 class="panel-title">Visão da base</h3>
                        <div class="panel-subtitle">Situação geral do SaaS</div>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="mini-grid">
                        <div class="mini-card"><div class="label">Empresas ativas</div><div class="value">{{ $totalAtivas }}</div><div class="hint">Status liberado</div></div>
                        <div class="mini-card"><div class="label">Leads</div><div class="value">{{ $totalLead }}</div><div class="hint">Base comercial cadastrada</div></div>
                        <div class="mini-card"><div class="label">Vencidos</div><div class="value">{{ $clientesVencidos }}</div><div class="hint">Plano pago expirado</div></div>
                        <div class="mini-card"><div class="label">LTV 12m</div><div class="value">R$ {{ number_format($ltv,2,',','.') }}</div><div class="hint">Projeção por cliente</div></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="panel">
            <div class="panel-head">
                <div>
                    <h3 class="panel-title">Assinaturas Mercado Pago recentes</h3>
                    <div class="panel-subtitle">Preapproval / Subscriptions sincronizadas no sistema</div>
                </div>
                <span class="badge-soft"><i class='bx bx-credit-card'></i> Subscriptions</span>
            </div>
            <div class="table-wrap">
                <table class="saas-table">
                    <thead><tr><th>ID</th><th>Usuário</th><th>Subscription MP</th><th>Plano MP</th><th>Status</th><th>Próxima cobrança</th></tr></thead>
                    <tbody>
                    @forelse($assinaturasRecentes as $assinatura)
                        @php
                            $statusClass = match($assinatura->status) {
                                'authorized' => 'status-authorized',
                                'paused' => 'status-paused',
                                'cancelled' => 'status-cancelled',
                                default => 'status-other'
                            };
                        @endphp
                        <tr>
                            <td>#{{ $assinatura->id }}</td>
                            <td><strong>{{ $assinatura->user_id }}</strong></td>
                            <td>{{ $assinatura->mp_subscription_id ?: '—' }}</td>
                            <td>{{ $assinatura->mp_plan_id ?: '—' }}</td>
                            <td><span class="status-badge {{ $statusClass }}">{{ $assinatura->status ?: 'indefinido' }}</span></td>
                            <td>{{ $assinatura->next_payment_date ? \Carbon\Carbon::parse($assinatura->next_payment_date)->format('d/m/Y H:i') : '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><div class="empty-state">{{ $assinaturasDisponiveis ? 'Ainda não existem assinaturas sincronizadas.' : 'A tabela subscriptions ainda não foi criada neste ambiente.' }}</div></td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const crescimentoLabels = @json($crescimento12Meses->pluck('mes')->values());
    const crescimentoValores = @json($crescimento12Meses->pluck('total')->values());
    const planosLabels = @json($planosResumo->pluck('nome')->values());
    const planosValores = @json($planosResumo->pluck('mrr')->values());

    const crescimentoEl = document.getElementById('chartCrescimento');
    if (crescimentoEl) {
        new Chart(crescimentoEl, {
            type: 'line',
            data: { labels: crescimentoLabels, datasets: [{ label: 'Novos pagantes', data: crescimentoValores, borderColor: '#4f46e5', backgroundColor: 'rgba(79,70,229,.10)', fill: true, tension: .38, borderWidth: 3, pointRadius: 3, pointBackgroundColor: '#4f46e5' }] },
            options: { responsive:true, maintainAspectRatio:true, plugins:{legend:{display:false}}, scales:{ x:{grid:{display:false},ticks:{font:{size:10}}}, y:{beginAtZero:true,ticks:{precision:0,font:{size:10}},grid:{color:'#f1f5f9'}} } }
        });
    }

    const planosEl = document.getElementById('chartPlanos');
    if (planosEl) {
        new Chart(planosEl, {
            type: 'bar',
            data: { labels: planosLabels, datasets: [{ label:'MRR', data: planosValores, backgroundColor:'#6366f1', borderRadius:8, maxBarThickness:42 }] },
            options: { responsive:true, maintainAspectRatio:true, plugins:{legend:{display:false},tooltip:{callbacks:{label:(ctx)=>'R$ '+Number(ctx.raw||0).toLocaleString('pt-BR',{minimumFractionDigits:2})}}}, scales:{ x:{grid:{display:false},ticks:{font:{size:10}}}, y:{beginAtZero:true,grid:{color:'#f1f5f9'},ticks:{font:{size:10},callback:(v)=>'R$ '+Number(v).toLocaleString('pt-BR')}} } }
        });
    }
});
</script>
@endsection