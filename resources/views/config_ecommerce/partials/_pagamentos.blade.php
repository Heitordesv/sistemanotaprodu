<div class="card border-0 shadow-sm mb-4" id="secao-pagamentos">
    <div class="card-header bg-white border-bottom py-3">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle bg-light-success text-success d-flex align-items-center justify-content-center" style="width:42px;height:42px;">
                <i class="bx bx-credit-card fs-4"></i>
            </div>
            <div>
                <h5 class="mb-0">Pagamentos</h5>
                <small class="text-muted">Formas aceitas e credenciais usadas na Loja Online e em Contas a Receber.</small>
            </div>
        </div>
    </div>

    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-5">
                <label class="form-label">Formas de pagamento</label>
                <select id="formas_pagamento" name="formas_pagamento[]" class="form-control multiple-select" multiple required>
                    @foreach(App\Models\ConfigEcommerce::formasPagamento() as $key => $f)
                        <option value="{{ $key }}"
                            {{ in_array($key, json_decode($item->formas_pagamento ?? '[]', true) ?? []) ? 'selected' : '' }}>
                            {{ $f }}
                        </option>
                    @endforeach
                </select>
                <small class="text-muted">Selecione uma ou mais formas disponíveis no checkout da loja.</small>
            </div>

            <div class="col-md-7">
                <div class="row g-3">
                    <div class="col-md-4">{!! Form::tel('desconto_padrao_pix', '% Desconto PIX') !!}</div>
                    <div class="col-md-4">{!! Form::tel('desconto_padrao_cartao', '% Desconto cartão') !!}</div>
                    <div class="col-md-4">{!! Form::tel('desconto_padrao_boleto', '% Desconto boleto') !!}</div>
                </div>
            </div>
        </div>

        <hr class="my-4">

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 gap-2">
            <div>
                <h6 class="mb-1">Mercado Pago</h6>
                <small class="text-muted">Estas credenciais são usadas tanto no checkout da loja quanto nas cobranças de Contas a Receber.</small>
            </div>
            @if(isset($item) && $item)
                <a href="{{ route('ecommerce.mercadopago.security') }}" class="btn btn-outline-primary btn-sm">
                    <i class="bx bx-cog me-1"></i>Configuração avançada
                </a>
            @endif
        </div>

        <div class="alert alert-light border d-flex align-items-start gap-2 mb-4">
            <i class="bx bx-info-circle text-primary fs-5 mt-1"></i>
            <div class="small">
                <strong>Contas a Receber:</strong> PIX e boleto são gerados diretamente pela API do Mercado Pago. Para cartão, o sistema cria um Checkout Pro seguro; os dados do cartão nunca passam pelo ERP.
            </div>
        </div>

        <div class="row g-3">
            <div class="col-md-6">
                {!! Form::text('mercadopago_public_key', 'Public Key') !!}
                @if(isset($item) && !empty($item->mercadopago_public_key))
                    <small class="text-success"><i class="bx bx-check-circle"></i> Public Key configurada.</small>
                @endif
            </div>

            <div class="col-md-6">
                <label class="form-label">Access Token</label>
                <input type="password" name="mercadopago_access_token" class="form-control" value="" autocomplete="new-password" placeholder="{{ isset($item) && !empty($item->mercadopago_access_token) ? 'Token já configurado' : 'Informe o Access Token' }}">
                @if(isset($item) && !empty($item->mercadopago_access_token))
                    <small class="text-success"><i class="bx bx-check-circle"></i> Access Token configurado.</small>
                @else
                    <small class="text-muted">O token não é exibido por segurança.</small>
                @endif
            </div>

            <div class="col-md-12">
                <label class="form-label">Webhook Secret</label>
                <input type="password" name="mercadopago_webhook_secret" class="form-control" value="" autocomplete="new-password" placeholder="{{ isset($item) && !empty($item->mercadopago_webhook_secret) ? 'Webhook Secret já configurado' : 'Informe a assinatura secreta dos Webhooks' }}">
                @if(isset($item) && !empty($item->mercadopago_webhook_secret))
                    <small class="text-success"><i class="bx bx-check-circle"></i> Webhook Secret configurado.</small>
                @else
                    <small class="text-muted">Disponível em Mercado Pago Developers → sua integração → Webhooks. O valor não é exibido após salvar.</small>
                @endif
            </div>
        </div>

        @if(isset($item) && $item)
            <div class="mt-4 rounded border bg-light p-3 small">
                <div class="fw-bold mb-1"><i class="bx bx-bell me-1"></i>Webhook de Contas a Receber</div>
                <div class="text-muted text-break">{{ route('conta-receber.mp.webhook', ['configId' => $item->id]) }}</div>
                <small class="text-muted">O próprio sistema também envia esta URL em cada pagamento criado.</small>
            </div>
        @endif
    </div>
</div>