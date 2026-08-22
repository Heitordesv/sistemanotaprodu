@extends('default.layout', ['title' => 'Integração Mercado Pago'])

@section('content')
<style>
    .mp-page{padding:24px 0 40px}
    .mp-hero{background:linear-gradient(135deg,#0b1f33 0%,#163a5f 100%);border-radius:18px;padding:28px;color:#fff;margin-bottom:22px;box-shadow:0 14px 35px rgba(15,23,42,.16)}
    .mp-hero h3{font-weight:800;margin:0 0 8px;font-size:26px}
    .mp-hero p{margin:0;color:rgba(255,255,255,.78);max-width:780px}
    .mp-grid{display:grid;grid-template-columns:minmax(0,1.35fr) minmax(300px,.65fr);gap:22px}
    .mp-card{background:#fff;border:1px solid #e5e7eb;border-radius:18px;box-shadow:0 10px 28px rgba(15,23,42,.06);overflow:hidden}
    .mp-card-head{padding:20px 22px;border-bottom:1px solid #eef2f7;display:flex;align-items:center;justify-content:space-between;gap:12px}
    .mp-card-head h5{margin:0;font-weight:800;color:#111827}
    .mp-card-body{padding:22px}
    .mp-status-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:18px}
    .mp-status{border:1px solid #e5e7eb;border-radius:14px;padding:14px;background:#f8fafc}
    .mp-status small{display:block;color:#64748b;font-weight:700;margin-bottom:5px}
    .mp-status strong{display:flex;align-items:center;gap:7px;font-size:14px}
    .mp-dot{width:9px;height:9px;border-radius:50%;display:inline-block;background:#94a3b8}
    .mp-dot.ok{background:#16a34a}
    .mp-dot.warn{background:#f59e0b}
    .mp-label{font-weight:800;color:#1f2937;margin-bottom:7px;display:block}
    .mp-help{display:block;color:#6b7280;font-size:12px;margin-top:6px;line-height:1.45}
    .mp-input-wrap{position:relative}
    .mp-toggle{position:absolute;right:10px;top:50%;transform:translateY(-50%);border:0;background:transparent;color:#64748b;cursor:pointer;padding:5px 8px}
    .mp-secret{padding-right:48px!important}
    .mp-note{border-radius:14px;padding:14px 16px;background:#eff6ff;border:1px solid #bfdbfe;color:#1e40af;font-size:13px;line-height:1.5}
    .mp-warning{border-radius:14px;padding:14px 16px;background:#fff7ed;border:1px solid #fed7aa;color:#9a3412;font-size:13px;line-height:1.5}
    .mp-actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:22px}
    .mp-btn-primary{background:#2563eb;color:#fff;border:0;border-radius:10px;padding:11px 18px;font-weight:800}
    .mp-btn-secondary{background:#fff;color:#334155;border:1px solid #cbd5e1;border-radius:10px;padding:11px 18px;font-weight:700;text-decoration:none}
    .mp-code{font-family:monospace;font-size:12px;background:#0f172a;color:#e2e8f0;border-radius:10px;padding:11px 12px;word-break:break-all}
    .mp-step{display:flex;gap:12px;margin-bottom:16px}
    .mp-step-number{width:28px;height:28px;border-radius:50%;background:#e0ecff;color:#1d4ed8;font-weight:900;display:flex;align-items:center;justify-content:center;flex:none}
    .mp-step strong{display:block;color:#111827;margin-bottom:3px}
    .mp-step p{margin:0;color:#64748b;font-size:13px;line-height:1.45}
    .mp-badge{font-size:12px;padding:6px 9px;border-radius:999px;font-weight:800}
    .mp-badge.ok{background:#dcfce7;color:#166534}
    .mp-badge.warn{background:#fef3c7;color:#92400e}
    @media(max-width:992px){.mp-grid{grid-template-columns:1fr}.mp-status-grid{grid-template-columns:1fr}}
</style>

<div class="page-content mp-page">
    <div class="container-fluid">
        <div class="mp-hero">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                <div>
                    <h3><i class='bx bx-credit-card-front me-2'></i> Mercado Pago</h3>
                    <p>Configure as credenciais utilizadas no checkout da sua loja para PIX, boleto e cartão. Os segredos salvos nunca são exibidos novamente nesta tela.</p>
                </div>
                <a href="{{ route('configEcommerce.index') }}" class="btn btn-light fw-bold">
                    <i class='bx bx-arrow-back me-1'></i> Voltar ao Ecommerce
                </a>
            </div>
        </div>

        @if(session('flash_sucesso'))
            <div class="alert alert-success border-0 shadow-sm">
                <i class='bx bx-check-circle me-1'></i> {{ session('flash_sucesso') }}
            </div>
        @endif

        @if(session('flash_erro'))
            <div class="alert alert-danger border-0 shadow-sm">
                <i class='bx bx-error-circle me-1'></i> {{ session('flash_erro') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger border-0 shadow-sm">
                <strong>Revise os campos:</strong>
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $erro)
                        <li>{{ $erro }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="mp-grid">
            <div class="mp-card">
                <div class="mp-card-head">
                    <h5>Credenciais da loja</h5>
                    @if($config->mercadopago_access_token && $config->mercadopago_public_key)
                        <span class="mp-badge ok"><i class='bx bx-check-shield'></i> Configurado</span>
                    @else
                        <span class="mp-badge warn"><i class='bx bx-error'></i> Incompleto</span>
                    @endif
                </div>

                <div class="mp-card-body">
                    <div class="mp-status-grid">
                        <div class="mp-status">
                            <small>Public Key</small>
                            <strong><span class="mp-dot {{ $config->mercadopago_public_key ? 'ok' : 'warn' }}"></span>{{ $config->mercadopago_public_key ? 'Configurada' : 'Não configurada' }}</strong>
                        </div>
                        <div class="mp-status">
                            <small>Access Token</small>
                            <strong><span class="mp-dot {{ $config->mercadopago_access_token ? 'ok' : 'warn' }}"></span>{{ $config->mercadopago_access_token ? 'Configurado' : 'Não configurado' }}</strong>
                        </div>
                        <div class="mp-status">
                            <small>Webhook Secret</small>
                            <strong><span class="mp-dot {{ $config->mercadopago_webhook_secret ? 'ok' : 'warn' }}"></span>{{ $config->mercadopago_webhook_secret ? 'Configurado' : 'Opcional / pendente' }}</strong>
                        </div>
                    </div>

                    @if(!$config->mercadopago_access_token)
                        <div class="mp-warning mb-4">
                            <strong><i class='bx bx-error-circle me-1'></i> Access Token obrigatório</strong><br>
                            Sem o Access Token, o sistema não consegue criar pagamentos PIX, boleto ou cartão no Mercado Pago.
                        </div>
                    @endif

                    <form method="post" action="{{ route('ecommerce.mercadopago.security.update') }}" autocomplete="off">
                        @csrf

                        <div class="form-group mb-4">
                            <label class="mp-label" for="mercadopago_public_key">Public Key</label>
                            <input
                                id="mercadopago_public_key"
                                class="form-control form-control-lg @error('mercadopago_public_key') is-invalid @enderror"
                                required
                                name="mercadopago_public_key"
                                value="{{ old('mercadopago_public_key', $config->mercadopago_public_key) }}"
                                placeholder="APP_USR-..."
                                autocomplete="off"
                            >
                            <small class="mp-help">Usada no navegador para inicializar o checkout seguro do cartão.</small>
                        </div>

                        <div class="form-group mb-4">
                            <label class="mp-label" for="mercadopago_access_token">Access Token</label>
                            <div class="mp-input-wrap">
                                <input
                                    id="mercadopago_access_token"
                                    type="password"
                                    autocomplete="new-password"
                                    class="form-control form-control-lg mp-secret @error('mercadopago_access_token') is-invalid @enderror"
                                    name="mercadopago_access_token"
                                    placeholder="{{ $config->mercadopago_access_token ? '••••••••••••••••  token já salvo' : 'APP_USR-...' }}"
                                >
                                <button type="button" class="mp-toggle" data-toggle-secret="mercadopago_access_token" title="Mostrar/ocultar">
                                    <i class='bx bx-show'></i>
                                </button>
                            </div>
                            <small class="mp-help">
                                @if($config->mercadopago_access_token)
                                    Um token já está salvo. <strong>Deixe este campo vazio para manter o atual.</strong>
                                @else
                                    Cole o Access Token de produção da aplicação do Mercado Pago.
                                @endif
                            </small>
                        </div>

                        <div class="form-group mb-4">
                            <label class="mp-label" for="mercadopago_webhook_secret">Webhook Secret</label>
                            <div class="mp-input-wrap">
                                <input
                                    id="mercadopago_webhook_secret"
                                    type="password"
                                    autocomplete="new-password"
                                    class="form-control form-control-lg mp-secret @error('mercadopago_webhook_secret') is-invalid @enderror"
                                    name="mercadopago_webhook_secret"
                                    placeholder="{{ $config->mercadopago_webhook_secret ? '••••••••••••••••  segredo já salvo' : 'Cole o segredo do webhook' }}"
                                >
                                <button type="button" class="mp-toggle" data-toggle-secret="mercadopago_webhook_secret" title="Mostrar/ocultar">
                                    <i class='bx bx-show'></i>
                                </button>
                            </div>
                            <small class="mp-help">Usado para validar se as notificações realmente vieram do Mercado Pago. Deixe vazio para manter o segredo atual.</small>
                        </div>

                        <div class="form-group mb-4">
                            <label class="mp-label">URL do Webhook</label>
                            <div class="input-group">
                                <input readonly id="webhook-url" class="form-control form-control-lg" value="{{ $webhook_url }}">
                                <button type="button" id="copy-webhook" class="btn btn-outline-secondary">
                                    <i class='bx bx-copy'></i> Copiar
                                </button>
                            </div>
                            <small id="copy-feedback" class="mp-help">Cadastre esta URL nas notificações de pagamento da sua aplicação no Mercado Pago.</small>
                        </div>

                        <div class="mp-note">
                            <strong><i class='bx bx-shield-quarter me-1'></i> Segurança</strong><br>
                            O Access Token e o Webhook Secret não são enviados de volta para o navegador. Ao editar esta tela, preencher um novo valor substitui o segredo; deixar vazio preserva o valor já armazenado.
                        </div>

                        <div class="mp-actions">
                            <button class="mp-btn-primary" type="submit">
                                <i class='bx bx-save me-1'></i> Salvar integração
                            </button>
                            <a href="{{ route('configEcommerce.index') }}" class="mp-btn-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>

            <div>
                <div class="mp-card mb-4">
                    <div class="mp-card-head">
                        <h5>Como configurar</h5>
                    </div>
                    <div class="mp-card-body">
                        <div class="mp-step">
                            <div class="mp-step-number">1</div>
                            <div>
                                <strong>Abra sua aplicação no Mercado Pago</strong>
                                <p>Entre em Credenciais de produção e copie a Public Key e o Access Token.</p>
                            </div>
                        </div>
                        <div class="mp-step">
                            <div class="mp-step-number">2</div>
                            <div>
                                <strong>Cole as credenciais aqui</strong>
                                <p>Use as credenciais pertencentes à conta que receberá os pagamentos desta loja.</p>
                            </div>
                        </div>
                        <div class="mp-step">
                            <div class="mp-step-number">3</div>
                            <div>
                                <strong>Configure o webhook</strong>
                                <p>Cadastre a URL abaixo nas notificações da aplicação e salve o segredo de assinatura.</p>
                            </div>
                        </div>
                        <div class="mp-step mb-0">
                            <div class="mp-step-number">4</div>
                            <div>
                                <strong>Teste o checkout</strong>
                                <p>Faça um pedido de teste e valide PIX, boleto e cartão habilitados na loja.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mp-card">
                    <div class="mp-card-head">
                        <h5>Webhook desta loja</h5>
                    </div>
                    <div class="mp-card-body">
                        <div class="mp-code">{{ $webhook_url }}</div>
                        <p class="text-muted small mt-3 mb-0">Cada configuração de e-commerce possui sua própria URL. Não reutilize a URL de outra empresa.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('javascript')
<script>
(function(){
    document.querySelectorAll('[data-toggle-secret]').forEach(function(button){
        button.addEventListener('click', function(){
            var input = document.getElementById(button.getAttribute('data-toggle-secret'));
            if(!input) return;
            input.type = input.type === 'password' ? 'text' : 'password';
            var icon = button.querySelector('i');
            if(icon){
                icon.className = input.type === 'password' ? 'bx bx-show' : 'bx bx-hide';
            }
        });
    });

    var copyButton = document.getElementById('copy-webhook');
    if(copyButton){
        copyButton.addEventListener('click', function(){
            var input = document.getElementById('webhook-url');
            var feedback = document.getElementById('copy-feedback');
            if(!input) return;

            if(navigator.clipboard && window.isSecureContext){
                navigator.clipboard.writeText(input.value).then(function(){
                    if(feedback) feedback.textContent = 'URL copiada para a área de transferência.';
                });
            }else{
                input.select();
                document.execCommand('copy');
                if(feedback) feedback.textContent = 'URL copiada para a área de transferência.';
            }
        });
    }
})();
</script>
@endsection