@extends('ecommerce.default')
@section('content')

@php
    $paymentStatus = strtolower((string) ($pedido->status_pagamento ?? 'pending'));
    $paymentApproved = $paymentStatus === 'approved';
    $paymentRejected = in_array($paymentStatus, ['rejected', 'cancelled'], true);
    $orderStatus = strtolower((string) ($pedido->status ?? 'novo'));
    $orderCancelled = $orderStatus === 'cancelado';

    $paymentLabel = match ($paymentStatus) {
        'approved' => 'Pagamento aprovado',
        'pending', 'in_process' => 'Aguardando pagamento',
        'rejected' => 'Pagamento recusado',
        'cancelled' => 'Pagamento cancelado',
        default => ucfirst(str_replace('_', ' ', $paymentStatus ?: 'pending')),
    };

    $orderLabel = match ($orderStatus) {
        'novo' => 'Pedido recebido',
        'preparacao' => 'Preparando pedido',
        'enviado' => 'Pedido enviado',
        'entregue' => 'Pedido entregue',
        'cancelado' => 'Pedido cancelado',
        default => ucfirst(str_replace('_', ' ', $orderStatus)),
    };

    $journeyPosition = 0;
    if ($paymentApproved) $journeyPosition = 1;
    if ($paymentApproved && in_array($orderStatus, ['novo', 'preparacao', 'enviado', 'entregue'], true)) $journeyPosition = 2;
    if ($paymentApproved && in_array($orderStatus, ['enviado', 'entregue'], true)) $journeyPosition = 3;
    if ($paymentApproved && $orderStatus === 'entregue') $journeyPosition = 4;

    $steps = [
        ['label' => 'Pedido recebido', 'help' => 'Recebemos sua compra'],
        ['label' => 'Pagamento', 'help' => $paymentApproved ? 'Pagamento confirmado' : ($paymentRejected ? 'Pagamento não aprovado' : 'Aguardando confirmação')],
        ['label' => 'Preparação', 'help' => 'Separação dos produtos'],
        ['label' => 'Envio', 'help' => 'Pedido a caminho'],
        ['label' => 'Entrega', 'help' => 'Pedido entregue'],
    ];
@endphp

<style>
    .order-ux{max-width:1120px;margin:34px auto 70px;padding:0 16px;color:#0f172a}
    .order-ux-card{background:#fff;border:1px solid #e5e7eb;border-radius:22px;box-shadow:0 12px 34px rgba(15,23,42,.06)}
    .order-ux-head{padding:26px;display:flex;align-items:flex-start;justify-content:space-between;gap:18px}
    .order-ux-kicker{font-size:11px;text-transform:uppercase;letter-spacing:.12em;color:#94a3b8;font-weight:900;margin-bottom:6px}
    .order-ux-title{font-size:28px;font-weight:900;color:#111827;margin:0}
    .order-ux-date{font-size:13px;color:#64748b;margin-top:5px}
    .order-ux-badge{display:inline-flex;align-items:center;gap:7px;border-radius:999px;padding:9px 13px;font-size:12px;font-weight:900;white-space:nowrap}
    .order-ux-badge.ok{background:#ecfdf5;color:#166534;border:1px solid #bbf7d0}
    .order-ux-badge.wait{background:#fff7ed;color:#9a3412;border:1px solid #fed7aa}
    .order-ux-badge.bad{background:#fef2f2;color:#991b1b;border:1px solid #fecaca}
    .order-ux-alert{margin:0 26px 24px;padding:15px;border-radius:14px;display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap;background:#eff6ff;border:1px solid #bfdbfe;color:#1e40af}
    .order-ux-alert strong{display:block;color:#1e3a8a;margin-bottom:2px}
    .order-ux-btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;border:0;border-radius:12px;padding:12px 16px;background:var(--main-color);color:#fff!important;text-decoration:none!important;font-weight:900;cursor:pointer;transition:.2s ease}
    .order-ux-btn:hover{filter:brightness(.92)}
    .order-ux-btn.secondary{background:#fff;color:#334155!important;border:1px solid #cbd5e1}
    .order-ux-journey{padding:24px 26px;border-top:1px solid #f1f5f9}
    .order-ux-journey h3{font-size:15px;font-weight:900;margin:0 0 18px;color:#111827}
    .order-ux-steps{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:8px;position:relative}
    .order-ux-step{position:relative;text-align:center;padding-top:38px;color:#94a3b8;min-width:0}
    .order-ux-step:before{content:'';position:absolute;top:14px;left:0;right:0;height:3px;background:#e2e8f0;z-index:0}
    .order-ux-step:first-child:before{left:50%}.order-ux-step:last-child:before{right:50%}
    .order-ux-dot{position:absolute;top:5px;left:50%;transform:translateX(-50%);width:22px;height:22px;border-radius:50%;background:#e2e8f0;border:4px solid #fff;box-shadow:0 0 0 1px #e2e8f0;z-index:2}
    .order-ux-step.done:before,.order-ux-step.active:before{background:var(--main-color)}
    .order-ux-step.done .order-ux-dot{background:var(--main-color);box-shadow:0 0 0 1px var(--main-color)}
    .order-ux-step.active .order-ux-dot{background:#fff;box-shadow:0 0 0 5px color-mix(in srgb,var(--main-color) 22%,white),0 0 0 1px var(--main-color)}
    .order-ux-step strong{display:block;font-size:12px;color:#475569;margin-bottom:3px}.order-ux-step.done strong,.order-ux-step.active strong{color:#111827}
    .order-ux-step small{display:block;font-size:10px;line-height:1.35}
    .order-ux-grid{display:grid;grid-template-columns:minmax(0,1.35fr) minmax(300px,.65fr);gap:22px;margin-top:22px}
    .order-ux-section{padding:22px}.order-ux-section-title{font-size:12px;text-transform:uppercase;letter-spacing:.1em;color:#94a3b8;font-weight:900;margin:0 0 14px}
    .order-ux-item{display:grid;grid-template-columns:72px minmax(0,1fr) auto;gap:15px;align-items:center;padding:14px 0;border-bottom:1px solid #f1f5f9}.order-ux-item:last-child{border-bottom:0}
    .order-ux-item img{width:72px;height:72px;border-radius:14px;object-fit:contain;background:#f8fafc;border:1px solid #f1f5f9}
    .order-ux-item h4{font-size:14px;color:#111827;font-weight:900;margin:0 0 5px}.order-ux-muted{font-size:12px;color:#64748b}
    .order-ux-price{font-size:14px;font-weight:900;color:#111827;text-align:right;white-space:nowrap}
    .order-ux-side{display:flex;flex-direction:column;gap:16px}.order-ux-box{padding:20px}.order-ux-box h4{font-size:14px;font-weight:900;color:#111827;margin:0 0 12px}
    .order-ux-row{display:flex;justify-content:space-between;gap:18px;padding:9px 0;color:#64748b;font-size:13px;border-bottom:1px solid #f1f5f9}.order-ux-row:last-child{border-bottom:0}.order-ux-row strong{color:#111827;text-align:right}
    .order-ux-total{font-size:24px;font-weight:900;color:#111827}
    .order-ux-track{background:#f8fafc;border:1px dashed #cbd5e1;border-radius:12px;padding:12px;display:flex;align-items:center;justify-content:space-between;gap:10px}
    .order-ux-track code{color:var(--main-color);font-weight:900;overflow-wrap:anywhere}
    .order-ux-actions{display:flex;flex-wrap:wrap;gap:10px;margin-top:14px}
    .order-ux-note{font-size:12px;color:#64748b;line-height:1.5;margin-top:10px}
    @media(max-width:820px){.order-ux-head{display:block}.order-ux-badge{margin-top:16px}.order-ux-grid{grid-template-columns:1fr}.order-ux-steps{grid-template-columns:1fr;gap:0}.order-ux-step{text-align:left;padding:0 0 22px 42px}.order-ux-step:before{top:0;bottom:0;left:11px!important;right:auto!important;width:3px;height:auto}.order-ux-step:last-child:before{bottom:50%}.order-ux-dot{top:0;left:12px}.order-ux-step strong{font-size:13px}.order-ux-step small{font-size:11px}.order-ux-item{grid-template-columns:58px minmax(0,1fr)}.order-ux-item img{width:58px;height:58px}.order-ux-price{grid-column:2;text-align:left}.order-ux-btn{width:100%}}
</style>

<div class="order-ux">
    <div class="order-ux-card">
        <div class="order-ux-head">
            <div>
                <div class="order-ux-kicker">Acompanhamento do pedido</div>
                <h1 class="order-ux-title">Pedido #{{ $pedido->id }}</h1>
                <div class="order-ux-date">Realizado em {{ optional($pedido->created_at)->format('d/m/Y \à\s H:i') }}</div>
            </div>
            <div class="order-ux-badge {{ $paymentApproved ? 'ok' : ($paymentRejected ? 'bad' : 'wait') }}">
                {{ $paymentApproved ? '✓' : ($paymentRejected ? '!' : '…') }} {{ $paymentLabel }}
            </div>
        </div>

        @if(!$paymentApproved && strtolower((string)$pedido->forma_pagamento) === 'pix' && !$paymentRejected)
            <div class="order-ux-alert">
                <div>
                    <strong>Seu PIX ainda está aguardando pagamento</strong>
                    <span>Abra o QR Code para concluir. Assim que o Mercado Pago confirmar, esta página será atualizada.</span>
                </div>
                <a class="order-ux-btn" href="{{ route('ecommerce.secure.pix', ['link' => $default['config']->link, 'pedidoId' => $pedido->id]) }}">Abrir QR Code PIX</a>
            </div>
        @endif

        @if($paymentRejected)
            <div class="order-ux-alert" style="background:#fef2f2;border-color:#fecaca;color:#991b1b">
                <div><strong style="color:#991b1b">Pagamento não concluído</strong><span>Você pode escolher outra forma de pagamento sem refazer todo o pedido.</span></div>
                <a class="order-ux-btn" href="{{ $rota }}/pagamento">Tentar outro pagamento</a>
            </div>
        @endif

        @if($orderCancelled)
            <div class="order-ux-alert" style="background:#fef2f2;border-color:#fecaca;color:#991b1b">
                <div><strong style="color:#991b1b">Este pedido foi cancelado</strong><span>Se precisar de ajuda, entre em contato com a loja.</span></div>
            </div>
        @else
            <div class="order-ux-journey">
                <h3>Progresso do seu pedido · {{ $orderLabel }}</h3>
                <div class="order-ux-steps">
                    @foreach($steps as $index => $step)
                        @php
                            $done = $index < $journeyPosition;
                            $active = $index === $journeyPosition;
                            if ($paymentRejected && $index > 0) { $done = false; $active = $index === 1; }
                        @endphp
                        <div class="order-ux-step {{ $done ? 'done' : '' }} {{ $active ? 'active' : '' }}">
                            <span class="order-ux-dot"></span>
                            <strong>{{ $step['label'] }}</strong>
                            <small>{{ $step['help'] }}</small>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <div class="order-ux-grid">
        <section class="order-ux-card order-ux-section">
            <h3 class="order-ux-section-title">Itens da compra</h3>
            @foreach($pedido->itens as $i)
                <div class="order-ux-item">
                    <img src="{{ $i->produto->galeria[0]->img ?? asset('img/no-image.png') }}" alt="{{ $i->produto->produto->nome ?? 'Produto' }}">
                    <div>
                        <h4>{{ $i->produto->produto->nome ?? 'Produto' }}</h4>
                        <div class="order-ux-muted">{{ $i->quantidade }} {{ $i->quantidade == 1 ? 'unidade' : 'unidades' }} · R$ {{ number_format((float)$i->produto->valor, 2, ',', '.') }} cada</div>
                    </div>
                    <div class="order-ux-price">R$ {{ number_format((float)$i->quantidade * (float)$i->produto->valor, 2, ',', '.') }}</div>
                </div>
            @endforeach
        </section>

        <aside class="order-ux-side">
            <div class="order-ux-card order-ux-box">
                <h4>Entrega</h4>
                @if($pedido->endereco)
                    <div class="order-ux-muted" style="line-height:1.65">
                        <strong style="display:block;color:#111827;font-size:13px">{{ $pedido->endereco->rua }}, {{ $pedido->endereco->numero }}</strong>
                        {{ $pedido->endereco->bairro }}<br>
                        {{ $pedido->endereco->cidade }} - {{ $pedido->endereco->uf }}<br>
                        CEP {{ $pedido->endereco->cep }}
                    </div>
                @else
                    <div class="order-ux-muted">Endereço não disponível.</div>
                @endif

                @if($pedido->codigo_rastreio)
                    <div style="margin-top:16px">
                        <div class="order-ux-kicker">Código de rastreio</div>
                        <div class="order-ux-track">
                            <code id="tracking-code">{{ $pedido->codigo_rastreio }}</code>
                            <button type="button" id="copy-tracking" class="order-ux-btn secondary" style="padding:8px 10px;width:auto">Copiar</button>
                        </div>
                        <div id="tracking-feedback" class="order-ux-note" aria-live="polite"></div>
                    </div>
                @endif
            </div>

            <div class="order-ux-card order-ux-box">
                <h4>Resumo</h4>
                <div class="order-ux-row"><span>Produtos</span><strong>R$ {{ number_format((float)$pedido->somaItens(), 2, ',', '.') }}</strong></div>
                <div class="order-ux-row"><span>Frete</span><strong>R$ {{ number_format((float)$pedido->valor_frete, 2, ',', '.') }}</strong></div>
                @if((float)($pedido->desconto ?? 0) > 0)
                    <div class="order-ux-row"><span>Desconto</span><strong>- R$ {{ number_format((float)$pedido->desconto, 2, ',', '.') }}</strong></div>
                @endif
                <div class="order-ux-row"><span>Total</span><strong class="order-ux-total">R$ {{ number_format((float)$pedido->valor_total, 2, ',', '.') }}</strong></div>
                <div class="order-ux-note">Forma de pagamento: <strong>{{ $pedido->forma_pagamento ?: 'Não informada' }}</strong></div>
            </div>

            <div class="order-ux-actions">
                @if(!$paymentApproved && !$paymentRejected)
                    <button type="button" id="refresh-payment-status" class="order-ux-btn">Atualizar pagamento</button>
                @endif
                <a href="{{ $rota }}" class="order-ux-btn secondary">Continuar comprando</a>
            </div>
        </aside>
    </div>
</div>

@section('javascript')
<script>
(function(){
    const statusUrl = @json(route('ecommerce.secure.status', ['link' => $default['config']->link, 'pedidoId' => $pedido->id]));
    const initialStatus = @json($paymentStatus);
    const refreshButton = document.getElementById('refresh-payment-status');
    const copyButton = document.getElementById('copy-tracking');
    const trackingCode = document.getElementById('tracking-code');
    const trackingFeedback = document.getElementById('tracking-feedback');
    let attempts = 0;
    let checking = false;

    async function checkPaymentStatus(manual = false){
        if(checking) return;
        checking = true;
        if(refreshButton && manual){ refreshButton.disabled = true; refreshButton.textContent = 'Atualizando...'; }
        try{
            const response = await fetch(statusUrl, {headers:{'Accept':'application/json'}});
            const data = await response.json();
            const status = String(data.status || '').toLowerCase();
            if(status === 'approved' || ['rejected','cancelled'].includes(status)){
                window.location.reload();
                return;
            }
        }catch(e){
            console.error('Falha ao atualizar pagamento:', e);
        }finally{
            checking = false;
            if(refreshButton && manual){ refreshButton.disabled = false; refreshButton.textContent = 'Atualizar pagamento'; }
        }
    }

    if(refreshButton) refreshButton.addEventListener('click', () => checkPaymentStatus(true));

    if(!['approved','rejected','cancelled'].includes(initialStatus)){
        const timer = setInterval(() => {
            attempts++;
            checkPaymentStatus(false);
            if(attempts >= 60) clearInterval(timer);
        }, 5000);
    }

    if(copyButton && trackingCode){
        copyButton.addEventListener('click', async () => {
            try{
                await navigator.clipboard.writeText(trackingCode.textContent.trim());
                copyButton.textContent = 'Copiado!';
                if(trackingFeedback) trackingFeedback.textContent = 'Código copiado. Use-o no rastreamento da transportadora.';
                setTimeout(() => copyButton.textContent = 'Copiar', 2200);
            }catch(e){
                if(trackingFeedback) trackingFeedback.textContent = 'Não foi possível copiar automaticamente. Selecione o código acima.';
            }
        });
    }
})();
</script>
@endsection
@endsection