@php
    // 1. Inicialização e Tipagem de Variáveis
    $totalRecebidas   = (float)($totalRecebidas ?? 0);
    $totalPendentes   = (float)($totalPendentes ?? 0);
    $totalAtrasadas   = (float)($totalAtrasadas ?? 0);
    $totalAVencer     = (float)($totalAVencer ?? 0);
    $indiceInadimplencia = (float)($indiceInadimplencia ?? 0);
    
    $exibirScore = !empty(request('cliente_id')) && isset($clienteLogado);
    
    // 2. Cálculo do Score (0 a 1000)
    $pontosAtraso = $pontosAtraso ?? 0;
    $scoreFinal = max(0, min(1000, 1000 - ($pontosAtraso * 5) - ($indiceInadimplencia * 20)));

    $scoreStatus = match(true) {
        $scoreFinal >= 850 => ['color' => 'success', 'label' => 'Excelente', 'icon' => 'bx-shield-quarter'],
        $scoreFinal >= 650 => ['color' => 'info',    'label' => 'Bom',       'icon' => 'bx-check-shield'],
        $scoreFinal >= 450 => ['color' => 'warning', 'label' => 'Regular',   'icon' => 'bx-error-circle'],
        default            => ['color' => 'danger',  'label' => 'Crítico',   'icon' => 'bx-shield-x'],
    };

    // 3. Lógica de Sugestão de Limite
    $limiteAtual = (float)($clienteLogado->limite_venda ?? 0);
    $sugestaoLimite = 0;

    if ($exibirScore && $limiteAtual <= 0 && $scoreFinal >= 450) {
        $baseCalculo = ($totalRecebidas > 0) ? ($totalRecebidas / 2) : 500;
        $multiplicador = match(true) {
            $scoreFinal >= 850 => 2.0,
            $scoreFinal >= 650 => 1.2,
            default            => 0.8,
        };
        $sugestaoLimite = round($baseCalculo * $multiplicador, 2);
    }

    $cards = [
        ['t' => 'Recebidas (Total)',    'v' => $totalRecebidas, 'c' => $quantRecebidas ?? 0, 'cls' => 'success', 'i' => 'bx-check-double'],
        ['t' => 'Pendentes (Integral)', 'v' => $totalPendentes, 'c' => $quantPendentes ?? 0, 'cls' => 'warning', 'i' => 'bx-time-five'],
        ['t' => 'Atrasadas (Saldo)',    'v' => $totalAtrasadas, 'c' => $quantAtrasadas ?? 0, 'cls' => 'danger',  'i' => 'bx-error-alt'],
        ['t' => 'A Receber (7 dias)',   'v' => $totalAVencer,   'c' => $quantAVencer ?? 0,   'cls' => 'info',    'i' => 'bx-calendar-event'],
    ];
@endphp

<div class="container-fluid py-4">

    @if($exibirScore)
    <!-- Dashboard de Crédito e Score -->
    <div class="card bg-dark text-white shadow-lg mb-4" style="border-radius: 15px; border: none;">
        <div class="row g-0">
            <!-- Coluna Score -->
            <div class="col-md-3 text-center p-4 border-end border-secondary border-opacity-25">
                <span class="badge bg-{{ $scoreStatus['color'] }} mb-2">{{ $scoreStatus['label'] }}</span>
                <h1 class="display-3 fw-bold text-{{ $scoreStatus['color'] }} mb-0">{{ round($scoreFinal) }}</h1>
                <small class="text-secondary text-uppercase" style="letter-spacing: 1px;">Credit Score</small>
            </div>
            
            <!-- Coluna Dados Financeiros -->
            <div class="col-md-6 p-4">
                <h5 class="fw-bold mb-3 text-truncate">
                    <i class="bx {{ $scoreStatus['icon'] }} me-2 text-{{ $scoreStatus['color'] }}"></i> 
                    {{ $clienteLogado->razao_social }}
                </h5>
                <div class="row text-center mt-4">
                    <div class="col-4 border-end border-secondary border-opacity-25">
                        <small class="d-block text-secondary mb-1">LIMITE ATUAL</small>
                        <span class="fw-bold fs-5">{{ __moeda($limiteAtual) }}</span>
                    </div>
                    <div class="col-4 border-end border-secondary border-opacity-25">
                        <small class="d-block text-secondary mb-1">DISPONÍVEL</small>
                        <span class="fw-bold fs-5 text-{{ ($limiteDisponivel ?? 0) > 0 ? 'success' : 'danger' }}">
                            {{ __moeda($limiteDisponivel ?? 0) }}
                        </span>
                    </div>
                    <div class="col-4">
                        <small class="d-block text-secondary mb-1">INADIMPLÊNCIA</small>
                        <span class="fw-bold fs-5 text-{{ $indiceInadimplencia > 10 ? 'danger' : 'white' }}">
                            {{ number_format($indiceInadimplencia, 1) }}%
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Coluna Ação -->
            <div class="col-md-3 p-4 bg-white bg-opacity-10 text-center d-flex flex-column justify-content-center">
                <small class="text-uppercase text-secondary fw-bold mb-2">Análise de Risco</small>
                
                @if($indiceInadimplencia > 15 || $scoreFinal < 350)
                    <span class="text-danger fw-bold fs-5"><i class="bx bx-block me-1"></i> BLOQUEADO</span>
                    <small class="text-muted">Risco de crédito alto</small>
                @else
                    <span class="text-success fw-bold fs-5 mb-2"><i class="bx bx-check-circle me-1"></i> LIBERADO</span>
                    
                    {{-- Botão Ajax para aplicar sugestão --}}
                    @if($limiteAtual <= 0 && $sugestaoLimite > 0)
                        <div class="p-2 border border-warning border-opacity-50 rounded bg-warning bg-opacity-10">
                            <small class="text-warning d-block mb-2">Sugestão: <strong>{{ __moeda($sugestaoLimite) }}</strong></small>
                            <button type="button" 
                                    onclick="atualizarLimiteRapido({{ $clienteLogado->id }}, {{ $sugestaoLimite }}, 'Definir')" 
                                    class="btn btn-xs btn-warning w-100 fw-bold shadow-sm">
                                <i class="bx bx-bolt-circle"></i> APLICAR
                            </button>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- Grid de Cards de Totais -->
    <div class="row g-3 mb-4">
        @foreach($cards as $card)
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="bg-{{ $card['cls'] }} bg-opacity-10 text-{{ $card['cls'] }} p-3 rounded-3">
                        <i class="bx {{ $card['i'] }} fs-3"></i>
                    </div>
                    <span class="badge rounded-pill bg-light text-dark border">{{ $card['c'] }}</span>
                </div>
                <div class="mt-3">
                    <small class="text-muted text-uppercase fw-bold" style="font-size: 0.65rem; letter-spacing: 0.5px;">
                        {{ $card['t'] }}
                    </small>
                    <h3 class="fw-bold mb-0 mt-1">{{ __moeda($card['v']) }}</h3>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>