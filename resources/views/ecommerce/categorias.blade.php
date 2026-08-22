@extends('ecommerce.default')

@section('content')
<section class="py-10 md:py-14 bg-gray-50/70 min-h-screen">
    <div class="container mx-auto px-4 max-w-7xl">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-5 mb-8 md:mb-10">
            <div>
                <nav class="flex items-center gap-2 text-[10px] uppercase tracking-widest font-black text-gray-400 mb-3"><a href="{{$rota}}" class="hover:text-main">Início</a><span>/</span><span>Categorias</span></nav>
                <span class="text-[10px] uppercase tracking-[0.18em] font-black text-main">Explore a loja</span>
                <h1 class="text-3xl md:text-4xl font-black tracking-[-0.03em] text-gray-950 mt-2">Todas as categorias</h1>
                <p class="text-sm text-gray-500 mt-2 max-w-2xl">Encontre rapidamente o tipo de produto que você procura.</p>
            </div>
            <a href="{{$rota}}" class="inline-flex items-center gap-2 text-xs font-black text-gray-600 hover:text-main"><i class="fa fa-arrow-left"></i> Voltar para a loja</a>
        </div>

        @if(sizeof($categorias) > 0)
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 md:gap-5">
            @foreach($categorias as $c)
            <a href="{{$rota}}/{{$c->id}}/categorias" class="group bg-white border border-gray-100 rounded-2xl md:rounded-3xl p-3 md:p-4 no-underline hover:border-main hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="aspect-square rounded-xl md:rounded-2xl bg-gray-50 overflow-hidden flex items-center justify-center">
                    <img src="{{$c->img}}" alt="{{$c->nome}}" class="w-full h-full object-contain p-3 md:p-5 transition-transform duration-300 group-hover:scale-105">
                </div>
                <div class="pt-4 px-1 pb-1">
                    <h2 class="text-sm md:text-base font-black text-gray-900 group-hover:text-main transition-colors line-clamp-2">{{$c->nome}}</h2>
                    <span class="inline-flex items-center gap-2 mt-2 text-[10px] md:text-xs font-black text-gray-400 group-hover:text-main">Ver produtos <i class="fa fa-arrow-right"></i></span>
                </div>
            </a>
            @endforeach
        </div>
        @else
        <div class="bg-white border border-dashed border-gray-200 rounded-3xl py-20 text-center">
            <i class="fa fa-th-large text-4xl text-gray-300 mb-4"></i>
            <h2 class="text-xl font-black text-gray-800">Nenhuma categoria disponível</h2>
            <p class="text-sm text-gray-500 mt-2">Os produtos da loja aparecerão aqui assim que forem organizados.</p>
        </div>
        @endif
    </div>
</section>
@endsection