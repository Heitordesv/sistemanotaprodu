@extends('default.layout', ['title' => 'Detalhes do Pedido'])

@section('content')
<!-- Scripts de Estilo e Ícones -->
<script src="https://cdn.tailwindcss.com"></script>
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap');
    body { font-family: 'Inter', sans-serif; }
    .swal2-popup { font-family: 'Inter', sans-serif !important; border-radius: 1.5rem !important; }
</style>

@php
    $formaPagamento = trim(strtoupper($pedido->forma_pagamento ?? ''));
    $isPix = ($formaPagamento == 'PIX');
    $porcentagemPix = $config_ecomercres->desconto_padrao_pix ?? 0;
    
    $somaBrutaProdutos = 0;
    foreach($pedido->itens as $i) {
        $somaBrutaProdutos += (($i->produto->valor ?? 0) * $i->quantidade);
    }

    $valorDescontoExibicao = $pedido->desconto;
    if($isPix && $porcentagemPix > 0 && ($pedido->desconto <= 0)){
        $valorDescontoExibicao = $somaBrutaProdutos * ($porcentagemPix / 100);
    }
@endphp

<div class="page-content bg-slate-50 min-h-screen pb-20">
    <div class="container mx-auto py-8 px-4 max-w-7xl">
        
        {{-- Header de Ações --}}
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-8 gap-6">
            <div>
                <nav class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-slate-400 mb-3">
                    <a href="{{ route('pedidosEcommerce.index') }}" class="hover:text-indigo-600 transition">Pedidos</a>
                    <i class='bx bx-chevron-right text-base'></i>
                    <span class="text-slate-600">ID #{{ $pedido->id }}</span>
                </nav>
                <div class="flex items-center gap-4">
                    <h1 class="text-4xl font-black text-slate-800 tracking-tight">Pedido #{{ $pedido->id }}</h1>
                    @php
                        $statusMap = [
                            'novo'       => ['bg-blue-100 text-blue-700', 'Novo'],
                            'aprovado'   => ['bg-emerald-100 text-emerald-700', 'Aprovado'],
                            'cancelado'  => ['bg-rose-100 text-rose-700', 'Cancelado'],
                            'finalizado' => ['bg-slate-200 text-slate-700', 'Finalizado'],
                        ];
                        $statusKey = strtolower($pedido->status ?? 'novo');
                        $style = $statusMap[$statusKey] ?? ['bg-amber-100 text-amber-700', $pedido->status];
                    @endphp
                    <span class="px-3 py-1 text-xs font-bold uppercase rounded-full shadow-sm {{ $style[0] }}">
                        {{ $style[1] }}
                    </span>
                </div>
            </div>
            
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('pedidosEcommerce.index') }}" class="flex items-center gap-2 px-5 py-2.5 bg-white border border-slate-200 text-slate-600 rounded-xl hover:bg-slate-50 transition shadow-sm font-bold text-sm">
                    <i class="bx bx-arrow-back text-lg"></i> Voltar
                </a>
                
                <a href="{{ route('pedidosEcommerce.declaracao', $pedido->id) }}" target="_blank" class="flex items-center gap-2 px-5 py-2.5 bg-amber-500 text-white rounded-xl hover:bg-amber-600 transition shadow-lg shadow-amber-100 font-bold text-sm">
                    <i class="bx bx-file text-lg"></i> Declaração
                </a>

                <a href="{{ route('pedidosEcommerce.danfe', $pedido->id) }}" target="_blank" class="flex items-center gap-2 px-5 py-2.5 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition shadow-lg shadow-indigo-100 font-bold text-sm">
                    <i class="bx bx-printer text-lg"></i> Imprimir DANFE
                </a>

                <button type="button" onclick="abrirEtiqueta({{ $pedido->id }})" class="flex items-center gap-2 px-5 py-2.5 bg-slate-800 text-white rounded-xl hover:bg-slate-900 transition shadow-lg font-bold text-sm">
                    <i class="bx bx-tag-alt text-lg"></i> Ver Etiqueta
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            {{-- Coluna Principal --}}
            <div class="lg:col-span-2 space-y-6">
                
                {{-- Infos Cliente e Entrega --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Card Cliente -->
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 relative overflow-hidden group">
                        <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition">
                            <i class="bx bx-user-circle text-6xl text-indigo-600"></i>
                        </div>
                        <h3 class="text-xs font-bold text-indigo-500 uppercase mb-4 tracking-widest">Dados do Cliente</h3>
                        <p class="text-xl font-bold text-slate-800">{{ $pedido->cliente->nome ?? 'N/D' }} {{ $pedido->cliente->sobre_nome ?? '' }}</p>
                        <p class="text-sm font-medium text-slate-400">{{ $pedido->cliente->cpf ?? 'CPF não informado' }}</p>
                        
                        <div class="mt-6 space-y-2">
                            <div class="flex items-center gap-3 text-slate-600 hover:text-indigo-600 transition">
                                <div class="w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center"><i class="bx bx-phone"></i></div>
                                <span class="text-sm font-medium">{{ $pedido->cliente->telefone ?? 'S/ Telefone' }}</span>
                            </div>
                            <div class="flex items-center gap-3 text-slate-600 hover:text-indigo-600 transition">
                                <div class="w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center"><i class="bx bx-envelope"></i></div>
                                <span class="text-sm font-medium">{{ $pedido->cliente->email ?? 'S/ Email' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Card Entrega -->
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                        <h3 class="text-xs font-bold text-indigo-500 uppercase mb-4 tracking-widest">Endereço de Entrega</h3>
                        <div class="flex gap-4">
                            <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                                <i class="bx bx-map-alt text-xl"></i>
                            </div>
                            <div>
                                <p class="text-sm text-slate-700 leading-relaxed font-semibold">
                                    {{ $pedido->endereco->rua ?? 'Retirada' }}, {{ $pedido->endereco->numero ?? '' }}<br>
                                    {{ $pedido->endereco->bairro ?? '' }} • {{ $pedido->endereco->cep ?? '' }}<br>
                                    <span class="text-slate-400">{{ $pedido->endereco->cidade ?? '' }} / {{ $pedido->endereco->uf ?? '' }}</span>
                                </p>
                                @if($pedido->tipo_frete)
                                    <span class="inline-flex items-center gap-1 mt-3 px-2 py-1 bg-slate-100 text-[10px] font-black text-slate-500 rounded-md uppercase">
                                        <i class='bx bx-package'></i> {{ $pedido->tipo_frete }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Tabela de Itens --}}
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-50 flex justify-between items-center">
                        <h3 class="font-bold text-slate-800 flex items-center gap-2">
                            Produtos <span class="ml-2 px-2 py-0.5 bg-slate-100 text-slate-500 text-xs rounded-full font-bold">{{ count($pedido->itens) }}</span>
                        </h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="bg-slate-50/50 text-slate-400 text-[11px] uppercase font-bold tracking-widest border-b border-slate-100">
                                    <th class="px-6 py-4">Produto</th>
                                    <th class="px-6 py-4 text-center">Qtd</th>
                                    <th class="px-6 py-4 text-right">Unitário Bruto</th>
                                    <th class="px-6 py-4 text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @foreach($pedido->itens as $item)
                                @php
                                    $valorBruto = $item->produto->valor ?? 0;
                                    $subtotalItem = $valorBruto * $item->quantidade;
                                @endphp
                                <tr class="group hover:bg-slate-50/50 transition">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-4">
                                            <div class="w-12 h-12 rounded-lg bg-slate-100 border border-slate-200 overflow-hidden flex-shrink-0">
                                                @if($item->produto && count($item->produto->galeria) > 0)
                                                    <img src="{{ $item->produto->galeria[0]->img }}" class="w-full h-full object-cover">
                                                @else
                                                    <div class="w-full h-full flex items-center justify-center text-slate-300"><i class="bx bx-image text-2xl"></i></div>
                                                @endif
                                            </div>
                                            <div>
                                                <p class="text-sm font-bold text-slate-700 group-hover:text-indigo-600 transition">
                                                    {{ $item->produto->produto->nome ?? 'Produto sem nome' }}
                                                </p>
                                                <p class="text-[10px] text-slate-400 font-medium">SKU: {{ $item->produto->id ?? 'N/A' }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center text-sm font-bold text-slate-600">{{ $item->quantidade }}</td>
                                    <td class="px-6 py-4 text-right text-sm font-semibold text-slate-600">R$ {{ number_format($valorBruto, 2, ',', '.') }}</td>
                                    <td class="px-6 py-4 text-right text-sm font-bold text-slate-800">
                                        R$ {{ number_format($subtotalItem, 2, ',', '.') }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Observações --}}
                @if($pedido->observacao)
                <div class="bg-amber-50/50 p-6 rounded-2xl border border-amber-100 flex gap-4">
                    <div class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center flex-shrink-0 shadow-sm">
                        <i class="bx bx-comment-detail text-amber-600 text-xl"></i>
                    </div>
                    <div>
                        <h4 class="text-xs font-black text-amber-700/50 uppercase tracking-widest">Observações do Cliente</h4>
                        <p class="text-sm text-amber-900 mt-1 font-medium leading-relaxed italic">"{{$pedido->observacao}}"</p>
                    </div>
                </div>
                @endif
            </div>

            {{-- Coluna Lateral (Financeiro) --}}
            <div class="space-y-6">
                <div class="bg-white p-8 rounded-3xl shadow-xl shadow-slate-200/50 border border-slate-100 sticky top-6">
                    <h3 class="font-bold text-slate-800 mb-6 flex items-center justify-between">
                        Pagamento
                        <i class='bx bx-shield-quarter text-indigo-200 text-2xl'></i>
                    </h3>
                    
                    <div class="space-y-4">
                        <div class="flex justify-between text-sm font-medium text-slate-400">
                            <span>Subtotal (Produtos)</span>
                            <span class="text-slate-700 font-bold">R$ {{ number_format($somaBrutaProdutos, 2, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-sm font-medium text-slate-400">
                            <span>Frete</span>
                            <span class="text-emerald-500 font-bold">+ R$ {{ number_format($pedido->valor_frete ?? 0, 2, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-sm font-medium text-slate-400">
                            <span>
                                Descontos 
                                @if($isPix && $porcentagemPix > 0 && $pedido->desconto <= 0)
                                    <small class="text-indigo-400 font-bold">({{ $porcentagemPix }}% PIX)</small>
                                @endif
                            </span>
                            <span class="text-rose-500 font-bold">- R$ {{ number_format($valorDescontoExibicao, 2, ',', '.') }}</span>
                        </div>
                        
                        <div class="pt-6 border-t border-slate-100 mt-6 text-center">
                            <p class="text-[10px] font-black text-slate-300 uppercase tracking-[0.2em] mb-1">Total a Pagar</p>
                            <div class="text-4xl font-black text-slate-800 tracking-tighter">
                                <span class="text-lg font-bold text-indigo-600 mr-1">R$</span>{{ number_format($pedido->valor_total, 2, ',', '.') }}
                            </div>
                        </div>

                        <div class="mt-6 p-4 bg-slate-50 rounded-2xl border border-slate-100">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Método</p>
                                    <p class="text-sm font-black text-slate-700">{{ $formaPagamento ?: 'NÃO INFORMADO' }}</p>
                                </div>
                                <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center shadow-sm text-indigo-600">
                                    @if($isPix)
                                        <i class="bx bx-qr-scan text-2xl"></i>
                                    @else
                                        <i class="bx bx-credit-card text-2xl"></i>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 pt-6 border-t border-slate-50 space-y-3">
                        <div class="flex justify-between text-[10px] font-bold uppercase tracking-widest text-slate-400">
                            <span>Criado em:</span>
                            <span class="text-slate-600">{{ $pedido->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="flex justify-between text-[10px] font-bold uppercase tracking-widest text-slate-400">
                            <span>Atualizado:</span>
                            <span class="text-slate-600">{{ $pedido->updated_at->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>
                </div>

                <div class="bg-slate-900 p-6 rounded-2xl text-white relative overflow-hidden shadow-lg shadow-slate-900/20">
                    <div class="relative z-10">
                        <p class="text-indigo-400 text-[10px] font-black uppercase tracking-widest mb-3 italic">Loja Emissora</p>
                        <p class="text-base font-bold">{{ $pedido->empresa->nome ?? $pedido->empresa->razao_social }}</p>
                        <p class="text-xs text-slate-400 mt-1">{{ $pedido->empresa->cpf_cnpj }}</p>
                    </div>
                    <i class='bx bxs-store absolute -bottom-4 -right-4 text-8xl text-white opacity-5'></i>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal de Etiqueta --}}
<div class="modal fade" id="modalEtiqueta" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document" style="max-width: 850px;">
        <div class="modal-content border-0 rounded-3xl overflow-hidden shadow-2xl">
            <div class="modal-header bg-white border-b border-slate-100 p-6 flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center">
                        <i class="bx bxs-tag-alt text-2xl"></i>
                    </div>
                    <h5 class="font-black text-slate-800 uppercase tracking-tight">Etiqueta Correios</h5>
                </div>
                <button type="button" class="text-slate-400 hover:text-rose-500 transition text-3xl" data-dismiss="modal">
                    <i class="bx bx-x"></i>
                </button>
            </div>
            <div class="modal-body p-0 bg-slate-50" style="height: 650px;">
                <div id="loaderEtiqueta" class="flex flex-col items-center justify-center h-full">
                    <div class="relative w-16 h-16">
                        <div class="absolute top-0 left-0 w-full h-full border-4 border-indigo-100 rounded-full"></div>
                        <div class="absolute top-0 left-0 w-full h-full border-4 border-indigo-600 rounded-full border-t-transparent animate-spin"></div>
                    </div>
                    <p class="mt-6 font-bold text-slate-700">Gerando Etiqueta Oficial...</p>
                    <p class="text-xs text-slate-400">Isso pode levar alguns instantes.</p>
                </div>
                <iframe id="frameEtiqueta" src="" frameborder="0" class="w-full h-full hidden"></iframe>
            </div>
        </div>
    </div>
</div>

<script>
function abrirEtiqueta(id) {
    const modal = $('#modalEtiqueta');
    const iframe = document.getElementById('frameEtiqueta');
    const loader = document.getElementById('loaderEtiqueta');

    // Limpeza de estado
    iframe.style.display = 'none';
    loader.style.display = 'flex';
    iframe.src = '';
    
    const url = "{{ route('pedidosEcommerce.etiqueta', ':id') }}".replace(':id', id);

    // Validação da URL antes de exibir
    fetch(url)
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => { throw err; });
            }
            return response.blob();
        })
        .then(blob => {
            const pdfUrl = URL.createObjectURL(blob);
            iframe.src = pdfUrl;
            modal.modal('show');

            iframe.onload = function() {
                loader.style.display = 'none';
                iframe.style.display = 'block';
            };
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: error.title || 'Falha na API',
                text: error.message || 'Verifique se os dados de endereço do cliente e da empresa estão corretos.',
                confirmButtonColor: '#4f46e5',
                footer: error.tecnico ? `<small class="text-slate-400">Dica: ${error.tecnico.msgs[0]}</small>` : ''
            });
        });
}
</script>
@endsection