@extends('default.layout', ['title' => 'Ordem de serviço'])
<script src="https://cdn.tailwindcss.com"></script>
@section('content')
<div class="p-6 bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto bg-white rounded-lg shadow-md overflow-hidden">
        
        <div class="p-6 border-b border-gray-200">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h5 class="text-lg font-semibold text-gray-700">
                        Status: <span class="px-3 py-1 rounded-full text-sm font-bold bg-blue-100 text-blue-700 uppercase">{{ $ordem->estado }}</span>
                    </h5>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <a href="{{ route('ordemServico.alterarEstado', $ordem->id) }}" class="inline-flex items-center px-4 py-2 bg-cyan-600 hover:bg-cyan-700 text-white text-sm font-medium rounded-md transition">
                            <i class="bx bx-refresh mr-2"></i> Mudar detalhes da OS
                        </a>
                        <a href="{{ route('ordemServico.imprimir', $ordem->id) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-md transition">
                            <i class="bx bx-printer mr-2"></i> Imprimir
                        </a>
                        <a href="{{ route('ordemServico.finalizar', $ordem->id) }}" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-md transition">
                            <i class="bx bx-check mr-2"></i> Finalizar OS
                        </a>
                        <form action="{{ route('ordemServico.destroy', $ordem->id) }}" method="POST" class="inline" onsubmit="return confirm('Excluir definitivamente esta ordem e todos os itens vinculados?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-md transition">
                                <i class="bx bx-trash mr-2"></i> Excluir OS
                            </button>
                        </form>
                    </div>
                </div>
                <div class="text-right text-gray-800">
                    <h5 class="text-xl">Total: <strong class="text-green-600">R$ {{ __moeda($ordem->valor) }}</strong></h5>
                    <p class="text-sm text-gray-500">Responsável: <span class="font-semibold">{{ $ordem->usuario->nome }}</span></p>
                </div>
            </div>
        </div>

        <div class="p-6 space-y-12">
            
            <section>
                {!! Form::open()->post()->route('ordemServico.storeServico') !!}
                <h6 class="text-lg font-bold text-gray-800 mb-4 flex items-center border-l-4 border-cyan-500 pl-2">Serviços</h6>
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end bg-gray-50 p-4 rounded-lg">
                    <input type="hidden" value="{{$ordem->id}}" name="ordem_servico_id">
                    <div class="md:col-span-4">
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Serviço</label>
                        {!! Form::select('servico_id', null, [null => 'Selecione'] + $servicos->pluck('nome', 'id')->all())->attrs(['class' => 'select2 w-full rounded-md border-gray-300 shadow-sm focus:ring-cyan-500'])->required() !!}
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Qtd</label>
                        {!! Form::tel('quantidade', null)->attrs(['class' => 'moeda w-full rounded-md border-gray-300 shadow-sm focus:ring-cyan-500'])->required() !!}
                    </div>
                    <div class="md:col-span-3">
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Valor Unitário</label>
                        {!! Form::tel('valor', null)->attrs(['class' => 'moeda w-full rounded-md border-gray-300 shadow-sm focus:ring-cyan-500'])->required() !!}
                    </div>
                    <div class="md:col-span-3">
                        <button type="submit" class="w-full bg-cyan-600 text-white px-4 py-2 rounded-md hover:bg-cyan-700 flex items-center justify-center transition font-bold">
                            <i class="bx bx-plus mr-1"></i> ADICIONAR
                        </button>
                    </div>
                    {!! Form::hidden('status', 'PENDENTE') !!}
                </div>
                {!! Form::close() !!}

                <div class="mt-4 overflow-x-auto">
                    <p class="text-sm text-gray-500 mb-2 font-medium">Registros: {{ count($ordem->servicos) }}</p>
                    <table class="min-w-full divide-y divide-gray-200 border border-gray-200 rounded-lg overflow-hidden">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase">Serviço</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase">Qtd</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase">Unitário</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase">Sub total</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase">Status</th>
                                <th class="px-4 py-3 text-center text-xs font-bold text-gray-600 uppercase">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($ordem->servicos as $item)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-3"><input readonly class="w-full bg-transparent border-none text-sm p-0 focus:ring-0" value="{{ $item->servico->nome }}"></td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $item->quantidade }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ __moeda($item->valor_unitario) }}</td>
                                <td class="px-4 py-3 text-sm font-bold text-gray-800">{{ __moeda($item->sub_total) }}</td>
                                <td class="px-4 py-3">
                                    @if($item->status)
                                        <span class="px-2 py-1 text-xs font-bold rounded bg-green-100 text-green-700">FINALIZADO</span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-bold rounded bg-yellow-100 text-yellow-700">PENDENTE</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex justify-center gap-1">
                                        <a href="{{ route('ordemServico.alterarStatusServico', $item->id) }}" class="p-1.5 bg-cyan-100 text-cyan-600 rounded hover:bg-cyan-200" title="Alterar Status"><i class="bx bx-check"></i></a>
                                        @if(!$item->status)
                                        <a href="{{ route('ordemServico.deleteServico', $item->id) }}" class="p-1.5 bg-red-100 text-red-600 rounded hover:bg-red-200"><i class="bx bx-trash"></i></a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

            <hr class="border-gray-200">

            <section>
                {!! Form::open()->post()->route('ordemServico.storeProduto') !!}
                <h6 class="text-lg font-bold text-gray-800 mb-4 flex items-center border-l-4 border-blue-500 pl-2">Produtos</h6>
                <div class="mb-4 rounded-lg border border-blue-200 bg-blue-50 p-4">
                    <label for="os-codigo-barras" class="block text-xs font-bold text-blue-800 uppercase mb-1">
                        <i class="bx bx-barcode-reader mr-1"></i> Leitor de código de barras
                    </label>
                    <div class="flex flex-col md:flex-row gap-2">
                        <input type="search" id="os-codigo-barras" autofocus class="flex-1 rounded-md border-blue-300 focus:border-blue-500 focus:ring-blue-500" autocomplete="off" placeholder="Leia ou digite o código e pressione Enter">
                        <button type="button" id="os-buscar-codigo-barras" class="bg-blue-700 text-white px-4 py-2 rounded-md hover:bg-blue-800 font-semibold">
                            <i class="bx bx-search mr-1"></i> Buscar código
                        </button>
                    </div>
                    <p id="os-barcode-feedback" class="text-sm mt-2 mb-0 text-blue-700" role="status" aria-live="polite">O leitor pode permanecer conectado: o produto será selecionado automaticamente.</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end bg-gray-50 p-4 rounded-lg">
                    <input type="hidden" value="{{$ordem->id}}" name="ordem_servico_id">
                    <div class="md:col-span-4">
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Produto por nome ou código</label>
                        {!! Form::select('produto_id', null)->attrs(['class' => 'produto_id w-full rounded-md border-gray-300 focus:ring-blue-500'])->required() !!}
                        <small class="text-gray-500">Digite pelo menos 2 caracteres do nome, referência ou código de barras.</small>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Qtd</label>
                        {!! Form::tel('quantidade', null)->attrs(['class' => 'qtd_produto w-full rounded-md border-gray-300 focus:ring-blue-500'])->required() !!}
                    </div>
                    <div class="md:col-span-3">
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Unitário</label>
                        {!! Form::tel('valor_unitario', null)->attrs(['class' => 'moeda valor_produto w-full rounded-md border-gray-300 focus:ring-blue-500'])->required() !!}
                    </div>
                    <div class="md:col-span-3">
                        <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition font-bold shadow-sm">
                            <i class="bx bx-plus mr-1"></i> ADICIONAR
                        </button>
                    </div>
                </div>
                {!! Form::close() !!}

                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 border border-gray-200 rounded-lg">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase">Produto</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase">Qtd</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase">Unitário</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase">Sub total</th>
                                <th class="px-4 py-3 text-center text-xs font-bold text-gray-600 uppercase">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($ordem->produtos as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm">{{ $item->produto->nome }}</td>
                                <td class="px-4 py-3 text-sm">{{ $item->quantidade }}</td>
                                <td class="px-4 py-3 text-sm">{{ __moeda($item->valor_unitario) }}</td>
                                <td class="px-4 py-3 text-sm font-bold">{{ __moeda($item->sub_total) }}</td>
                                <td class="px-4 py-3 text-center">
                                    <a href="{{ route('ordemServico.deleteProduto', $item->id) }}" class="p-1.5 bg-red-100 text-red-600 rounded hover:bg-red-200 inline-block"><i class="bx bx-trash"></i></a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

            <hr class="border-gray-200">

            <section>
                {!! Form::open()->post()->route('ordemServico.storeFuncionario') !!}
                <h6 class="text-lg font-bold text-gray-800 mb-4 flex items-center border-l-4 border-gray-700 pl-2">Funcionários</h6>
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end bg-gray-50 p-4 rounded-lg">
                    <input type="hidden" value="{{$ordem->id}}" name="ordem_servico_id">
                    <div class="md:col-span-5">
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Funcionário</label>
                        {!! Form::select('funcionario_id', null, [null => 'Selecione'] + $funcionarios->pluck('nome', 'id')->all())->attrs(['class' => 'select2 w-full rounded-md border-gray-300'])->required() !!}
                    </div>
                    <div class="md:col-span-4">
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Função</label>
                        {!! Form::text('funcao', null)->attrs(['class' => 'w-full rounded-md border-gray-300'])->required() !!}
                    </div>
                    <div class="md:col-span-3">
                        @if(!isset($not_submit))
                        <button type="submit" class="w-full bg-gray-800 text-white px-4 py-2 rounded-md hover:bg-black transition font-bold uppercase text-sm tracking-wider">
                            <i class="bx bx-plus mr-1"></i> Adicionar
                        </button>
                        @endif
                    </div>
                    {!! Form::hidden('celular', 'Celular') !!}
                </div>
                {!! Form::close() !!}

                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 border border-gray-200 rounded-lg shadow-sm">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase">Nome</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase">Função</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase">Telefone</th>
                                <th class="px-4 py-3 text-center text-xs font-bold text-gray-600 uppercase">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($ordem->funcionarios as $funcionario)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-3 text-sm text-gray-800">{{ $funcionario->funcionario->nome }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600 italic">{{ $funcionario->funcao }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $funcionario->funcionario->celular }}</td>
                                <td class="px-4 py-3 text-center">
                                    <a href="{{ route('ordemServico.deleteFuncionario', $funcionario->id) }}" class="p-1.5 bg-red-100 text-red-600 rounded hover:bg-red-200 inline-block">
                                        <i class="bx bx-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</div>

@section('js')
<script type="text/javascript" src="/js/ordem_servico.js"></script>
@endsection

@endsection