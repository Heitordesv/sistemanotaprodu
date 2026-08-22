@extends('default.layout', ['title' => 'Ordens de serviço'])

@section('content')
<div class="page-content">
    <div class="card border-top border-0 border-4 border-primary">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
                <div>
                    <h4 class="mb-1">Ordens de serviço</h4>
                    <p class="text-muted mb-0">Acompanhe, consulte e gerencie todas as ordens.</p>
                </div>
                <a href="{{ route('ordemServico.create') }}" class="btn btn-success">
                    <i class="bx bx-plus"></i> Nova ordem de serviço
                </a>
            </div>

            <form method="GET" action="{{ route('ordemServico.index') }}" class="card bg-light border-0 mb-4">
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label for="cliente_id" class="form-label">Cliente</label>
                            <select name="cliente_id" id="cliente_id" class="form-select select2">
                                <option value="">Todos os clientes</option>
                                @foreach($clientes as $cliente)
                                    <option value="{{ $cliente->id }}" @selected((string) request('cliente_id') === (string) $cliente->id)>
                                        {{ $cliente->razao_social }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="start_date" class="form-label">Data inicial</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" value="{{ request('start_date') }}">
                        </div>
                        <div class="col-md-2">
                            <label for="end_date" class="form-label">Data final</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" value="{{ request('end_date') }}">
                        </div>
                        <div class="col-md-2">
                            <label for="estado" class="form-label">Status</label>
                            <select name="estado" id="estado" class="form-select">
                                <option value="">Todos</option>
                                @foreach(['pendente' => 'Pendente', 'Em Andamento' => 'Em andamento', 'pronto' => 'Pronto', 'finalizado' => 'Finalizado', 'reprovado' => 'Reprovado'] as $value => $label)
                                    <option value="{{ $value }}" @selected(request('estado') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 d-flex gap-2">
                            <button class="btn btn-primary flex-grow-1" type="submit" title="Aplicar filtros"><i class="bx bx-search"></i> Buscar</button>
                            <a class="btn btn-outline-secondary" href="{{ route('ordemServico.index') }}" title="Limpar filtros"><i class="bx bx-eraser"></i></a>
                        </div>
                    </div>
                </div>
            </form>

            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0">Resultados</h6>
                <span class="badge bg-primary">{{ $data->total() }} {{ $data->total() === 1 ? 'ordem' : 'ordens' }}</span>
            </div>

            <div class="table-responsive os-table-wrapper">
                <table class="table table-sm table-striped table-hover align-middle mb-0 os-table">
                    <thead class="table-light">
                        <tr>
                            <th>Nº</th>
                            <th>Cliente</th>
                            <th class="d-none d-lg-table-cell">Descrição</th>
                            <th class="d-none d-md-table-cell">Data</th>
                            <th class="text-end">Total</th>
                            <th>Status</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $item)
                            @php
                                $statusClasses = [
                                    'pendente' => 'bg-warning text-dark',
                                    'Em Andamento' => 'bg-info text-dark',
                                    'pronto' => 'bg-primary',
                                    'finalizado' => 'bg-success',
                                    'reprovado' => 'bg-danger',
                                ];
                            @endphp
                            <tr>
                                <td class="fw-semibold">#{{ $item->numero_sequencial ?? $item->id }}</td>
                                <td class="os-client" title="{{ $item->cliente ? $item->cliente->razao_social : 'Cliente não encontrado' }}">{{ $item->cliente ? $item->cliente->razao_social : 'Cliente não encontrado' }}</td>
                                <td class="d-none d-lg-table-cell os-description" title="{{ $item->descricao }}">{{ \Illuminate\Support\Str::limit($item->descricao, 38) }}</td>
                                <td class="d-none d-md-table-cell text-nowrap">{{ $item->created_at ? $item->created_at->format('d/m/Y') : '-' }}</td>
                                <td class="text-end text-nowrap">R$ {{ __moeda($item->valor ?? 0) }}</td>
                                <td><span class="badge {{ $statusClasses[$item->estado] ?? 'bg-secondary' }}">{{ mb_strtoupper($item->estado) }}</span></td>
                                <td class="os-actions-cell">
                                    <div class="os-actions" role="group" aria-label="Ações da ordem #{{ $item->id }}">
                                        <button type="button" class="btn btn-secondary btn-sm os-action" data-bs-toggle="modal" data-bs-target="#modal-os-{{ $item->id }}" title="Visualizar e imprimir em 80 mm" aria-label="Visualizar ordem #{{ $item->id }}">
                                            <i class="bx bx-show"></i>
                                        </button>
                                        <a href="{{ route('ordemServico.completa', $item->id) }}" class="btn btn-info btn-sm text-white os-action" title="Abrir ordem" aria-label="Abrir ordem #{{ $item->id }}"><i class="bx bx-detail"></i></a>
                                        <a href="{{ route('ordemServico.imprimir', $item->id) }}" class="btn btn-primary btn-sm os-action" title="Imprimir em A4" aria-label="Imprimir ordem #{{ $item->id }} em A4"><i class="bx bx-printer"></i></a>
                                        <form action="{{ route('ordemServico.destroy', $item->id) }}" method="POST" class="js-delete-order" data-order="#{{ $item->numero_sequencial ?? $item->id }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm os-action" title="Excluir" aria-label="Excluir ordem #{{ $item->id }}"><i class="bx bx-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-5"><i class="bx bx-search-alt fs-1 d-block mb-2"></i>Nenhuma ordem encontrada.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">{{ $data->appends(request()->query())->links() }}</div>

            @foreach($data as $item)
                @include('ordem_servico.partials.modal_detalhes', ['item' => $item])
            @endforeach
        </div>
    </div>
</div>

<style>
.os-table-wrapper { border: 1px solid #e9ecef; border-radius: .375rem; }
.os-table { font-size: .78rem; }
.os-table > :not(caption) > * > * { padding: .35rem .4rem; }
.os-table thead th { font-size: .7rem; letter-spacing: .02em; text-transform: uppercase; white-space: nowrap; }
.os-table .badge { font-size: .62rem; padding: .3rem .4rem; }
.os-client { max-width: 180px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.os-description { max-width: 220px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.os-actions-cell { width: 132px; min-width: 132px; }
.os-actions { display: flex; align-items: center; justify-content: flex-end; gap: 4px; white-space: nowrap; }
.os-actions form { display: inline-flex; margin: 0; }
.os-action { display: inline-flex; align-items: center; justify-content: center; width: 27px; height: 27px; padding: 0; }
.os-action i { font-size: .9rem; line-height: 1; }
@media (max-width: 767.98px) {
    .os-table { font-size: .72rem; }
    .os-client { max-width: 130px; }
}

.thermal-receipt { width: 80mm; max-width: 100%; color: #000; font-family: "Courier New", monospace; font-size: 12px; line-height: 1.3; box-shadow: 0 0 12px rgba(0,0,0,.12); }
.thermal-title { font-size: 16px; }
.thermal-separator { border-top: 1px dashed #000; margin: 8px 0; }
.thermal-item { margin: 6px 0; }
.thermal-total { font-size: 14px; margin: 4px 0; }
</style>
@endsection

@section('js')
<script>
document.querySelectorAll('.js-delete-order').forEach(function (form) {
    form.addEventListener('submit', function (event) {
        if (!window.confirm('Excluir definitivamente a ordem ' + form.dataset.order + '? Todos os itens vinculados também serão removidos.')) {
            event.preventDefault();
        }
    });
});

document.querySelectorAll('.js-print-80').forEach(function (button) {
    button.addEventListener('click', function () {
        const receipt = document.getElementById(button.dataset.target);
        if (!receipt) return;

        const printWindow = window.open('', '_blank', 'width=420,height=700');
        if (!printWindow) {
            window.alert('Permita pop-ups no navegador para imprimir a ordem.');
            return;
        }

        printWindow.document.write(`<!doctype html><html><head><meta charset="utf-8"><title>Ordem de serviço</title><style>
            @@page { size: 80mm auto; margin: 3mm; }
            * { box-sizing: border-box; }
            html, body { width: 74mm; margin: 0; padding: 0; color: #000; font-family: "Courier New", monospace; font-size: 11px; line-height: 1.3; }
            .thermal-receipt { width: 74mm; }
            .text-center { text-align: center; } .text-muted { color: #444; }
            .d-block { display: block; } .d-flex { display: flex; } .justify-content-between { justify-content: space-between; }
            .gap-2 { gap: 4px; } .mb-2 { margin-bottom: 6px; } .mt-3 { margin-top: 10px; } .mt-4 { margin-top: 16px; }
            .thermal-title { font-size: 15px; } .thermal-separator { border-top: 1px dashed #000; margin: 7px 0; }
            .thermal-item { margin: 5px 0; } .thermal-total { font-size: 13px; margin: 4px 0; }
        </style></head><body>${receipt.innerHTML}</body></html>`);
        printWindow.document.close();
        printWindow.focus();
        printWindow.onload = function () { printWindow.print(); printWindow.close(); };
    });
});
</script>
@endsection