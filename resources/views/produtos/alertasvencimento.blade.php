@extends('default.layout',['title' => 'Gestão de Validade'])

@section('content')
<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>

<div class="p-4 sm:p-6 lg:p-8 space-y-6 bg-gray-50 min-h-screen">
    
    {{-- Cabeçalho --}}
    <div class="flex flex-wrap gap-4 justify-between items-center bg-white p-6 rounded-xl shadow-md border-l-4 border-red-600">
        <div>
            <h1 class="text-2xl font-black text-gray-800 uppercase tracking-tight">Alertas de Vencimento</h1>
            <p class="text-gray-500 text-sm">Exibindo apenas produtos vencidos ou com vencimento até 90 dias</p>
        </div>
        <a href="{{ route('produtos.index') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-bold transition flex items-center">
            <i class="bx bx-left-arrow-alt mr-1"></i> Voltar aos Produtos
        </a>
    </div>

    {{-- Cards de Resumo --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white p-5 rounded-xl shadow-sm border-b-4 border-red-600">
            <div class="flex justify-between items-center mb-2">
                <span class="text-gray-500 font-bold text-xs uppercase">Já Vencidos</span>
                <i class="bx bx-x-circle text-red-600 text-2xl"></i>
            </div>
            <div class="text-3xl font-black text-red-600">{{ $contagem['vencidos'] }}</div>
        </div>
        <div class="bg-white p-5 rounded-xl shadow-sm border-b-4 border-orange-500">
            <div class="flex justify-between items-center mb-2">
                <span class="text-gray-500 font-bold text-xs uppercase">Crítico (30 dias)</span>
                <i class="bx bx-error text-orange-500 text-2xl"></i>
            </div>
            <div class="text-3xl font-black text-orange-600">{{ $contagem['critico'] }}</div>
        </div>
        <div class="bg-white p-5 rounded-xl shadow-sm border-b-4 border-yellow-400">
            <div class="flex justify-between items-center mb-2">
                <span class="text-gray-500 font-bold text-xs uppercase">Atenção (90 dias)</span>
                <i class="bx bx-time text-yellow-500 text-2xl"></i>
            </div>
            <div class="text-3xl font-black text-yellow-600">{{ $contagem['atencao'] }}</div>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="bg-white shadow-md rounded-xl p-6">
        {!! Form::open()->fill(request()->all())->get() !!}
        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
            <div class="md:col-span-3">
                {!! Form::select('tipo', 'Buscar por:', ['nome'=>'Nome','lote'=>'Lote','codBarras'=>'Cód. Barras'])->attrs(['class'=>'w-full rounded-lg border-gray-300']) !!}
            </div>
            <div class="md:col-span-3">
                {!! Form::text('nome','Termo de pesquisa')->attrs(['class'=>'w-full rounded-lg border-gray-300', 'placeholder' => 'Digite aqui...']) !!}
            </div>
            <div class="md:col-span-3">
                {!! Form::select('status_vencimento', 'Status de Alerta', [
                    '' => 'Todos os alertas (90 dias)',
                    'vencido' => '🔴 Já Vencidos',
                    'critico' => '🟠 Crítico (Até 30 dias)',
                    'atencao' => '🟡 Atenção (31 a 90 dias)'
                ])->attrs(['class'=>'w-full rounded-lg border-gray-300']) !!}
            </div>
            <div class="md:col-span-3 flex items-end gap-2">
                <button type="submit" class="w-full bg-red-600 text-white font-bold py-2 rounded-lg hover:bg-red-700 transition">Filtrar Alertas</button>
                <a href="{{ route('produtos.alertasvencimento') }}" class="bg-gray-200 p-2 rounded-lg text-gray-600 hover:bg-gray-300 shadow-sm"><i class="bx bx-refresh text-xl"></i></a>
            </div>
        </div>
        {!! Form::close() !!}
    </div>

    {{-- Tabela --}}
    <div class="bg-white shadow-xl rounded-xl overflow-hidden border border-gray-100">
        <table class="w-full text-left border-collapse">
            <thead class="bg-gray-50 text-gray-600 text-xs uppercase font-bold border-b">
                <tr>
                    <th class="p-4">Produto / Código</th>
                    <th class="p-4 text-center">Lote</th>
                    <th class="p-4 text-center">Data Vencimento</th>
                    <th class="p-4 text-center">Situação</th>
                    <th class="p-4 text-center">Estoque Atual</th>
                    <th class="p-4 text-right">Ação</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($data as $p)
                @php 
                    $venc = \Carbon\Carbon::parse($p->vencimento);
                    $dias = \Carbon\Carbon::today()->diffInDays($venc, false);
                @endphp
                <tr class="hover:bg-gray-50 transition">
                    <td class="p-4">
                        <div class="font-bold text-gray-800">{{ $p->nome }}</div>
                        <div class="text-[10px] text-gray-400 font-mono">{{ $p->codBarras }}</div>
                    </td>
                    <td class="p-4 text-center">
                        <span class="px-2 py-1 bg-gray-100 text-gray-600 rounded font-mono text-xs border">
                            {{ $p->lote ?? 'N/D' }}
                        </span>
                    </td>
                    <td class="p-4 text-center font-bold">
                        <span class="{{ $dias < 0 ? 'text-red-600' : 'text-gray-700' }}">
                            {{ $venc->format('d/m/Y') }}
                        </span>
                    </td>
                    <td class="p-4 text-center">
                        @if($dias < 0)
                            <span class="px-3 py-1 bg-red-600 text-white text-[10px] font-black rounded shadow-sm">VENCIDO ({{ abs($dias) }} dias)</span>
                        @elseif($dias <= 30)
                            <span class="px-3 py-1 bg-red-100 text-red-700 text-[10px] font-black rounded border border-red-300 animate-pulse">🔴 CRÍTICO: {{ $dias }} DIAS</span>
                        @else
                            <span class="px-3 py-1 bg-yellow-100 text-yellow-800 text-[10px] font-black rounded border border-yellow-400">🟡 ATENÇÃO: {{ $dias }} DIAS</span>
                        @endif
                    </td>
                    <td class="p-4 text-center text-indigo-700 font-black text-lg">
                        {{ $p->estoquePorLocal($filial_id) }}
                    </td>
                    <td class="p-4 text-right">
                        <a href="{{ route('produtos.edit', $p->id) }}" class="inline-flex items-center justify-center w-9 h-9 bg-indigo-50 text-indigo-600 rounded-lg hover:bg-indigo-600 hover:text-white transition shadow-sm">
                            <i class="bx bx-edit-alt text-lg"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="p-24 text-center">
                        <i class="bx bx-check-double text-6xl text-green-400 mb-4 block"></i>
                        <p class="text-gray-500 font-medium">Tudo limpo! Nenhum produto vencido ou a vencer nos próximos 90 dias.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4 bg-gray-50 border-t">
            {!! $data->appends(request()->all())->links() !!}
        </div>
    </div>
</div>
@endsection