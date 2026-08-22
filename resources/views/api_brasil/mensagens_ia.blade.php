@extends('default.layout', ['title' => 'Mensagens IA do WhatsApp'])

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
            <div>
                <h4 class="mb-1">Mensagens geradas pela IA</h4>
                <p class="text-muted mb-0">Veja o texto final que foi enviado pelo WhatsApp após o processamento do Gemini.</p>
            </div>
            <a href="{{ route('dispositivos.index') }}" class="btn btn-outline-primary">
                <i class="bx bx-arrow-back me-1"></i> Voltar
            </a>
        </div>

        <div class="card">
            <div class="card-body p-0">
                @forelse($mensagens as $item)
                    <div class="border-bottom p-3">
                        <div class="d-flex flex-wrap justify-content-between gap-2 mb-2">
                            <div>
                                <span class="badge bg-primary">{{ $item->tipo }}</span>
                                <span class="badge bg-{{ $item->status === 'enviado' ? 'success' : ($item->status === 'erro' ? 'danger' : 'secondary') }}">
                                    {{ $item->status }}
                                </span>
                            </div>
                            <small class="text-muted">{{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y H:i') }}</small>
                        </div>

                        <div class="small text-muted mb-2">
                            <i class="bx bxl-whatsapp me-1"></i> {{ $item->numero }}
                        </div>

                        <div class="bg-light border rounded p-3" style="white-space: pre-wrap">{{ $item->mensagem }}</div>
                    </div>
                @empty
                    <div class="text-center text-muted p-5">
                        <i class="bx bx-message-rounded-dots fs-1 d-block mb-2"></i>
                        Nenhuma mensagem enviada foi encontrada ainda.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection