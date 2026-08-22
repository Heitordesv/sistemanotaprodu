@extends('default.layout', ['title' => 'Apuração Mensal'])

@section('content')
<script src="https://cdn.tailwindcss.com"></script>
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="min-h-screen p-4 lg:p-8 bg-[#f8fafc] dark:bg-gray-900">
    <div class="max-w-7xl mx-auto space-y-6">
        
        {{-- Header Area --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-800 dark:text-white tracking-tight">
                    Apuração Mensal
                </h1>
                <p class="text-gray-500 dark:text-gray-400 mt-1">Gerencie e visualize os fechamentos de funcionários.</p>
            </div>
            
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('apuracao_mensal.relatorio.pdf', ['nome' => $nome ?? null, 'start_date' => $dt_inicio ?? null, 'end_date' => $dt_fim ?? null, 'empresa_id' => request()->empresa_id]) }}" 
                   target="_blank"
                   class="inline-flex items-center px-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 font-semibold rounded-xl shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200">
                    <i class='bx bxs-file-pdf mr-2 text-xl text-red-500'></i>
                    Relatório PDF
                </a>
                
                <a href="{{ route('apuracaoMensal.create')}}" 
                   class="inline-flex items-center px-5 py-2.5 bg-indigo-600 border border-transparent text-white font-semibold rounded-xl shadow-lg shadow-indigo-200 dark:shadow-none hover:bg-indigo-700 focus:ring-4 focus:ring-indigo-300 transition-all duration-200">
                    <i class='bx bx-plus-circle mr-2 text-xl'></i>
                    Nova Apuração
                </a>
            </div>
        </div>

        {{-- Filters Card --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <h2 class="text-sm font-semibold uppercase tracking-wider text-gray-400 mb-4 flex items-center">
                <i class='bx bx-filter-alt mr-2'></i> Filtros de Busca
            </h2>
            
            {!! Form::open()->fill(request()->all())->get() !!}
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                <div class="md:col-span-5">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1 ml-1">Nome do Funcionário</label>
                    {!! Form::text('nome')->placeholder('Buscar por nome...')->attrs(['class' => 'w-full rounded-xl border-gray-200 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-900 dark:border-gray-700 dark:text-white']) !!}
                </div>
                <div class="md:col-span-3">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1 ml-1">Início</label>
                    {!! Form::date('start_date')->attrs(['class' => 'w-full rounded-xl border-gray-200 dark:bg-gray-900 dark:border-gray-700 dark:text-white']) !!}
                </div>
                <div class="md:col-span-3">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1 ml-1">Fim</label>
                    {!! Form::date('end_date')->attrs(['class' => 'w-full rounded-xl border-gray-200 dark:bg-gray-900 dark:border-gray-700 dark:text-white']) !!}
                </div>
                <div class="md:col-span-1 flex items-end gap-2">
                    <button type="submit" class="w-full h-[42px] bg-gray-800 dark:bg-indigo-600 text-white rounded-xl hover:bg-gray-700 transition-colors flex items-center justify-center" title="Pesquisar">
                        <i class='bx bx-search-alt-2 text-xl'></i>
                    </button>
                    <a href="{{ route('apuracaoMensal.index') }}" class="w-full h-[42px] bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl hover:bg-gray-200 transition-colors flex items-center justify-center" title="Limpar Filtros">
                        <i class='bx bx-refresh text-xl'></i>
                    </a>
                </div>
            </div>
            {!! Form::close() !!}
        </div>

        {{-- Table Card --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto text-left">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700 text-gray-500 dark:text-gray-300 text-xs uppercase font-bold">
                            <th class="px-6 py-4">Funcionário</th>
                            <th class="px-6 py-4 text-center">Data Registro</th>
                            <th class="px-6 py-4 text-center">Mês/Ano</th>
                            <th class="px-6 py-4 text-center">Conta a Pagar</th>
                            <th class="px-6 py-4 text-right">Valor Final</th>
                            <th class="px-6 py-4 text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-700">
                        @php $somaTotal = 0; @endphp
                        @forelse($data as $item)
                        @php $somaTotal += $item->valor_final; @endphp
                        <tr class="hover:bg-indigo-50/30 dark:hover:bg-indigo-900/10 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold text-xs">
                                        {{ substr($item->funcionario->nome, 0, 1) }}
                                    </div>
                                    <span class="font-semibold text-gray-700 dark:text-gray-200">{{ $item->funcionario->nome }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center text-gray-500 dark:text-gray-400 text-sm">
                                {{ $item->created_at->format('d/m/Y') }}
                                <span class="block text-[10px] opacity-60">{{ $item->created_at->format('H:i') }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded text-xs font-medium text-gray-600 dark:text-gray-300">
                                    {{ strtoupper($item->mes) }}/{{ $item->ano }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($item->conta_pagar_id == 0)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400">
                                        <i class='bx bxs-circle mr-1 text-[8px]'></i> Pendente
                                    </span>
                                @else
                                    <div class="flex flex-col items-center gap-1">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400">
                                            <i class='bx bxs-check-circle mr-1'></i> Vinculada
                                        </span>
                                        <a href="{{ route('conta-pagar.edit', $item->conta_pagar_id) }}" class="text-[11px] text-indigo-500 hover:underline">#{{$item->conta_pagar_id}}</a>
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="text-lg font-bold text-gray-800 dark:text-white">{{ __moeda($item->valor_final) }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('apuracaoMensal.pdf', $item->id) }}" target="_blank" 
                                       class="p-2 text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg transition-colors" title="Ver Holerite">
                                        <i class='bx bxs-file-blank text-xl'></i>
                                    </a>
                                    
                                    <form action="{{ route('apuracaoMensal.destroy', $item->id) }}" method="post" id="form-{{$item->id}}">
                                        @method('delete')
                                        @csrf
                                        <button type="button" class="btn-delete p-2 text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-colors" data-id="{{$item->id}}" title="Excluir">
                                            <i class='bx bx-trash text-xl'></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                                <i class='bx bx-cloud-upload text-5xl mb-2'></i>
                                <p>Nenhuma apuração encontrada para os filtros selecionados.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    
                    {{-- RODAPÉ COM SOMA --}}
                    @if(count($data) > 0)
                    <tfoot class="bg-gray-50 dark:bg-gray-700/50 border-t-2 border-gray-200 dark:border-gray-600">
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-sm font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                                Total Acumulado (Nesta Página)
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="text-xl font-black text-indigo-600 dark:text-indigo-400">
                                    {{ __moeda($somaTotal) }}
                                </span>
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>

            @if($data->hasPages())
            <div class="px-6 py-4 bg-white dark:bg-gray-800 border-t border-gray-100 dark:border-gray-700">
                {{ $data->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', function(e) {
            const formId = this.getAttribute('data-id');
            const form = document.getElementById('form-' + formId);

            Swal.fire({
                title: 'Deseja excluir?',
                text: "Esta ação não poderá ser desfeita!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#4f46e5',
                cancelButtonColor: '#ef4444',
                confirmButtonText: 'Sim, excluir!',
                cancelButtonText: 'Cancelar',
                background: document.documentElement.classList.contains('dark') ? '#1f2937' : '#fff',
                color: document.documentElement.classList.contains('dark') ? '#fff' : '#000',
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
</script>
@endsection