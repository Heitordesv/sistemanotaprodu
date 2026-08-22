@extends('default.layout', ['title' => 'Detalhes do Lead'])

@section('content')
@include('leads.components.css')

@php
    $statusUi = [
        'Novo' => ['cor' => 'info', 'icone' => 'fas fa-user-plus'],
        'Em Contato' => ['cor' => 'warning', 'icone' => 'fas fa-comments'],
        'Qualificado' => ['cor' => 'primary', 'icone' => 'fas fa-bullseye'],
        'Convertido' => ['cor' => 'success', 'icone' => 'fas fa-check-circle'],
        'Descartado' => ['cor' => 'danger', 'icone' => 'fas fa-times-circle'],
    ];
    $statusAtual = $statusUi[$lead->status] ?? ['cor' => 'secondary', 'icone' => 'fas fa-circle'];
    $whatsappNumero = preg_replace('/\D/', '', (string) $lead->whatsapp);
    $observacoesOrdenadas = $lead->observacoes->sortByDesc(function ($observacao) {
        return $observacao->data_observacao ? $observacao->data_observacao->timestamp : (int) $observacao->obs_id;
    });
@endphp

<div class="page-content lead-details-page py-4">
    <div class="container-fluid">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <div class="fw-bold mb-1">Não foi possível concluir a operação:</div>
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
            </div>
        @endif

        <div class="card border-0 shadow-sm crm-shell mb-4">
            <div class="card-header bg-white border-0 p-4">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap mb-2">
                            <span class="badge bg-{{ $statusAtual['cor'] }} px-3 py-2">
                                <i class="{{ $statusAtual['icone'] }} me-1"></i>{{ $lead->status ?: 'Sem status' }}
                            </span>
                            <span class="text-muted small">Lead #{{ $lead->id }}</span>
                        </div>
                        <h4 class="mb-1 fw-bold">{{ $lead->nome_completo }}</h4>
                        <p class="text-muted mb-0">{{ $lead->empresa ?: ($lead->tipo_loja ?: 'Empresa não informada') }}</p>
                    </div>

                    <div class="d-flex gap-2 flex-wrap">
                        @if($whatsappNumero)
                            <a href="https://wa.me/{{ $whatsappNumero }}" target="_blank" rel="noopener" class="btn btn-success">
                                <i class="fab fa-whatsapp me-1"></i> WhatsApp
                            </a>
                        @endif
                        <a href="{{ route('leads.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Voltar
                        </a>
                    </div>
                </div>
            </div>

            <div class="card-body p-4">
                <div class="row g-4">
                    <div class="col-lg-7">
                        <div class="card border h-100">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3"><i class="fas fa-address-card text-primary me-2"></i>Dados do lead</h6>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="small text-muted">Nome</div>
                                        <div class="fw-semibold">{{ $lead->nome_completo }}</div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="small text-muted">WhatsApp</div>
                                        <div class="fw-semibold">{{ $lead->whatsapp ?: 'Não informado' }}</div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="small text-muted">E-mail</div>
                                        <div class="fw-semibold text-break">{{ $lead->email ?: 'Não informado' }}</div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="small text-muted">Tipo de negócio</div>
                                        <div class="fw-semibold">{{ $lead->tipo_loja ?: 'Não informado' }}</div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="small text-muted">Origem</div>
                                        <div class="fw-semibold">{{ $lead->origem_lead ?: 'Cadastro manual' }}</div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="small text-muted">Vendedor responsável</div>
                                        <div class="fw-semibold">{{ optional($lead->vendedor)->nome ?? 'Não informado' }}</div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="small text-muted">Cadastrado em</div>
                                        <div class="fw-semibold">
                                            {{ $lead->data_cadastro ? $lead->data_cadastro->format('d/m/Y H:i') : 'Não informado' }}
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="small text-muted">IP de origem</div>
                                        <div class="fw-semibold">{{ $lead->ip_origem ?: 'Não informado' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-5">
                        <div class="card border mb-4">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3"><i class="fas fa-exchange-alt text-primary me-2"></i>Alterar status</h6>
                                <form action="{{ route('leads.updateStatus', $lead->id) }}" method="POST" class="row g-2">
                                    @csrf
                                    <div class="col-sm-8">
                                        <select name="status" class="form-select" aria-label="Status do lead" required>
                                            @foreach(array_keys($statusUi) as $status)
                                                <option value="{{ $status }}" {{ $lead->status === $status ? 'selected' : '' }}>{{ $status }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-sm-4">
                                        <button type="submit" class="btn btn-primary w-100">Atualizar</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="card border">
                            <div class="card-body">
                                <h6 class="fw-bold mb-2"><i class="fas fa-sticky-note text-primary me-2"></i>Nova observação</h6>
                                <p class="small text-muted">Registre contatos, necessidades, objeções e próximos passos.</p>

                                <form action="{{ route('leads.storeObservation', $lead->id) }}" method="POST" id="formObservacao">
                                    @csrf
                                    <textarea
                                        name="observacao"
                                        rows="4"
                                        maxlength="2000"
                                        class="form-control @error('observacao') is-invalid @enderror"
                                        placeholder="Ex.: Cliente pediu retorno amanhã às 15h..."
                                        required
                                    >{{ old('observacao') }}</textarea>
                                    @error('observacao')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="d-flex justify-content-between align-items-center mt-2 gap-2">
                                        <small class="text-muted">Máximo de 2.000 caracteres</small>
                                        <button type="submit" class="btn btn-success" id="btnSalvarObservacao">
                                            <i class="fas fa-plus me-1"></i> Adicionar
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm crm-shell">
            <div class="card-header bg-white p-4 border-0">
                <div class="d-flex justify-content-between align-items-center gap-2">
                    <div>
                        <h5 class="mb-1 fw-bold">Timeline de observações</h5>
                        <p class="text-muted small mb-0">Histórico comercial deste lead.</p>
                    </div>
                    <span class="badge bg-light text-dark border">{{ $lead->observacoes->count() }} registro(s)</span>
                </div>
            </div>

            <div class="card-body p-4">
                @forelse($observacoesOrdenadas as $observacao)
                    <div class="timeline-item">
                        <span class="timeline-dot"></span>
                        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-1">
                            <div class="fw-semibold">
                                {{ $observacao->usuario_responsavel ?: (optional($observacao->vendedor)->nome ?? 'Equipe') }}
                            </div>
                            <small class="text-muted">
                                {{ $observacao->data_observacao ? $observacao->data_observacao->format('d/m/Y H:i') : 'Data não informada' }}
                            </small>
                        </div>
                        <div class="text-dark" style="white-space: pre-line;">{{ $observacao->observacao }}</div>
                    </div>
                @empty
                    <div class="text-center py-4">
                        <div class="text-muted fs-3 mb-2"><i class="far fa-comment-dots"></i></div>
                        <h6 class="fw-bold">Nenhuma observação cadastrada</h6>
                        <p class="text-muted small mb-0">Use o formulário acima para registrar o primeiro contato.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('formObservacao');
    const button = document.getElementById('btnSalvarObservacao');

    if (form && button) {
        form.addEventListener('submit', function () {
            button.disabled = true;
            button.innerHTML = '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Salvando...';
        });
    }
});
</script>
@endsection