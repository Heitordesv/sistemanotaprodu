@extends('default.layout', ['title' => 'Assinatura e Plano'])

@php
    $statusAssinatura = $assinaturaAtual->status ?? null;
    $statusLabels = [
        'authorized' => 'Ativa',
        'pending' => 'Pendente',
        'paused' => 'Pausada',
        'canceled' => 'Cancelada',
        'cancelled' => 'Cancelada',
        'approved' => 'Aprovada',
        'rejected' => 'Recusada',
    ];
    $statusLabel = $statusLabels[$statusAssinatura] ?? ($statusAssinatura ? ucfirst($statusAssinatura) : 'Sem assinatura');

    $diasRestantes = null;
    $progressoAcesso = 0;
    $intervaloAtual = (int) (optional($plano->plano)->intervalo_dias ?: 30);

    if ($plano->expiracao) {
        $hoje = \Carbon\Carbon::today();
        $vencimento = \Carbon\Carbon::parse($plano->expiracao)->startOfDay();
        $diasRestantes = $hoje->diffInDays($vencimento, false);
        $progressoAcesso = max(0, min(100, (int) round((max(0, $diasRestantes) / max(1, $intervaloAtual)) * 100)));
    }

    $planoAtualId = (int) ($plano->plano_id ?? 0);
    $planoSelecionadoId = (int) ($selectedPlano->id ?? 0);
    $mesmoPlano = $planoSelecionadoId > 0 && $planoSelecionadoId === $planoAtualId;
    $valorAtual = (float) ($plano->getValor() ?? 0);
    $valorSelecionado = (float) ($selectedPlano->valor ?? 0);
    $diferencaValor = $valorSelecionado - $valorAtual;
@endphp

@section('content')
<div class="page-content subscription-page">
    <div class="container-fluid py-2 py-lg-3">
        <div class="row justify-content-center">
            <div class="col-12 col-xxl-11">

                <div class="subscription-hero mb-4">
                    <div class="hero-orb hero-orb-one"></div>
                    <div class="hero-orb hero-orb-two"></div>

                    <div class="row align-items-center g-4 position-relative">
                        <div class="col-12 col-lg-7">
                            <div class="hero-badge mb-3">
                                <i class="bx bx-shield-quarter"></i>
                                Área segura de assinatura
                            </div>

                            <div class="d-flex align-items-center gap-3 mb-3">
                                <span class="hero-icon"><i class="bx bx-crown"></i></span>
                                <div>
                                    <div class="hero-eyebrow">NF-e Notas</div>
                                    <h2 class="hero-title mb-0">Seu plano, do seu jeito.</h2>
                                </div>
                            </div>

                            <p class="hero-text mb-4">
                                Escolha o plano ideal, defina a forma de pagamento e acompanhe a ativação em um só lugar.
                                Nenhuma mudança de acesso acontece antes da aprovação do Mercado Pago.
                            </p>

                            <div class="hero-steps">
                                <div class="hero-step active">
                                    <span>1</span>
                                    <div><strong>Plano</strong><small>Escolha o melhor ciclo</small></div>
                                </div>
                                <div class="hero-step">
                                    <span>2</span>
                                    <div><strong>Pagamento</strong><small>Cartão, PIX ou boleto</small></div>
                                </div>
                                <div class="hero-step">
                                    <span>3</span>
                                    <div><strong>Confirmação</strong><small>Ativação após aprovação</small></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-lg-5">
                            <div class="hero-plan-card">
                                <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
                                    <div>
                                        <span class="hero-plan-label">Plano atual</span>
                                        <div class="hero-plan-name">{{ optional($plano->plano)->nome ?? 'Não definido' }}</div>
                                        <div class="hero-plan-price">R$ {{ __moeda($plano->getValor()) }}</div>
                                    </div>
                                    <span class="status-pill {{ $assinaturaAtiva ? 'status-active' : 'status-neutral' }}">
                                        <i class="bx {{ $assinaturaAtiva ? 'bx-check-circle' : 'bx-time-five' }}"></i>
                                        {{ $statusLabel }}
                                    </span>
                                </div>

                                <div class="access-progress-box">
                                    <div class="d-flex justify-content-between align-items-end gap-3 mb-2">
                                        <div>
                                            <span class="access-label">Acesso</span>
                                            <strong>{{ $plano->expiracao ? __data_pt($plano->expiracao) : 'Sem vencimento informado' }}</strong>
                                        </div>
                                        @if($diasRestantes !== null)
                                            <span class="days-badge {{ $diasRestantes < 0 ? 'expired' : ($diasRestantes <= 7 ? 'warning' : '') }}">
                                                {{ $diasRestantes < 0 ? 'Vencido' : $diasRestantes . ' dia(s)' }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="access-progress">
                                        <span style="width: {{ $progressoAcesso }}%"></span>
                                    </div>
                                </div>

                                <div class="hero-plan-meta mt-4">
                                    <div>
                                        <span>Empresa</span>
                                        <strong>{{ $empresa->nome_fantasia ?? $empresa->razao_social }}</strong>
                                    </div>
                                    <div>
                                        <span>Renovação</span>
                                        <strong>{{ $assinaturaAtiva ? 'Automática' : 'Manual' }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="section-card plan-section mb-4">
                    <div class="section-header align-items-lg-center">
                        <div>
                            <span class="section-kicker">Etapa 1</span>
                            <h4 class="section-title mb-1">Escolha seu plano</h4>
                            <p class="text-muted mb-0">Selecione o plano que deseja contratar ou renovar.</p>
                        </div>
                        @if($selectedPlano)
                            <div class="selected-summary">
                                <span class="selected-summary-icon"><i class="bx bx-check"></i></span>
                                <div>
                                    <small>Selecionado agora</small>
                                    <strong>{{ $selectedPlano->nome }}</strong>
                                </div>
                                <b>R$ {{ __moeda($selectedPlano->valor) }}</b>
                            </div>
                        @endif
                    </div>

                    <div class="row g-3 g-lg-4 mt-1">
                        @forelse($planosDisponiveis as $itemPlano)
                            @php
                                $isSelected = $selectedPlano && (int) $selectedPlano->id === (int) $itemPlano->id;
                                $isCurrent = (int) $itemPlano->id === $planoAtualId;
                            @endphp
                            <div class="col-12 col-md-6 col-xl-4">
                                <a href="{{ route('payment.finish', ['empresa_id' => $empresa->id, 'plano_id' => $itemPlano->id]) }}" class="plan-link">
                                    <div class="plan-card {{ $isSelected ? 'selected' : '' }}">
                                        <div class="plan-card-top">
                                            <div>
                                                <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                                    <span class="plan-name">{{ $itemPlano->nome }}</span>
                                                    @if($isCurrent)
                                                        <span class="current-plan-badge">Atual</span>
                                                    @endif
                                                </div>
                                                <span class="plan-cycle"><i class="bx bx-calendar"></i> {{ $itemPlano->intervalo_dias ?: 30 }} dias de acesso</span>
                                            </div>
                                            <span class="plan-check">
                                                <i class="bx {{ $isSelected ? 'bx-check' : 'bx-chevron-right' }}"></i>
                                            </span>
                                        </div>

                                        <div class="plan-price-row">
                                            <div class="plan-price">
                                                <span>R$</span>
                                                <strong>{{ __moeda($itemPlano->valor) }}</strong>
                                            </div>
                                            <small>por ciclo</small>
                                        </div>

                                        <div class="plan-divider"></div>

                                        <div class="plan-benefit"><i class="bx bx-check-circle"></i> Ativação após pagamento aprovado</div>
                                        <div class="plan-benefit"><i class="bx bx-check-circle"></i> Pagamento por cartão, PIX ou boleto</div>
                                        <div class="plan-benefit"><i class="bx bx-check-circle"></i> Controle direto nesta área</div>

                                        <div class="plan-action mt-4">
                                            <span>{{ $isSelected ? 'Plano selecionado' : 'Selecionar este plano' }}</span>
                                            <i class="bx {{ $isSelected ? 'bx-check-circle' : 'bx-right-arrow-alt' }}"></i>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="empty-state">
                                    <span><i class="bx bx-package"></i></span>
                                    <strong>Nenhum plano disponível</strong>
                                    <p class="mb-0">Não há planos visíveis para contratação neste momento.</p>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>

                @if($selectedPlano)
                    <div class="row g-4 align-items-start">
                        <div class="col-12 col-xl-8">
                            <div class="section-card payment-card">
                                <div class="section-header payment-header">
                                    <div>
                                        <span class="section-kicker">Etapa 2</span>
                                        <h4 class="section-title mb-1">Como você quer pagar?</h4>
                                        <p class="text-muted mb-0">Escolha uma opção para continuar com o plano {{ $selectedPlano->nome }}.</p>
                                    </div>
                                    <span class="secure-badge"><i class="bx bx-lock-alt"></i> Processado pelo Mercado Pago</span>
                                </div>

                                <div class="payment-methods" role="tablist" aria-label="Formas de pagamento">
                                    <button class="payment-method active" type="button" data-method="card" onclick="selecionarMetodo('card', this)">
                                        <span class="payment-method-icon method-card"><i class="bx bx-credit-card"></i></span>
                                        <span class="payment-method-copy">
                                            <strong>Cartão de crédito</strong>
                                            <small>Renovação automática</small>
                                        </span>
                                        <span class="payment-method-tag">Recomendado para recorrência</span>
                                        <i class="bx bx-chevron-right payment-chevron"></i>
                                    </button>

                                    <button class="payment-method" type="button" data-method="pix" onclick="selecionarMetodo('pix', this)">
                                        <span class="payment-method-icon method-pix"><i class="bx bx-qr-scan"></i></span>
                                        <span class="payment-method-copy">
                                            <strong>PIX</strong>
                                            <small>Pagamento deste ciclo</small>
                                        </span>
                                        <span class="payment-method-tag neutral">QR Code na hora</span>
                                        <i class="bx bx-chevron-right payment-chevron"></i>
                                    </button>

                                    <button class="payment-method" type="button" data-method="boleto" onclick="selecionarMetodo('boleto', this)">
                                        <span class="payment-method-icon method-boleto"><i class="bx bx-barcode"></i></span>
                                        <span class="payment-method-copy">
                                            <strong>Boleto</strong>
                                            <small>Pagamento deste ciclo</small>
                                        </span>
                                        <span class="payment-method-tag neutral">Link para pagamento</span>
                                        <i class="bx bx-chevron-right payment-chevron"></i>
                                    </button>
                                </div>

                                <div class="payment-panel active" id="paymentPanel-card">
                                    <div class="payment-panel-head">
                                        <div class="payment-panel-title">
                                            <span class="panel-icon method-card"><i class="bx bx-credit-card"></i></span>
                                            <div>
                                                <h5 class="mb-1">Cartão de crédito</h5>
                                                <p class="text-muted mb-0">Ideal para manter a assinatura renovando automaticamente.</p>
                                            </div>
                                        </div>
                                        <span class="panel-value">R$ {{ __moeda($selectedPlano->valor) }}</span>
                                    </div>

                                    @if(empty($mpPublicKey))
                                        <div class="alert alert-danger soft-alert mb-0">
                                            <i class="bx bx-error-circle"></i>
                                            <div>
                                                <strong>Mercado Pago não configurado</strong>
                                                <span>Public Key não encontrada na ConfigEcommerce da empresa 1.</span>
                                            </div>
                                        </div>
                                    @else
                                        <div id="cardStart" class="card-start-box">
                                            <div class="card-visual mb-4">
                                                <div class="card-chip"></div>
                                                <i class="bx bx-wifi card-contactless"></i>
                                                <span class="card-number-preview">•••• •••• •••• ••••</span>
                                                <div class="d-flex justify-content-between align-items-end">
                                                    <div><small>TITULAR</small><strong>{{ strtoupper($empresa->nome_fantasia ?? $empresa->razao_social ?? 'CLIENTE') }}</strong></div>
                                                    <i class="bx bx-credit-card-alt"></i>
                                                </div>
                                            </div>

                                            <button id="btnCarregarCartao" type="button" class="btn btn-primary btn-lg w-100 primary-action" onclick="iniciarCartao()">
                                                <i class="bx bx-lock-open-alt me-1"></i>
                                                {{ $assinaturaAtiva ? 'Alterar cartão ou assinatura' : 'Continuar com cartão' }}
                                            </button>

                                            <div class="security-note">
                                                <span><i class="bx bx-shield-quarter"></i></span>
                                                <div>
                                                    <strong>Dados protegidos</strong>
                                                    <p class="mb-0">O cartão é tokenizado diretamente pelo Mercado Pago. O NF-e Notas não armazena o número do cartão.</p>
                                                </div>
                                            </div>
                                        </div>

                                        <div id="cardLoading" class="checkout-loading d-none">
                                            <div class="spinner-border text-primary" role="status"></div>
                                            <div>
                                                <strong>Preparando pagamento seguro</strong>
                                                <span>Carregando o ambiente do Mercado Pago...</span>
                                            </div>
                                        </div>

                                        <div id="cardError" class="alert alert-danger d-none"></div>

                                        <form id="form-checkout" class="d-none card-form" autocomplete="off">
                                            <div class="form-section-title">
                                                <span>Dados do cartão</span>
                                                <small>Preencha os dados abaixo</small>
                                            </div>

                                            <div class="row g-3">
                                                <div class="col-12">
                                                    <label class="form-label">Número do cartão</label>
                                                    <div id="form-checkout__cardNumber" class="mp-field"></div>
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label">Validade</label>
                                                    <div id="form-checkout__expirationDate" class="mp-field"></div>
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label">CVV</label>
                                                    <div id="form-checkout__securityCode" class="mp-field"></div>
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label">Nome do titular</label>
                                                    <input id="form-checkout__cardholderName" class="form-control form-control-lg" type="text" placeholder="Como está no cartão">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Documento</label>
                                                    <select id="form-checkout__identificationType" class="form-select form-select-lg"></select>
                                                </div>
                                                <div class="col-md-8">
                                                    <label class="form-label">CPF/CNPJ</label>
                                                    <input id="form-checkout__identificationNumber" class="form-control form-control-lg" type="text" inputmode="numeric" placeholder="Somente números">
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label">E-mail</label>
                                                    <input id="form-checkout__cardholderEmail" class="form-control form-control-lg" type="email" value="{{ $payerEmail }}">
                                                </div>
                                                <div class="d-none">
                                                    <select id="form-checkout__issuer"></select>
                                                    <select id="form-checkout__installments"></select>
                                                </div>
                                                <div class="col-12 pt-2">
                                                    <button id="form-checkout__submit" class="btn btn-primary btn-lg w-100 primary-action" type="submit">
                                                        <i class="bx bx-lock-alt me-1"></i>
                                                        {{ $assinaturaAtiva ? 'Confirmar alteração da assinatura' : 'Assinar e pagar com cartão' }}
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    @endif
                                </div>

                                <div class="payment-panel" id="paymentPanel-pix">
                                    <div class="payment-panel-head">
                                        <div class="payment-panel-title">
                                            <span class="panel-icon method-pix"><i class="bx bx-qr-scan"></i></span>
                                            <div>
                                                <h5 class="mb-1">Pagamento por PIX</h5>
                                                <p class="text-muted mb-0">Gere o QR Code e pague pelo aplicativo do seu banco.</p>
                                            </div>
                                        </div>
                                        <span class="panel-value">R$ {{ __moeda($selectedPlano->valor) }}</span>
                                    </div>

                                    <div class="instant-payment-box">
                                        <div class="instant-payment-icon"><i class="bx bx-qr-scan"></i></div>
                                        <div>
                                            <strong>PIX copia e cola + QR Code</strong>
                                            <p class="mb-0">Após o pagamento, esta tela acompanha a confirmação automaticamente.</p>
                                        </div>
                                    </div>

                                    <button id="btnPix" type="button" class="btn btn-success btn-lg w-100 primary-action" onclick="gerarPixPlano()">
                                        <i class="bx bx-qr me-1"></i> Gerar PIX de R$ {{ __moeda($selectedPlano->valor) }}
                                    </button>

                                    <div class="payment-footnote"><i class="bx bx-info-circle"></i> O plano só será ativado depois que o Mercado Pago retornar o pagamento como aprovado.</div>
                                </div>

                                <div class="payment-panel" id="paymentPanel-boleto">
                                    <div class="payment-panel-head">
                                        <div class="payment-panel-title">
                                            <span class="panel-icon method-boleto"><i class="bx bx-barcode"></i></span>
                                            <div>
                                                <h5 class="mb-1">Pagamento por boleto</h5>
                                                <p class="text-muted mb-0">Gere o boleto e acompanhe a compensação nesta mesma área.</p>
                                            </div>
                                        </div>
                                        <span class="panel-value">R$ {{ __moeda($selectedPlano->valor) }}</span>
                                    </div>

                                    <div class="instant-payment-box boleto-box">
                                        <div class="instant-payment-icon"><i class="bx bx-barcode-reader"></i></div>
                                        <div>
                                            <strong>Boleto Mercado Pago</strong>
                                            <p class="mb-0">Você receberá o link para abrir e pagar o boleto em uma nova aba.</p>
                                        </div>
                                    </div>

                                    <button id="btnBoleto" type="button" class="btn btn-info btn-lg w-100 text-white primary-action" onclick="gerarBoletoPlano()">
                                        <i class="bx bx-barcode-reader me-1"></i> Gerar boleto de R$ {{ __moeda($selectedPlano->valor) }}
                                    </button>

                                    <div class="payment-footnote"><i class="bx bx-info-circle"></i> O acesso permanece no plano atual até a confirmação do pagamento.</div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-xl-4">
                            <div class="checkout-sidebar">
                                <div class="section-card summary-card mb-4">
                                    <div class="summary-header">
                                        <div>
                                            <span class="section-kicker">Etapa 3</span>
                                            <h5 class="mb-0">Resumo da contratação</h5>
                                        </div>
                                        <span class="summary-icon"><i class="bx bx-receipt"></i></span>
                                    </div>

                                    <div class="plan-change-box">
                                        <div class="plan-change-item">
                                            <span>Plano atual</span>
                                            <strong>{{ optional($plano->plano)->nome ?? 'Não definido' }}</strong>
                                            <small>R$ {{ __moeda($plano->getValor()) }}</small>
                                        </div>
                                        <span class="plan-change-arrow"><i class="bx bx-right-arrow-alt"></i></span>
                                        <div class="plan-change-item selected">
                                            <span>Novo plano</span>
                                            <strong>{{ $selectedPlano->nome }}</strong>
                                            <small>R$ {{ __moeda($selectedPlano->valor) }}</small>
                                        </div>
                                    </div>

                                    @if($mesmoPlano)
                                        <div class="change-message neutral-message">
                                            <i class="bx bx-refresh"></i>
                                            <span>Você está renovando o mesmo plano.</span>
                                        </div>
                                    @elseif($diferencaValor > 0)
                                        <div class="change-message upgrade-message">
                                            <i class="bx bx-up-arrow-alt"></i>
                                            <span>Diferença de R$ {{ __moeda(abs($diferencaValor)) }} em relação ao plano atual.</span>
                                        </div>
                                    @elseif($diferencaValor < 0)
                                        <div class="change-message saving-message">
                                            <i class="bx bx-down-arrow-alt"></i>
                                            <span>Economia de R$ {{ __moeda(abs($diferencaValor)) }} por ciclo.</span>
                                        </div>
                                    @endif

                                    <div class="order-summary mt-4">
                                        <div class="summary-row">
                                            <span>Ciclo</span>
                                            <strong>{{ $selectedPlano->intervalo_dias ?: 30 }} dias</strong>
                                        </div>
                                        <div class="summary-row">
                                            <span>Ativação</span>
                                            <strong>Após aprovação</strong>
                                        </div>
                                        <div class="summary-row total">
                                            <span>Total</span>
                                            <strong>R$ {{ __moeda($selectedPlano->valor) }}</strong>
                                        </div>
                                    </div>

                                    <div class="approval-info mt-4">
                                        <span><i class="bx bx-check-shield"></i></span>
                                        <div>
                                            <strong>Proteção contra ativação antecipada</strong>
                                            <p class="mb-0">Seu plano atual permanece válido até o pagamento escolhido ser confirmado.</p>
                                        </div>
                                    </div>
                                </div>

                                @if($assinaturaAtiva)
                                    <div class="section-card subscription-management">
                                        <div class="d-flex align-items-start gap-3">
                                            <span class="management-icon"><i class="bx bx-refresh"></i></span>
                                            <div class="flex-grow-1">
                                                <span class="section-kicker danger-kicker">Gerenciar assinatura</span>
                                                <h6 class="mb-1">Renovação automática ativa</h6>
                                                <p class="text-muted small mb-3">Ao cancelar, os dias já pagos continuam válidos até o vencimento atual.</p>
                                                <button type="button" class="btn btn-outline-danger w-100" onclick="cancelarAssinatura()">
                                                    <i class="bx bx-x-circle me-1"></i> Cancelar renovação automática
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalPagamento" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content payment-modal border-0 shadow-lg">
            <div class="modal-body p-4 p-md-5 text-center" id="modalBody"></div>
        </div>
    </div>
</div>
@endsection

@section('css')
<style>
.subscription-page{--sub-primary:#4f46e5;--sub-primary-dark:#3730a3;--sub-primary-soft:#eef2ff;--sub-success:#16a34a;--sub-info:#0891b2;--sub-warning:#ea580c;--sub-danger:#dc2626;--sub-border:#e6e9f0;--sub-text:#111827;--sub-muted:#6b7280;--sub-bg:#f5f7fb;background:radial-gradient(circle at top right,rgba(79,70,229,.05),transparent 28%),var(--sub-bg);min-height:100vh;color:var(--sub-text)}
.subscription-page *{box-sizing:border-box}
.subscription-hero{position:relative;overflow:hidden;border-radius:28px;padding:34px;background:linear-gradient(135deg,#0f172a 0%,#312e81 48%,#4f46e5 100%);box-shadow:0 22px 55px rgba(15,23,42,.16)}
.hero-orb{position:absolute;border-radius:50%;pointer-events:none}.hero-orb-one{width:300px;height:300px;right:-100px;top:-150px;background:rgba(255,255,255,.09)}.hero-orb-two{width:220px;height:220px;left:42%;bottom:-170px;background:rgba(129,140,248,.22)}
.hero-badge{display:inline-flex;align-items:center;gap:7px;padding:7px 11px;border-radius:999px;border:1px solid rgba(255,255,255,.14);background:rgba(255,255,255,.08);color:#e0e7ff;font-size:11px;font-weight:700;backdrop-filter:blur(8px)}
.hero-icon{width:58px;height:58px;border-radius:18px;background:rgba(255,255,255,.14);display:inline-flex;align-items:center;justify-content:center;color:#fff;font-size:30px;backdrop-filter:blur(8px);box-shadow:inset 0 0 0 1px rgba(255,255,255,.1)}
.hero-eyebrow{text-transform:uppercase;letter-spacing:.15em;font-size:11px;font-weight:800;color:#c7d2fe;margin-bottom:2px}.hero-title{color:#fff;font-size:clamp(1.7rem,3vw,2.35rem);font-weight:800;letter-spacing:-.03em}.hero-text{max-width:720px;color:#dbe4ff;font-size:15px;line-height:1.7}
.hero-steps{display:flex;gap:12px;flex-wrap:wrap}.hero-step{display:flex;align-items:center;gap:9px;padding:9px 12px;border-radius:14px;background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.1);min-width:150px}.hero-step>span{width:28px;height:28px;border-radius:9px;background:rgba(255,255,255,.12);display:flex;align-items:center;justify-content:center;color:#fff;font-size:11px;font-weight:800}.hero-step strong,.hero-step small{display:block}.hero-step strong{color:#fff;font-size:11px}.hero-step small{color:#cbd5e1;font-size:9px;margin-top:1px}.hero-step.active{background:rgba(99,102,241,.22);border-color:rgba(199,210,254,.22)}.hero-step.active>span{background:#fff;color:#4338ca}
.hero-plan-card{background:rgba(255,255,255,.11);border:1px solid rgba(255,255,255,.18);border-radius:22px;padding:22px;color:#fff;backdrop-filter:blur(12px);box-shadow:inset 0 1px 0 rgba(255,255,255,.08)}
.hero-plan-label{display:block;color:#cbd5e1;font-size:11px;text-transform:uppercase;letter-spacing:.08em;margin-bottom:4px}.hero-plan-name{font-size:22px;font-weight:800;letter-spacing:-.02em}.hero-plan-price{font-size:14px;color:#e0e7ff;margin-top:2px}.status-pill{display:inline-flex;align-items:center;gap:6px;padding:7px 10px;border-radius:999px;font-size:11px;font-weight:700;white-space:nowrap}.status-active{background:rgba(34,197,94,.16);color:#bbf7d0;border:1px solid rgba(134,239,172,.22)}.status-neutral{background:rgba(255,255,255,.09);color:#e5e7eb;border:1px solid rgba(255,255,255,.14)}
.access-progress-box{padding:15px;border-radius:16px;background:rgba(15,23,42,.17);border:1px solid rgba(255,255,255,.08)}.access-label{display:block;font-size:10px;color:#cbd5e1;text-transform:uppercase;letter-spacing:.07em}.access-progress-box strong{display:block;font-size:13px;margin-top:2px}.days-badge{padding:5px 8px;border-radius:999px;background:rgba(34,197,94,.16);color:#bbf7d0;font-size:10px;font-weight:700}.days-badge.warning{background:rgba(245,158,11,.18);color:#fde68a}.days-badge.expired{background:rgba(239,68,68,.18);color:#fecaca}.access-progress{height:7px;background:rgba(255,255,255,.1);border-radius:999px;overflow:hidden}.access-progress span{display:block;height:100%;border-radius:inherit;background:linear-gradient(90deg,#a5b4fc,#fff);min-width:4px}.hero-plan-meta{display:grid;grid-template-columns:1fr 1fr;gap:14px;padding-top:16px;border-top:1px solid rgba(255,255,255,.13)}.hero-plan-meta span{display:block;font-size:10px;color:#cbd5e1;text-transform:uppercase;letter-spacing:.05em}.hero-plan-meta strong{display:block;font-size:12px;margin-top:3px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.section-card{background:#fff;border:1px solid var(--sub-border);border-radius:22px;padding:26px;box-shadow:0 10px 34px rgba(15,23,42,.045)}.section-header{display:flex;align-items:flex-start;justify-content:space-between;gap:20px;margin-bottom:20px}.section-kicker{display:block;text-transform:uppercase;letter-spacing:.09em;font-size:10px;font-weight:800;color:var(--sub-primary);margin-bottom:5px}.section-title{font-size:20px;font-weight:800;letter-spacing:-.02em}.selected-summary{display:flex;align-items:center;gap:10px;background:linear-gradient(135deg,#eef2ff,#f8faff);border:1px solid #dfe3ff;border-radius:16px;padding:10px 12px;white-space:nowrap}.selected-summary-icon{width:34px;height:34px;border-radius:11px;background:var(--sub-primary);color:#fff;display:flex;align-items:center;justify-content:center;font-size:18px}.selected-summary small,.selected-summary strong{display:block}.selected-summary small{font-size:9px;color:#6b7280}.selected-summary strong{font-size:12px;color:#312e81}.selected-summary b{font-size:13px;color:#111827;margin-left:4px}
.plan-link{text-decoration:none;color:inherit}.plan-card{height:100%;padding:21px;border:1.5px solid var(--sub-border);border-radius:19px;background:#fff;transition:all .2s ease;position:relative;overflow:hidden}.plan-card:before{content:"";position:absolute;inset:0 auto 0 0;width:3px;background:transparent;transition:.2s}.plan-card:hover{transform:translateY(-3px);border-color:#c7d2fe;box-shadow:0 16px 34px rgba(79,70,229,.08)}.plan-card.selected{border-color:#8b8bf4;background:linear-gradient(180deg,#fff 0%,#f8f8ff 100%);box-shadow:0 14px 34px rgba(79,70,229,.1)}.plan-card.selected:before{background:linear-gradient(180deg,#4f46e5,#818cf8)}.plan-card-top{display:flex;align-items:flex-start;justify-content:space-between;gap:12px}.plan-name{font-weight:800;font-size:17px;color:var(--sub-text)}.current-plan-badge{display:inline-flex;align-items:center;padding:3px 7px;border-radius:999px;background:#f1f5f9;color:#475569;font-size:9px;font-weight:700}.plan-cycle{display:flex;align-items:center;gap:5px;font-size:11px;color:var(--sub-muted)}.plan-check{width:31px;height:31px;border-radius:10px;background:#f3f4f6;color:#6b7280;display:inline-flex;align-items:center;justify-content:center;font-size:19px;flex:0 0 auto}.plan-card.selected .plan-check{background:var(--sub-primary);color:#fff}.plan-price-row{display:flex;align-items:end;justify-content:space-between;gap:10px;margin-top:24px}.plan-price span{font-size:12px;color:var(--sub-muted);margin-right:4px}.plan-price strong{font-size:28px;line-height:1;color:var(--sub-text);letter-spacing:-.03em}.plan-price-row>small{font-size:10px;color:var(--sub-muted)}.plan-divider{height:1px;background:#eef0f4;margin:18px 0 14px}.plan-benefit{display:flex;align-items:center;gap:7px;font-size:11px;color:#4b5563;margin-top:8px}.plan-benefit i{color:#16a34a;font-size:16px}.plan-action{display:flex;align-items:center;justify-content:space-between;padding:11px 12px;border-radius:12px;background:#f8fafc;color:#4f46e5;font-size:11px;font-weight:800}.plan-card.selected .plan-action{background:#eef2ff;color:#3730a3}.empty-state{text-align:center;padding:35px 20px;border:1px dashed #d7dce6;border-radius:18px;background:#fafbfc}.empty-state>span{width:50px;height:50px;border-radius:15px;display:inline-flex;align-items:center;justify-content:center;background:#eef2ff;color:#4f46e5;font-size:25px}.empty-state strong,.empty-state p{display:block}.empty-state strong{margin-top:12px}.empty-state p{color:var(--sub-muted);font-size:12px;margin-top:4px}
.payment-card{overflow:hidden}.payment-header{align-items:center}.secure-badge{display:inline-flex;align-items:center;gap:6px;font-size:10px;font-weight:700;color:#15803d;background:#f0fdf4;border:1px solid #dcfce7;padding:7px 10px;border-radius:999px;white-space:nowrap}.payment-methods{display:flex;flex-direction:column;gap:10px;margin-bottom:24px}.payment-method{width:100%;border:1px solid var(--sub-border);border-radius:16px;background:#fff;padding:13px 14px;display:flex;align-items:center;gap:12px;text-align:left;transition:.18s ease;color:var(--sub-text)}.payment-method:hover{border-color:#c7d2fe;background:#fcfcff}.payment-method.active{border-color:#8b8bf4;background:linear-gradient(90deg,#f8f8ff,#fff);box-shadow:0 7px 20px rgba(79,70,229,.07)}.payment-method-icon,.panel-icon{width:42px;height:42px;border-radius:13px;display:inline-flex;align-items:center;justify-content:center;font-size:21px;flex:0 0 auto}.method-card{background:#eef2ff;color:#4f46e5}.method-pix{background:#ecfdf5;color:#16a34a}.method-boleto{background:#ecfeff;color:#0891b2}.payment-method-copy{display:flex;flex-direction:column;min-width:0}.payment-method-copy strong{font-size:13px}.payment-method-copy small{font-size:10px;color:var(--sub-muted);margin-top:1px}.payment-method-tag{margin-left:auto;padding:5px 8px;border-radius:999px;background:#eef2ff;color:#4f46e5;font-size:9px;font-weight:700;white-space:nowrap}.payment-method-tag.neutral{background:#f3f4f6;color:#6b7280}.payment-chevron{font-size:20px;color:#9ca3af}.payment-method.active .payment-chevron{color:#4f46e5}.payment-panel{display:none;border-top:1px solid #eef0f4;padding-top:24px}.payment-panel.active{display:block}.payment-panel-head{display:flex;align-items:flex-start;justify-content:space-between;gap:18px;margin-bottom:22px}.payment-panel-title{display:flex;align-items:center;gap:12px}.payment-panel-title h5{font-size:16px;font-weight:800}.payment-panel-title p{font-size:11px}.panel-value{font-size:16px;font-weight:800;color:#111827;white-space:nowrap}.soft-alert{display:flex;gap:10px;align-items:flex-start;border-radius:15px}.soft-alert>i{font-size:20px}.soft-alert strong,.soft-alert span{display:block}.soft-alert span{font-size:11px;margin-top:2px}.card-start-box{max-width:610px}.card-visual{width:min(100%,390px);aspect-ratio:1.58/1;border-radius:20px;padding:22px;background:linear-gradient(135deg,#171c36 0%,#3730a3 55%,#6366f1 100%);color:#fff;box-shadow:0 18px 36px rgba(49,46,129,.25);position:relative;overflow:hidden}.card-visual:after{content:"";position:absolute;width:180px;height:180px;border-radius:50%;background:rgba(255,255,255,.08);right:-65px;top:-70px}.card-chip{width:38px;height:29px;border-radius:7px;background:linear-gradient(135deg,#fcd34d,#f59e0b);box-shadow:inset 0 0 0 1px rgba(255,255,255,.25)}.card-contactless{position:absolute;right:22px;top:23px;font-size:24px;transform:rotate(90deg);opacity:.85}.card-number-preview{display:block;font-size:20px;letter-spacing:.13em;margin:36px 0 24px;font-weight:600}.card-visual small,.card-visual strong{display:block}.card-visual small{font-size:8px;color:#c7d2fe;letter-spacing:.1em}.card-visual strong{font-size:10px;margin-top:3px;max-width:240px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.card-visual .bx-credit-card-alt{font-size:30px;opacity:.85}.primary-action{min-height:52px;border-radius:13px;font-weight:700;font-size:13px;box-shadow:none}.security-note{display:flex;align-items:flex-start;gap:10px;color:var(--sub-muted);font-size:11px;line-height:1.5;margin-top:15px}.security-note>span{width:34px;height:34px;border-radius:11px;background:#ecfdf5;color:#16a34a;display:flex;align-items:center;justify-content:center;font-size:18px;flex:0 0 auto}.security-note strong{display:block;color:#374151;font-size:11px}.checkout-loading{display:flex;align-items:center;gap:13px;padding:28px 12px;border:1px dashed #d8dde7;border-radius:15px;background:#fafbfc}.checkout-loading strong,.checkout-loading span{display:block}.checkout-loading strong{font-size:12px}.checkout-loading span{font-size:10px;color:var(--sub-muted);margin-top:2px}.card-form{max-width:660px}.form-section-title{display:flex;align-items:end;justify-content:space-between;gap:10px;margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid #eef0f4}.form-section-title span{font-size:13px;font-weight:800}.form-section-title small{font-size:10px;color:var(--sub-muted)}.card-form .form-label{font-size:11px;font-weight:700;color:#374151;margin-bottom:6px}.card-form .form-control,.card-form .form-select{border-radius:11px;border-color:#d9dde5;font-size:13px}.mp-field{height:48px;border:1px solid #d9dde5;border-radius:11px;padding:11px 12px;background:#fff}.mp-field:focus-within{border-color:#818cf8;box-shadow:0 0 0 .2rem rgba(79,70,229,.09)}.instant-payment-box{display:flex;align-items:center;gap:14px;padding:18px;border-radius:16px;background:#f0fdf4;border:1px solid #dcfce7;margin-bottom:18px}.instant-payment-box.boleto-box{background:#ecfeff;border-color:#cffafe}.instant-payment-icon{width:50px;height:50px;border-radius:15px;background:#fff;color:#16a34a;display:flex;align-items:center;justify-content:center;font-size:25px;flex:0 0 auto;box-shadow:0 5px 15px rgba(15,23,42,.04)}.boleto-box .instant-payment-icon{color:#0891b2}.instant-payment-box strong{font-size:12px}.instant-payment-box p{font-size:10px;color:#5f6b7a;margin-top:2px}.payment-footnote{display:flex;align-items:flex-start;gap:6px;color:#6b7280;font-size:10px;line-height:1.5;margin-top:12px}.payment-footnote i{font-size:15px;color:#9ca3af}
.checkout-sidebar{position:sticky;top:88px}.summary-card{background:linear-gradient(180deg,#fff 0%,#fbfbff 100%)}.summary-header{display:flex;justify-content:space-between;align-items:start;gap:12px}.summary-header h5{font-weight:800;font-size:16px}.summary-icon{width:40px;height:40px;border-radius:13px;background:#eef2ff;color:#4f46e5;display:flex;align-items:center;justify-content:center;font-size:21px}.plan-change-box{display:grid;grid-template-columns:1fr auto 1fr;gap:10px;align-items:center;margin-top:22px;padding:14px;border:1px solid var(--sub-border);border-radius:16px;background:#fff}.plan-change-item span,.plan-change-item strong,.plan-change-item small{display:block}.plan-change-item span{font-size:9px;color:var(--sub-muted);text-transform:uppercase;letter-spacing:.05em}.plan-change-item strong{font-size:12px;margin-top:2px}.plan-change-item small{font-size:10px;color:#6b7280;margin-top:2px}.plan-change-item.selected strong{color:#4338ca}.plan-change-arrow{width:28px;height:28px;border-radius:9px;background:#eef2ff;color:#4f46e5;display:flex;align-items:center;justify-content:center;font-size:17px}.change-message{display:flex;align-items:flex-start;gap:7px;padding:10px 11px;border-radius:12px;font-size:10px;font-weight:600;margin-top:10px}.neutral-message{background:#f8fafc;color:#475569}.upgrade-message{background:#fff7ed;color:#c2410c}.saving-message{background:#ecfdf5;color:#15803d}.change-message i{font-size:15px}.order-summary{border:1px solid var(--sub-border);border-radius:16px;overflow:hidden;background:#fff}.summary-row{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:13px 15px;border-bottom:1px solid var(--sub-border);font-size:12px}.summary-row:last-child{border-bottom:0}.summary-row span{color:var(--sub-muted)}.summary-row.total{background:#f8f8ff}.summary-row.total strong{font-size:18px;color:var(--sub-primary)}.approval-info{display:flex;gap:11px;background:#f8fafc;border-radius:15px;padding:13px}.approval-info>span{width:34px;height:34px;display:flex;align-items:center;justify-content:center;border-radius:10px;background:#ecfdf5;color:#16a34a;font-size:19px;flex:0 0 auto}.approval-info strong{font-size:11px}.approval-info p{font-size:10px;color:var(--sub-muted);line-height:1.45;margin-top:2px}.subscription-management{border-color:#fee2e2;background:linear-gradient(180deg,#fff,#fffafa)}.management-icon{width:40px;height:40px;border-radius:12px;background:#fef2f2;color:#dc2626;display:inline-flex;align-items:center;justify-content:center;font-size:21px;flex:0 0 auto}.danger-kicker{color:#dc2626}.payment-modal{border-radius:22px}
@media(max-width:1199.98px){.checkout-sidebar{position:static;top:auto}}
@media(max-width:991.98px){.subscription-hero{padding:25px}.hero-steps{gap:8px}.hero-step{min-width:135px}.section-header{flex-direction:column}.selected-summary{width:100%;white-space:normal}.selected-summary b{margin-left:auto}.payment-method-tag{display:none}.hero-plan-meta{grid-template-columns:1fr 1fr}}
@media(max-width:767.98px){.subscription-page{background:#f7f8fb}.subscription-hero{border-radius:20px}.hero-steps{display:grid;grid-template-columns:1fr}.hero-step{min-width:0}.payment-panel-head{flex-direction:column}.panel-value{font-size:20px}.card-visual{width:100%}.plan-change-box{grid-template-columns:1fr}.plan-change-arrow{transform:rotate(90deg);margin:auto}.payment-method{align-items:center}.payment-chevron{margin-left:auto}}
@media(max-width:575.98px){.subscription-page .container-fluid{padding-left:12px;padding-right:12px}.subscription-hero{padding:19px}.hero-icon{width:50px;height:50px;border-radius:15px;font-size:26px}.hero-title{font-size:1.55rem}.hero-text{font-size:13px}.hero-plan-card{padding:17px}.hero-plan-meta{grid-template-columns:1fr}.section-card{padding:18px;border-radius:18px}.section-title{font-size:18px}.plan-card{padding:17px}.plan-price strong{font-size:25px}.payment-method{padding:11px}.payment-method-icon{width:39px;height:39px}.payment-method-copy strong{font-size:12px}.selected-summary{align-items:center}.selected-summary b{font-size:12px}.card-number-preview{font-size:16px;margin:28px 0 20px}.card-visual{padding:18px}.summary-card{padding:18px}}
</style>
@endsection

@section('js')
<script>
const empresaId = {{ (int) $empresa->id }};
const planoEmpresaId = {{ (int) $plano->id }};
const selectedPlanoId = {{ (int) ($selectedPlano->id ?? 0) }};
const selectedPlanoAmount = {{ (float) ($selectedPlano->valor ?? 0) }};
const mpPublicKey = @json($mpPublicKey);
const payerEmail = @json($payerEmail);
const csrfToken = @json(csrf_token());
const existingPlanWebhookUrl = `/mercadopago/notification/plano/${planoEmpresaId}`;
let cardForm = null;
let mercadoPagoLoading = null;
let sweetAlertLoading = null;

function loadScript(src, globalName) {
    if (globalName && window[globalName]) return Promise.resolve();

    return new Promise((resolve, reject) => {
        const found = document.querySelector(`script[data-lazy-src="${src}"]`);
        if (found) {
            if (globalName && window[globalName]) return resolve();
            found.addEventListener('load', resolve, {once:true});
            found.addEventListener('error', reject, {once:true});
            return;
        }

        const script = document.createElement('script');
        script.src = src;
        script.async = true;
        script.dataset.lazySrc = src;
        script.onload = resolve;
        script.onerror = reject;
        document.head.appendChild(script);
    });
}

function ensureMercadoPago() {
    if (!mercadoPagoLoading) {
        mercadoPagoLoading = loadScript('https://sdk.mercadopago.com/js/v2', 'MercadoPago');
    }
    return mercadoPagoLoading;
}

function ensureSweetAlert() {
    if (!sweetAlertLoading) {
        sweetAlertLoading = loadScript('https://cdn.jsdelivr.net/npm/sweetalert2@11', 'Swal');
    }
    return sweetAlertLoading;
}

async function showAlert(title, text, icon = 'info', options = {}) {
    try {
        await ensureSweetAlert();
        return Swal.fire({title, text, icon, ...options});
    } catch (_) {
        alert(`${title}\n\n${text || ''}`);
        return {isConfirmed:true};
    }
}

function selecionarMetodo(method, button) {
    document.querySelectorAll('.payment-method').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.payment-panel').forEach(el => el.classList.remove('active'));
    button.classList.add('active');
    document.getElementById(`paymentPanel-${method}`)?.classList.add('active');
}

function mostrarModal(html) {
    document.getElementById('modalBody').innerHTML = html;
    new bootstrap.Modal(document.getElementById('modalPagamento')).show();
}

function monitorarPagamento(paymentId) {
    if (!paymentId) return;

    let tentativas = 0;
    const timer = setInterval(async () => {
        tentativas++;
        if (tentativas > 50) {
            clearInterval(timer);
            return;
        }

        try {
            const response = await fetch(`/planos/verificar-status/${planoEmpresaId}?payment_id=${paymentId}`, {
                headers: {'Accept':'application/json'}
            });
            const data = await response.json();

            if (data.status === 'approved') {
                clearInterval(timer);
                await showAlert('Pagamento aprovado', data.mensagem || 'Plano ativado.', 'success');
                window.location.reload();
                return;
            }

            if (['rejected','cancelled','canceled'].includes(data.status)) {
                clearInterval(timer);
                await showAlert('Pagamento não aprovado', data.mensagem || 'O pagamento não foi aprovado.', 'warning');
            }
        } catch (_) {}
    }, 8000);
}

async function gerarPixPlano() {
    const button = document.getElementById('btnPix');
    const original = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Gerando PIX...';

    try {
        const response = await fetch(`/empresa/${empresaId}/planos/gerar-pix/${selectedPlanoId}`, {
            headers: {'Accept':'application/json'}
        });
        const data = await response.json();
        if (!response.ok || !data.qr_code_text) throw new Error(data.erro || 'Não foi possível gerar o PIX.');

        mostrarModal(`
            <span class="payment-method-icon method-pix mx-auto mb-3" style="width:56px;height:56px;font-size:29px"><i class="bx bx-qr-scan"></i></span>
            <h5 class="fw-bold">PIX gerado</h5>
            <p class="text-muted small">Pague pelo aplicativo do seu banco. O plano só será alterado após a confirmação do Mercado Pago.</p>
            ${data.qr_code_base64 ? `<div class="p-3 border rounded-3 d-inline-block mb-3 bg-white"><img src="data:image/png;base64,${data.qr_code_base64}" class="img-fluid" style="max-width:230px"></div>` : ''}
            <div class="input-group mb-3">
                <input id="pixCodigo" class="form-control" readonly value="${data.qr_code_text}">
                <button class="btn btn-outline-secondary" type="button" onclick="copiarPix()"><i class="bx bx-copy"></i></button>
            </div>
            <div class="alert alert-light border small mb-0"><span class="spinner-border spinner-border-sm me-2"></span>Aguardando confirmação do pagamento...</div>
        `);
        monitorarPagamento(data.payment_id);
    } catch (error) {
        await showAlert('Erro ao gerar PIX', error.message, 'error');
    } finally {
        button.disabled = false;
        button.innerHTML = original;
    }
}

async function copiarPix() {
    const input = document.getElementById('pixCodigo');
    if (!input) return;

    try {
        await navigator.clipboard.writeText(input.value);
        await showAlert('PIX copiado', 'Código PIX copiado para a área de transferência.', 'success', {
            timer:1200,
            showConfirmButton:false
        });
    } catch (_) {
        input.select();
        document.execCommand('copy');
    }
}

async function gerarBoletoPlano() {
    const button = document.getElementById('btnBoleto');
    const original = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Gerando boleto...';

    try {
        const response = await fetch(existingPlanWebhookUrl, {
            method:'POST',
            headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrfToken},
            body:JSON.stringify({metodo:'boleto', plano_id:selectedPlanoId, payer_email:payerEmail})
        });
        const data = await response.json();
        if (!response.ok || !data.boleto_link) throw new Error(data.erro || 'Não foi possível gerar o boleto.');

        mostrarModal(`
            <span class="payment-method-icon method-boleto mx-auto mb-3" style="width:56px;height:56px;font-size:29px"><i class="bx bx-barcode"></i></span>
            <h5 class="fw-bold">Boleto gerado</h5>
            <p class="text-muted small">O acesso será atualizado somente depois que o Mercado Pago confirmar o pagamento.</p>
            <a href="${data.boleto_link}" target="_blank" rel="noopener" class="btn btn-primary btn-lg w-100 primary-action">
                <i class="bx bx-link-external me-1"></i> Abrir boleto
            </a>
            <div class="alert alert-light border small mt-3 mb-0">Você pode fechar esta janela. A confirmação continuará sendo acompanhada.</div>
        `);
        monitorarPagamento(data.payment_id);
    } catch (error) {
        await showAlert('Erro ao gerar boleto', error.message, 'error');
    } finally {
        button.disabled = false;
        button.innerHTML = original;
    }
}

async function iniciarCartao() {
    if (!mpPublicKey || !selectedPlanoId || cardForm) return;

    const start = document.getElementById('cardStart');
    const loading = document.getElementById('cardLoading');
    const errorBox = document.getElementById('cardError');

    start?.classList.add('d-none');
    loading?.classList.remove('d-none');
    errorBox?.classList.add('d-none');

    try {
        await ensureMercadoPago();
        const mp = new MercadoPago(mpPublicKey, {locale:'pt-BR'});

        cardForm = mp.cardForm({
            amount:Number(selectedPlanoAmount).toFixed(2),
            iframe:true,
            form:{
                id:'form-checkout',
                cardNumber:{id:'form-checkout__cardNumber', placeholder:'Número do cartão'},
                expirationDate:{id:'form-checkout__expirationDate', placeholder:'MM/AA'},
                securityCode:{id:'form-checkout__securityCode', placeholder:'CVV'},
                cardholderName:{id:'form-checkout__cardholderName', placeholder:'Nome do titular'},
                issuer:{id:'form-checkout__issuer'},
                installments:{id:'form-checkout__installments'},
                identificationType:{id:'form-checkout__identificationType'},
                identificationNumber:{id:'form-checkout__identificationNumber'},
                cardholderEmail:{id:'form-checkout__cardholderEmail'}
            },
            callbacks:{
                onFormMounted:error => {
                    loading?.classList.add('d-none');
                    if (error) {
                        errorBox.textContent = 'Não foi possível carregar o formulário seguro do Mercado Pago.';
                        errorBox.classList.remove('d-none');
                        start?.classList.remove('d-none');
                        cardForm = null;
                        return;
                    }
                    document.getElementById('form-checkout')?.classList.remove('d-none');
                },
                onSubmit:async event => {
                    event.preventDefault();
                    await assinarCartao();
                }
            }
        });
    } catch (error) {
        loading?.classList.add('d-none');
        errorBox.textContent = error.message || 'Falha ao carregar o Mercado Pago.';
        errorBox.classList.remove('d-none');
        start?.classList.remove('d-none');
        cardForm = null;
    }
}

async function assinarCartao() {
    const button = document.getElementById('form-checkout__submit');
    if (!button || !cardForm) return;

    const original = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processando...';

    try {
        const formData = cardForm.getCardFormData();
        if (!formData.token) throw new Error('Confira os dados do cartão.');

        const response = await fetch(existingPlanWebhookUrl, {
            method:'POST',
            headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrfToken},
            body:JSON.stringify({
                metodo:'cartao_assinatura',
                plano_id:selectedPlanoId,
                card_token:formData.token,
                payer_email:formData.cardholderEmail || payerEmail
            })
        });
        const data = await response.json();
        if (!response.ok) throw new Error(data.erro || data.message || 'Não foi possível configurar a assinatura.');

        await showAlert('Assinatura configurada', data.message || 'Assinatura criada com sucesso.', 'success');
        if (data.payment_id) monitorarPagamento(data.payment_id);
    } catch (error) {
        await showAlert('Erro no cartão', error.message, 'error');
    } finally {
        button.disabled = false;
        button.innerHTML = original;
    }
}

async function cancelarAssinatura() {
    await ensureSweetAlert().catch(() => null);

    let confirmed = true;
    if (window.Swal) {
        const result = await Swal.fire({
            icon:'warning',
            title:'Cancelar renovação automática?',
            text:'Os dias já pagos continuam válidos até o vencimento atual.',
            showCancelButton:true,
            confirmButtonText:'Sim, cancelar renovação',
            cancelButtonText:'Voltar',
            confirmButtonColor:'#dc2626'
        });
        confirmed = result.isConfirmed;
    } else {
        confirmed = confirm('Cancelar a renovação automática?');
    }

    if (!confirmed) return;

    try {
        const response = await fetch(existingPlanWebhookUrl, {
            method:'POST',
            headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrfToken},
            body:JSON.stringify({metodo:'cancelar_assinatura'})
        });
        const data = await response.json();
        if (!response.ok) throw new Error(data.erro || 'Não foi possível cancelar a renovação.');

        await showAlert('Renovação cancelada', data.message || 'A renovação automática foi cancelada.', 'success');
        window.location.reload();
    } catch (error) {
        await showAlert('Erro', error.message, 'error');
    }
}
</script>
@endsection