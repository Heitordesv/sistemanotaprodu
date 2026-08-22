@extends('default.layout')
<script src="https://cdn.tailwindcss.com"></script>

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 gap-4">
        <div>
            <h3 class="text-2xl font-extrabold text-gray-800 tracking-tight">Dashboard de Ordens de Serviço</h3>
            <p class="text-sm text-gray-500">Período: <span class="font-semibold text-blue-600">{{ $mesReferencia }}</span></p>
        </div>

        <form action="{{ route('ordemServico.dashboard') }}" method="GET" class="flex flex-wrap md:flex-nowrap gap-2">
            <input type="date" name="start_date" value="{{ request('start_date') }}" 
                class="bg-white border border-gray-300 text-gray-700 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5 shadow-sm outline-none">
            
            <input type="date" name="end_date" value="{{ request('end_date') }}" 
                class="bg-white border border-gray-300 text-gray-700 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5 shadow-sm outline-none">
            
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg transition duration-200 shadow-md">
                Filtrar
            </button>
            <a href="{{ route('ordemServico.dashboard') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-600 py-2 px-4 rounded-lg transition duration-200 flex items-center justify-center">
                Limpar
            </a>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-gradient-to-br from-blue-600 to-blue-700 rounded-2xl shadow-lg p-6 text-white transform hover:scale-[1.02] transition-all">
            <h5 class="text-blue-100 uppercase text-xs font-black tracking-widest opacity-80">Total de OS</h5>
            <p class="text-4xl font-black mt-2">{{ $totalOs }}</p>
        </div>
        <div class="bg-gradient-to-br from-emerald-600 to-emerald-700 rounded-2xl shadow-lg p-6 text-white transform hover:scale-[1.02] transition-all">
            <h5 class="text-emerald-100 uppercase text-xs font-black tracking-widest opacity-80">Faturamento Total</h5>
            <p class="text-3xl font-black mt-2">R$ {{ number_format($totalFaturado, 2, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col justify-between">
            <div>
                <h5 class="text-gray-400 uppercase text-[10px] font-black tracking-widest">Total em Serviços</h5>
                <p class="text-2xl font-bold mt-1 text-gray-800">R$ {{ number_format($totalServicos, 2, ',', '.') }}</p>
            </div>
            <div class="mt-4 flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                <span class="text-[10px] font-bold text-blue-600 uppercase">Mão de obra</span>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col justify-between">
            <div>
                <h5 class="text-gray-400 uppercase text-[10px] font-black tracking-widest">Total em Produtos</h5>
                <p class="text-2xl font-bold mt-1 text-gray-800">R$ {{ number_format($totalProdutos, 2, ',', '.') }}</p>
            </div>
            <div class="mt-4 flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-orange-500"></span>
                <span class="text-[10px] font-bold text-orange-600 uppercase">Peças e vendas</span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-1 bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h5 class="text-lg font-bold text-gray-800 mb-6 flex items-center gap-2">
                <i class="bx bx-map-alt text-blue-500"></i> Status OS
            </h5>
            <div class="space-y-5">
                @forelse($osPorEstado as $estado)
                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-sm font-bold text-gray-600">{{ strtoupper($estado->estado) }}</span>
                            <span class="text-xs font-black text-blue-700">{{ $estado->total }} OS</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2">
                            <div class="bg-blue-500 h-2 rounded-full" style="width: {{ ($estado->total / max($totalOs, 1)) * 100 }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-400 italic">Nenhum dado por estado.</p>
                @endforelse
            </div>
        </div>

        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 flex flex-col h-full overflow-hidden">
            <div class="p-6 border-b border-gray-50 flex justify-between items-center bg-gray-50/50">
                <h5 class="text-lg font-bold text-gray-800">Listagem de Ordens de Serviço</h5>
            </div>
            
            <div class="flex-grow overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-white text-gray-400 uppercase text-[10px] font-black tracking-widest border-b border-gray-100">
                            <th class="px-6 py-4">Data</th>
                            <th class="px-6 py-4">Cliente</th>
                            <th class="px-6 py-4 text-right">Valor</th>
                            <th class="px-6 py-4 text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($osRecentes as $os)
                        <tr class="hover:bg-blue-50/30 transition-colors group">
                            <td class="px-6 py-4">
                                <span class="text-sm font-bold text-gray-700 block">{{ \Carbon\Carbon::parse($os->created_at)->format('d/m') }}</span>
                                <span class="text-[10px] text-gray-400">{{ \Carbon\Carbon::parse($os->created_at)->format('Y') }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-gray-800 group-hover:text-blue-700 transition-colors line-clamp-1">{{ $os->cliente->razao_social ?? 'N/A' }}</div>
                                <div class="text-[10px] text-gray-400 font-mono italic">REF: #{{ $os->id }}</div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="text-sm font-black text-gray-800 italic text-nowrap">R$ {{ number_format($os->valor, 2, ',', '.') }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <a href="{{ route('ordemServico.completa', $os->id) }}" 
                                   class="inline-flex items-center justify-center w-8 h-8 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-600 hover:text-white transition-all shadow-sm"
                                   title="Ver Detalhes">
                                    <i class="bx bx-detail text-lg"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-gray-400 italic text-sm">Nenhuma OS encontrada.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($osRecentes->hasPages())
            <div class="px-6 py-4 bg-gray-50/50 border-t border-gray-100">
                <div class="pagination-custom">
                    {{ $osRecentes->appends(request()->query())->links() }}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<style>
    /* Estilização para garantir que a paginação do Laravel combine com o Tailwind do seu projeto */
    .pagination-custom nav svg { width: 1.5rem; height: 1.5rem; display: inline; }
    .pagination-custom nav div:first-child { display: none; } /* Esconde o "Showing X to Y" em telas pequenas se preferir */
    @media (min-width: 640px) { .pagination-custom nav div:first-child { display: block; } }
</style>
@endsection