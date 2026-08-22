@extends('default.layout', ['title' => 'Receber Conta'])

@section('content')
<script src="https://cdn.tailwindcss.com"></script>

@php
    $saldoRestante = max(0, (float) $item->valor_integral - (float) $item->valor_recebido);
    $quitada = (int) $item->status === 1 || $saldoRestante <= 0.009;
@endphp

<div class="page-content p-4 md:p-6">
    <div class="mx-auto max-w-6xl space-y-5">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="mb-1 flex items-center gap-2 text-xs font-black uppercase tracking-[.16em] text-indigo-500">
                    <i class="bx bx-wallet"></i> Financeiro
                </div>
                <h1 class="text-2xl font-black text-slate-900">Receber conta</h1>
                <p class="mt-1 text-sm text-slate-500">Registre pagamentos parciais ou divida o mesmo recebimento entre várias formas.</p>
            </div>
            <a href="{{ route('conta-receber.index') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-600 shadow-sm hover:bg-slate-50">
                <i class="bx bx-arrow-back"></i> Voltar
            </a>
        </div>

        @if(session('flash_erro'))
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-bold text-red-700">{{ session('flash_erro') }}</div>
        @endif

        @if($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <strong class="block font-black">Revise os dados do recebimento:</strong>
                <ul class="mt-2 list-disc pl-5">
                    @foreach($errors->all() as $erro)
                        <li>{{ $erro }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <span class="text-xs font-black uppercase tracking-wider text-slate-400">Valor da conta</span>
                <strong class="mt-2 block text-2xl text-slate-900">R$ {{ __moeda($item->valor_integral) }}</strong>
            </div>
            <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-5">
                <span class="text-xs font-black uppercase tracking-wider text-emerald-500">Já recebido</span>
                <strong class="mt-2 block text-2xl text-emerald-700">R$ {{ __moeda($item->valor_recebido) }}</strong>
            </div>
            <div class="rounded-2xl border {{ $quitada ? 'border-emerald-100 bg-emerald-50' : 'border-amber-100 bg-amber-50' }} p-5">
                <span class="text-xs font-black uppercase tracking-wider {{ $quitada ? 'text-emerald-500' : 'text-amber-500' }}">Saldo restante</span>
                <strong class="mt-2 block text-2xl {{ $quitada ? 'text-emerald-700' : 'text-amber-700' }}">R$ {{ __moeda($saldoRestante) }}</strong>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <span class="text-xs font-black uppercase tracking-wider text-slate-400">Status</span>
                <div class="mt-2">
                    @if($quitada)
                        <span class="inline-flex items-center gap-2 rounded-full bg-emerald-100 px-3 py-1.5 text-xs font-black text-emerald-700"><i class="bx bx-check-circle"></i> Quitada</span>
                    @elseif((float) $item->valor_recebido > 0)
                        <span class="inline-flex items-center gap-2 rounded-full bg-blue-100 px-3 py-1.5 text-xs font-black text-blue-700"><i class="bx bx-time"></i> Parcial</span>
                    @elseif(\Carbon\Carbon::parse($item->data_vencimento)->isPast())
                        <span class="inline-flex items-center gap-2 rounded-full bg-red-100 px-3 py-1.5 text-xs font-black text-red-700"><i class="bx bx-error"></i> Atrasada</span>
                    @else
                        <span class="inline-flex items-center gap-2 rounded-full bg-amber-100 px-3 py-1.5 text-xs font-black text-amber-700"><i class="bx bx-time-five"></i> Pendente</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="grid grid-cols-1 gap-4 text-sm md:grid-cols-4">
                <div><span class="block text-xs font-bold text-slate-400">Referência</span><strong class="mt-1 block text-slate-800">{{ $item->referencia ?: 'Sem referência' }}</strong></div>
                <div><span class="block text-xs font-bold text-slate-400">Vencimento</span><strong class="mt-1 block text-slate-800">{{ __data_pt($item->data_vencimento, false) }}</strong></div>
                <div><span class="block text-xs font-bold text-slate-400">Cliente</span><strong class="mt-1 block text-slate-800">{{ optional($item->cliente)->razao_social ?: optional($item->cliente)->nome_fantasia ?: 'Não informado' }}</strong></div>
                <div><span class="block text-xs font-bold text-slate-400">Categoria</span><strong class="mt-1 block text-slate-800">{{ optional($item->categoria)->nome ?: 'Sem categoria' }}</strong></div>
            </div>
        </div>

        @if(!$quitada)
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm md:p-6">
                <div class="mb-5">
                    <h2 class="text-lg font-black text-slate-900">Novo recebimento</h2>
                    <p class="text-sm text-slate-500">Exemplo: R$ 300 no PIX + R$ 200 no cartão + R$ 100 em dinheiro.</p>
                </div>

                <form action="{{ route('conta-receber.payPut', $item->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    @include('conta_receber._forms_pay')
                </form>
            </div>
        @endif

        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-2 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="font-black text-slate-900">Histórico de recebimentos</h2>
                    <p class="text-xs text-slate-500">Cada forma de pagamento fica registrada separadamente.</p>
                </div>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-600">{{ $pagamentos->count() }} lançamento(s)</span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50 text-left text-[11px] font-black uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-5 py-3">Data</th>
                            <th class="px-5 py-3">Forma</th>
                            <th class="px-5 py-3">Origem</th>
                            <th class="px-5 py-3">Observação</th>
                            <th class="px-5 py-3 text-right">Valor</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @if($recebidoAnteriorAoHistorico > 0)
                            <tr class="bg-amber-50/60">
                                <td class="px-5 py-4 text-slate-500">Antes do histórico</td>
                                <td class="px-5 py-4"><span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-700">Legado</span></td>
                                <td class="px-5 py-4 text-slate-500">Sistema anterior</td>
                                <td class="px-5 py-4 text-slate-500">Valor já recebido antes da implantação do histórico detalhado.</td>
                                <td class="px-5 py-4 text-right font-black text-slate-800">R$ {{ __moeda($recebidoAnteriorAoHistorico) }}</td>
                            </tr>
                        @endif

                        @forelse($pagamentos as $pagamento)
                            <tr>
                                <td class="whitespace-nowrap px-5 py-4 text-slate-600">{{ optional($pagamento->data_pagamento)->format('d/m/Y H:i') }}</td>
                                <td class="px-5 py-4 font-bold text-slate-800">{{ $formasPagamento[$pagamento->forma_pagamento] ?? $pagamento->forma_pagamento }}</td>
                                <td class="px-5 py-4"><span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600">{{ ucfirst($pagamento->provedor ?: $pagamento->origem) }}</span></td>
                                <td class="max-w-xs px-5 py-4 text-slate-500">{{ $pagamento->observacao ?: '—' }}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-right font-black text-emerald-700">R$ {{ __moeda($pagamento->valor) }}</td>
                            </tr>
                        @empty
                            @if($recebidoAnteriorAoHistorico <= 0)
                                <tr><td colspan="5" class="px-5 py-10 text-center text-slate-400">Nenhum recebimento registrado ainda.</td></tr>
                            @endif
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection