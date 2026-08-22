<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento | Mercado Pago</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
@php
    $pago = (bool) ($resultado['pago'] ?? false);
    $status = (string) ($resultado['status'] ?? '');
    $statusLabels = [
        'approved' => 'Pagamento aprovado',
        'pending' => 'Pagamento pendente',
        'in_process' => 'Pagamento em análise',
        'action_required' => 'Aguardando pagamento',
        'rejected' => 'Pagamento recusado',
        'cancelled' => 'Pagamento cancelado',
        'refunded' => 'Pagamento estornado',
        'charged_back' => 'Pagamento contestado',
        'checkout_created' => 'Aguardando conclusão do pagamento',
    ];
    $statusTexto = $statusLabels[$status] ?? 'Status do pagamento atualizado';
@endphp

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 p-md-5 text-center">
                    <div class="display-4 mb-3">{{ $pago ? '✓' : '⏳' }}</div>
                    <h1 class="h4 fw-bold mb-2">{{ $statusTexto }}</h1>
                    <p class="text-muted mb-4">
                        Referência: {{ $conta->referencia ?: 'Conta #' . $conta->id }}
                    </p>

                    <div class="bg-light rounded-3 p-3 mb-4 text-start">
                        <div class="d-flex justify-content-between gap-3 mb-2">
                            <span class="text-muted">Valor</span>
                            <strong>R$ {{ number_format((float) $conta->valor_integral, 2, ',', '.') }}</strong>
                        </div>
                        <div class="d-flex justify-content-between gap-3">
                            <span class="text-muted">Situação</span>
                            <strong class="{{ $pago ? 'text-success' : 'text-warning' }}">{{ $pago ? 'Recebido' : 'Aguardando confirmação' }}</strong>
                        </div>
                    </div>

                    @if($pago)
                        <div class="alert alert-success mb-0">
                            O pagamento foi identificado. Você já pode fechar esta página.
                        </div>
                    @else
                        <div class="alert alert-info mb-0">
                            Se você acabou de pagar, a confirmação pode levar alguns instantes. O sistema atualizará a conta automaticamente pelo Mercado Pago.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>