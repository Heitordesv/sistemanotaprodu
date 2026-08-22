@php
    $uiText = static fn (string $texto): string => html_entity_decode($texto, ENT_QUOTES | ENT_HTML5, 'UTF-8');
@endphp

<div class="card table-card mb-4">
    <div class="table-toolbar d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h5 class="fw-bold mb-0">Carteira de recebimentos</h5>
                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-10">
                    {{ $data->total() }}
                </span>
            </div>
            <small class="text-muted">Priorize atrasadas, receba pagamentos e envie cobran&ccedil;as sem sair da lista.</small>
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap bulk-actions">
            <button id="btn-pay-selected" class="btn btn-success btn-sm d-none">
                <i class="bx bx-check-double me-1"></i>
                Receber <span class="badge bg-white text-success ms-1" id="selected-count-pay">0</span>
            </button>

            <button id="btn-delete-selected" class="btn btn-outline-danger btn-sm d-none">
                <i class="bx bx-trash me-1"></i>
                Excluir <span class="badge bg-danger text-white ms-1" id="selected-count">0</span>
            </button>

            <span class="small text-muted d-none d-md-inline">
                <i class="bx bx-mouse me-1"></i> Marque contas para a&ccedil;&otilde;es em lote
            </span>
        </div>
    </div>

    <form id="form-delete-mass" action="{{ route('conta-receber.destroy-mass') }}" method="POST" class="d-none">
        @csrf
        @method('DELETE')
        <input type="hidden" name="ids" id="input-delete-ids">
    </form>

    <form id="form-pay-mass" action="{{ route('conta-receber.receber.massa') }}" method="POST" class="d-none">
        @csrf
        <input type="hidden" name="ids" id="input-pay-ids">
    </form>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th style="width:42px;" class="text-center">
                        <input type="checkbox" id="check-all" class="form-check-input" aria-label="Selecionar todas">
                    </th>
                    <th>Cliente / Refer&ecirc;ncia</th>
                    <th class="d-none d-lg-table-cell">Categoria</th>
                    @if(empresaComFilial())
                        <th class="d-none d-xl-table-cell">Local</th>
                    @endif
                    <th>Vencimento</th>
                    <th class="text-end">Valor</th>
                    <th>Status</th>
                    <th class="text-end">A&ccedil;&otilde;es</th>
                </tr>
            </thead>

            <tbody>
                @forelse($data as $item)
                    @php
                        $nome = $isSuper
                            ? ($item->empresa->razao_social ?? $item->empresa->nome_fantasia ?? 'Empresa')
                            : ($item->cliente->razao_social ?? 'Cliente');

                        $valorIntegral = (float) ($item->valor_integral ?? 0);
                        $valorRecebido = (float) ($item->valor_recebido ?? 0);
                        $saldo = max(0, $valorIntegral - $valorRecebido);
                        $pago = $valorIntegral > 0 && $valorRecebido >= $valorIntegral;
                        $statusRaw = strtolower(trim((string) $item->status));
                        $statusRecebido = $pago || in_array($statusRaw, ['1', 'true', 'recebido', 'aprovado', 'pago'], true);

                        $vencimento = \Carbon\Carbon::parse($item->data_vencimento)->startOfDay();
                        $hoje = now()->startOfDay();
                        $diasParaVencer = (int) $hoje->diffInDays($vencimento, false);
                        $isVencido = !$statusRecebido && $diasParaVencer < 0;
                        $isHoje = !$statusRecebido && $diasParaVencer === 0;
                        $parcial = !$statusRecebido && $valorRecebido > 0;

                        if ($statusRecebido) {
                            $st = ['c' => 'success', 'i' => 'bx-check-circle', 't' => 'Recebido'];
                        } elseif ($parcial) {
                            $st = ['c' => 'info', 'i' => 'bx-adjust', 't' => 'Parcial'];
                        } elseif ($isVencido) {
                            $st = ['c' => 'danger', 'i' => 'bx-error-circle', 't' => 'Atrasado'];
                        } elseif ($isHoje) {
                            $st = ['c' => 'warning', 'i' => 'bx-alarm', 't' => 'Vence hoje'];
                        } else {
                            $st = ['c' => 'warning', 'i' => 'bx-time-five', 't' => 'Pendente'];
                        }

                        $iniciais = collect(preg_split('/\s+/', trim($nome)))
                            ->filter()
                            ->take(2)
                            ->map(fn ($parte) => mb_substr($parte, 0, 1))
                            ->implode('');

                        $referencia = (string) ($item->referencia ?? '');
                        $vendaPdvId = null;

                        if (preg_match('/\bParcela\b.*?\bCompra\b.*?c.{0,3}digo\s*[:#-]?\s*(\d+)\s*$/iu', $referencia, $referenciaMatch)) {
                            $vendaPdvId = (int) $referenciaMatch[1];
                        }
                    @endphp

                    <tr class="{{ $isVencido ? 'row-overdue' : ($statusRecebido ? 'row-paid' : '') }}">
                        <td class="text-center">
                            <input type="checkbox" class="form-check-input check-item" value="{{ $item->id }}" aria-label="Selecionar conta {{ $item->id }}">
                        </td>

                        <td style="min-width:240px;">
                            <div class="d-flex align-items-center gap-2">
                                <span class="customer-avatar">{{ $iniciais ?: 'C' }}</span>
                                <div class="min-w-0">
                                    <div class="fw-bold text-dark text-truncate" style="max-width:260px;" title="{{ $nome }}">
                                        {{ $nome }}
                                    </div>
                                    <div class="reference-text small text-muted show-referencia cursor-pointer"
                                         title="Ver detalhes"
                                         data-referencia="{{ $item->referencia ?? $uiText('Sem descri&ccedil;&atilde;o') }}">
                                        <i class="bx bx-file me-1"></i>{{ $item->referencia ?: $uiText('Sem refer&ecirc;ncia') }}
                                    </div>

                                    @if($vendaPdvId)
                                        <a href="{{ route('nfce.show', $vendaPdvId) }}"
                                           class="btn btn-outline-primary btn-sm mt-2"
                                           title="Ver detalhes da venda do PDV">
                                            <i class="bx bx-receipt me-1"></i>Ver venda PDV #{{ $vendaPdvId }}
                                        </a>
                                    @endif

                                    <div class="d-lg-none mt-1">
                                        <span class="badge bg-light text-muted border fw-normal">
                                            {{ $item->categoria->nome ?? 'Sem categoria' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </td>

                        <td class="d-none d-lg-table-cell text-muted" style="min-width:140px;">
                            {{ $item->categoria->nome ?? 'Sem categoria' }}
                        </td>

                        @if(empresaComFilial())
                            <td class="d-none d-xl-table-cell text-muted small">
                                <i class="bx bx-map me-1"></i>{{ $item->filial->descricao ?? 'Matriz' }}
                            </td>
                        @endif

                        <td class="text-nowrap" style="min-width:155px;">
                            <div class="fw-semibold {{ $isVencido ? 'text-danger' : 'text-dark' }}">
                                {{ __data_pt($item->data_vencimento, false) }}
                            </div>

                            @if($statusRecebido)
                                @if($item->data_recebimento)
                                    <small class="text-success">
                                        <i class="bx bx-check me-1"></i>Recebido em {{ __data_pt($item->data_recebimento, false) }}
                                    </small>
                                @endif
                            @elseif($isVencido)
                                <span class="badge bg-danger bg-opacity-10 text-danger days-badge mt-1">
                                    {{ abs($diasParaVencer) }} dia(s) em atraso
                                </span>
                            @elseif($isHoje)
                                <span class="badge bg-warning bg-opacity-10 text-warning days-badge mt-1">Vence hoje</span>
                            @else
                                <small class="text-muted">em {{ $diasParaVencer }} dia(s)</small>
                            @endif
                        </td>

                        <td class="text-end text-nowrap" style="min-width:135px;">
                            <div class="fw-bold text-dark fs-6">{{ __moeda($valorIntegral) }}</div>
                            @if($parcial)
                                <small class="text-info d-block">Recebido: {{ __moeda($valorRecebido) }}</small>
                                <small class="text-danger d-block">Saldo: {{ __moeda($saldo) }}</small>
                            @elseif($statusRecebido && $valorRecebido > 0)
                                <small class="text-success">{{ __moeda($valorRecebido) }} recebido</small>
                            @endif
                        </td>

                        <td class="text-nowrap">
                            <span class="status-badge bg-{{ $st['c'] }} bg-opacity-10 text-{{ $st['c'] }}">
                                <i class="bx {{ $st['i'] }}"></i>{{ $st['t'] }}
                            </span>
                        </td>

                        <td class="text-end text-nowrap" style="min-width:150px;">
                            <div class="d-inline-flex align-items-center gap-1">
                                @if(!$statusRecebido)
                                    <a href="{{ route('conta-receber.pay', $item) }}"
                                       class="btn btn-outline-success action-btn"
                                       title="Receber agora">
                                        <i class="bx bx-money"></i>
                                    </a>

                                    <button type="button"
                                            class="btn btn-outline-primary action-btn btn-enviar-cobranca"
                                            data-id="{{ $item->id }}"
                                            title="Enviar cobran&ccedil;a pelo WhatsApp">
                                        <i class="bx bxl-whatsapp"></i>
                                    </button>
                                @endif

                                <div class="dropdown d-inline-block">
                                    <button class="btn btn-light border action-btn"
                                            type="button"
                                            data-bs-toggle="dropdown"
                                            aria-expanded="false"
                                            title="Mais a&ccedil;&otilde;es">
                                        <i class="bx bx-dots-horizontal-rounded"></i>
                                    </button>

                                    <ul class="dropdown-menu dropdown-menu-end">
                                        @if(!$statusRecebido)
                                            <li class="px-2 py-1 text-muted small fw-bold">Cobran&ccedil;a</li>
                                            <li>
                                                <button type="button" class="dropdown-item gerarPixBtn" data-id="{{ $item->id }}">
                                                    <i class="bx bx-qr me-2 text-primary"></i>Gerar PIX
                                                </button>
                                            </li>

                                            @if(empty($item->boleto_link))
                                                <li>
                                                    <button type="button" class="dropdown-item {{ $isSuper ? 'gerarBoletoEBtn' : 'gerarBoletoBtn' }}" data-id="{{ $item->id }}">
                                                        <i class="bx bx-file me-2"></i>Gerar Boleto MP
                                                    </button>
                                                </li>
                                                <li>
                                                    <button type="button" class="dropdown-item gerarBoletoCoraBtn" data-id="{{ $item->id }}">
                                                        <i class="bx bx-receipt me-2"></i>Gerar Boleto Cora
                                                    </button>
                                                </li>
                                            @else
                                                <li>
                                                    <a href="{{ $item->boleto_link }}" target="_blank" rel="noopener" class="dropdown-item">
                                                        <i class="bx bx-show me-2"></i>Ver boleto
                                                    </a>
                                                </li>
                                            @endif

                                            <li><hr class="dropdown-divider"></li>
                                        @endif

                                        <li>
                                            <a class="dropdown-item" href="{{ route('conta-receber.edit', $item) }}">
                                                <i class="bx bx-edit me-2 text-primary"></i>Editar conta
                                            </a>
                                        </li>
                                        <li>
                                            <button type="button"
                                                    class="dropdown-item show-referencia"
                                                    data-referencia="{{ $item->referencia ?? $uiText('Sem descri&ccedil;&atilde;o') }}">
                                                <i class="bx bx-info-circle me-2"></i>Ver refer&ecirc;ncia
                                            </button>
                                        </li>
                                        <li>
                                            <a href="#"
                                               class="dropdown-item text-danger btn-delete"
                                               data-form-id="form-del-{{ $item->id }}">
                                                <i class="bx bx-trash me-2"></i>Excluir
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <form id="form-del-{{ $item->id }}" action="{{ route('conta-receber.destroy', $item->id) }}" method="POST" class="d-none">
                                @csrf
                                @method('DELETE')
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ empresaComFilial() ? 8 : 7 }}">
                            <div class="empty-state text-center">
                                <span class="empty-state-icon mb-3"><i class="bx bx-receipt"></i></span>
                                <h5 class="fw-bold mb-1">Nenhuma conta encontrada</h5>
                                <p class="text-muted mb-3">Ajuste os filtros ou cadastre uma nova conta a receber.</p>
                                <a href="{{ route('conta-receber.create') }}" class="btn btn-primary">
                                    <i class="bx bx-plus-circle me-1"></i> Nova conta
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($data->hasPages() || $data->total() > 0)
        <div class="px-3 px-lg-4 py-3 border-top d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
            <small class="text-muted">
                Exibindo <strong>{{ $data->firstItem() ?? 0 }}</strong> a <strong>{{ $data->lastItem() ?? 0 }}</strong>
                de <strong>{{ $data->total() }}</strong> registro(s)
            </small>
            <div>
                {!! $data->appends(request()->all())->links() !!}
            </div>
        </div>
    @endif
</div>