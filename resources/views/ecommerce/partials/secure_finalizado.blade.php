@php
    $status = strtolower((string)($pedido->status_pagamento ?? 'pending'));
    $approved = $status === 'approved';
    $rejected = in_array($status, ['rejected','cancelled'], true);
    $pending = !$approved && !$rejected;
    $forma = strtolower((string)($pedido->forma_pagamento ?? ''));
    $statusLabel = match($status) {
        'approved' => 'Pagamento aprovado',
        'rejected' => 'Pagamento recusado',
        'cancelled' => 'Pagamento cancelado',
        'pending', 'in_process' => 'Aguardando pagamento',
        default => ucfirst(str_replace('_', ' ', $status ?: 'pending')),
    };
@endphp

<style>
    .finish-safe{max-width:940px;margin:38px auto 70px;padding:0 16px;color:#0f172a}
    .finish-safe-card{background:#fff;border:1px solid #e5e7eb;border-radius:24px;padding:30px;box-shadow:0 14px 38px rgba(15,23,42,.08)}
    .finish-safe-head{display:flex;align-items:flex-start;justify-content:space-between;gap:20px;margin-bottom:22px}
    .finish-safe-icon{width:58px;height:58px;border-radius:18px;display:flex;align-items:center;justify-content:center;font-size:28px;font-weight:900;flex:none}
    .finish-safe-badge{display:inline-block;padding:7px 12px;border-radius:999px;font-size:12px;font-weight:900;text-transform:uppercase;letter-spacing:.04em}
    .finish-safe-title{font-size:30px;margin:12px 0 5px;font-weight:900;color:#111827}
    .finish-safe-sub{color:#64748b;margin:0;line-height:1.55;max-width:650px}
    .finish-safe-progress{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin:26px 0 8px}
    .finish-safe-step{position:relative;padding-top:34px;text-align:center;color:#94a3b8;font-size:11px;font-weight:800}
    .finish-safe-step:before{content:'';position:absolute;top:11px;left:0;right:0;height:3px;background:#e2e8f0}.finish-safe-step:first-child:before{left:50%}.finish-safe-step:last-child:before{right:50%}
    .finish-safe-step span{position:absolute;top:2px;left:50%;transform:translateX(-50%);width:21px;height:21px;border-radius:50%;background:#e2e8f0;border:4px solid #fff;box-shadow:0 0 0 1px #e2e8f0;z-index:2}
    .finish-safe-step.done:before{background:var(--main-color)}.finish-safe-step.done span{background:var(--main-color);box-shadow:0 0 0 1px var(--main-color)}.finish-safe-step.active strong{color:#111827}.finish-safe-step.active span{background:#fff;box-shadow:0 0 0 5px color-mix(in srgb,var(--main-color) 22%,white),0 0 0 1px var(--main-color)}
    .finish-safe-grid{display:grid;grid-template-columns:minmax(0,1fr) 300px;gap:22px;margin-top:26px}
    .finish-safe-box{border:1px solid #e5e7eb;border-radius:16px;padding:18px;background:#f8fafc}
    .finish-safe-box h4{margin:0 0 12px;font-size:16px;font-weight:900;color:#111827}
    .finish-safe-row{display:flex;justify-content:space-between;gap:18px;padding:10px 0;border-bottom:1px solid #e5e7eb;color:#475569}.finish-safe-row:last-child{border-bottom:0}.finish-safe-row strong{color:#111827;text-align:right}
    .finish-safe-actions{display:flex;flex-wrap:wrap;gap:10px;margin-top:24px}
    .finish-safe-btn{display:inline-flex;align-items:center;justify-content:center;gap:7px;padding:14px 18px;background:#111827;color:#fff!important;border-radius:12px;font-weight:900;text-decoration:none!important;border:0;transition:.2s ease}.finish-safe-btn:hover{filter:brightness(.94)}
    .finish-safe-btn.primary{background:var(--main-color)}.finish-safe-btn.secondary{background:#fff;color:#334155!important;border:1px solid #cbd5e1}
    .finish-safe-note{margin-top:14px;border-radius:12px;padding:12px 14px;font-size:13px;line-height:1.5}.finish-safe-note.info{background:#eff6ff;border:1px solid #bfdbfe;color:#1e40af}.finish-safe-note.warn{background:#fff7ed;border:1px solid #fed7aa;color:#9a3412}.finish-safe-note.ok{background:#ecfdf5;border:1px solid #bbf7d0;color:#166534}
    .finish-safe-help{margin-top:16px;color:#64748b;font-size:12px;line-height:1.5}
    @media(max-width:800px){.finish-safe-grid{grid-template-columns:1fr}.finish-safe-head{display:block}.finish-safe-icon{margin-bottom:14px}.finish-safe-card{padding:22px}.finish-safe-title{font-size:25px}.finish-safe-btn{width:100%}.finish-safe-progress{grid-template-columns:1fr;gap:0}.finish-safe-step{text-align:left;padding:0 0 20px 40px}.finish-safe-step:before{top:0;bottom:0;left:10px!important;right:auto!important;width:3px;height:auto}.finish-safe-step:last-child:before{bottom:50%}.finish-safe-step span{top:0;left:11px}}
</style>

<div class="finish-safe">
    <div class="finish-safe-card">
        <div class="finish-safe-head">
            <div style="display:flex;gap:16px;align-items:flex-start">
                <div class="finish-safe-icon" style="background:{{ $approved ? '#dcfce7' : ($rejected ? '#fee2e2' : '#ffedd5') }};color:{{ $approved ? '#166534' : ($rejected ? '#991b1b' : '#9a3412') }}">
                    {{ $approved ? '✓' : ($rejected ? '!' : '…') }}
                </div>
                <div>
                    <span class="finish-safe-badge" style="background:{{ $approved ? '#ecfdf5' : ($rejected ? '#fef2f2' : '#fff7ed') }};color:{{ $approved ? '#065f46' : ($rejected ? '#991b1b' : '#9a3412') }}">{{ $statusLabel }}</span>
                    <h2 class="finish-safe-title">Pedido #{{ $pedido->id }}</h2>
                    <p class="finish-safe-sub">
                        @if($approved)
                            Tudo certo com o pagamento. Agora a loja vai preparar seu pedido para envio ou retirada.
                        @elseif($rejected)
                            O pagamento não foi concluído. Seu pedido continua disponível para uma nova tentativa.
                        @else
                            Seu pedido foi criado. Assim que o pagamento for identificado, o status será atualizado automaticamente.
                        @endif
                    </p>
                </div>
            </div>
        </div>

        @if(!$rejected)
            <div class="finish-safe-progress" aria-label="Progresso do pedido">
                <div class="finish-safe-step done"><span></span><strong>Pedido criado</strong></div>
                <div class="finish-safe-step {{ $approved ? 'done' : 'active' }}"><span></span><strong>Pagamento</strong></div>
                <div class="finish-safe-step {{ $approved ? 'active' : '' }}"><span></span><strong>Preparação</strong></div>
                <div class="finish-safe-step"><span></span><strong>Envio/entrega</strong></div>
            </div>
        @endif

        <div class="finish-safe-grid">
            <div class="finish-safe-box">
                <h4>Resumo do pedido</h4>
                <div class="finish-safe-row"><span>Forma de pagamento</span><strong>{{ $pedido->forma_pagamento ?: 'Não informada' }}</strong></div>
                <div class="finish-safe-row"><span>Status</span><strong>{{ $statusLabel }}</strong></div>
                <div class="finish-safe-row"><span>Total</span><strong>R$ {{ number_format((float)$pedido->valor_total, 2, ',', '.') }}</strong></div>
                @if($pedido->transacao_id)
                    <div class="finish-safe-row"><span>Transação</span><strong>#{{ $pedido->transacao_id }}</strong></div>
                @endif
            </div>

            <div class="finish-safe-box">
                <h4>O que acontece agora?</h4>
                @if($approved)
                    <div class="finish-safe-note ok">Acompanhe a preparação, o envio e o rastreamento do seu pedido em uma única tela.</div>
                @elseif($rejected)
                    <div class="finish-safe-note warn">Escolha outra forma de pagamento. Você não precisa montar o carrinho novamente.</div>
                @elseif(str_contains($forma, 'boleto'))
                    <div class="finish-safe-note info">Pague o boleto e aguarde a compensação. A confirmação será atualizada automaticamente.</div>
                @elseif(str_contains($forma, 'pix'))
                    <div class="finish-safe-note info">Conclua o PIX e aguarde a confirmação. Você pode acompanhar o status do pedido a qualquer momento.</div>
                @else
                    <div class="finish-safe-note info">Aguarde a confirmação do pagamento.</div>
                @endif
            </div>
        </div>

        <div class="finish-safe-actions">
            @if($approved)
                <a class="finish-safe-btn primary" href="{{ $rota }}/pedido_detalhe/{{ $pedido->id }}">Acompanhar meu pedido</a>
            @endif

            @if($pedido->link_boleto && !$approved)
                <a class="finish-safe-btn primary" href="{{ $pedido->link_boleto }}" target="_blank" rel="noopener noreferrer">Abrir boleto</a>
            @endif

            @if($pending && str_contains($forma, 'pix'))
                <a class="finish-safe-btn primary" href="{{ route('ecommerce.secure.pix', ['link' => $default['config']->link, 'pedidoId' => $pedido->id]) }}">Abrir PIX</a>
            @endif

            @if($rejected)
                <a class="finish-safe-btn primary" href="{{ $rota }}/pagamento">Tentar outro pagamento</a>
            @endif

            @if(!$approved)
                <a class="finish-safe-btn secondary" href="{{ $rota }}/pedido_detalhe/{{ $pedido->id }}">Ver meu pedido</a>
            @endif

            <a class="finish-safe-btn secondary" href="{{ $rota }}">Continuar comprando</a>
        </div>

        <div class="finish-safe-help">Você não precisa guardar esta tela: seus pedidos ficam disponíveis na sua conta da loja.</div>
    </div>
</div>