@extends('default.layout',['title' => 'Central de atendimento'])
@section('content')
<div class="page-content">
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                <div>
                    <h4 class="mb-1">Central de atendimento</h4>
                    <p class="text-muted mb-0">Fila de suporte de todas as empresas clientes.</p>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <span class="badge bg-warning text-dark p-2">Aguardando: {{ $data->where('estado', 'aberto')->count() }}</span>
                    <span class="badge bg-info text-dark p-2">Respondidos: {{ $data->where('estado', 'respondida')->count() }}</span>
                    <span class="badge bg-success p-2">Finalizados: {{ $data->where('estado', 'finalizado')->count() }}</span>
                </div>
            </div>

            {!!Form::open()->fill(request()->all())->get()!!}
            <div class="row g-3 align-items-end">
                <div class="col-md-5">
                    {!!Form::text('empresa', 'Empresa')!!}
                </div>
                <div class="col-md-3">
                    {!!Form::select('estado', 'Estado', [
                        '' => 'Todos',
                        'aberto' => 'Aguardando suporte',
                        'respondida' => 'Respondido',
                        'finalizado' => 'Finalizado'
                    ])->attrs(['class' => 'form-select'])!!}
                </div>
                <div class="col-md-2">
                    {!!Form::select('departamento', 'Departamento', [
                        '' => 'Todos',
                        '1' => 'Suporte',
                        '2' => 'Conta e Vendas'
                    ])->attrs(['class' => 'form-select'])!!}
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-primary" type="submit"><i class="bx bx-search"></i></button>
                    <a class="btn btn-light" href="{{ route('ticketsSuper.index') }}"><i class="bx bx-eraser"></i></a>
                </div>
            </div>
            {!!Form::close()!!}

            <div class="mt-4">
                @forelse($data as $t)
                    @php
                        $empresaNome = $t->empresa->nome_fantasia ?? $t->empresa->razao_social ?? ('Empresa #' . $t->empresa_id);
                        $ultima = $t->mensagens->last();
                    @endphp
                    <a href="{{ route('ticketsSuper.show', $t->id) }}" class="support-admin-row text-decoration-none">
                        <div class="support-admin-icon"><i class='bx bx-message-rounded-dots'></i></div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <strong class="text-dark">{{ $empresaNome }}</strong>
                                <span class="text-muted small">TCK-{{ $t->id }}</span>
                                @if($t->estado === 'aberto')
                                    <span class="badge bg-warning text-dark">Aguardando</span>
                                @elseif($t->estado === 'respondida')
                                    <span class="badge bg-info text-dark">Respondido</span>
                                @else
                                    <span class="badge bg-success">Finalizado</span>
                                @endif
                            </div>
                            <div class="text-dark mt-1">{{ $t->assunto }}</div>
                            @if($ultima)
                                <div class="text-muted small text-truncate mt-1">
                                    {{ $ultima->mensagemSuper() ? 'Suporte: ' : 'Cliente: ' }}{{ \Illuminate\Support\Str::limit(strip_tags($ultima->mensagem), 110) }}
                                </div>
                            @endif
                        </div>
                        <div class="text-end text-muted small">
                            {{ optional($t->updated_at)->format('d/m/Y') }}<br>
                            {{ optional($t->updated_at)->format('H:i') }}
                        </div>
                    </a>
                @empty
                    <div class="text-center text-muted py-5">Nenhum atendimento encontrado.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@section('css')
<style>
.support-admin-row{display:flex;align-items:center;gap:14px;padding:15px 12px;border-top:1px solid #edf0f4;transition:.15s ease}.support-admin-row:hover{background:#f8faff}.support-admin-icon{width:42px;height:42px;flex:0 0 42px;border-radius:13px;background:#eef4ff;color:#2563eb;display:flex;align-items:center;justify-content:center;font-size:22px}.min-w-0{min-width:0}
</style>
@endsection