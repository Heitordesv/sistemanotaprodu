@extends('default.layout', ['title' => 'Categorias'])

<script src="https://cdn.tailwindcss.com"></script>

@section('content')
<div class="p-6 bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto">

        {{-- HEADER --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-extrabold text-gray-900">Categorias</h1>
                <p class="text-sm text-gray-500">Gerencie categorias e descontos de produtos</p>
            </div>

            <a href="{{ route('categorias.create') }}"
               class="inline-flex items-center px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-xl">
                <i class="bx bx-plus mr-2"></i> Nova Categoria
            </a>
        </div>

        {{-- TABELA --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

            <div class="overflow-x-auto">
                <table class="w-full text-left">

                    <thead>
                    <tr class="bg-gray-50 border-b">
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Categoria</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Desconto</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Produtos</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase text-right">Ações</th>
                    </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">

                    @forelse($data as $item)

                        <tr class="hover:bg-indigo-50/30 transition">

                            {{-- NOME --}}
                            <td class="px-6 py-4 font-semibold text-gray-700">
                                {{ $item->nome }}
                            </td>

                            {{-- DESCONTO --}}
                            <td class="px-6 py-4">
                                @if($item->desconto_ativo && $item->desconto > 0)
                                    <span class="px-3 py-1 text-xs font-bold text-green-700 bg-green-100 rounded-full">
                                        {{ $item->desconto }}%
                                    </span>
                                @else
                                    <span class="px-3 py-1 text-xs font-bold text-gray-500 bg-gray-100 rounded-full">
                                        Sem desconto
                                    </span>
                                @endif
                            </td>

                            {{-- STATUS --}}
                            <td class="px-6 py-4">
                                @if($item->desconto_ativo)
                                    <span class="text-green-600 font-semibold text-sm">Ativo</span>
                                @else
                                    <span class="text-red-500 font-semibold text-sm">Inativo</span>
                                @endif
                            </td>

                            {{-- PRODUTOS --}}
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ $item->produtos_count ?? 0 }}
                            </td>

                            {{-- AÇÕES --}}
                            <td class="px-6 py-4">
                                <div class="flex justify-end gap-2">

                                    <a href="{{ route('subcategoria.index', [$item->id]) }}"
                                       class="p-2 text-blue-500 bg-blue-50 hover:bg-blue-100 rounded-lg">
                                        <i class="bx bx-menu"></i>
                                    </a>

                                    <a href="{{ route('categorias.edit', $item) }}"
                                       class="p-2 text-yellow-500 bg-yellow-50 hover:bg-yellow-100 rounded-lg">
                                        <i class="bx bx-edit"></i>
                                    </a>

                                    <form action="{{ route('categorias.destroy', $item->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                                class="p-2 text-red-500 bg-red-50 hover:bg-red-100 rounded-lg">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </form>

                                </div>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="5" class="text-center py-12 text-gray-400">
                                Nenhuma categoria encontrada
                            </td>
                        </tr>

                    @endforelse

                    </tbody>

                </table>
            </div>

        </div>
    </div>
</div>
@endsection