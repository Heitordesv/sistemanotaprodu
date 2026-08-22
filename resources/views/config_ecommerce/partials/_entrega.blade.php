<div class="card border-0 shadow-sm mb-4" id="secao-entrega">
    <div class="card-header bg-white border-bottom py-3">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle bg-light-warning text-warning d-flex align-items-center justify-content-center" style="width:42px;height:42px;">
                <i class="bx bx-package fs-4"></i>
            </div>
            <div>
                <h5 class="mb-0">Entrega e Correios</h5>
                <small class="text-muted">Frete grátis, retirada e credenciais para PAC/SEDEX.</small>
            </div>
        </div>
    </div>

    <div class="card-body">
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                {!! Form::text('frete_gratis_valor', 'Frete grátis a partir de')->attrs(['class' => 'moeda']) !!}
                <small class="text-muted">Deixe zerado para não oferecer frete grátis automático.</small>
            </div>
            <div class="col-md-6">
                {!! Form::select('habilitar_retirada', 'Permitir retirada na loja', [0 => 'Não', 1 => 'Sim'])->attrs(['class' => 'form-select']) !!}
            </div>
        </div>

        <div class="alert alert-light border d-flex align-items-start gap-2 mb-4">
            <i class="bx bx-info-circle text-primary fs-5 mt-1"></i>
            <div>
                <strong>Integração Correios</strong>
                <div class="small text-muted mt-1">
                    Use o login do Meu Correios, o código de acesso da API/CWS e o cartão de postagem do contrato.
                    Ao editar a loja, deixe o código de acesso em branco para manter o valor já salvo.
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Usuário Meu Correios</label>
                <input type="text" name="correios_usuario" class="form-control" value="{{ old('correios_usuario', $item->correios_usuario ?? '') }}" autocomplete="off" placeholder="Usuário do Meu Correios">
            </div>

            <div class="col-md-4">
                <label class="form-label">Código de acesso da API (CWS)</label>
                <input type="password" name="correios_senha" class="form-control" value="" autocomplete="new-password" placeholder="{{ !empty($item?->correios_senha) ? 'Código já configurado' : 'Informe o código de acesso' }}">
                @if(isset($item) && !empty($item->correios_senha))
                    <small class="text-success"><i class="bx bx-check-circle"></i> Credencial já configurada.</small>
                @else
                    <small class="text-muted">Não é necessário exibir o código já salvo.</small>
                @endif
            </div>

            <div class="col-md-4">
                <label class="form-label">Cartão de Postagem</label>
                <input type="text" name="correios_cartao" class="form-control" value="{{ old('correios_cartao', $item->correios_cartao ?? '') }}" autocomplete="off" placeholder="Número do cartão de postagem">
            </div>
        </div>
    </div>
</div>