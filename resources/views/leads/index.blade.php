@extends('default.layout', ['title' => html_entity_decode('Gest&atilde;o de Leads', ENT_QUOTES | ENT_HTML5, 'UTF-8')])

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
    $totalLeadsGeral = max(array_sum($statusCounts ?? []), 1);
    $usuarioPadrao = html_entity_decode('Usu&aacute;rio', ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $negocioNaoInformado = html_entity_decode('Neg&oacute;cio n&atilde;o informado', ENT_QUOTES | ENT_HTML5, 'UTF-8');
@endphp

<div id="loading-import">
    <div class="spinner-border text-primary" role="status" aria-label="Importando"></div>
    <h5 class="mt-3 mb-1 fw-bold">Importando leads...</h5>
    <p class="text-muted mb-0">Aguarde enquanto processamos os dados.</p>
</div>

<div class="page-content leads-page py-4">
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

        @if(session('info'))
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="fas fa-info-circle me-2"></i>{{ session('info') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
            </div>
        @endif

        <div class="card border-0 shadow-sm crm-shell">
            <div class="card-header bg-white border-0 p-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <div class="text-uppercase text-primary fw-bold small mb-1">CRM Comercial</div>
                        <h4 class="mb-1 fw-bold">Gest&atilde;o de Leads</h4>
                        <p class="mb-0 text-muted">
                            Atendente: <strong>{{ session('user_logged')['nome'] ?? $usuarioPadrao }}</strong>
                        </p>
                    </div>

                    <div class="d-flex gap-2 flex-wrap crm-header-actions">
                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalFunil">
                            <i class="fas fa-chart-line me-1"></i> Analisar funil
                        </button>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalLead">
                            <i class="fas fa-plus me-1"></i> Novo Lead
                        </button>
                    </div>
                </div>
            </div>

            <div class="card-body p-4">
                <div class="row g-3 mb-4">
                    @foreach($statusUi as $status => $dados)
                        @php
                            $quantidade = (int) ($statusCounts[$status] ?? 0);
                            $percentual = ($quantidade / $totalLeadsGeral) * 100;
                        @endphp
                        <div class="col-6 col-lg">
                            <div class="summary-card bg-white shadow-sm h-100 p-3">
                                <div class="d-flex align-items-center justify-content-between gap-2">
                                    <div>
                                        <div class="small text-muted mb-1">{{ $status }}</div>
                                        <div class="h4 mb-0 fw-bold">{{ $quantidade }}</div>
                                    </div>
                                    <span class="summary-icon bg-{{ $dados['cor'] }} bg-opacity-10 text-{{ $dados['cor'] }}">
                                        <i class="{{ $dados['icone'] }}"></i>
                                    </span>
                                </div>
                                <div class="small text-muted mt-2">{{ number_format($percentual, 0) }}% do total</div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="card border shadow-sm mb-4">
                    <div class="card-body">
                        <form action="{{ route('leads.index') }}" method="GET" class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label for="filtro_nome" class="form-label small fw-semibold">Nome</label>
                                <input type="text" id="filtro_nome" name="nome" value="{{ request('nome') }}" class="form-control" placeholder="Buscar por nome">
                            </div>
                            <div class="col-md-3">
                                <label for="filtro_telefone" class="form-label small fw-semibold">WhatsApp</label>
                                <input type="text" id="filtro_telefone" name="telefone" value="{{ request('telefone') }}" class="form-control" placeholder="DDD + n&uacute;mero">
                            </div>
                            <div class="col-md-3">
                                <label for="filtro_status" class="form-label small fw-semibold">Status</label>
                                <select id="filtro_status" name="status" class="form-select">
                                    <option value="Todos">Todos</option>
                                    @foreach(array_keys($statusUi) as $status)
                                        <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>{{ $status }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 d-flex gap-2">
                                <button type="submit" class="btn btn-outline-primary flex-fill" title="Filtrar">
                                    <i class="fas fa-search"></i>
                                </button>
                                <a href="{{ route('leads.index') }}" class="btn btn-outline-secondary flex-fill" title="Limpar filtros">
                                    <i class="fas fa-eraser"></i>
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                @if($temPermissao)
                    <div class="card border shadow-sm mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                                <div>
                                    <h6 class="mb-1 fw-bold"><i class="fas fa-cloud-download-alt text-primary me-2"></i>Importa&ccedil;&atilde;o r&aacute;pida</h6>
                                    <small class="text-muted">Importe empresas para alimentar o funil comercial.</small>
                                </div>
                            </div>

                            <form action="{{ route('leads.importar') }}" method="POST" id="formImportar" class="row g-3 align-items-end">
                                @csrf
                                <div class="col-md-3">
                                    <label for="import_uf" class="form-label small fw-semibold">UF</label>
                                    <select id="import_uf" name="uf" class="form-select">
                                        <option value="">Todas</option>
                                        @foreach(['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'] as $uf)
                                            <option value="{{ $uf }}">{{ $uf }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="import_cnae" class="form-label small fw-semibold">CNAE principal</label>
                                    <select id="import_cnae" name="cnae" class="form-select">
                                        <option value="">Todos os setores</option>
                                        <option value="4791201">E-commerce</option>
                                        <option value="4120400">Constru&ccedil;&atilde;o de edif&iacute;cios</option>
                                        <option value="5611201">Restaurantes</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check pb-2">
                                        <input class="form-check-input" type="checkbox" name="com_telefone" value="1" id="import_telefone" checked>
                                        <label class="form-check-label" for="import_telefone">Apenas com telefone</label>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-download me-1"></i> Importar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif

                @if($leads->count() === 0)
                    <div class="text-center py-5">
                        <div class="display-6 text-muted mb-3"><i class="fas fa-users"></i></div>
                        <h5 class="fw-bold">Nenhum lead encontrado</h5>
                        <p class="text-muted mb-3">Cadastre um novo lead ou ajuste os filtros da pesquisa.</p>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalLead">
                            <i class="fas fa-plus me-1"></i> Cadastrar Lead
                        </button>
                    </div>
                @else
                    @foreach($statusPrioridade as $status)
                        @php
                            $lista = $leadsAgrupados[$status] ?? collect();
                            $dadosStatus = $statusUi[$status] ?? ['cor' => 'secondary', 'icone' => 'fas fa-circle'];
                        @endphp

                        @if($lista->isNotEmpty())
                            <div class="d-flex align-items-center gap-2 mt-4 mb-3">
                                <span class="badge bg-{{ $dadosStatus['cor'] }} px-3 py-2">
                                    <i class="{{ $dadosStatus['icone'] }} me-1"></i>{{ $status }} &middot; {{ $lista->count() }}
                                </span>
                                <div class="border-bottom flex-grow-1"></div>
                            </div>

                            <div class="leads-grid">
                                @foreach($lista as $lead)
                                    <article class="lead-card border-left-{{ $dadosStatus['cor'] }}">
                                        <div class="lead-card-body">
                                            <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                                                <div class="min-w-0">
                                                    <h6 class="fw-bold mb-1 text-truncate">{{ $lead->nome_completo }}</h6>
                                                    <small class="text-muted">Lead #{{ $lead->id }}</small>
                                                </div>
                                                <span class="badge bg-{{ $dadosStatus['cor'] }} bg-opacity-10 text-{{ $dadosStatus['cor'] }}">{{ $status }}</span>
                                            </div>

                                            <div class="small mb-2">
                                                <i class="fab fa-whatsapp text-success me-2"></i>
                                                <span class="fw-semibold">{{ $lead->whatsapp }}</span>
                                            </div>

                                            @if($lead->email)
                                                <div class="small mb-2 text-truncate">
                                                    <i class="fas fa-envelope text-muted me-2"></i>{{ $lead->email }}
                                                </div>
                                            @endif

                                            <div class="small mb-3 text-truncate">
                                                <i class="fas fa-briefcase text-muted me-2"></i>{{ $lead->empresa ?: ($lead->tipo_loja ?: $negocioNaoInformado) }}
                                            </div>

                                            <span class="badge-origem">
                                                <i class="fas fa-tag"></i>
                                                <span class="text-truncate">{{ $lead->origem_lead ?: 'Cadastro manual' }}</span>
                                            </span>
                                        </div>

                                        <div class="lead-card-footer">
                                            <a href="{{ route('leads.show', $lead->id) }}" class="btn btn-sm btn-primary">
                                                <i class="fas fa-eye me-1"></i> Gerenciar
                                            </a>

                                            @if($temPermissao)
                                                <form action="{{ route('leads.destroy', $lead->id) }}" method="POST" onsubmit="return confirm('Deseja realmente excluir este lead?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Excluir lead">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        @endif
                    @endforeach
                @endif

                @if($leads->hasPages())
                    <div class="mt-4 d-flex justify-content-center">
                        {{ $leads->appends(request()->query())->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@include('leads.components.modal-create')
@include('leads.components.modal-funil')
@endsection

@section('js')
    @include('leads.components.scripts')
@endsection