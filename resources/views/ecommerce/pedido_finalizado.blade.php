@extends('ecommerce.default')

@section('content')
<section class="py-12 bg-[#fbfbfd] min-h-screen font-sans antialiased">
    <div class="container mx-auto px-4 max-w-6xl">
        
        {{-- Inputs Ocultos --}}
        <input type="hidden" value="{{$pedido->transacao_id}}" id="transacao_id">
        <input type="hidden" value="{{$pedido->status_pagamento}}" id="status">

        {{-- MENSAGEM DE STATUS --}}
        <div class="mb-10">
            @if($pedido->status != 2)
                <div class="bg-blue-50 border border-blue-100 rounded-[2rem] p-8 flex items-center gap-6 shadow-sm">
                    <div class="bg-blue-500 text-white p-4 rounded-2xl shadow-lg shadow-blue-200">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-black text-blue-900 tracking-tight">Pedido Realizado com Sucesso!</h2>
                        <p class="text-blue-700/70 font-medium">
                            @if($pedido->forma_pagamento == "Boleto")
                                Aguardamos o pagamento do seu boleto para prosseguir.
                            @elseif($pedido->forma_pagamento == "Pix")
                                Use o QR Code ou o código "Copia e Cola" abaixo para pagar.
                            @else
                                Obrigado por sua compra! Estamos processando seu pedido.
                            @endif
                        </p>
                    </div>
                </div>
            @else
                <div class="bg-green-50 border border-green-100 rounded-[2rem] p-8 flex items-center gap-6 shadow-sm">
                    <div class="bg-green-500 text-white p-4 rounded-2xl shadow-lg shadow-green-200">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-black text-green-900 tracking-tight">Pagamento Confirmado!</h2>
                        <p class="text-green-700/70 font-medium text-sm">Seu PIX foi aprovado. Obrigado pela confiança!</p>
                    </div>
                </div>
            @endif
        </div>

        {{-- ÁREA DO PIX --}}
        @if($pedido->forma_pagamento == 'Pix' && $pedido->status != 2)
        <div class="mb-12 max-w-2xl mx-auto bg-white rounded-[3rem] p-8 md:p-12 shadow-xl shadow-gray-200/50 border border-gray-100 text-center">
            <span class="text-[10px] font-black text-main uppercase tracking-[0.3em] mb-6 block">Pagamento Via PIX</span>
            
            <div class="bg-gray-50 p-6 rounded-[2rem] inline-block mb-8 border border-gray-100">
                <img class="w-64 h-64 object-contain mx-auto mix-blend-multiply" src="data:image/jpeg;base64,{{$pedido->qr_code_base64}}"/>
            </div>

            <div class="space-y-4">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Código Copia e Cola</p>
                <div class="relative group">
                    <input type="text" readonly value="{{$pedido->qr_code}}" id="qrcode_input" 
                           class="w-full bg-gray-50 border-2 border-dashed border-gray-200 text-gray-600 font-mono text-xs py-4 px-6 rounded-2xl focus:outline-none focus:border-main transition-colors">
                    <button onclick="copy()" class="absolute right-3 top-1/2 -translate-y-1/2 bg-gray-900 text-white p-3 rounded-xl hover:bg-main transition-all active:scale-95 shadow-lg">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
                    </button>
                </div>
                <p class="text-[10px] text-gray-400 font-medium">O status do seu pagamento será atualizado automaticamente nesta página.</p>
            </div>
        </div>
        @endif

        <h3 class="text-2xl font-black text-gray-900 tracking-tight mb-8 italic">Detalhes do Pedido</h3>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            {{-- TABELA DE PRODUTOS --}}
            <div class="lg:col-span-8 bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-gray-50/50 border-b border-gray-100">
                                <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Produto</th>
                                <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Preço</th>
                                <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Qtd</th>
                                <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($pedido->itens as $i)
                            <tr class="group hover:bg-gray-50/30 transition-colors">
                                <td class="px-8 py-6 flex items-center gap-4">
                                    <div class="w-16 h-16 rounded-xl bg-gray-100 overflow-hidden flex-shrink-0 border border-gray-100">
                                        <img class="w-full h-full object-cover" src="/ecommerce/produtos/{{$i->produto->galeria[0]->img}}">
                                    </div>
                                    <span class="font-bold text-gray-900 group-hover:text-main transition-colors">{{$i->produto->produto->nome}}</span>
                                </td>
                                <td class="px-8 py-6 text-sm font-bold text-gray-500 italic">R$ {{number_format($i->produto->valor, 2, ',', '.')}}</td>
                                <td class="px-8 py-6 text-center font-black text-gray-900">x{{$i->quantidade}}</td>
                                <td class="px-8 py-6 text-sm font-black text-gray-900 tracking-tighter italic">
                                    R$ {{number_format($i->quantidade * $i->produto->valor, 2, ',', '.')}}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- RESUMO E ENDEREÇO --}}
            <div class="lg:col-span-4 space-y-8">
                {{-- Endereço --}}
                <div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-gray-100">
                    <h5 class="text-[11px] font-black text-gray-400 uppercase tracking-[0.2em] mb-6">Endereço de Entrega</h5>
                    <div class="space-y-4">
                        <div class="flex gap-3">
                            <div class="w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                            </div>
                            <div class="text-sm">
                                <p class="text-gray-900 font-bold leading-tight">{{ $pedido->endereco->rua }}, {{ $pedido->endereco->numero }}</p>
                                <p class="text-gray-500 font-medium">{{ $pedido->endereco->bairro }} - {{ $pedido->endereco->cidade }}/{{ $pedido->endereco->uf }}</p>
                                <p class="text-gray-400 text-[10px] mt-1 tracking-widest">{{ $pedido->endereco->cep }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Totais --}}
                <div class="bg-gray-900 rounded-[2.5rem] p-8 shadow-xl shadow-gray-900/10 text-white">
                    <h5 class="text-[11px] font-black text-gray-500 uppercase tracking-[0.2em] mb-6">Resumo Financeiro</h5>
                    <ul class="space-y-4 mb-8">
                        <li class="flex justify-between text-sm font-medium">
                            <span class="text-gray-400 tracking-wide">Subtotal</span>
                            <span class="font-bold tracking-tighter italic">R$ {{ number_format($pedido->somaItens(), 2, ',', '.') }}</span>
                        </li>
                        <li class="flex justify-between text-sm font-medium">
                            <span class="text-gray-400 tracking-wide">Frete</span>
                            <span class="font-bold tracking-tighter italic">R$ {{ number_format($pedido->valor_frete, 2, ',', '.') }}</span>
                        </li>
                        <li class="pt-4 border-t border-white/10 flex justify-between items-end">
                            <span class="text-[10px] font-black uppercase tracking-widest text-main">Total Pago</span>
                            <span class="text-3xl font-black italic tracking-tighter text-white">R$ {{ number_format($pedido->valor_total, 2, ',', '.') }}</span>
                        </li>
                    </ul>

                    @if($pedido->link_boleto != "")
                    <a target="_blank" href="{{$pedido->link_boleto}}" 
                       class="flex items-center justify-center gap-3 w-full bg-white text-gray-900 py-4 rounded-2xl font-black text-[11px] uppercase tracking-widest hover:bg-main hover:text-white transition-all transform active:scale-95 shadow-lg">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                        Imprimir Boleto
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>

@section('javascript')
<script type="text/javascript">
    function copy() {
        const inputTest = document.querySelector("#qrcode_input");
        inputTest.select();
        document.execCommand('copy');
        
        // Substituído Swal por uma notificação mais simples ou manter o Swal se ele estiver no default
        if (typeof swal === "function") {
            swal("Sucesso", "Código PIX copiado com sucesso!", "success");
        } else {
            alert("Código PIX copiado!");
        }
    }

    var prot = window.location.protocol;
    var host = window.location.host;
    let path = prot + "//" + host;

    // Monitoramento de status automático
    if($('#status').val() != "approved"){
        setInterval(() => {
            let transacao_id = $('#transacao_id').val();
            if(transacao_id){
                $.get(path + '/ecommercePay/consulta/' + transacao_id)
                .done((success) => {
                    if(success == "approved"){
                        location.reload();
                    }
                })
                .fail((err) => console.log(err));
            }
        }, 3000); // Aumentado para 3s para evitar sobrecarga
    }
</script>
@endsection
@endsection