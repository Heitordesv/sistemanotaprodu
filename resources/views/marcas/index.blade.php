@extends('default.layout',['title' => 'Marcas'])
<script src="https://cdn.tailwindcss.com"></script>

@section('content')
<div class="p-6 bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto">
        
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">Gestão de Marcas</h1>
                <p class="text-sm text-gray-500">Organize os fabricantes e marcas dos seus produtos.</p>
            </div>
            <div>
                <a href="{{ route('marcas.create')}}" 
                   class="inline-flex items-center px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition-all shadow-sm shadow-indigo-100 gap-2">
                    <i class="bx bx-plus text-lg"></i> Nova Marca
                </a>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-6">
            {!! Form::open()->fill(request()->all())->get() !!}
            <div class="flex flex-wrap items-end gap-4">
                <div class="w-full md:w-80">
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-1.5 ml-1">Pesquisar por nome</label>
                    {!! Form::text('nome')->attrs([
                        'class' => 'w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2.5',
                        'placeholder' => 'Ex: Samsung, Nike...'
                    ]) !!}
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="px-6 py-2.5 bg-gray-900 hover:bg-black text-white text-sm font-medium rounded-xl transition-all flex items-center gap-2">
                        <i class="bx bx-search text-lg"></i> Filtrar
                    </button>
                    <a href="{{ route('marcas.index') }}" class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-600 text-sm font-medium rounded-xl transition-all flex items-center gap-2">
                        <i class="bx bx-eraser text-lg"></i> Limpar
                    </a>
                </div>
            </div>
            {!! Form::close() !!}
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/50 border-b border-gray-100">
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest">Nome da Marca</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($data as $item)
                        <tr class="hover:bg-indigo-50/20 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-xs">
                                        {{ substr($item->nome, 0, 1) }}
                                    </div>
                                    <span class="text-sm font-semibold text-gray-700 group-hover:text-indigo-600 transition-colors">
                                        {{ $item->nome }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <form action="{{ route('marcas.destroy', $item->id) }}" method="post" id="form-{{$item->id}}" class="inline-flex gap-2 justify-end">
                                    @method('delete')
                                    @csrf
                                    <a href="{{ route('marcas.edit', $item) }}" 
                                       class="p-2.5 text-amber-500 bg-amber-50 hover:bg-amber-100 rounded-xl transition-colors"
                                       title="Editar">
                                        <i class="bx bx-edit text-xl"></i>
                                    </a>

                                    <button type="button" 
                                            class="btn-delete p-2.5 text-red-500 bg-red-50 hover:bg-red-100 rounded-xl transition-colors"
                                            title="Excluir">
                                        <i class="bx bx-trash text-xl"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="2" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="p-4 bg-gray-50 rounded-full mb-4">
                                        <i class="bx bx-tag-alt text-4xl text-gray-300"></i>
                                    </div>
                                    <p class="text-gray-400 text-sm font-medium">Nenhuma marca cadastrada ou encontrada.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($data->hasPages())
            <div class="px-6 py-5 bg-gray-50/50 border-t border-gray-100">
                {!! $data->appends(request()->all())->links() !!}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection