@extends('default.layout',['title' => 'Suporte'])

@section('content')
<div class="page-content">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="mb-1">Central de Suporte</h4>
            <p class="text-muted mb-0">Converse com nossa equipe e acompanhe seus atendimentos.</p>
        </div>
        <a href="{{ route('tickets.create') }}" class="btn btn-primary">
            <i class='bx bx-message-square-add'></i> Novo atendimento
        </a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="support-summary-icon bg-light-warning text-warning"><i class='bx bx-time-five'></i></div>
                    <div><small class="text-muted">Aguardando</small><h4 class="mb-0">{{ $data->where('estado', 'aberto')->count() }}</h4></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="support-summary-icon bg-light-success text-success"><i class='bx bx-message-rounded-check'></i></div>
                    <div><small class="text-muted">Respondidos</small><h4 class="mb-0">{{ $data->where('estado', 'respondida')->count() }}</h4></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="support-summary-icon bg-light-secondary text-secondary"><i class='bx bx-check-circle'></i></div>
                    <div><small class="text-muted">Finalizados</small><h4 class="mb-0">{{ $data->where('estado', 'finalizado')->count() }}</h4></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            @forelse($data as $item)
                @php
                    $ultima = $item->mensagens->last();
                    $statusLabel = $item->estado === 'aberto' ? 'Aguardando suporte' : ($item->estado === 'respondida' ? 'Respondido' : 'Finalizado');
                @endphp
                <a href="{{ route('tickets.show', $item->id) }}" class="support-ticket-row text-decoration-none">
                    <div class="support-ticket-avatar"><i class='bx bx-support'></i></div>
                    <div class="support-ticket-main">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <strong class="text-dark">{{ $item->assunto }}</strong>
                            <span class="support-ticket-status status-{{ $item->estado }}">{{ $statusLabel }}</span>
                        </div>
                        <div class="text-muted small mt-1 support-ticket-preview">
                            @if($ultima)
                                <strong>{{ $ultima->mensagemSuper() ? 'Suporte:' : 'Você:' }}</strong>
                                {{ \Illuminate\Support\Str::limit(strip_tags($ultima->mensagem), 90) }}
                            @else
                                Atendimento sem mensagens.
                            @endif
                        </div>
                        <div class="text-muted mt-1" style="font-size:11px;">
                            TCK-{{ $item->id }} · {{ App\Models\Ticket::departamentos()[$item->departamento] ?? $item->departamento }}
                        </div>
                    </div>
                    <div class="support-ticket-date text-end">
                        <small class="text-muted">{{ optional($item->updated_at)->format('d/m/Y') }}</small>
                        <div class="text-muted" style="font-size:11px;">{{ optional($item->updated_at)->format('H:i') }}</div>
                        <i class='bx bx-chevron-right mt-2'></i>
                    </div>
                </a>
            @empty
                <div class="text-center py-5 px-3">
                    <div class="support-empty-icon"><i class='bx bx-conversation'></i></div>
                    <h5 class="mt-3">Nenhum atendimento ainda</h5>
                    <p class="text-muted">Quando precisar de ajuda, inicie uma conversa com nossa equipe.</p>
                    <a href="{{ route('tickets.create') }}" class="btn btn-primary btn-sm">Iniciar atendimento</a>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection

@section('css')
<style>
.support-summary-icon{width:46px;height:46px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:24px}.support-ticket-row{display:flex;align-items:center;gap:14px;padding:17px 20px;border-bottom:1px solid #edf0f4;transition:.18s ease}.support-ticket-row:last-child{border-bottom:0}.support-ticket-row:hover{background:#f8faff}.support-ticket-avatar{flex:0 0 44px;width:44px;height:44px;border-radius:14px;background:#eef4ff;color:#2563eb;display:flex;align-items:center;justify-content:center;font-size:23px}.support-ticket-main{min-width:0;flex:1}.support-ticket-preview{white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.support-ticket-date{flex:0 0 70px}.support-ticket-status{display:inline-flex;padding:3px 8px;border-radius:999px;font-size:10px;font-weight:700}.status-aberto{background:#fff4ce;color:#9a6700}.status-respondida{background:#e6f6ec;color:#157347}.status-finalizado{background:#eef1f4;color:#667085}.support-empty-icon{width:62px;height:62px;border-radius:20px;background:#eef4ff;color:#2563eb;margin:auto;display:flex;align-items:center;justify-content:center;font-size:30px}@media(max-width:576px){.support-ticket-row{padding:14px 12px}.support-ticket-avatar{display:none}.support-ticket-date{flex-basis:55px}}
</style>
@endsection