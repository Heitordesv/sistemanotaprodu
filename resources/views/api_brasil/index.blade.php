@extends('default.layout', ['title' => 'WhatsApp / Evolution + Gemini'])

@section('content')
<div class="page-content">
    <div class="container-fluid">
        @php
            $evolutionConfigurada = filled(config('services.evolution.base_url')) && filled(config('services.evolution.api_key'));
            $geminiConfigurado = filled(config('services.gemini.api_key'));
        @endphp

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <h4 class="mb-1">WhatsApp, Cobranças e Agente IA</h4>
                <p class="text-muted mb-0">Conecte o WhatsApp da empresa, ative o Gemini e configure as mensagens automáticas.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <span class="badge bg-{{ $integracao->evolution_status === 'open' ? 'success' : 'secondary' }} px-3 py-2">
                    <i class="bx bxl-whatsapp me-1"></i>
                    {{ $integracao->evolution_status === 'open' ? 'WhatsApp conectado' : 'WhatsApp desconectado' }}
                </span>
                <span class="badge bg-{{ $geminiConfigurado ? 'primary' : 'warning text-dark' }} px-3 py-2">
                    <i class="bx bx-bot me-1"></i>
                    {{ $geminiConfigurado ? 'Gemini configurado' : 'Gemini sem API Key' }}
                </span>
            </div>
        </div>

        @if(session('flash_sucesso'))
            <div class="alert alert-success">{{ session('flash_sucesso') }}</div>
        @endif
        @if(session('flash_erro'))
            <div class="alert alert-danger">{{ session('flash_erro') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $erro)
                        <li>{{ $erro }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('dispositivos.store') }}" id="form-integracao">
            @csrf

            <div class="row g-3">
                <div class="col-xl-6">
                    <div class="card h-100 border-top border-0 border-4 border-success">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div>
                                    <h5 class="mb-1"><i class="bx bxl-whatsapp text-success me-1"></i> Conexão WhatsApp</h5>
                                    <small class="text-muted">Evolution API usada para enviar e receber mensagens.</small>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="whatsapp_ativo" name="whatsapp_ativo" value="1" {{ $integracao->whatsapp_ativo ? 'checked' : '' }}>
                                    <label class="form-check-label" for="whatsapp_ativo">Ativo</label>
                                </div>
                            </div>

                            <div class="alert alert-{{ $evolutionConfigurada ? 'success' : 'warning' }} py-2">
                                <i class="bx bx-server me-1"></i>
                                Evolution no servidor:
                                <strong>{{ $evolutionConfigurada ? 'configurada' : 'não configurada' }}</strong>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Nome da instância deste cliente</label>
                                <input type="text" name="evolution_instance" class="form-control"
                                       value="{{ old('evolution_instance', $integracao->evolution_instance ?: 'nfenotas-' . session('user_logged')['empresa']) }}">
                                <small class="text-muted">Cada empresa possui sua própria instância dentro da Evolution API.</small>
                            </div>

                            <div class="d-flex flex-wrap gap-2">
                                <button type="button" class="btn btn-success" id="btn-conectar-whatsapp">
                                    <i class="bx bx-qr-scan me-1"></i> Conectar / QR Code
                                </button>
                                <button type="button" class="btn btn-outline-primary" id="btn-status-whatsapp">
                                    <i class="bx bx-refresh me-1"></i> Consultar status
                                </button>
                                <button type="button" class="btn btn-outline-dark" id="btn-webhook">
                                    <i class="bx bx-link-alt me-1"></i> Configurar webhook
                                </button>
                            </div>

                            <div id="qr-box" class="text-center mt-3"></div>
                            <div id="evolution-result" class="alert alert-light border mt-3 mb-0 d-none" style="white-space: pre-wrap"></div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-6">
                    <div class="card h-100 border-top border-0 border-4 border-primary">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h5 class="mb-1"><i class="bx bx-bot text-primary me-1"></i> Agente Gemini</h5>
                                    <small class="text-muted">O Gemini responde as mensagens recebidas usando os dados reais do sistema.</small>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="agente_ativo" name="agente_ativo" value="1" {{ $integracao->agente_ativo ? 'checked' : '' }}>
                                    <label class="form-check-label" for="agente_ativo">Ativo</label>
                                </div>
                            </div>

                            <div class="alert alert-{{ $geminiConfigurado ? 'success' : 'warning' }} py-2">
                                <i class="bx bx-key me-1"></i>
                                GEMINI_API_KEY:
                                <strong>{{ $geminiConfigurado ? 'configurada no servidor' : 'não configurada' }}</strong>
                            </div>

                            @if(!$geminiConfigurado)
                                <div class="alert alert-light border small">
                                    Configure <code>GEMINI_API_KEY</code> no <code>.env</code> do servidor e execute <code>php artisan config:clear</code>.
                                </div>
                            @endif

                            <div class="mb-3">
                                <label class="form-label">Modelo Gemini</label>
                                <input type="text" name="gemini_model" class="form-control"
                                       value="{{ old('gemini_model', $integracao->gemini_model ?: config('services.gemini.model', 'gemini-flash-latest')) }}">
                                <small class="text-muted">Padrão recomendado: {{ config('services.gemini.model', 'gemini-flash-latest') }}.</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Como o Gemini deve atender</label>
                                <textarea name="agente_instrucoes" class="form-control" rows="6" placeholder="Ex.: Seja cordial, responda de forma curta, ajude com cobranças e Ordens de Serviço e encaminhe para atendimento humano quando não encontrar a informação.">{{ old('agente_instrucoes', $integracao->agente_instrucoes) }}</textarea>
                                <small class="text-muted">Use este campo para definir tom de voz, regras de atendimento e orientações específicas da empresa.</small>
                            </div>

                            <div class="d-flex flex-wrap gap-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="agente_cobranca" name="agente_cobranca" value="1" {{ $integracao->agente_cobranca ? 'checked' : '' }}>
                                    <label class="form-check-label" for="agente_cobranca">Cobranças</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="agente_ordem_servico" name="agente_ordem_servico" value="1" {{ $integracao->agente_ordem_servico ? 'checked' : '' }}>
                                    <label class="form-check-label" for="agente_ordem_servico">Ordens de Serviço</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="responder_whatsapp" name="responder_whatsapp" value="1" {{ $integracao->responder_whatsapp ? 'checked' : '' }}>
                                    <label class="form-check-label" for="responder_whatsapp">Responder clientes</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card border-top border-0 border-4 border-info">
                        <div class="card-body p-4">
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
                                <div>
                                    <h5 class="mb-1"><i class="bx bx-message-rounded-dots text-info me-1"></i> Mensagens automáticas</h5>
                                    <small class="text-muted">Aqui você define quando enviar. O texto de cada mensagem continua sendo alterado em Mensagens Personalizadas.</small>
                                </div>
                                <a href="{{ route('mensagem_personalizada.index', ['empresa_id' => session('user_logged')['empresa']]) }}" class="btn btn-info text-white">
                                    <i class="bx bx-edit me-1"></i> Editar mensagens
                                </a>
                            </div>

                            <div class="alert alert-info bg-light border-info text-dark">
                                <i class="bx bx-info-circle me-1"></i>
                                <strong>Importante:</strong> mensagens automáticas são disparos programados. Quando o cliente responder no WhatsApp, o Gemini interpreta a conversa e cria a resposta com base nos dados do sistema.
                            </div>

                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Nome do agente</label>
                                    <input type="text" name="nome_agente" class="form-control" value="{{ old('nome_agente', $agent->nome_agente ?: 'Assistente Financeiro') }}">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Horário dos disparos</label>
                                    <input type="time" name="hora_envio" class="form-control" value="{{ old('hora_envio', substr($agent->hora_envio ?: '09:00:00', 0, 5)) }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Dias antes do vencimento</label>
                                    <input type="text" name="dias_antes" class="form-control" value="{{ old('dias_antes', implode(',', $agent->dias_antes ?: [5,3,1,0])) }}" placeholder="5,3,1,0">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Dias em atraso</label>
                                    <input type="text" name="dias_atraso" class="form-control" value="{{ old('dias_atraso', implode(',', $agent->dias_atraso ?: [1,3,7,15,30])) }}" placeholder="1,3,7,15,30">
                                </div>
                            </div>

                            <div class="alert alert-light border mt-3 mb-0">
                                <strong>Mensagens utilizadas:</strong>
                                <span class="badge bg-secondary">Cobrança Antes</span>
                                <span class="badge bg-secondary">Cobrança Hoje</span>
                                <span class="badge bg-secondary">Cobrança Atraso</span>
                                <span class="badge bg-secondary">OS Pendente</span>
                                <span class="badge bg-secondary">OS Em Andamento</span>
                                <span class="badge bg-secondary">OS Pronto</span>
                                <span class="badge bg-secondary">OS Finalizado</span>
                                <span class="badge bg-secondary">OS Reprovado</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end mt-3">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bx bx-save me-1"></i> Salvar configurações
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function () {
    const csrf = '{{ csrf_token() }}';
    const form = document.getElementById('form-integracao');
    const result = document.getElementById('evolution-result');
    const qrBox = document.getElementById('qr-box');

    function showResult(text, type = 'light') {
        result.className = 'alert alert-' + type + ' border mt-3 mb-0';
        result.textContent = text;
        result.classList.remove('d-none');
    }

    function getErrorMessage(data, fallback) {
        if (data && data.errors) {
            const validationErrors = Object.values(data.errors).flat().filter(Boolean);
            if (validationErrors.length) {
                return validationErrors.join('\n');
            }
        }

        return data?.mensagem || data?.message || fallback;
    }

    async function request(url, method = 'GET') {
        const response = await fetch(url, {
            method,
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'Content-Type': 'application/json'
            }
        });

        const data = await response.json().catch(() => ({}));
        if (!response.ok) {
            throw new Error(getErrorMessage(data, 'Não foi possível concluir a operação.'));
        }
        return data;
    }

    async function saveConfiguration() {
        if (!form.reportValidity()) {
            throw new Error('Revise os campos obrigatórios antes de continuar.');
        }

        const response = await fetch(form.action, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf
            },
            body: new FormData(form)
        });

        const data = await response.json().catch(() => ({}));
        if (!response.ok) {
            throw new Error(getErrorMessage(data, 'Não foi possível salvar a configuração.'));
        }

        return data;
    }

    document.getElementById('btn-conectar-whatsapp').addEventListener('click', async function () {
        const button = this;
        button.disabled = true;
        qrBox.innerHTML = '<div class="spinner-border text-success" role="status"></div><p class="text-muted mt-2">Gerando QR Code...</p>';
        showResult('Salvando a instância da empresa e solicitando conexão...');

        try {
            await saveConfiguration();
            const data = await request('{{ route('dispositivos.start', ['device_token' => 'evolution']) }}', 'POST');
            const base64 = data.base64 || data.qrcode?.base64 || data.qrCode?.base64 || null;

            if (base64) {
                const src = base64.startsWith('data:image') ? base64 : 'data:image/png;base64,' + base64;
                qrBox.innerHTML = '<p class="fw-semibold">Leia com o WhatsApp da empresa</p><img src="' + src + '" class="img-fluid border rounded p-2" style="max-width:300px">';
            } else {
                qrBox.innerHTML = '<p class="text-muted">A Evolution respondeu, mas o QR Code não veio em formato de imagem. Veja o retorno abaixo.</p>';
            }

            showResult(JSON.stringify(data, null, 2), 'success');
        } catch (error) {
            qrBox.innerHTML = '';
            showResult(error.message, 'danger');
            Swal.fire('Erro', error.message, 'error');
        } finally {
            button.disabled = false;
        }
    });

    document.getElementById('btn-status-whatsapp').addEventListener('click', async function () {
        const button = this;
        button.disabled = true;

        try {
            await saveConfiguration();
            const data = await request('{{ route('dispositivos.status') }}');
            showResult('Status: ' + (data.state || 'não informado') + '\n\n' + JSON.stringify(data.response || data, null, 2), 'info');
        } catch (error) {
            showResult(error.message, 'danger');
            Swal.fire('Erro', error.message, 'error');
        } finally {
            button.disabled = false;
        }
    });

    document.getElementById('btn-webhook').addEventListener('click', async function () {
        const button = this;
        button.disabled = true;

        try {
            await saveConfiguration();
            const data = await request('{{ route('dispositivos.webhook') }}', 'POST');
            showResult('Webhook configurado com sucesso.\n\n' + JSON.stringify(data.response || data, null, 2), 'success');
            Swal.fire('Pronto', 'Webhook da Evolution configurado.', 'success');
        } catch (error) {
            showResult(error.message, 'danger');
            Swal.fire('Erro', error.message, 'error');
        } finally {
            button.disabled = false;
        }
    });
})();
</script>
@endsection