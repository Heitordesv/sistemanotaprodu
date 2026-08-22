@extends('default.layout', ['title' => 'Pedidos da Loja'])

<script src="https://cdn.tailwindcss.com"></script>
<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

<style>
    @keyframes merchantSlideIn { from { transform: translateY(-10px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    @keyframes merchantProgress { from { width: 100%; } to { width: 0%; } }
    .merchant-toast { animation: merchantSlideIn .25s ease-out forwards; }
    .merchant-progress { animation: merchantProgress 5s linear forwards; }
</style>

@section('content')
@php
    $temFiltros = request()->filled('periodo') || request()->filled('status') || request()->filled('search');
@endphp

<div class="min-h-screen bg-slate-50/80 text-slate-900">
    <div class="mx-auto max-w-[1500px] px-4 py-6 md:px-6 lg:px-8 lg:py-8">

        {{-- Feedback --}}
        <div class="fixed right-4 top-4 z-[1000] flex w-[calc(100%-2rem)] max-w-sm flex-col gap-3">
            @if(session('success'))
                <div id="toast-success" class="merchant-toast overflow-hidden rounded-2xl border border-emerald-100 bg-white shadow-2xl">
                    <div class="flex items-start gap-3 p-4">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                            <i class="bx bx-check-circle text-2xl"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-black text-slate-800">Tudo certo</p>
                            <p class="mt-0.5 text-xs font-medium leading-5 text-slate-500">{{ session('success') }}</p>
                        </div>
                        <button type="button" onclick="this.closest('#toast-success').remove()" class="text-slate-300 hover:text-slate-600">
                            <i class="bx bx-x text-xl"></i>
                        </button>
                    </div>
                    <div class="merchant-progress h-1 bg-emerald-500"></div>
                </div>
            @endif

            @if(session('error'))
                <div id="toast-error" class="merchant-toast overflow-hidden rounded-2xl border border-rose-100 bg-white shadow-2xl">
                    <div class="flex items-start gap-3 p-4">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-rose-50 text-rose-600">
                            <i class="bx bx-error-circle text-2xl"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-black text-slate-800">Não foi possível concluir</p>
                            <p class="mt-0.5 text-xs font-medium leading-5 text-slate-500">{{ session('error') }}</p>
                        </div>
                        <button type="button" onclick="this.closest('#toast-error').remove()" class="text-slate-300 hover:text-slate-600">
                            <i class="bx bx-x text-xl"></i>
                        </button>
                    </div>
                    <div class="merchant-progress h-1 bg-rose-500"></div>
                </div>
            @endif
        </div>

        {{-- Cabeçalho --}}
        <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <div class="mb-2 flex items-center gap-2 text-xs font-black uppercase tracking-[0.16em] text-indigo-600">
                    <i class="bx bx-store-alt text-lg"></i>
                    Área do lojista
                </div>
                <h1 class="text-3xl font-black tracking-tight text-slate-950 md:text-4xl">Pedidos da loja</h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">
                    Acompanhe pagamentos, preparação e entrega dos pedidos em um único lugar.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2 text-xs font-bold text-slate-500">
                <span class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 shadow-sm">
                    <i class="bx bx-list-ul text-indigo-500"></i>
                    {{ $pedidos->total() }} {{ $pedidos->total() == 1 ? 'pedido encontrado' : 'pedidos encontrados' }}
                </span>
                @if($temFiltros)
                    <a href="{{ route('pedidosEcommerce.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-3 py-2 text-white transition hover:bg-slate-700">
                        <i class="bx bx-x"></i> Limpar filtros
                    </a>
                @endif
            </div>
        </div>

        {{-- Indicadores --}}
        <div class="mb-6 grid grid-cols-2 gap-3 xl:grid-cols-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm md:p-5">
                <div class="mb-3 flex items-center justify-between">
                    <span class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">Vendas hoje</span>
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600"><i class="bx bx-wallet"></i></span>
                </div>
                <p class="text-xl font-black tracking-tight text-slate-900 md:text-2xl">R$ {{ number_format($resumo['total_hoje'] ?? 0, 2, ',', '.') }}</p>
                <p class="mt-1 text-[11px] font-medium text-slate-400">Valor dos pedidos de hoje</p>
            </div>

            <div class="rounded-2xl border border-amber-100 bg-white p-4 shadow-sm md:p-5">
                <div class="mb-3 flex items-center justify-between">
                    <span class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">Pagamento pendente</span>
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-50 text-amber-600"><i class="bx bx-time-five"></i></span>
                </div>
                <p class="text-2xl font-black tracking-tight text-amber-600">{{ $resumo['pendentes'] ?? 0 }}</p>
                <p class="mt-1 text-[11px] font-medium text-slate-400">Pedidos aguardando confirmação</p>
            </div>

            <div class="rounded-2xl border border-emerald-100 bg-white p-4 shadow-sm md:p-5">
                <div class="mb-3 flex items-center justify-between">
                    <span class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">Pagos</span>
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600"><i class="bx bx-check-double"></i></span>
                </div>
                <p class="text-2xl font-black tracking-tight text-emerald-600">{{ $resumo['pagos'] ?? 0 }}</p>
                <p class="mt-1 text-[11px] font-medium text-slate-400">Pagamentos confirmados</p>
            </div>

            <div class="rounded-2xl bg-slate-900 p-4 text-white shadow-lg shadow-slate-200 md:p-5">
                <div class="mb-3 flex items-center justify-between">
                    <span class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-300">Total filtrado</span>
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/10 text-white"><i class="bx bx-receipt"></i></span>
                </div>
                <p class="text-2xl font-black tracking-tight">{{ $resumo['total_geral'] ?? 0 }}</p>
                <p class="mt-1 text-[11px] font-medium text-slate-400">Pedidos no filtro atual</p>
            </div>
        </div>

        {{-- Filtros --}}
        <div class="mb-5 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <form method="GET" action="{{ route('pedidosEcommerce.index') }}" class="grid gap-3 lg:grid-cols-[170px_190px_minmax(280px,1fr)_auto] lg:items-center">
                <label class="relative block">
                    <span class="sr-only">Período</span>
                    <i class="bx bx-calendar absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <select name="periodo" class="w-full appearance-none rounded-xl border border-slate-200 bg-slate-50 py-3 pl-10 pr-9 text-xs font-bold text-slate-700 outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                        <option value="">Todos os períodos</option>
                        <option value="hoje" {{ request('periodo') == 'hoje' ? 'selected' : '' }}>Hoje</option>
                        <option value="7" {{ request('periodo') == '7' ? 'selected' : '' }}>Últimos 7 dias</option>
                        <option value="30" {{ request('periodo') == '30' ? 'selected' : '' }}>Últimos 30 dias</option>
                    </select>
                </label>

                <label class="relative block">
                    <span class="sr-only">Status do pagamento</span>
                    <i class="bx bx-credit-card absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <select name="status" class="w-full appearance-none rounded-xl border border-slate-200 bg-slate-50 py-3 pl-10 pr-9 text-xs font-bold text-slate-700 outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                        <option value="">Todos os pagamentos</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Pago</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pendente</option>
                        <option value="canceled" {{ request('status') == 'canceled' ? 'selected' : '' }}>Cancelado</option>
                    </select>
                </label>

                <label class="relative block">
                    <span class="sr-only">Buscar pedido</span>
                    <i class="bx bx-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="search" name="search" value="{{ request('search') }}" placeholder="Buscar por pedido, cliente ou CPF/CNPJ" class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 pl-10 pr-4 text-sm font-medium text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                </label>

                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-3 text-xs font-black uppercase tracking-wider text-white shadow-sm transition hover:bg-indigo-700">
                    <i class="bx bx-filter-alt"></i> Filtrar
                </button>
            </form>
        </div>

        {{-- Desktop --}}
        <div class="hidden overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm md:block">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[950px] border-collapse text-left">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/80 text-[10px] font-black uppercase tracking-[0.12em] text-slate-400">
                            <th class="px-5 py-4">Pedido e cliente</th>
                            <th class="px-5 py-4">Pagamento</th>
                            <th class="px-5 py-4">Entrega</th>
                            <th class="px-5 py-4 text-right">Total</th>
                            <th class="px-5 py-4 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($pedidos as $p)
                            @php
                                $pagamento = match(strtolower((string) $p->status_pagamento)) {
                                    'approved' => ['Pago', 'bg-emerald-50 text-emerald-700 border-emerald-100', 'bx-check-circle'],
                                    'pending' => ['Pendente', 'bg-amber-50 text-amber-700 border-amber-100', 'bx-time-five'],
                                    'refunded' => ['Estornado', 'bg-sky-50 text-sky-700 border-sky-100', 'bx-revision'],
                                    'rejected' => ['Recusado', 'bg-rose-50 text-rose-700 border-rose-100', 'bx-error-circle'],
                                    default => ['Cancelado', 'bg-rose-50 text-rose-700 border-rose-100', 'bx-x-circle'],
                                };

                                $logistica = match(strtolower((string) $p->status)) {
                                    'novo' => ['Novo pedido', 'bg-slate-100 text-slate-600', 'bx-receipt'],
                                    'preparacao' => ['Em preparação', 'bg-violet-50 text-violet-700', 'bx-package'],
                                    'enviado' => ['Enviado', 'bg-blue-50 text-blue-700', 'bx-truck'],
                                    'entregue' => ['Entregue', 'bg-emerald-50 text-emerald-700', 'bx-check-double'],
                                    'cancelado' => ['Cancelado', 'bg-rose-50 text-rose-700', 'bx-x-circle'],
                                    default => [ucfirst((string) ($p->status ?: 'Novo')), 'bg-slate-100 text-slate-600', 'bx-info-circle'],
                                };

                                $clienteNome = trim((string) ($p->cliente->nome ?? 'Cliente não identificado'));
                                $telefone = preg_replace('/\D/', '', (string) ($p->cliente->telefone ?? ''));
                                if ($telefone !== '' && !str_starts_with($telefone, '55')) $telefone = '55' . $telefone;
                            @endphp
                            <tr class="group transition hover:bg-slate-50/70">
                                <td class="px-5 py-4">
                                    <a href="{{ route('pedidosEcommerce.show', $p->id) }}" class="block">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-sm font-black text-slate-700 transition group-hover:bg-indigo-600 group-hover:text-white">#{{ $p->id }}</div>
                                            <div class="min-w-0">
                                                <p class="truncate text-sm font-black text-slate-800">{{ $clienteNome }}</p>
                                                <p class="mt-0.5 text-[11px] font-medium text-slate-400">{{ $p->created_at ? $p->created_at->format('d/m/Y \à\s H:i') : '' }}</p>
                                            </div>
                                        </div>
                                    </a>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center gap-1.5 rounded-lg border px-2.5 py-1.5 text-[10px] font-black uppercase {{ $pagamento[1] }}">
                                        <i class="bx {{ $pagamento[2] }}"></i> {{ $pagamento[0] }}
                                    </span>
                                    <p class="mt-1.5 text-[10px] font-bold uppercase text-slate-400">{{ $p->forma_pagamento ?: 'Não informado' }}</p>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-[10px] font-black uppercase {{ $logistica[1] }}">
                                        <i class="bx {{ $logistica[2] }}"></i> {{ $logistica[0] }}
                                    </span>
                                    @if($p->tipo_frete)
                                        <p class="mt-1.5 text-[10px] font-bold uppercase text-slate-400">{{ $p->tipo_frete }}</p>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <p class="text-base font-black text-slate-900">R$ {{ number_format($p->valor_total ?? 0, 2, ',', '.') }}</p>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        @if($telefone !== '')
                                            <a href="https://wa.me/{{ $telefone }}" target="_blank" rel="noopener" title="Chamar no WhatsApp" class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 transition hover:bg-emerald-600 hover:text-white">
                                                <i class="bx bxl-whatsapp text-lg"></i>
                                            </a>
                                        @endif
                                        <button type="button" data-id="{{ $p->id }}" data-cliente="{{ $clienteNome }}" onclick="openModalStatusFromButton(this)" title="Gerenciar status" class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-50 text-amber-600 transition hover:bg-amber-500 hover:text-white">
                                            <i class="bx bx-slider-alt text-lg"></i>
                                        </button>
                                        <a href="{{ route('pedidosEcommerce.show', $p->id) }}" title="Abrir pedido" class="inline-flex h-9 items-center justify-center gap-2 rounded-xl bg-slate-900 px-3 text-[10px] font-black uppercase tracking-wider text-white transition hover:bg-indigo-600">
                                            Abrir <i class="bx bx-right-arrow-alt text-lg"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-16 text-center">
                                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400"><i class="bx bx-package text-3xl"></i></div>
                                    <h3 class="mt-4 text-base font-black text-slate-800">Nenhum pedido encontrado</h3>
                                    <p class="mt-1 text-sm text-slate-400">Tente alterar os filtros ou faça uma nova busca.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Mobile --}}
        <div class="space-y-3 md:hidden">
            @forelse($pedidos as $p)
                @php
                    $pagamento = match(strtolower((string) $p->status_pagamento)) {
                        'approved' => ['Pago', 'bg-emerald-50 text-emerald-700', 'bx-check-circle'],
                        'pending' => ['Pendente', 'bg-amber-50 text-amber-700', 'bx-time-five'],
                        'refunded' => ['Estornado', 'bg-sky-50 text-sky-700', 'bx-revision'],
                        'rejected' => ['Recusado', 'bg-rose-50 text-rose-700', 'bx-error-circle'],
                        default => ['Cancelado', 'bg-rose-50 text-rose-700', 'bx-x-circle'],
                    };
                    $logistica = match(strtolower((string) $p->status)) {
                        'novo' => ['Novo', 'bg-slate-100 text-slate-600', 'bx-receipt'],
                        'preparacao' => ['Preparando', 'bg-violet-50 text-violet-700', 'bx-package'],
                        'enviado' => ['Enviado', 'bg-blue-50 text-blue-700', 'bx-truck'],
                        'entregue' => ['Entregue', 'bg-emerald-50 text-emerald-700', 'bx-check-double'],
                        default => [ucfirst((string) ($p->status ?: 'Novo')), 'bg-slate-100 text-slate-600', 'bx-info-circle'],
                    };
                    $clienteNome = trim((string) ($p->cliente->nome ?? 'Cliente não identificado'));
                    $telefoneMobile = preg_replace('/\D/', '', (string) ($p->cliente->telefone ?? ''));
                    if ($telefoneMobile !== '' && !str_starts_with($telefoneMobile, '55')) $telefoneMobile = '55' . $telefoneMobile;
                @endphp
                <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <a href="{{ route('pedidosEcommerce.show', $p->id) }}" class="block p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-black text-indigo-600">#{{ $p->id }}</span>
                                    <span class="text-[10px] font-bold text-slate-300">•</span>
                                    <span class="text-[10px] font-bold text-slate-400">{{ $p->created_at ? $p->created_at->format('d/m/Y H:i') : '' }}</span>
                                </div>
                                <h3 class="mt-1 truncate text-base font-black text-slate-900">{{ $clienteNome }}</h3>
                            </div>
                            <p class="shrink-0 text-base font-black text-slate-900">R$ {{ number_format($p->valor_total ?? 0, 2, ',', '.') }}</p>
                        </div>

                        <div class="mt-4 flex flex-wrap gap-2">
                            <span class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1.5 text-[10px] font-black uppercase {{ $pagamento[1] }}"><i class="bx {{ $pagamento[2] }}"></i>{{ $pagamento[0] }}</span>
                            <span class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1.5 text-[10px] font-black uppercase {{ $logistica[1] }}"><i class="bx {{ $logistica[2] }}"></i>{{ $logistica[0] }}</span>
                        </div>
                    </a>
                    <div class="grid grid-cols-3 border-t border-slate-100">
                        <a href="{{ route('pedidosEcommerce.show', $p->id) }}" class="flex items-center justify-center gap-1.5 py-3 text-[10px] font-black uppercase text-slate-600"><i class="bx bx-show text-base"></i>Ver</a>
                        <button type="button" data-id="{{ $p->id }}" data-cliente="{{ $clienteNome }}" onclick="openModalStatusFromButton(this)" class="flex items-center justify-center gap-1.5 border-x border-slate-100 py-3 text-[10px] font-black uppercase text-amber-600"><i class="bx bx-slider-alt text-base"></i>Status</button>
                        @if($telefoneMobile !== '')
                            <a href="https://wa.me/{{ $telefoneMobile }}" target="_blank" rel="noopener" class="flex items-center justify-center gap-1.5 py-3 text-[10px] font-black uppercase text-emerald-600"><i class="bx bxl-whatsapp text-base"></i>WhatsApp</a>
                        @else
                            <span class="flex items-center justify-center gap-1.5 py-3 text-[10px] font-black uppercase text-slate-300"><i class="bx bx-phone-off text-base"></i>Sem fone</span>
                        @endif
                    </div>
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400"><i class="bx bx-package text-3xl"></i></div>
                    <h3 class="mt-4 font-black text-slate-800">Nenhum pedido encontrado</h3>
                    <p class="mt-1 text-sm text-slate-400">Altere os filtros para tentar novamente.</p>
                </div>
            @endforelse
        </div>

        @if($pedidos->hasPages())
            <div class="mt-6 rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
                {{ $pedidos->appends(request()->all())->links() }}
            </div>
        @endif
    </div>
</div>

{{-- Gerenciar pedido --}}
<div id="modalStatus" class="fixed inset-0 z-[998] hidden items-center justify-center bg-slate-950/60 p-4 backdrop-blur-sm">
    <div class="w-full max-w-md overflow-hidden rounded-3xl bg-white shadow-2xl">
        <div class="flex items-start justify-between border-b border-slate-100 bg-slate-50 p-5">
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.14em] text-indigo-600">Gestão do pedido</p>
                <h3 class="mt-1 text-xl font-black text-slate-900">Atualizar andamento</h3>
                <p id="labelPedidoModal" class="mt-1 text-xs font-semibold text-slate-400"></p>
            </div>
            <button type="button" onclick="closeModalStatus()" class="flex h-9 w-9 items-center justify-center rounded-xl bg-white text-slate-400 shadow-sm hover:text-rose-500"><i class="bx bx-x text-2xl"></i></button>
        </div>

        <div class="space-y-6 p-5">
            <section>
                <div class="mb-3 flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Pagamento</p>
                        <p class="mt-1 text-xs text-slate-500">Use apenas quando tiver certeza da confirmação.</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" onclick="confirmAction('aprovar')" class="rounded-2xl border border-emerald-100 bg-emerald-50 p-4 text-left text-emerald-700 transition hover:bg-emerald-600 hover:text-white">
                        <i class="bx bx-check-double text-2xl"></i>
                        <span class="mt-3 block text-xs font-black uppercase">Marcar pago</span>
                    </button>
                    <button type="button" onclick="confirmAction('cancelar')" class="rounded-2xl border border-rose-100 bg-rose-50 p-4 text-left text-rose-700 transition hover:bg-rose-600 hover:text-white">
                        <i class="bx bx-x-circle text-2xl"></i>
                        <span class="mt-3 block text-xs font-black uppercase">Cancelar</span>
                    </button>
                </div>
            </section>

            <section class="border-t border-slate-100 pt-5">
                <p class="mb-3 text-[10px] font-black uppercase tracking-widest text-slate-400">Logística</p>
                <div class="space-y-2">
                    <a id="linkPrep" href="#" class="flex items-center gap-3 rounded-xl border border-slate-100 bg-slate-50 px-4 py-3 text-xs font-black uppercase text-slate-600 transition hover:border-violet-200 hover:bg-violet-50 hover:text-violet-700"><i class="bx bx-package text-xl"></i>Em preparação</a>
                    <a id="linkEnvio" href="#" class="flex items-center gap-3 rounded-xl border border-slate-100 bg-slate-50 px-4 py-3 text-xs font-black uppercase text-slate-600 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700"><i class="bx bx-truck text-xl"></i>Pedido enviado</a>
                    <a id="linkEntregue" href="#" class="flex items-center gap-3 rounded-xl border border-slate-100 bg-slate-50 px-4 py-3 text-xs font-black uppercase text-slate-600 transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-700"><i class="bx bx-check-circle text-xl"></i>Entregue ao cliente</a>
                </div>
            </section>
        </div>
    </div>
</div>

{{-- Confirmação --}}
<div id="modalConfirm" class="fixed inset-0 z-[1001] hidden items-center justify-center bg-slate-950/50 p-4 backdrop-blur-sm">
    <div class="w-full max-w-sm rounded-3xl bg-white p-6 text-center shadow-2xl">
        <div id="confirmIcon" class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl text-3xl"></div>
        <h4 id="confirmTitle" class="text-xl font-black text-slate-900"></h4>
        <p id="confirmText" class="mx-auto mt-2 max-w-xs text-sm leading-6 text-slate-500"></p>
        <div class="mt-6 grid grid-cols-2 gap-2">
            <button type="button" onclick="closeConfirm()" class="rounded-xl bg-slate-100 py-3 text-xs font-black uppercase text-slate-600 transition hover:bg-slate-200">Voltar</button>
            <a id="btnConfirmAction" href="#" class="rounded-xl py-3 text-xs font-black uppercase text-white">Confirmar</a>
        </div>
    </div>
</div>

<script>
let currentUrls = {};

setTimeout(() => {
    document.getElementById('toast-success')?.remove();
    document.getElementById('toast-error')?.remove();
}, 5000);

function openModalStatusFromButton(button) {
    openModalStatus(button.dataset.id, button.dataset.cliente || 'Cliente não identificado');
}

function openModalStatus(id, cliente) {
    const modal = document.getElementById('modalStatus');
    document.getElementById('labelPedidoModal').innerText = `Pedido #${id} • ${cliente}`;

    const baseUrl = "{{ url('pedidosEcommerce/alterarStatus') }}";

    currentUrls = {
        aprovar: `${baseUrl}/${id}/approved/pagamento`,
        cancelar: `${baseUrl}/${id}/canceled/pagamento`
    };

    document.getElementById('linkPrep').href = `${baseUrl}/${id}/preparacao/pedido`;
    document.getElementById('linkEnvio').href = `${baseUrl}/${id}/enviado/pedido`;
    document.getElementById('linkEntregue').href = `${baseUrl}/${id}/entregue/pedido`;

    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.style.overflow = 'hidden';
}

function confirmAction(tipo) {
    const modal = document.getElementById('modalConfirm');
    const title = document.getElementById('confirmTitle');
    const text = document.getElementById('confirmText');
    const btn = document.getElementById('btnConfirmAction');
    const iconBox = document.getElementById('confirmIcon');

    if (tipo === 'aprovar') {
        title.innerText = 'Confirmar pagamento?';
        text.innerText = 'O pedido será marcado como pago e poderá seguir para preparação e envio.';
        btn.className = 'rounded-xl bg-emerald-600 py-3 text-xs font-black uppercase text-white transition hover:bg-emerald-700';
        btn.href = currentUrls.aprovar;
        iconBox.className = 'mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-emerald-50 text-3xl text-emerald-600';
        iconBox.innerHTML = "<i class='bx bx-check-double'></i>";
    } else {
        title.innerText = 'Cancelar pedido?';
        text.innerText = 'Confirme somente se o pedido realmente deve ser cancelado.';
        btn.className = 'rounded-xl bg-rose-600 py-3 text-xs font-black uppercase text-white transition hover:bg-rose-700';
        btn.href = currentUrls.cancelar;
        iconBox.className = 'mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-rose-50 text-3xl text-rose-600';
        iconBox.innerHTML = "<i class='bx bx-x-circle'></i>";
    }

    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeConfirm() {
    const modal = document.getElementById('modalConfirm');
    modal.classList.remove('flex');
    modal.classList.add('hidden');
}

function closeModalStatus() {
    const modal = document.getElementById('modalStatus');
    modal.classList.remove('flex');
    modal.classList.add('hidden');
    document.body.style.overflow = '';
}

document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeConfirm();
        closeModalStatus();
    }
});

window.addEventListener('click', function(event) {
    if (event.target.id === 'modalStatus') closeModalStatus();
    if (event.target.id === 'modalConfirm') closeConfirm();
});
</script>
@endsection