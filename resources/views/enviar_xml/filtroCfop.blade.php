@extends('default.layout', ['title' => 'Filtro CFOP'])

@section('content')
<script src="https://cdn.tailwindcss.com"></script>

<div class="page-content bg-gray-50 min-h-screen py-8 px-4">
    <div class="max-w-7xl mx-auto">
        
        <div class="mb-8">
            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Filtro por CFOP</h1>
            <p class="text-gray-600 mt-1">An&aacute;lise detalhada de opera&ccedil;&otilde;es e representatividade sobre o faturamento.</p>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-8">
            {!! Form::open()->get()->route('enviarXml.filtroCfopGet') !!}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 items-end">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Data Inicial</label>
                    {!! Form::date('start_date')->attrs(['class' => 'w-full rounded-xl border-gray-300 shadow-sm'])->value($start_date ?? '') !!}
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Data Final</label>
                    {!! Form::date('end_date')->attrs(['class' => 'w-full rounded-xl border-gray-300 shadow-sm'])->value($end_date ?? '') !!}
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">CFOP</label>
                    {!! Form::tel('cfop')->attrs(['class' => 'cfop w-full rounded-xl border-gray-300 shadow-sm'])->placeholder('Ex: 5102')->value($cfop_filtro ?? '') !!}
                </div>
                <div>
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-xl transition-all shadow-lg shadow-blue-200">
                        <i class="bx bx-search text-xl"></i> Filtrar
                    </button>
                </div>
            </div>
            {!! Form::close() !!}
        </div>

        @isset($itens)
        @php
            // CALCULAMOS TUDO AQUI NO TOPO PARA EVITAR ERRO DE VARIè™°VEL INDEFINIDA
            $somaValor = $itens->sum('total'); 
            $somaQuantidade = $itens->sum('qtd');
            $vendasTotal = $somaTotalVendas ?? 0;
            $percentual = $vendasTotal > 0 ? ($somaValor / $vendasTotal) * 100 : 0;
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm text-center">
                <span class="text-xs font-bold text-blue-500 uppercase tracking-widest">Volume de Itens</span>
                <p class="text-3xl font-black text-gray-800">{{ number_format($somaQuantidade, 2, ',', '.') }}</p>
            </div>
            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm text-center">
                <span class="text-xs font-bold text-emerald-500 uppercase tracking-widest">Faturamento no CFOP</span>
                <p class="text-3xl font-black text-emerald-600">R$ {{ number_format($somaValor, 2, ',', '.') }}</p>
            </div>
            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm text-center">
                <span class="text-xs font-bold text-orange-500 uppercase tracking-widest">Representatividade</span>
                <p class="text-3xl font-black text-orange-600">{{ number_format($percentual, 2, ',', '.') }}%</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">CFOP</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Produto</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase text-center">UN</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase text-center">Qtd.</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($itens as $i)
                    <tr class="hover:bg-gray-50/80 transition-colors text-sm">
                        <td class="px-6 py-4"><span class="bg-blue-50 text-blue-700 px-2 py-1 rounded-full font-bold text-xs">{{ $i->cfop }}</span></td>
                        <td class="px-6 py-4 font-bold text-gray-900">{{ $i->nome }}</td>
                        <td class="px-6 py-4 text-center text-gray-500">{{ $i->unidade ?? 'UN' }}</td>
                        <td class="px-6 py-4 text-center font-mono text-gray-600">{{ number_format($i->qtd, 2, ',', '.') }}</td>
                        <td class="px-6 py-4 text-right font-bold text-gray-900">R$ {{ number_format($i->total, 2, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="bg-gray-50 p-6 flex justify-between items-center border-t border-gray-200">
                <p class="text-xs text-gray-400">Relat&oacute;rio baseado em dados de emiss&atilde;o.</p>
                <form method="get" action="{{ route('enviarXml.imprimir') }}">
    {{-- Mantenha os inputs hidden exatamente como estè´™o --}}
    <input type="hidden" value="{{ $start_date ?? '' }}" name="dataInicial">
    <input type="hidden" value="{{ number_format($percentual, 2, '.', '') }}" name="percentual">
    <input type="hidden" value="{{ $end_date ?? '' }}" name="dataFinal">
    <input type="hidden" value="{{ $cfop_filtro ?? '' }}" name="cfop">
    <input type="hidden" value="{{ $vendasTotal }}" name="somaTotalVendas">

    <button type="submit" class="bg-gray-900 hover:bg-black text-white px-8 py-3 rounded-xl font-bold flex items-center gap-2 transition-all active:scale-95 shadow-lg">
        <i class="bx bx-printer text-xl"></i> 
        Imprimir Relat&oacute;rio
    </button>
</form>
            </div>
        </div>
        @endisset
    </div>
</div>
@endsection