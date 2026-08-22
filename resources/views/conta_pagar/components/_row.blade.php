@php
    $status = $item->status; 
    $vencimento = \Carbon\Carbon::parse($item->data_vencimento);
    $isAtrasado = !$status && $vencimento->isPast();
    $isProximo = !$status && !$isAtrasado && $vencimento->diffInDays(now()) <= 3;

    $statusColor = $status ? 'success' : ($isAtrasado ? 'danger' : ($isProximo ? 'warning' : 'secondary'));
    
    $qtdComprovantes = $item->comprovantes->count();
    $temComprovante = !empty($item->comprovante) || $qtdComprovantes > 0;

    // 🔥 VALORES
    $valorTotal = $item->valor_integral ?? 0;
    $valorPago = $item->valor_pago ?? 0;
    $valorRestante = $valorTotal - $valorPago;
@endphp

<style>
    /* Linha com efeito Card */
    .tr-modern {
        background-color: #ffffff;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        border-bottom: 1px solid #f1f5f9 !important;
    }

    .tr-modern:hover {
        background-color: #ffffff !important;
        transform: scale(1.01); /* Leve crescimento */
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.07);
        z-index: 5;
        position: relative;
    }

    /* Borda lateral mais elegante */
    .status-indicator { position: relative; }
    .status-indicator::before {
        content: "";
        position: absolute;
        left: 0;
        top: 20%;
        height: 60%;
        width: 5px; /* Um pouco mais grossa para destaque */
        border-radius: 0 6px 6px 0;
        transition: all 0.3s ease;
    }

    /* Cores vivas para o indicador */
    .status-indicator-success::before { background: #10b981; }
    .status-indicator-danger::before { background: #ef4444; }
    .status-indicator-warning::before { background: #f59e0b; }
    .status-indicator-secondary::before { background: #64748b; }

    /* Badges com cores da imagem, mas mais nítidas */
    .badge-soft {
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.65rem;
        padding: 4px 10px;
        letter-spacing: 0.5px;
    }
    .badge-soft-success { background: #ecfdf5; color: #059669; border: 1px solid #10b98133; }
    .badge-soft-danger { background: #fef2f2; color: #dc2626; border: 1px solid #ef444433; }
    .badge-soft-warning { background: #fffbeb; color: #d97706; border: 1px solid #f59e0b33; }

    .main-value { 
        font-size: 1.1rem; 
        color: #0f172a; 
        letter-spacing: -0.5px;
    }
</style>
<tr class="tr-modern status-indicator status-indicator-{{ $statusColor }}">

    {{-- FORNECEDOR --}}
    <td class="ps-4 py-3">
        <div class="d-flex flex-column">
            <div class="d-flex align-items-center gap-2">
                <span class="fw-bold text-dark mb-0" style="font-size: 1rem; letter-spacing: -0.2px;">
                    {{ $item->fornecedor?->razao_social ?? $item->funcionario?->nome ?? 'N/A' }}
                </span>
                
                {{-- INDICADOR DE COMPROVANTE --}}
                @if($temComprovante)
                    <span class="badge bg-label-info p-1 rounded-circle" title="Possui {{ $qtdComprovantes }} anexo(s)">
                        <i class="bx bx-paperclip fs-tiny"></i>
                    </span>
                @endif
            </div>

            <div class="text-muted d-flex align-items-center gap-1" style="font-size: 0.75rem; font-weight: 500;">
                <i class="bx bx-subdirectory-right text-secondary"></i>
                {{ $item->categoria?->nome ?? 'Geral' }}
            </div>
        </div>
    </td>

    {{-- STATUS / DATAS (Igual ao anterior) --}}
    <td class="py-3">
        <div class="d-flex flex-column align-items-start gap-1">
            @if($status)
                <span class="badge badge-soft badge-soft-success rounded">Pago</span>
                <small class="text-muted fw-medium" style="font-size: 0.75rem;">
                    <i class="bx bx-calendar-check text-success"></i> {{ __data_pt($item->data_pagamento, false) }}
                </small>
            @else
                <span class="badge badge-soft badge-soft-{{ $isAtrasado ? 'danger' : 'warning' }} rounded">
                    {{ $isAtrasado ? 'Atrasado' : 'Pendente' }}
                </span>
                <small class="{{ $isAtrasado ? 'text-danger' : 'text-muted' }} fw-bold" style="font-size: 0.75rem;">
                    Vence {{ $vencimento->format('d/m/Y') }}
                    @if($isAtrasado) 
                        <span class="badge bg-danger text-white ms-1" style="font-size: 0.6rem;">{{ $vencimento->diffInDays(now()) }}d</span> 
                    @endif
                </small>
            @endif
        </div>
    </td>

     {{-- VALORES 🔥 COMPLETO --}}
    <td class="text-end pe-4 py-3">
        <div class="d-flex flex-column align-items-end gap-1">

            {{-- TOTAL --}}
            <span class="main-value">
                <span class="text-secondary">R$</span>
                <strong>{{ __moeda($valorTotal) }}</strong>
            </span>

            {{-- PAGO --}}
            @if($valorPago > 0)
                <div class="d-flex align-items-center text-success fw-bold"
                     style="font-size: 0.72rem; background:#ecfdf5; padding:3px 8px; border-radius:6px;">
                    <i class="bx bx-check-circle me-1"></i>
                    Pago: R$ {{ __moeda($valorPago) }}
                </div>
            @endif

            {{-- FALTA PAGAR --}}
            @if($valorRestante > 0)
                <div class="d-flex align-items-center text-danger fw-bold"
                     style="font-size: 0.72rem; background:#fef2f2; padding:3px 8px; border-radius:6px;">
                    <i class="bx bx-time-five me-1"></i>
                    Falta: R$ {{ __moeda($valorRestante) }}
                </div>
            @else
                <div class="d-flex align-items-center text-success fw-bold"
                     style="font-size: 0.72rem; background:#ecfdf5; padding:3px 8px; border-radius:6px;">
                    <i class="bx bx-badge-check me-1"></i>
                    Quitado
                </div>
            @endif

        </div>
    </td>

    {{-- FILIAL (Opcional) --}}
    @if(empresaComFilial())
        <td class="py-3">
            <span class="badge bg-light text-secondary border fw-medium" style="font-size: 0.7rem;">
                <i class="bx bx-buildings me-1"></i> {{ $item->filial?->descricao ?? 'Matriz' }}
            </span>
        </td>
    @endif

    {{-- AÇÕES --}}
    <td class="text-center py-3">
        <div class="dropdown">
            <button class="btn btn-light btn-sm shadow-sm border rounded-circle p-1" data-bs-toggle="dropdown" style="width: 32px; height: 32px;">
                <i class="bx bx-dots-vertical-rounded fs-5"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0" style="min-width: 200px;">
                <li><h6 class="dropdown-header text-uppercase fs-tiny fw-bold">Gestão Financeira</h6></li>
                
                <li>
                    <button class="dropdown-item py-2" data-bs-toggle="modal" data-bs-target="#modalPagamento-{{ $item->id }}">
                        <i class="bx bx-spreadsheet me-2 text-primary"></i> Detalhes
                    </button>
                </li>

                @if(!$status)
                    <li>
                        <a class="dropdown-item py-2 fw-bold text-success" href="{{ route('conta-pagar.pay', $item) }}">
                            <i class="bx bx-check-double me-2"></i> Baixar Título
                        </a>
                    </li>
                @endif

                <li><hr class="dropdown-divider opacity-50"></li>
                <li><h6 class="dropdown-header text-uppercase fs-tiny fw-bold">Documentos</h6></li>
                
                {{-- BOTÃO ANEXAR --}}
                <li>
                    <button class="dropdown-item py-2" data-bs-toggle="modal" data-bs-target="#comprovanteModal" data-id="{{ $item->id }}">
                        <i class="bx bx-upload me-2 text-info"></i> {{ $temComprovante ? 'Adicionar Comprovante' : 'Anexar Comprovante' }}
                    </button>
                </li>

                {{-- LISTAR COMPROVANTES SE EXISTIREM --}}
                @if($temComprovante)
                    @if(!empty($item->comprovante))
                        <li>
                            <button class="dropdown-item py-2" onclick="visualizarComprovante('{{ asset('storage/' . $item->comprovante) }}')">
                                <i class="bx bx-file me-2 text-secondary"></i> Comprovante Principal
                            </button>
                        </li>
                    @endif
                    @if($qtdComprovantes > 0)
                        <li>
                            <button class="dropdown-item py-2" data-bs-toggle="modal" data-bs-target="#modalComprovantes-{{ $item->id }}">
                                <i class="bx bx-images me-2 text-secondary"></i> Ver Comprovante ({{ $qtdComprovantes }})
                            </button>
                        </li>
                    @endif
                @endif

                <li><hr class="dropdown-divider opacity-50"></li>
                
<li>
    <a class="dropdown-item py-2" 
       href="{{ route('conta-pagar.edit', $item->id) }}">

        <i class="bx bx-edit-alt me-2 text-warning"></i>
        Editar Conta

    </a>
</li>
                <li>
                    <form action="{{ route('conta-pagar.destroy', $item->id) }}" method="POST" class="form-delete">
                        @csrf @method('delete')
                        <button type="submit" class="dropdown-item py-2 text-danger">
                            <i class="bx bx-trash me-2"></i> Remover Registro
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </td>
</tr>