@extends('default.layout',['title' => 'Clientes'])
@section('content')
<script src="https://cdn.tailwindcss.com"></script>
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="page-content p-6 lg:p-10 bg-gray-50 min-h-screen">

    <div class="flex flex-col sm:flex-row justify-between items-center mb-8 border-b border-blue-200 pb-4">
        <h3 class="text-3xl font-extrabold text-blue-700 flex items-center gap-3">
            <i class="bx bxs-group text-4xl"></i> Clientes
            <span class="rounded-full bg-blue-100 px-3 py-1 text-sm font-semibold text-blue-700">{{ $data->total() }} cadastrados</span>
        </h3>
        <div class="flex gap-3 mt-4 sm:mt-0">
            <a href="{{ route('clientes.import') }}" class="px-5 py-2 bg-yellow-500 hover:bg-yellow-600 text-white font-medium rounded-lg shadow-md transition duration-300 transform hover:scale-[1.02] flex items-center gap-2">
                <i class="bx bx-cloud-upload"></i> Importar
            </a>
            <a href="{{ route('clientes.create') }}" class="px-5 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg shadow-md transition duration-300 transform hover:scale-[1.02] flex items-center gap-2">
                <i class="bx bx-user-plus"></i> Novo Cliente
            </a>
        </div>
    </div>

    {!! Form::open()->fill(request()->all())->get() !!}
    <div class="flex flex-col md:flex-row gap-4 mb-8 bg-white p-6 rounded-xl shadow-lg items-end border border-gray-100">
        <div class="flex-1 w-full">
            {!! Form::text('razao_social', 'Nome/Razão Social')->attrs(['class'=>'w-full px-4 py-2 border border-gray-300 rounded-lg focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition duration-150', 'placeholder' => 'Buscar por nome...']) !!}
        </div>
        <div class="flex-1 w-full">
            {!! Form::text('cpf_cnpj', 'CPF/CNPJ')->attrs(['class' => 'cpf_cnpj w-full px-4 py-2 border border-gray-300 rounded-lg focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition duration-150', 'placeholder' => 'Buscar por CPF ou CNPJ...'])->type('tel') !!}
        </div>
        <div class="flex gap-3 w-full md:w-auto">
            <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-md transition duration-300 w-1/2 md:w-auto flex items-center justify-center gap-1">
                <i class="bx bx-search-alt"></i> Buscar
            </button>
            <a href="{{ route('clientes.index') }}" class="px-6 py-2 bg-gray-500 hover:bg-gray-600 text-white font-semibold rounded-lg shadow-md transition duration-300 w-1/2 md:w-auto flex items-center justify-center gap-1">
                <i class="bx bx-reset"></i> Limpar
            </a>
        </div>
    </div>
    {!! Form::close() !!}

    <div class="bg-white rounded-xl shadow-lg overflow-x-auto border border-gray-100">
        
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-blue-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-bold text-blue-700 uppercase tracking-wider w-1/3">Cliente</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-blue-700 uppercase tracking-wider hidden sm:table-cell">CPF/CNPJ</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-blue-700 uppercase tracking-wider hidden md:table-cell">E-mail</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-blue-700 uppercase tracking-wider hidden lg:table-cell">Localização</th>
                    <th class="px-6 py-3 text-center text-xs font-bold text-blue-700 uppercase tracking-wider">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($data as $item)
                <tr class="hover:bg-gray-50 transition duration-150">
                    
                    {{-- Coluna Cliente (Razão Social e Nome Fantasia) --}}
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-full object-cover border-2 border-blue-100 bg-blue-500 text-white flex items-center justify-center text-sm font-bold flex-shrink-0 mr-3">
                                {{ substr($item->razao_social, 0, 1) }}
                            </div>
                            <div>
                                <div class="text-sm font-semibold text-gray-900 truncate max-w-[200px]" title="{{ $item->razao_social }}">{{ $item->razao_social }}</div>
                                <div class="text-xs text-gray-500 truncate max-w-[200px]">{{ $item->nome_fantasia }}</div>
                                <div class="mt-1 text-xs text-gray-500 sm:hidden">
                                    <span class="block">{{ $item->celular ?: ($item->telefone ?: 'Sem telefone') }}</span>
                                    <span class="block max-w-[180px] truncate">{{ $item->email ?: 'Sem e-mail' }}</span>
                                </div>
                            </div>
                        </div>
                    </td>

                    {{-- Coluna CPF/CNPJ --}}
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 hidden sm:table-cell">
                        {{ $item->cpf_cnpj }}
                    </td>

                    {{-- Coluna E-mail --}}
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600 hidden md:table-cell">
                        <span class="truncate max-w-[150px] inline-block">{{ $item->email ?? 'N/A' }}</span>
                    </td>

                    {{-- Coluna Localização --}}
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 hidden lg:table-cell">
                        {{ $item->cidade->info ?? 'Não Informado' }}
                    </td>

                    {{-- Coluna Ações --}}
                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                        <div class="flex justify-center items-center gap-2">
                            
                            {{-- Botão Detalhes (Modal) --}}
                            <button
                                onclick="openModal(this)"
                                class="p-2 bg-blue-500 text-white hover:bg-blue-600 rounded-lg transition duration-150 shadow-md"
                                data-id="{{ $item->id }}"
                                data-razao="{{ $item->razao_social }}"
                                data-nome="{{ $item->nome_fantasia }}"
                                data-cpf="{{ $item->cpf_cnpj }}"
                                data-email="{{ $item->email }}"
                                data-celular="{{ $item->celular }}"
                                data-telefone="{{ $item->telefone }}"
                                data-endereco="{{ $item->rua }}, {{ $item->numero }} - {{ $item->bairro }}"
                                data-cep="{{ $item->cep }}"
                                data-cidade="{{ $item->cidade->info ?? '' }}"
                                data-pais="{{ $item->getPais() }}"
                                data-complemento="{{ $item->complemento }}"
                                data-observacao="{{ $item->observacao }}"
                                data-aniversario="{{ $item->data_aniversario ? \Carbon\Carbon::parse($item->data_aniversario)->format('d/m/Y') : '' }}"
                                data-limite="R$ {{ number_format((float) $item->limite_venda, 2, ',', '.') }}"
                                data-edit-url="{{ route('clientes.edit', $item) }}"
                                aria-label="Ver detalhes de {{ $item->razao_social }}"
                                title="Ver detalhes do cliente"
                            >
                                <i class="bx bx-search-alt-2 text-lg"></i>
                            </button>

                            {{-- Botão Editar --}}
                            <a href="{{ route('clientes.edit', $item) }}" class="p-2 bg-yellow-400 text-white hover:bg-yellow-500 rounded-lg transition duration-150" title="Editar Cliente">
                                <i class="bx bx-edit text-lg"></i>
                            </a>
                            
                            {{-- Botão Excluir --}}
                            <form action="{{ route('clientes.destroy', $item) }}" method="POST" id="form-{{ $item->id }}" class="inline">
                                @csrf
                                @method('delete')
                                <button type="button" class="p-2 bg-red-500 text-white hover:bg-red-600 rounded-lg transition duration-150 btn-delete" data-id="{{ $item->id }}" title="Excluir Cliente">
                                    <i class="bx bx-trash text-lg"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center bg-blue-50 text-blue-700">
                        <i class="bx bx-info-circle text-xl me-2"></i>
                        <strong>Nenhum cliente encontrado</strong> com os filtros aplicados.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-10">
        {!! $data->appends(request()->all())->links() !!}
    </div>
</div>

<div id="clienteModal" class="fixed inset-0 bg-black bg-opacity-60 hidden items-center justify-center z-50 p-4 transition duration-300 opacity-0" aria-modal="true" aria-labelledby="modalTitle" role="dialog">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl transform transition duration-300 scale-95 opacity-0" id="modalContent">
        <div class="p-6 overflow-y-auto max-h-[90vh]">
            
            <div class="flex justify-between items-center border-b pb-4 mb-4">
                <h3 class="text-2xl font-bold text-blue-600 flex items-center gap-2" id="modalTitle">
                    <i class="bx bx-user-detail"></i> Detalhes do Cliente
                </h3>
                <button onclick="closeModal()" id="modalClose" aria-label="Fechar detalhes" class="text-gray-400 hover:text-gray-600 transition duration-150 p-1 rounded-full bg-gray-100 hover:bg-gray-200">
                    <i class='bx bx-x text-2xl'></i>
                </button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-gray-700">
                    <div class="p-3 bg-blue-50 rounded-lg">
                    <p class="text-xs font-semibold text-blue-600 uppercase">Razão Social</p>
                    <p class="font-medium text-gray-900" id="modalRazao"></p>
                </div>
                <div class="p-3 bg-blue-50 rounded-lg">
                    <p class="text-xs font-semibold text-blue-600 uppercase">Nome Fantasia</p>
                    <p class="font-medium text-gray-900" id="modalNome"></p>
                </div>
                <div class="p-3 bg-blue-50 rounded-lg">
                    <p class="text-xs font-semibold text-blue-600 uppercase">CPF/CNPJ</p>
                    <p class="font-medium text-gray-900" id="modalCPF"></p>
                </div>
                <div class="p-3 bg-blue-50 rounded-lg">
                    <p class="text-xs font-semibold text-blue-600 uppercase">Email</p>
                        <p class="font-medium text-blue-700 break-all" id="modalEmail"></p>
                    </div>
                    <div class="p-3 bg-blue-50 rounded-lg">
                        <p class="text-xs font-semibold text-blue-600 uppercase">Aniversário</p>
                        <p class="font-medium text-gray-900" id="modalAniversario"></p>
                    </div>
                    <div class="p-3 bg-blue-50 rounded-lg">
                        <p class="text-xs font-semibold text-blue-600 uppercase">Limite de venda</p>
                        <p class="font-medium text-gray-900" id="modalLimite"></p>
                    </div>
            </div>

            <div class="mt-5 border-t pt-4">
                <h4 class="text-lg font-semibold text-gray-800 mb-3 flex items-center gap-2"><i class='bx bx-phone-call text-blue-500'></i> Contato</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-gray-700">
                    <div class="p-3 bg-gray-100 rounded-lg">
                        <p class="text-xs font-semibold text-gray-600 uppercase">Celular</p>
                        <p class="font-medium text-gray-900" id="modalCelular"></p>
                    </div>
                    <div class="p-3 bg-gray-100 rounded-lg">
                        <p class="text-xs font-semibold text-gray-600 uppercase">Telefone</p>
                        <p class="font-medium text-gray-900" id="modalTelefone"></p>
                    </div>
                </div>
            </div>

            <div class="mt-5 border-t pt-4">
                <h4 class="text-lg font-semibold text-gray-800 mb-3 flex items-center gap-2"><i class='bx bx-home-alt text-blue-500'></i> Endereço</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-gray-700">
                    <div class="p-3 bg-gray-100 rounded-lg md:col-span-2">
                        <p class="text-xs font-semibold text-gray-600 uppercase">Rua, Número, Bairro</p>
                        <p class="font-medium text-gray-900" id="modalEndereco"></p>
                    </div>
                    <div class="p-3 bg-gray-100 rounded-lg md:col-span-2">
                        <p class="text-xs font-semibold text-gray-600 uppercase">Complemento</p>
                        <p class="font-medium text-gray-900" id="modalComplemento"></p>
                    </div>
                    <div class="p-3 bg-gray-100 rounded-lg">
                        <p class="text-xs font-semibold text-gray-600 uppercase">CEP</p>
                        <p class="font-medium text-gray-900" id="modalCEP"></p>
                    </div>
                    <div class="p-3 bg-gray-100 rounded-lg">
                        <p class="text-xs font-semibold text-gray-600 uppercase">Cidade</p>
                        <p class="font-medium text-gray-900" id="modalCidade"></p>
                    </div>
                    <div class="p-3 bg-gray-100 rounded-lg">
                        <p class="text-xs font-semibold text-gray-600 uppercase">País</p>
                        <p class="font-medium text-gray-900" id="modalPais"></p>
                    </div>
                </div>
            </div>
            <div class="mt-5 border-t pt-4">
                <h4 class="mb-2 text-lg font-semibold text-gray-800"><i class="bx bx-note text-blue-500"></i> Observações</h4>
                <p class="whitespace-pre-wrap rounded-lg bg-amber-50 p-3 text-gray-700" id="modalObservacao"></p>
            </div>
            <div class="mt-5 flex justify-end border-t pt-4">
                <a href="#" id="modalEditar" class="rounded-lg bg-yellow-400 px-5 py-2 font-semibold text-white hover:bg-yellow-500"><i class="bx bx-edit mr-1"></i> Editar cliente</a>
            </div>
        </div>
    </div>
</div>


<script>
    const modal = document.getElementById('clienteModal');
    const modalContent = document.getElementById('modalContent');
    const modalTitle = document.getElementById('modalTitle');
    const modalClose = document.getElementById('modalClose');
    const modalEditar = document.getElementById('modalEditar');
    let modalTrigger = null;

    function openModal(button) {
        if (typeof button === 'object') {
            modalTrigger = button;
            const dataMap = {
                'razao': 'modalRazao',
                'nome': 'modalNome',
                'cpf': 'modalCPF',
                'email': 'modalEmail',
                'celular': 'modalCelular',
                'telefone': 'modalTelefone',
                'endereco': 'modalEndereco',
                'cep': 'modalCEP',
                'cidade': 'modalCidade',
                'pais': 'modalPais',
                'complemento': 'modalComplemento',
                'observacao': 'modalObservacao',
                'aniversario': 'modalAniversario',
                'limite': 'modalLimite',
            };

            for (const dataAttr in dataMap) {
                document.getElementById(dataMap[dataAttr]).innerText = button.dataset[dataAttr] || 'Não Informado';
            }

            // Atualiza o título do modal
            const razaoSocial = button.dataset.razao || 'Cliente';
            modalTitle.replaceChildren();
            const icon = document.createElement('i');
            icon.className = 'bx bx-user-detail';
            modalTitle.append(icon, document.createTextNode(` Detalhes de: ${razaoSocial.split(' ')[0]}`));
            modalEditar.href = button.dataset.editUrl;
        }

        // 1. Torna o modal visível
        modal.classList.remove('hidden');
        
        // 2. Aplica as classes de transição após um pequeno delay
        setTimeout(() => {
            modal.classList.add('opacity-100');
            modalContent.classList.remove('scale-95', 'opacity-0');
            modalContent.classList.add('scale-100', 'opacity-100');
        }, 10);
        document.body.style.overflow = 'hidden';
        modalClose.focus();
    }

    function closeModal() {
        // 1. Inicia a transição de fechamento
        modal.classList.remove('opacity-100');
        modalContent.classList.remove('scale-100', 'opacity-100');
        modalContent.classList.add('scale-95', 'opacity-0');
        
        // 2. Esconde o modal após a transição
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300); 
        document.body.style.overflow = '';
        if (modalTrigger) modalTrigger.focus();
    }

    // Listener para ações de fechamento (clique externo e ESC)

    
    modal.addEventListener('click', function (event) {
        if (event.target === modal) closeModal();
    });
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && !modal.classList.contains('hidden')) closeModal();
    });

         document.addEventListener('DOMContentLoaded', function () {
    // Seleciona todos os botões de delete
    const buttons = document.querySelectorAll('.btn-delete');

    // Remove event listener antigo se houver (evita duplicidade)
    buttons.forEach(btn => btn.replaceWith(btn.cloneNode(true)));

    // Re-seleciona após o clone
    document.querySelectorAll('.btn-delete').forEach(function(button){
            button.addEventListener('click', function(){
                var clienteId = this.getAttribute('data-id');
                Swal.fire({
                    title: 'Tem certeza?',
                text: "Ao excluir este cliente, TODOS os registros relacionados (vendas, remessas, ordens de serviço, contas a receber, orçamentos, pré-vendas, agendamentos etc.) serão apagados.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sim, excluir!',
                    cancelButtonText: 'Cancelar',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('form-' + clienteId).submit();
                    }
                });
            });
        });
    });
</script>

@endsection