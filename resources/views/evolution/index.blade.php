@extends('default.layout', ['title' => 'WhatsApp e Agente de Cobrança'])

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="mb-1">WhatsApp + Agente de Cobrança</h4>
                <p class="text-muted mb-0">Conecte o WhatsApp da empresa e configure as cobranças automáticas.</p>
            </div>
            <span class="badge bg-{{ ($instance && $instance->status === 'open') ? 'success' : 'secondary' }} px-3 py-2">
                {{ ($instance && $instance->status === 'open') ? 'WhatsApp conectado' : 'WhatsApp não conectado' }}
            </span>
        </div>

        <div class="row g-3">
            <div class="col-lg-5">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Evolution API</h5>
                        <form method="POST" action="{{ route('evolution.save') }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">URL da Evolution</label>
                                <input type="url" name="base_url" class="form-control" required value="{{ old('base_url', optional($instance)->base_url) }}" placeholder="https://evolution.seudominio.com">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">API Key</label>
                                <input type="password" name="api_key" class="form-control" required value="{{ old('api_key', optional($instance)->api_key) }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Nome da instância</label>
                                <input type="text" name="instance_name" class="form-control" value="{{ old('instance_name', optional($instance)->instance_name) }}" placeholder="nfenotas-empresa">
                            </div>
                            <input type="hidden" name="integration" value="WHATSAPP-BAILEYS">
                            <button class="btn btn-primary w-100">Salvar configuração</button>
                        </form>

                        @if($instance)
                            <hr>
                            <div class="d-grid gap-2">
                                <button class="btn btn-outline-primary" onclick="evolutionAction('{{ route('evolution.instance.create') }}', 'POST')">Criar instância</button>
                                <button class="btn btn-success" onclick="showQrCode()">Conectar / mostrar QR Code</button>
                                <button class="btn btn-outline-secondary" onclick="evolutionAction('{{ route('evolution.instance.status') }}', 'GET')">Consultar conexão</button>
                                <button class="btn btn-outline-dark" onclick="evolutionAction('{{ route('evolution.instance.webhook') }}', 'POST')">Configurar webhook</button>
                            </div>
                            <div id="evolution-result" class="alert alert-light border mt-3 mb-0" style="display:none; white-space:pre-wrap"></div>
                            <div id="evolution-qr" class="text-center mt-3"></div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Agente de cobrança</h5>
                        <form method="POST" action="{{ route('evolution.agent.save') }}">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <label class="form-label">Nome do agente</label>
                                    <input type="text" name="nome_agente" class="form-control" required value="{{ old('nome_agente', $agent->nome_agente) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Horário de envio</label>
                                    <input type="time" name="hora_envio" class="form-control" required value="{{ old('hora_envio', substr($agent->hora_envio ?: '09:00:00', 0, 5)) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Dias antes do vencimento</label>
                                    <input type="text" name="dias_antes" class="form-control" value="{{ old('dias_antes', implode(',', $agent->dias_antes ?: [5,3,1,0])) }}" placeholder="5,3,1,0">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Dias após o vencimento</label>
                                    <input type="text" name="dias_atraso" class="form-control" value="{{ old('dias_atraso', implode(',', $agent->dias_atraso ?: [1,3,7,15,30])) }}" placeholder="1,3,7,15,30">
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-4 form-check ms-3">
                                    <input class="form-check-input" type="checkbox" name="ativo" value="1" id="ativo" {{ $agent->ativo ? 'checked' : '' }}>
                                    <label class="form-check-label" for="ativo">Agente ativo</label>
                                </div>
                                <div class="col-md-4 form-check">
                                    <input class="form-check-input" type="checkbox" name="os_notificacoes" value="1" id="os" {{ $agent->os_notificacoes ? 'checked' : '' }}>
                                    <label class="form-check-label" for="os">Notificar ordens de serviço</label>
                                </div>
                                <div class="col-md-3 form-check">
                                    <input class="form-check-input" type="checkbox" name="responder_clientes" value="1" id="responder" {{ $agent->responder_clientes ? 'checked' : '' }}>
                                    <label class="form-check-label" for="responder">Receber respostas</label>
                                </div>
                            </div>

                            <div class="mt-3">
                                <label class="form-label">Mensagem antes do vencimento</label>
                                <textarea name="template_antes" class="form-control" rows="2">{{ old('template_antes', $agent->template_antes) }}</textarea>
                            </div>
                            <div class="mt-3">
                                <label class="form-label">Mensagem no vencimento</label>
                                <textarea name="template_hoje" class="form-control" rows="2">{{ old('template_hoje', $agent->template_hoje) }}</textarea>
                            </div>
                            <div class="mt-3">
                                <label class="form-label">Mensagem em atraso</label>
                                <textarea name="template_atraso" class="form-control" rows="2">{{ old('template_atraso', $agent->template_atraso) }}</textarea>
                                <small class="text-muted">Variáveis: {cliente}, {valor}, {vencimento}, {link}, {referencia}, {agente}</small>
                            </div>
                            <button class="btn btn-success mt-3">Salvar agente</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-body">
                <h5 class="card-title">Últimas mensagens</h5>
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead><tr><th>Data</th><th>Tipo</th><th>Número</th><th>Mensagem</th><th>Status</th></tr></thead>
                        <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td>{{ $log->created_at }}</td>
                                <td>{{ $log->tipo }}</td>
                                <td>{{ $log->numero }}</td>
                                <td style="max-width:520px">{{ \Illuminate\Support\Str::limit($log->mensagem, 130) }}</td>
                                <td><span class="badge bg-{{ $log->status === 'enviado' ? 'success' : ($log->status === 'erro' ? 'danger' : 'secondary') }}">{{ $log->status }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">Nenhuma mensagem registrada.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
async function evolutionAction(url, method) {
    const result = document.getElementById('evolution-result');
    result.style.display = 'block';
    result.textContent = 'Processando...';
    try {
        const response = await fetch(url, {
            method: method,
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
        const data = await response.json();
        result.textContent = JSON.stringify(data, null, 2);
        return data;
    } catch (e) {
        result.textContent = e.message;
        throw e;
    }
}

async function showQrCode() {
    const data = await evolutionAction('{{ route('evolution.instance.connect') }}', 'GET');
    const qr = data.base64 || data.qrcode?.base64 || data.code || null;
    const box = document.getElementById('evolution-qr');
    if (qr && typeof qr === 'string' && qr.startsWith('data:image')) {
        box.innerHTML = '<p class="fw-bold">Leia o QR Code com o WhatsApp da empresa</p><img src="' + qr + '" style="max-width:280px" class="img-fluid border rounded p-2">';
    } else if (data.code) {
        box.innerHTML = '<p class="text-muted">QR recebido em formato de código. Consulte o retorno acima.</p>';
    }
}
</script>
@endsection