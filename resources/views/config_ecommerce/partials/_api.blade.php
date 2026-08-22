<div class="card border-0 shadow-sm mb-4" id="secao-api">
    <div class="card-header bg-white border-bottom py-3">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle bg-light-dark text-dark d-flex align-items-center justify-content-center" style="width:42px;height:42px;">
                <i class="bx bx-code-alt fs-4"></i>
            </div>
            <div>
                <h5 class="mb-0">API e Configurações Avançadas</h5>
                <small class="text-muted">Use somente quando a loja for integrada a uma aplicação externa.</small>
            </div>
        </div>
    </div>

    <div class="card-body">
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                {!! Form::select('usar_api', 'Usar Ecommerce API', [0 => 'Não', 1 => 'Sim'])->attrs(['class' => 'form-select']) !!}
            </div>
        </div>

        <div class="div-usarApi d-none mt-4">
            <div class="alert alert-warning border mb-4">
                <div class="d-flex align-items-start gap-2">
                    <i class="bx bx-error-circle fs-5 mt-1"></i>
                    <div>
                        <strong>Modo API ativado</strong>
                        <div class="small mt-1">Mantenha o token protegido. Ele permite comunicação entre o ERP e aplicações externas.</div>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-lg-6">
                    <label class="form-label">API Token</label>
                    <div class="input-group">
                        <input readonly value="{{ isset($item) ? $item->api_token : '' }}" id="api_token" name="api_token" type="text" class="form-control">
                        @if (!isset($not_submit))
                            <button type="button" class="btn btn-info" id="btn_token" title="Gerar novo token">
                                <i class="bx bx-refresh"></i>
                            </button>
                        @endif
                    </div>
                </div>

                <div class="col-lg-6">
                    {!! Form::tel('timer_carrossel', 'Timer do Carrossel')->attrs(['class' => 'form-control']) !!}
                </div>

                <div class="col-12">
                    {!! Form::textarea('mensagem_agradecimento', 'Mensagem de Agradecimento') !!}
                </div>

                <div class="col-md-6">
                    <label class="form-label">Cor de Fundo</label>
                    <input name="cor_fundo" class="form-control form-control-color" type="color" value="{{ isset($item) && $item->cor_fundo ? $item->cor_fundo : '#000000' }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Cor do Botão</label>
                    <input name="cor_btn" class="form-control form-control-color" type="color" value="{{ isset($item) && $item->cor_btn ? $item->cor_btn : '#000000' }}">
                </div>
            </div>
        </div>
    </div>
</div>