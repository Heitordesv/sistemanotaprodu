@extends('ecommerce.default')

@section('content')
<section class="py-10 md:py-16 bg-[#fbfbfd] min-h-screen">
    <div class="container mx-auto px-4 max-w-7xl">
        <div class="mb-10 md:mb-12 border-b border-gray-100 pb-8">
            <nav class="flex items-center gap-2 text-[10px] font-bold text-main uppercase tracking-widest mb-4" aria-label="Navegação estrutural">
                <a href="{{$rota}}" class="hover:text-gray-900 transition-colors">Home</a>
                <span class="text-gray-300">/</span>
                <span class="text-gray-400">Produtos</span>
            </nav>

            <h1 class="text-3xl md:text-5xl font-black text-gray-900 tracking-tight">
                @if(isset($categoria))
                    @if($categoria != null)
                        <span class="font-light text-gray-400">Categoria:</span> {{$categoria->nome}}
                    @else
                        <span class="font-light text-gray-400">Resultado para:</span> “{{$pesquisa}}”
                    @endif
                @else
                    <span class="font-light text-gray-400">Subcategoria:</span> {{$subcategoria->nome}}
                @endif
            </h1>
        </div>

        @if(sizeof($produtos) > 0)
        <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 md:gap-8">
            @foreach($produtos as $p)
                @if(sizeof($p->galeria) > 0)
                @php
                    $semEstoque = $p->controlar_estoque && (($p->estoque_disponivel ?? 0) <= 0);
                @endphp
                <article class="group bg-white rounded-2xl md:rounded-[2.5rem] border border-gray-100 overflow-hidden hover:shadow-[0_30px_60px_-15px_rgba(0,0,0,0.1)] transition-all duration-500 md:hover:-translate-y-2 flex flex-col h-full">
                    <div class="relative overflow-hidden aspect-[4/5] bg-gray-50">
                        <a href="{{$rota}}/{{$p->id}}/verProduto" class="block h-full w-full">
                            <img class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
                                 src="{{$p->galeria[0]->img}}"
                                 alt="{{$p->produto->nome}}">
                        </a>

                        @if($semEstoque)
                        <div class="absolute top-3 left-3 bg-red-600 text-white text-[9px] md:text-[10px] font-black px-3 py-1.5 rounded-full uppercase tracking-tight shadow-lg">
                            Sem estoque
                        </div>
                        @elseif($p->valor_pix > 0 && $default['config']->desconto_padrao_pix > 0)
                        <div class="absolute top-3 right-3 bg-main text-white text-[9px] md:text-[10px] font-black px-3 py-1.5 rounded-full uppercase tracking-tight shadow-lg">
                            -{{number_format($default['config']->desconto_padrao_pix)}}% PIX
                        </div>
                        @endif
                    </div>

                    <div class="p-4 md:p-8 flex flex-col flex-1">
                        <h2 class="mb-2 md:mb-3">
                            <a href="{{$rota}}/{{$p->id}}/verProduto" class="text-sm md:text-xl font-bold text-gray-900 hover:text-main transition-colors line-clamp-2 leading-tight">
                                {{$p->produto->nome}}
                            </a>
                        </h2>

                        <div class="hidden md:block text-gray-400 text-sm mb-6 line-clamp-2 font-medium leading-relaxed">
                            {{ trim(strip_tags($p->descricao)) }}
                        </div>

                        <div class="mt-auto space-y-2">
                            <div class="mb-3 md:mb-4">
                                <span class="text-xl md:text-3xl font-black text-gray-900 tracking-tighter italic">
                                    R$ {{ number_format($p->valor, 2, ',', '.')}}
                                </span>
                            </div>

                            @if(!$semEstoque)
                            <div class="bg-gray-50 rounded-xl md:rounded-2xl p-3 md:p-4 space-y-1.5 border border-gray-100/50">
                                @if($p->valor_pix > 0)
                                <div class="flex justify-between items-center gap-2">
                                    <span class="text-[9px] md:text-[10px] font-black text-gray-400 uppercase tracking-wider">PIX</span>
                                    <span class="text-[10px] md:text-xs font-bold text-green-600">R$ {{number_format($p->valor_pix,2,',', '.')}}</span>
                                </div>
                                @endif
                                @if($p->valor_cartao > 0)
                                <div class="flex justify-between items-center gap-2 border-t border-gray-200/50 pt-1.5">
                                    <span class="text-[9px] md:text-[10px] font-black text-gray-400 uppercase tracking-wider">Cartão</span>
                                    <span class="text-[10px] md:text-xs font-bold text-blue-600">R$ {{number_format($p->valor_cartao,2,',', '.')}}</span>
                                </div>
                                @endif
                            </div>
                            @else
                            <div class="bg-red-50 text-red-700 rounded-xl p-3 text-xs font-bold text-center border border-red-100">
                                Indisponível no momento
                            </div>
                            @endif

                            <a href="{{$rota}}/{{$p->id}}/verProduto"
                               class="w-full mt-4 md:mt-6 bg-gray-900 text-white text-center py-3 md:py-4 rounded-xl md:rounded-2xl font-black text-[9px] md:text-[11px] uppercase tracking-[0.15em] hover:bg-main transition-all flex items-center justify-center gap-2">
                                Ver produto
                            </a>
                        </div>
                    </div>
                </article>
                @endif
            @endforeach
        </div>
        @else
            <div class="py-24 md:py-32 text-center">
                <span class="text-6xl mb-6 block opacity-20 grayscale">📦</span>
                <h2 class="text-xl md:text-2xl font-bold text-gray-400">Nenhum produto encontrado.</h2>
                <a href="{{$rota}}" class="mt-8 inline-block text-main font-bold border-b-2 border-main pb-1 hover:text-gray-900 hover:border-gray-900 transition-all">Voltar para a loja</a>
            </div>
        @endif
    </div>
</section>
@endsection