@extends('default.layout', ['title' => 'Relatórios de Vendas'])

@section('content')
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
<script src="https://cdn.tailwindcss.com"></script>

<div class="p-6 bg-gray-50 min-h-screen">
    
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
        <div>
            <h3 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                <i class='bx bx-bar-chart-alt-2 text-blue-600'></i> Relatório de Vendas
            </h3>
            <p class="text-sm text-gray-500">
                Período: <span class="font-semibold">{{ \Carbon\Carbon::parse($data_inicial)->format('d/m/Y') }}</span> 
                até <span class="font-semibold">{{ \Carbon\Carbon::parse($data_final)->format('d/m/Y') }}</span>
            </p>
        </div>

        <form method="GET" action="{{ route('relatorios.vendasGeralView') }}" class="bg-white p-3 rounded-lg shadow-sm border border-gray-200 flex flex-wrap gap-3 items-end">
            <input type="hidden" name="empresa_id" value="{{ request()->empresa_id ?? auth()->user()->empresa_id }}">
            
            <div>
                <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Início</label>
                <input type="date" name="start_date" value="{{ $data_inicial }}" class="border rounded px-2 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            
            <div>
                <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Fim</label>
                <input type="date" name="end_date" value="{{ $data_final }}" class="border rounded px-2 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            </div>

            <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm font-medium transition-colors flex items-center gap-2">
                <i class='bx bx-filter-alt'></i> Filtrar
            </button>
        </form>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        @foreach($resumo as $r)
            <div class="bg-white p-4 rounded-xl shadow-sm border-l-4 border-blue-500">
                <p class="text-xs text-gray-500 uppercase font-bold tracking-wider">{{ $r->tipo_pagamento }}</p>
                <p class="text-xl font-bold text-gray-800">R$ {{ number_format($r->total, 2, ',', '.') }}</p>
            </div>
        @endforeach

        <div class="bg-blue-600 p-4 rounded-xl shadow-md text-white">
            <p class="text-xs uppercase font-bold tracking-wider opacity-80">Total Geral</p>
            <p class="text-2xl font-black">R$ {{ number_format($totalGeral, 2, ',', '.') }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-200">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-100 border-b border-gray-200">
                        <th class="px-4 py-3 text-xs font-bold text-gray-600 uppercase">Data/Hora</th>
                        <th class="px-4 py-3 text-xs font-bold text-gray-600 uppercase">ID</th>
                        <th class="px-4 py-3 text-xs font-bold text-gray-600 uppercase">Cliente</th>
                        <th class="px-4 py-3 text-xs font-bold text-gray-600 uppercase">Tipo</th>
                        <th class="px-4 py-3 text-xs font-bold text-gray-600 uppercase">Pagamento</th>
                        <th class="px-4 py-3 text-xs font-bold text-gray-600 uppercase text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($vendas as $v)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 text-sm text-gray-600">{{ \Carbon\Carbon::parse($v->created_at)->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3 text-sm font-mono text-blue-600">#{{ $v->id }}</td>
                            <td class="px-4 py-3 text-sm text-gray-800 font-medium">{{ $v->cliente }}</td>
                            <td class="px-4 py-3 text-xs">
                                <span class="px-2 py-1 rounded-full bg-gray-200 font-semibold text-gray-700">
                                    {{ strtoupper($v->tipo) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $v->tipo_pagamento }}</td>
                            <td class="px-4 py-3 text-sm font-bold text-gray-900 text-right">R$ {{ number_format($v->valor_total, 2, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-gray-400">
                                <i class='bx bx-info-circle text-4xl mb-2'></i>
                                <p>Nenhum registro encontrado para este período.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">
        {{ $vendas->appends(request()->query())->links() }}
    </div>
</div>
@endsection