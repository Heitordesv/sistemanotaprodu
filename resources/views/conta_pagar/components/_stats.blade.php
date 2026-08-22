<div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 g-3 mb-4">
    @php
        $stats = [

            [
                'label' => 'A Pagar (Pendente) ⏳',
                'value' => $totalValorPendente,
                'count' => $totalPendentes,
                'color' => 'warning',
                'icon' => 'bx-time'
            ],

            [
                'label' => 'Total Pago ✅',
                'value' => $totalValorPago,
                'count' => $totalPagas,
                'color' => 'success',
                'icon' => 'bx-check-circle'
            ],

            // 🔥 NOVO CARD PRINCIPAL
            [
                'label' => 'Falta Pagar 💸',
                'value' => $totalFaltaPagar,
                'count' => 'Saldo em aberto',
                'color' => 'danger',
                'icon' => 'bx-wallet'
            ],

            [
                'label' => 'Vencidas (Em Atraso) 🚨',
                'value' => $totalValorVencidoPendente,
                'count' => $totalVencidasPendentes,
                'color' => 'danger',
                'icon' => 'bx-calendar-exclamation'
            ],

            [
                'label' => 'Valor Total (Integral) 💰',
                'value' => $totalGeral,
                'count' => 'Total filtrado',
                'color' => 'info',
                'icon' => 'bx-bar-chart-alt-2'
            ],
        ];
    @endphp

    @foreach($stats as $stat)
    <div class="col">
        <div class="card radius-10 border-start border-0 border-5 border-{{ $stat['color'] }} shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">

                    <div class="flex-grow-1">
                        <p class="mb-0 text-secondary">{{ $stat['label'] }}</p>

                        <h4 class="my-1 text-{{ $stat['color'] }}">
                            R$ {{ __moeda($stat['value']) }}
                        </h4>

                        <p class="mb-0 font-13">
                            {{ is_numeric($stat['count']) ? $stat['count'].' Títulos' : $stat['count'] }}
                        </p>
                    </div>

                    <div class="ms-auto text-{{ $stat['color'] }}">
                        <i class='bx {{ $stat['icon'] }} fs-2'></i>
                    </div>

                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>