@extends('default.layout', ['title' => 'Grupo Clientes'])
<script src="https://cdn.tailwindcss.com"></script>
@section('content')
<div class="p-4 sm:p-8 bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto">
        
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Grupos de Clientes</h1>
                <p class="text-sm text-gray-500">Gerencie as categorias para organização da sua base de clientes.</p>
            </div>
            <a href="{{ route('gruposCliente.create')}}" 
               class="inline-flex items-center px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition-all shadow-sm shadow-indigo-200 gap-2">
                <i class="bx bx-plus text-lg"></i> Novo Grupo
            </a>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 mb-6">
            {!! Form::open()->fill(request()->all())->get() !!}
            <div class="flex flex-wrap items-end gap-4">
                <div class="flex-1 min-w-[280px]">
                    {{-- Usando inline para evitar que o label do pacote quebre o layout --}}
                    {!! Form::text('nome', 'Pesquisar por nome')
                        ->attrs(['class' => 'mt-1 block w-full rounded-lg border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2.5']) 
                    !!}
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="px-6 py-2.5 bg-gray-900 hover:bg-black text-white text-sm font-medium rounded-lg transition-colors flex items-center gap-2">
                        <i class="bx bx-search text-lg"></i> Pesquisar
                    </button>
                    <a href="{{ route('gruposCliente.index') }}" 
                       class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-600 text-sm font-medium rounded-lg transition-colors flex items-center gap-2">
                        <i class="bx bx-eraser text-lg"></i> Limpar
                    </a>
                </div>
            </div>
            {!! Form::close() !!}
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest">Nome do Grupo</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($data as $item)
                        <tr class="hover:bg-indigo-50/30 transition-colors group">
                            <td class="px-6 py-4">
                                <span class="text-sm font-semibold text-gray-700 group-hover:text-indigo-600 transition-colors">
                                    {{ $item->nome }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex justify-end gap-2">
                                    <form action="{{ route('gruposCliente.destroy', $item->id) }}" method="post" id="form-{{$item->id}}" class="flex gap-2">
                                        @method('delete')
                                        @csrf
                                        
                                        <a href="{{ route('gruposCliente.edit', $item) }}" 
                                           class="p-2 text-amber-500 bg-amber-50 hover:bg-amber-100 rounded-lg transition-colors"
                                           title="Editar">
                                            <i class="bx bx-edit text-xl"></i>
                                        </a>

                                        <button type="button" 
                                                class="btn-delete p-2 text-red-500 bg-red-50 hover:bg-red-100 rounded-lg transition-colors"
                                                title="Excluir">
                                            <i class="bx bx-trash text-xl"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="2" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <i class="bx bx-folder-open text-5xl text-gray-200 mb-2"></i>
                                    <p class="text-gray-400 text-sm italic">Nenhum registro encontrado para esta busca.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($data->hasPages())
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                <div class="flex justify-center">
                    {!! $data->appends(request()->all())->links() !!}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection