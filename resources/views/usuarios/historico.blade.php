



@extends('default.layout', ['title' => 'Histórico de Usuário'])
@section('content')

{{-- Inclusão de estilos e scripts (CDN para Tailwind e Boxicons) --}}
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

<div class="py-8 px-4 sm:px-6 lg:px-8 bg-gray-50 min-h-screen">
	<div class="max-w-4xl mx-auto bg-white rounded-xl shadow-2xl border-t-4 border-indigo-600 overflow-hidden">
		
		<div class="p-6 sm:p-8">
			
			{{-- Cabeçalho / Botão Voltar --}}
			<div class="flex justify-end mb-6 pb-4 border-b border-gray-100">
				<a href="{{ route('usuarios.index')}}" type="button" 
					class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-full text-gray-700 bg-white hover:bg-gray-50 hover:text-indigo-600 transition duration-150">
					<i class="bx bx-arrow-back text-lg mr-1"></i> Voltar
				</a>
			</div>
			
			{{-- Informações do Usuário (Texto fixo CORRIGIDO: 'Histórico') --}}
			<div class="mb-6 p-4 bg-indigo-50 rounded-lg border border-indigo-100">
				<h5 class="text-xl font-semibold text-gray-800 flex items-center">
                    <i class='bx bx-history text-indigo-600 mr-2 text-2xl'></i>
					**Hist贸rico** de: 
                    <strong class="text-indigo-700 ml-1">{{ $usuario->nome }}</strong>
                </h5>
				<p class="text-sm text-gray-600 mt-1">
                    Total de Registros: 
                    <span class="font-bold text-base text-indigo-600">{{ sizeof($acessos) }}</span>
                </p>
			</div>
            
            {{-- Tabela de Acessos --}}
			<div class="overflow-x-auto shadow-md rounded-lg border border-gray-100">
				<table class="min-w-full divide-y divide-gray-200">
					
                    {{-- Cabeçalho da Tabela (Texto fixo CORRIGIDO: 'Acesso') --}}
					<thead>
						<tr class="bg-indigo-600 text-white uppercase text-xs tracking-wider font-bold">
							<th class="px-6 py-3 text-left">Data do **Acesso**</th> 
							<th class="px-6 py-3 text-left"> IP</th>
						</tr>
					</thead>
                    
                    {{-- Corpo da Tabela (COMPLETO) --}}
					<tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($acessos as $item)
                            <tr class="{{ $loop->odd ? 'bg-gray-50' : 'bg-white' }} hover:bg-indigo-50 transition duration-150">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <i class='bx bx-calendar-check text-base mr-1 text-gray-400'></i> 
                                    {{ $item->created_at }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    <code class="bg-gray-200 text-gray-700 p-1 rounded text-xs font-mono">{{ $item->ip_address }}</code>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="px-6 py-10 text-center text-md text-gray-500 font-semibold">
                                    <i class='bx bx-info-circle text-4xl mr-2 block text-gray-300'></i>
                                    Nenhum registro de acesso encontrado.
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