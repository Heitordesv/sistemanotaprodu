@extends('ecommerce.default')

@section('content')

<!-- 1. Hero Slider Premium -->
<section class="relative bg-white overflow-hidden">
    <div class="latest-product__slider owl-carousel">
        @foreach($carrossel as $c)
        <div class="relative min-h-[500px] md:min-h-[650px] flex items-center">
            <div class="container mx-auto px-4 z-10">
                <div class="grid grid-cols-1 md:grid-cols-2 items-center gap-12">
                    <!-- Texto -->
                    <div class="order-2 md:order-1 text-center md:text-left space-y-6">
                        <span class="inline-block text-xs font-black tracking-[0.4em] uppercase text-main bg-main/10 px-4 py-2 rounded-full">
                            {{$c->titulo}}
                        </span>
                        <h2 class="text-4xl md:text-7xl font-black leading-[1.1] text-gray-900 tracking-tighter italic">
                            {!! str_replace(' ', '<br class="hidden md:block">', $c->descricao) !!}
                        </h2>
                        @if($c->nome_botao != "")
                        <div class="pt-6">
                            <a href="{{$c->link_acao}}" class="inline-flex items-center gap-4 bg-gray-900 text-white px-10 py-4 rounded-full text-sm font-black uppercase tracking-widest hover:bg-main transition-all duration-300 group shadow-xl">
                                {{$c->nome_botao}}
                                <i class="fa fa-arrow-right group-hover:translate-x-2 transition-transform"></i>
                            </a>
                        </div>
                        @endif
                    </div>
                    <!-- Imagem -->
                    <div class="order-1 md:order-2 flex justify-center">
                        <div class="relative w-full max-w-sm md:max-w-md aspect-square">
                            <div class="absolute inset-0 bg-main/5 rounded-[3rem] rotate-6 transform group-hover:rotate-0 transition-transform duration-700"></div>
                            <img src="{{$c->image}}" alt="" class="relative z-10 w-full h-full object-contain drop-shadow-2xl scale-110">
                        </div>
                    </div>
                </div>
            </div>
            <!-- Background Element -->
            <div class="absolute top-0 right-0 w-1/3 h-full bg-gray-50 -z-0 hidden md:block" style="clip-path: polygon(20% 0%, 100% 0%, 100% 100%, 0% 100%);"></div>
        </div>
        @endforeach
    </div>
</section>

<!-- 2. Carrossel de Categorias (Estilo Instagram/Moderno) -->
<section class="py-12 bg-white border-b border-gray-50">
    <div class="container mx-auto px-4">
        <div class="categories-slider owl-carousel overflow-visible">
            @foreach($default['categorias'] as $c)
            <div class="item px-2">
                <a href="{{$rota}}/{{$c->id}}/categorias" class="group flex flex-col items-center space-y-3">
                    <div class="relative w-20 h-20 md:w-28 md:h-28 rounded-full p-1 bg-gradient-to-tr from-gray-100 to-gray-50 group-hover:from-main group-hover:to-main transition-all duration-500 shadow-sm">
                        <div class="w-full h-full rounded-full border-2 border-white overflow-hidden bg-white">
                            <img src="{{$c->img}}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                        </div>
                    </div>
                    <span class="text-[10px] md:text-xs font-black uppercase tracking-widest text-gray-500 group-hover:text-main transition-colors text-center">
                        {{$c->nome}}
                    </span>
                </a>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- 3. Grid de Produtos (Feed Duplo Mobile) -->
@if(sizeof($categoriasEmDestaque) > 0)
<section class="py-16 md:py-24 bg-[#fbfbfd]">
    <div class="container mx-auto px-2 md:px-4">
        
        <div class="flex flex-col md:flex-row justify-between items-center mb-12 md:mb-20 space-y-8 md:space-y-0 text-center md:text-left">
            <div>
                <span class="text-[11px] font-black text-main uppercase tracking-[0.3em] block mb-2">Curadoria Especial</span>
                <h2 class="text-3xl md:text-5xl font-black text-gray-900 tracking-tighter italic uppercase">Destaques</h2>
            </div>
            
            <!-- Filtros Minimalistas (Scroll horizontal no mobile) -->
            <div class="flex bg-white p-1 rounded-full shadow-sm border border-gray-100 overflow-x-auto max-w-full no-scrollbar">
                <button class="whitespace-nowrap px-6 py-2.5 rounded-full text-[10px] font-black uppercase tracking-widest transition-all bg-black text-white active-filter" data-filter="*">Todos</button>
                @foreach($categoriasEmDestaque as $c)
                <button class="whitespace-nowrap px-6 py-2.5 rounded-full text-[10px] font-black uppercase tracking-widest text-gray-400 hover:text-black transition-all" data-filter=".{{str_replace(' ', '', $c->nome)}}">
                    {{$c->nome}}
                </button>
                @endforeach
            </div>
        </div>

        <!-- Grid: 2 Colunas Mobile / 4 Colunas Desktop -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 md:gap-8 featured__filter">
            @foreach($produtosEmDestaque as $p)
            @if(sizeof($p->galeria) > 0)
            <div class="mix {{str_replace(' ', '', $p->categoria->nome)}}">
                <div class="group relative bg-white rounded-[2.5rem] p-2 md:p-4 border border-gray-100 shadow-sm hover:shadow-[0_30px_60px_-15px_rgba(0,0,0,0.1)] transition-all duration-500 flex flex-col h-full transform hover:-translate-y-2">
                    
                    <!-- Imagem do Produto -->
                    <div class="relative aspect-[4/5] overflow-hidden rounded-[2rem] bg-gray-50 mb-4 md:mb-6">
                        <a href="{{$rota}}/{{$p->id}}/verProduto" class="block w-full h-full">
                            <img src="{{$p->img}}" class="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110">
                        </a>
                        
                        <!-- Wishlist -->
                        <a href="{{$rota}}/{{$p->id}}/curtirProduto" class="absolute top-4 right-4 w-9 h-9 bg-white/90 backdrop-blur rounded-full flex items-center justify-center text-gray-400 hover:text-red-500 transition-all shadow-sm z-10">
                            <i class="fa fa-heart @if($p->curtido) text-red-500 @endif"></i>
                        </a>

                        <!-- Badge Opcional -->
                        <div class="absolute top-4 left-4">
                            <span class="bg-black/90 text-white text-[8px] font-black px-3 py-1.5 rounded-full uppercase tracking-widest">Novo</span>
                        </div>
                    </div>

                    <!-- Info do Produto -->
                    <div class="flex flex-col flex-grow px-2 pb-4">
                        <h3 class="font-bold text-gray-900 text-sm md:text-lg leading-tight mb-2 line-clamp-2">
                            <a href="{{$rota}}/{{$p->id}}/verProduto" class="hover:text-main transition-colors">{{$p->produto->nome}}</a>
                        </h3>
                        
                        <div class="mt-auto">
                            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-1">A partir de</p>
                            <span class="text-lg md:text-2xl font-black text-gray-900 tracking-tighter italic">R$ {{number_format($p->valor, 2, ',', '.')}}</span>
                            
                            <!-- Pagamento -->
                            <div class="flex flex-wrap gap-1.5 mt-3 pt-3 border-t border-gray-50">
                                @if($p->valor_pix > 0)
                                    <span class="text-[8px] md:text-[9px] font-black uppercase tracking-tighter text-green-600 bg-green-50 px-2 py-0.5 rounded-full">Pix R$ {{number_format($p->valor_pix,2,',', '.')}}</span>
                                @endif
                                @if($p->valor_cartao > 0)
                                    <span class="text-[8px] md:text-[9px] font-black uppercase tracking-tighter text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full">12x R$ {{number_format($p->valor_cartao/12,2,',', '.')}}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            @endforeach
        </div>
    </div>
</section>
@endif

@endsection

@section('javascript')
<script type="text/javascript">
    $(document).ready(function(){
        // Inicializa o Carrossel de Categorias
        $(".categories-slider").owlCarousel({
            loop: false,
            margin: 10,
            nav: false,
            dots: false,
            responsive: {
                0: { items: 4 },
                600: { items: 6 },
                1000: { items: 8 }
            }
        });

        // Lógica de Filtro dos Produtos
        $('.flex button[data-filter]').on('click', function() {
            const filterValue = $(this).attr('data-filter');
            
            // Estilo dos botões ativos
            $(this).parent().find('button').removeClass('bg-black text-white').addClass('text-gray-400');
            $(this).addClass('bg-black text-white').removeClass('text-gray-400');

            if(filterValue === '*') {
                $('.featured__filter > div').fadeIn(400);
            } else {
                $('.featured__filter > div').hide();
                $('.featured__filter > div' + filterValue).fadeIn(400);
            }
        });
    });
</script>

<style>
    /* Esconder scrollbar dos filtros no mobile */
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
@endsection