@extends('default.layout', ['title' => 'Gestão de Comissões'])

@section('content')
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="p-6 bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto bg-white shadow-md rounded-lg overflow-hidden">
        <div class="p-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 border-b pb-4">
                <h4 class="text-xl font-bold text-gray-800 uppercase tracking-wider">Relatório de Comissões</h4>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white border-l-4 border-blue-500 shadow-sm rounded-r-lg p-4 flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Total Filtrado</p>
                        <h4 class="text-2xl font-bold text-blue-600">R$ {{ number_format($totalGeral ?? 0, 2, ',', '.') }}</h4>
                    </div>
                    <div class="p-3 bg-blue-100 rounded-full"><i class='bx bx-line-chart text-2xl text-blue-500'></i></div>
                </div>

                <div class="bg-white border-l-4 border-green-500 shadow-sm rounded-r-lg p-4 flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Total Pago</p>
                        <h4 class="text-2xl font-bold text-green-600">R$ {{ number_format($totalPago ?? 0, 2, ',', '.') }}</h4>
                    </div>
                    <div class="p-3 bg-green-100 rounded-full"><i class='bx bx-check-double text-2xl text-green-500'></i></div>
                </div>

                <div class="bg-white border-l-4 border-yellow-500 shadow-sm rounded-r-lg p-4 flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 font-medium">A Receber</p>
                        <h4 class="text-2xl font-bold text-yellow-600">R$ {{ number_format($totalPendente ?? 0, 2, ',', '.') }}</h4>
                    </div>
                    <div class="p-3 bg-yellow-100 rounded-full"><i class='bx bx-time-five text-2xl text-yellow-500'></i></div>
                </div>
            </div>

            <div class="bg-gray-100 p-4 rounded-lg mb-6">
                {!! Form::open()->fill(request()->all())->get() !!}
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                    <div class="md:col-span-9 grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Vendedor</label>
                            {!! Form::select('nome', null, ['' => 'Todos'] + $vendedor->pluck('nome', 'id')->all())->attrs(['class' => 'w-full rounded border-gray-300 py-2']) !!}
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Início</label>
                            {!! Form::date('data_inicial', null)->attrs(['class' => 'w-full rounded border-gray-300 py-2']) !!}
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Fim</label>
                            {!! Form::date('data_final', null)->attrs(['class' => 'w-full rounded border-gray-300 py-2']) !!}
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Status</label>
                            {!! Form::select('estado', null, ['' => 'Todos', '0' => 'Pendente', '1' => 'Pago'])->attrs(['class' => 'w-full rounded border-gray-300 py-2']) !!}
                        </div>
                    </div>
                    <div class="md:col-span-3 flex gap-2">
                        <button class="flex-1 bg-blue-600 text-white font-bold py-2 rounded shadow hover:bg-blue-700 transition" type="submit">Filtrar</button>
                        <button type="button" class="px-6 bg-green-600 text-white font-bold py-2 rounded shadow hover:bg-green-700 transition" onclick="confirmarPagamento()">Pagar Tudo</button>
                    </div>
                </div>
                {!! Form::close() !!}
            </div>

            <div class="overflow-x-auto border rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50 text-gray-500 text-xs font-bold uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Vendedor</th>
                            <th class="px-4 py-3 text-left text-blue-600">Comissão</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-left">Data</th>
                            <th class="px-4 py-3 text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 text-sm">
                        @forelse($comissoes as $c)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3 font-medium text-gray-700">{{ $c->funcionario->nome ?? 'N/D' }}</td>
                            <td class="px-4 py-3 font-bold text-blue-600">R$ {{ number_format($c->valor, 2, ',', '.') }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 text-[10px] font-black uppercase rounded-full {{ $c->status == 0 ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700' }}">
                                    {{ $c->status == 0 ? 'Pendente' : 'Pago' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-500">{{ $c->created_at->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 text-center">
                                <button type="button" 
                                    onclick="verDetalhes({{ json_encode([
                                        'vendedor' => $c->funcionario->nome ?? 'Não identificado',
                                        'valor_venda' => number_format($c->valodDaVenda->valor_total ?? 0, 2, ',', '.'),
                                        'comissao' => number_format($c->valor, 2, ',', '.'),
                                        'data' => $c->created_at->format('d/m/Y H:i'),
                                        'origem' => $c->tabela == 'vendas' ? 'Pedido (Vendas)' : 'PDV (Frente de Caixa)',
                                        'status' => $c->status == 0 ? 'PENDENTE' : 'PAGO'
                                    ]) }})"
                                    class="text-blue-600 hover:text-blue-800 bg-blue-50 p-2 rounded-full transition shadow-sm">
                                    <i class="bx bx-show text-xl"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="px-4 py-10 text-center text-gray-400">Nenhum registro.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    // Função para ver Detalhes Individuais
    function verDetalhes(dados) {
        Swal.fire({
            title: '<span class="text-gray-800">Detalhes da Comissão</span>',
            html: `
                <div class="text-left space-y-3 mt-4">
                    <div class="flex justify-between border-b pb-1">
                        <span class="text-gray-500 font-medium">Vendedor:</span>
                        <span class="text-gray-900 font-bold">${dados.vendedor}</span>
                    </div>
                    <div class="flex justify-between border-b pb-1">
                        <span class="text-gray-500 font-medium">Origem da Venda:</span>
                        <span class="text-blue-600 font-bold">${dados.origem}</span>
                    </div>
                    <div class="flex justify-between border-b pb-1">
                        <span class="text-gray-500 font-medium">Valor Total da Venda:</span>
                        <span class="text-gray-900 font-bold">R$ ${dados.valor_venda}</span>
                    </div>
                    <div class="flex justify-between border-b pb-1">
                        <span class="text-gray-500 font-medium">Data/Hora:</span>
                        <span class="text-gray-900 font-bold">${dados.data}</span>
                    </div>
                    <div class="flex justify-between border-b pb-1">
                        <span class="text-gray-500 font-medium">Status Atual:</span>
                        <span class="${dados.status === 'PAGO' ? 'text-green-600' : 'text-yellow-600'} font-black">${dados.status}</span>
                    </div>
                    <div class="flex justify-between items-center bg-blue-50 p-3 rounded-lg mt-4">
                        <span class="text-blue-800 font-bold text-lg">Comissão:</span>
                        <span class="text-blue-800 font-black text-2xl">R$ ${dados.comissao}</span>
                    </div>
                </div>
            `,
            showCloseButton: true,
            confirmButtonText: 'Fechar',
            confirmButtonColor: '#3b82f6',
        });
    }

    // Função de Pagamento Geral (Já ajustada antes)
    function confirmarPagamento() {
        Swal.fire({
            title: 'Baixar Comissões?',
            text: "Deseja pagar todos os registros pendentes do filtro atual?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#16a34a',
            confirmButtonText: 'Sim, pagar!',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                const urlParams = new URLSearchParams(window.location.search);
                window.location.href = "{{ route('funcionarios.comissao.pagar') }}?" + urlParams.toString();
            }
        });
    }
</script>
@endsection