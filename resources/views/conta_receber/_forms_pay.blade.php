@php
    $saldoRestante = max(0, (float) $item->valor_integral - (float) $item->valor_recebido);
@endphp

<div class="space-y-5">
    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <div>
            <label class="mb-1 block text-sm font-bold text-slate-700">Data do recebimento</label>
            <input type="date"
                   name="data_recebimento"
                   value="{{ old('data_recebimento', now()->format('Y-m-d')) }}"
                   class="block w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100"
                   required>
        </div>

        <div class="md:col-span-2 rounded-xl border border-indigo-100 bg-indigo-50 px-4 py-3">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-black uppercase tracking-wider text-indigo-500">Saldo disponível para receber</p>
                    <p class="mt-1 text-2xl font-black text-indigo-700">R$ {{ __moeda($saldoRestante) }}</p>
                </div>
                <button type="button" id="preencherRestante" class="rounded-lg bg-white px-4 py-2 text-xs font-black text-indigo-700 shadow-sm ring-1 ring-indigo-100 hover:bg-indigo-100">
                    Usar saldo restante
                </button>
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white">
        <div class="flex flex-col gap-3 border-b border-slate-100 px-4 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="font-black text-slate-800">Formas de pagamento</h3>
                <p class="text-xs text-slate-500">Adicione uma linha para cada forma usada pelo cliente.</p>
            </div>
            <button type="button" id="adicionarPagamento" class="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-900 px-4 py-2 text-xs font-black text-white hover:bg-slate-800">
                <i class="bx bx-plus"></i> Adicionar forma
            </button>
        </div>

        <div id="pagamentosContainer" class="divide-y divide-slate-100"></div>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="rounded-xl bg-slate-50 p-4">
            <span class="text-xs font-bold text-slate-500">Total informado</span>
            <strong id="totalInformado" class="mt-1 block text-xl text-slate-900">R$ 0,00</strong>
        </div>
        <div class="rounded-xl bg-slate-50 p-4">
            <span class="text-xs font-bold text-slate-500">Saldo atual</span>
            <strong class="mt-1 block text-xl text-slate-900">R$ {{ __moeda($saldoRestante) }}</strong>
        </div>
        <div id="cardSaldoDepois" class="rounded-xl bg-amber-50 p-4">
            <span class="text-xs font-bold text-amber-600">Saldo após recebimento</span>
            <strong id="saldoDepois" class="mt-1 block text-xl text-amber-700">R$ {{ __moeda($saldoRestante) }}</strong>
        </div>
    </div>

    <input type="hidden" name="lote_pagamento" value="{{ old('lote_pagamento', $lotePagamento) }}">
    <input type="hidden" name="previous_url" value="{{ url()->previous() }}">

    <div id="alertaPagamento" class="hidden rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-bold text-red-700"></div>

    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
        <a href="{{ route('conta-receber.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-bold text-slate-600 hover:bg-slate-50">
            Cancelar
        </a>
        <button type="submit" id="btnRegistrarRecebimento" class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-6 py-2.5 text-sm font-black text-white shadow-sm hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50">
            <i class="bx bx-check-circle"></i> Registrar recebimento
        </button>
    </div>
</div>

<template id="pagamentoTemplate">
    <div class="pagamento-row grid grid-cols-1 gap-3 p-4 md:grid-cols-12 md:items-end">
        <div class="md:col-span-4">
            <label class="mb-1 block text-xs font-black uppercase tracking-wide text-slate-500">Forma</label>
            <select data-field="forma_pagamento" class="forma-pagamento block w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm" required>
                <option value="">Selecione</option>
                @foreach($formasPagamento as $codigo => $descricao)
                    <option value="{{ $codigo }}">{{ $descricao }}</option>
                @endforeach
            </select>
        </div>
        <div class="md:col-span-3">
            <label class="mb-1 block text-xs font-black uppercase tracking-wide text-slate-500">Valor</label>
            <input type="text" inputmode="decimal" data-field="valor" class="valor-pagamento block w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm font-bold" placeholder="0,00" required>
        </div>
        <div class="md:col-span-4">
            <label class="mb-1 block text-xs font-black uppercase tracking-wide text-slate-500">Observação</label>
            <input type="text" data-field="observacao" maxlength="500" class="block w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm" placeholder="Ex.: cartão final 1234">
        </div>
        <div class="md:col-span-1 md:text-right">
            <button type="button" class="remover-pagamento inline-flex h-10 w-10 items-center justify-center rounded-xl border border-red-100 text-red-500 hover:bg-red-50" title="Remover forma">
                <i class="bx bx-trash"></i>
            </button>
        </div>
    </div>
</template>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const saldoRestante = Number(@json(round($saldoRestante, 2)));
    const container = document.getElementById('pagamentosContainer');
    const template = document.getElementById('pagamentoTemplate');
    const btnAdicionar = document.getElementById('adicionarPagamento');
    const btnRestante = document.getElementById('preencherRestante');
    const totalEl = document.getElementById('totalInformado');
    const saldoEl = document.getElementById('saldoDepois');
    const cardSaldo = document.getElementById('cardSaldoDepois');
    const alerta = document.getElementById('alertaPagamento');
    const submit = document.getElementById('btnRegistrarRecebimento');

    function moeda(valor) {
        return valor.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
    }

    function parseValor(valor) {
        valor = String(valor || '').trim().replace(/[^0-9,.-]/g, '');
        if (valor.includes(',') && valor.includes('.')) {
            valor = valor.replace(/\./g, '').replace(',', '.');
        } else if (valor.includes(',')) {
            valor = valor.replace(',', '.');
        }
        const numero = Number(valor);
        return Number.isFinite(numero) ? numero : 0;
    }

    function reindexar() {
        container.querySelectorAll('.pagamento-row').forEach((row, index) => {
            row.querySelectorAll('[data-field]').forEach((field) => {
                field.name = `pagamentos[${index}][${field.dataset.field}]`;
            });
            const remover = row.querySelector('.remover-pagamento');
            remover.classList.toggle('invisible', container.querySelectorAll('.pagamento-row').length === 1);
        });
    }

    function atualizarResumo() {
        const total = Array.from(container.querySelectorAll('.valor-pagamento'))
            .reduce((soma, input) => soma + parseValor(input.value), 0);
        const depois = Math.max(0, saldoRestante - total);
        const excedeu = total > saldoRestante + 0.009;

        totalEl.textContent = moeda(total);
        saldoEl.textContent = moeda(depois);

        cardSaldo.classList.toggle('bg-emerald-50', !excedeu && depois <= 0.009 && total > 0);
        cardSaldo.classList.toggle('bg-amber-50', depois > 0.009 && !excedeu);
        cardSaldo.classList.toggle('bg-red-50', excedeu);

        if (excedeu) {
            alerta.textContent = 'O total das formas de pagamento não pode ultrapassar o saldo restante.';
            alerta.classList.remove('hidden');
        } else {
            alerta.classList.add('hidden');
        }

        submit.disabled = excedeu || total <= 0 || saldoRestante <= 0;
    }

    function adicionarLinha(valores = {}) {
        const fragment = template.content.cloneNode(true);
        const row = fragment.querySelector('.pagamento-row');
        row.querySelector('[data-field="forma_pagamento"]').value = valores.forma_pagamento || '';
        row.querySelector('[data-field="valor"]').value = valores.valor || '';
        row.querySelector('[data-field="observacao"]').value = valores.observacao || '';
        container.appendChild(fragment);
        reindexar();
        atualizarResumo();
    }

    btnAdicionar.addEventListener('click', () => adicionarLinha());

    container.addEventListener('click', function (event) {
        const button = event.target.closest('.remover-pagamento');
        if (!button) return;
        if (container.querySelectorAll('.pagamento-row').length <= 1) return;
        button.closest('.pagamento-row').remove();
        reindexar();
        atualizarResumo();
    });

    container.addEventListener('input', atualizarResumo);
    container.addEventListener('change', atualizarResumo);

    btnRestante.addEventListener('click', function () {
        const inputs = container.querySelectorAll('.valor-pagamento');
        const outros = Array.from(inputs).slice(0, -1).reduce((soma, input) => soma + parseValor(input.value), 0);
        const alvo = inputs[inputs.length - 1];
        if (alvo) alvo.value = Math.max(0, saldoRestante - outros).toFixed(2).replace('.', ',');
        atualizarResumo();
    });

    const antigos = @json(old('pagamentos', []));
    if (Array.isArray(antigos) && antigos.length) {
        antigos.forEach(adicionarLinha);
    } else {
        adicionarLinha({ valor: saldoRestante.toFixed(2).replace('.', ',') });
    }
});
</script>