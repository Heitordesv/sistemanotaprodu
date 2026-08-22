@extends('default.layout', ['title' => 'Dashboard Financeiro'])

@section('content')

<div class="page-content p-4">

    {{-- ================= HEADER ================= --}}
    <div class="mb-4">
        <h3 class="fw-bold mb-1">Dashboard Financeiro</h3>
        <small class="text-muted">Visão geral do fluxo de caixa da sua empresa</small>
    </div>

    {{-- ================= FILTROS ================= --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">

                <div class="col-md-2">
                    <label class="form-label small text-muted">Dia</label>
                    <select name="dia" class="form-select">
                        <option value="">Todos</option>
                        @for($i=1;$i<=31;$i++)
                            <option value="{{ $i }}" {{ request('dia') == $i ? 'selected' : '' }}>
                                {{ $i }}
                            </option>
                        @endfor
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label small text-muted">Mês</label>
                    <select name="mes" class="form-select">
                        @for($m=1;$m<=12;$m++)
                            <option value="{{ $m }}" {{ ($mes ?? now()->month) == $m ? 'selected' : '' }}>
                                {{ date('M', mktime(0,0,0,$m,1)) }}
                            </option>
                        @endfor
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label small text-muted">Ano</label>
                    <input type="number" name="ano" class="form-control" value="{{ $ano ?? now()->year }}">
                </div>

                <div class="col-md-6 text-end">
                    <button class="btn btn-dark px-4 rounded-3">
                        Filtrar
                    </button>
                </div>

            </form>
        </div>
    </div>

    @php
        $saldo = ($recebidoMes ?? 0) - ($pagoMes ?? 0);
    @endphp

    {{-- ================= KPIs ================= --}}
    <div class="row g-3 mb-4">

        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 kpi">
                <div class="card-body">
                    <small class="text-muted">Entradas</small>
                    <h4 class="text-success fw-bold">
                        R$ {{ number_format($recebidoMes ?? 0,2,',','.') }}
                    </h4>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 kpi">
                <div class="card-body">
                    <small class="text-muted">Saídas</small>
                    <h4 class="text-danger fw-bold">
                        R$ {{ number_format($pagoMes ?? 0,2,',','.') }}
                    </h4>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 kpi">
                <div class="card-body">
                    <small class="text-muted">Saldo</small>
                    <h4 class="{{ $saldo >= 0 ? 'text-success' : 'text-danger' }} fw-bold">
                        R$ {{ number_format($saldo,2,',','.') }}
                    </h4>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 kpi">
                <div class="card-body">
                    <small class="text-muted">Inadimplência</small>
                    <h4 class="text-warning fw-bold">
                        {{ $inadimplencia ?? 0 }}%
                    </h4>
                </div>
            </div>
        </div>

    </div>

    {{-- ================= RESUMO ================= --}}
    <div class="row g-3 mb-4">

        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3">
                <small class="text-muted">Total a Receber</small>
                <h5 class="fw-bold mb-0">R$ {{ number_format($totalReceber ?? 0,2,',','.') }}</h5>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3">
                <small class="text-muted">Total a Pagar</small>
                <h5 class="fw-bold mb-0">R$ {{ number_format($totalPagar ?? 0,2,',','.') }}</h5>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-dark text-white">
                <small class="opacity-75">Fluxo Previsto</small>
                <h5 class="fw-bold mb-0">
                    R$ {{ number_format($saldo,2,',','.') }}
                </h5>
            </div>
        </div>

    </div>

    {{-- ================= GRÁFICOS ================= --}}
    <div class="row g-4">

        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 p-3">
                <h6 class="fw-bold">Fluxo Mensal</h6>
                <canvas id="graficoFluxo"></canvas>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-3">
                <h6 class="fw-bold">Categoria</h6>
                <canvas id="graficoCategoria"></canvas>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 p-3">
                <h6 class="fw-bold">Grupo</h6>
                <canvas id="graficoGrupo"></canvas>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 p-3">
                <h6 class="fw-bold">Fluxo Acumulado</h6>
                <canvas id="fluxoAcumulado"></canvas>
            </div>
        </div>

    </div>

</div>

{{-- ================= STYLE PREMIUM ================= --}}
<style>
.kpi{
    transition: .2s;
    border-left: 4px solid #eaeaea;
}
.kpi:hover{
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(0,0,0,.08);
}
body{
    background: #f6f7fb;
}
.card{
    border-radius: 16px;
}
</style>

{{-- ================= CHART ================= --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {

    const meses = ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'];

    new Chart(document.getElementById('graficoFluxo'), {
        type: 'line',
        data: {
            labels: meses,
            datasets: [
                {
                    label: 'Receber',
                    data: @json($receberMensal ?? array_fill(0,12,0)),
                    borderColor: '#16a34a',
                    tension: 0.4
                },
                {
                    label: 'Pagar',
                    data: @json($pagarMensal ?? array_fill(0,12,0)),
                    borderColor: '#dc2626',
                    tension: 0.4
                }
            ]
        }
    });

    new Chart(document.getElementById('graficoCategoria'), {
        type: 'doughnut',
        data: {
            labels: @json(collect($graficoCategoria ?? [])->pluck('label')),
            datasets: [{
                data: @json(collect($graficoCategoria ?? [])->pluck('total'))
            }]
        }
    });

    new Chart(document.getElementById('graficoGrupo'), {
        type: 'bar',
        data: {
            labels: @json(collect($graficoGrupo ?? [])->pluck('label')),
            datasets: [{
                data: @json(collect($graficoGrupo ?? [])->pluck('total')),
                backgroundColor: '#0f172a'
            }]
        }
    });

    new Chart(document.getElementById('fluxoAcumulado'), {
        type: 'line',
        data: {
            labels: meses,
            datasets: [{
                label: 'Acumulado',
                data: @json($fluxoAcumulado ?? array_fill(0,12,0)),
                borderColor: '#2563eb',
                fill: true,
                tension: 0.4
            }]
        }
    });

});
</script>

@endsection