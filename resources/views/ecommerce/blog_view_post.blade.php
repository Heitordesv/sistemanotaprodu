@extends('ecommerce.default')

@section('content')
<section class="py-10 md:py-14 bg-gray-50/70 min-h-screen">
    <div class="container mx-auto px-4 max-w-7xl">
        <nav class="flex items-center gap-2 text-[10px] uppercase tracking-widest font-black text-gray-400 mb-6">
            <a href="{{$rota}}" class="hover:text-main">Início</a>
            <span>/</span>
            <a href="{{$rota}}/blog" class="hover:text-main">Blog</a>
            <span>/</span>
            <span class="truncate">{{$post->titulo}}</span>
        </nav>

        <div class="grid lg:grid-cols-[minmax(0,1fr)_280px] gap-7 lg:gap-10 items-start">
            <main class="min-w-0">
                <article class="bg-white border border-gray-100 rounded-3xl overflow-hidden shadow-sm">
                    <header class="p-5 md:p-8 lg:p-10 pb-6">
                        <div class="flex flex-wrap items-center gap-2 mb-4">
                            @if($post->categoria)
                                <span class="px-3 py-1.5 rounded-full bg-main/10 text-main text-[10px] uppercase tracking-widest font-black">{{$post->categoria->nome}}</span>
                            @endif
                            <span class="text-[10px] text-gray-400 font-black uppercase tracking-widest">{{\Carbon\Carbon::parse($post->created_at)->format('d/m/Y')}}</span>
                        </div>
                        <h1 class="text-3xl md:text-4xl lg:text-5xl font-black text-gray-950 tracking-[-0.04em] leading-[1.08]">{{$post->titulo}}</h1>
                    </header>

                    <div class="aspect-[16/9] bg-gray-100 overflow-hidden">
                        <img src="{{$post->image}}" alt="{{$post->titulo}}" class="w-full h-full object-cover">
                    </div>

                    <div class="p-5 md:p-8 lg:p-10">
                        <article class="prose prose-lg max-w-none text-gray-600 leading-8 prose-headings:text-gray-950 prose-headings:font-black prose-a:text-main prose-strong:text-gray-900 prose-img:rounded-2xl">
                            {!! $post->texto !!}
                        </article>

                        @if($post->tags != "")
                            <div class="flex flex-wrap gap-2 mt-8 pt-6 border-t border-gray-100">
                                @foreach(explode(',', $post->tags) as $tag)
                                    <span class="px-3 py-1.5 rounded-full bg-gray-50 border border-gray-100 text-[10px] text-gray-500 font-black">#{{trim($tag)}}</span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </article>

                @if($post->autor)
                <div class="bg-white border border-gray-100 rounded-3xl p-5 md:p-6 mt-5 flex flex-col sm:flex-row sm:items-center gap-4 justify-between">
                    <div class="flex items-center gap-4 min-w-0">
                        <div class="w-14 h-14 rounded-2xl bg-gray-50 overflow-hidden flex-shrink-0">
                            @if($post->autor->image)
                                <img src="{{$post->autor->image}}" alt="{{$post->autor->nome}}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-main"><i class="fa fa-user"></i></div>
                            @endif
                        </div>
                        <div class="min-w-0">
                            <span class="text-[10px] uppercase tracking-widest text-gray-400 font-black">Publicado por</span>
                            <strong class="block text-sm md:text-base text-gray-900 truncate">{{$post->autor->nome}}</strong>
                            @if($post->autor->tipo)<span class="block text-[10px] text-gray-500 mt-0.5">{{$post->autor->tipo}}</span>@endif
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        @if($default['config']['link_facebook'] != "")
                            <a target="_blank" rel="noopener noreferrer" href="{{$default['config']['link_facebook']}}" class="w-10 h-10 rounded-xl border border-gray-100 bg-gray-50 flex items-center justify-center text-gray-500 hover:text-main"><i class="fa fa-facebook"></i></a>
                        @endif
                        @if($default['config']['link_instagram'] != "")
                            <a target="_blank" rel="noopener noreferrer" href="{{$default['config']['link_instagram']}}" class="w-10 h-10 rounded-xl border border-gray-100 bg-gray-50 flex items-center justify-center text-gray-500 hover:text-main"><i class="fa fa-instagram"></i></a>
                        @endif
                    </div>
                </div>
                @endif
            </main>

            <aside class="lg:sticky lg:top-28 space-y-4">
                @if(sizeof($categoriasPost) > 0)
                <div class="bg-white border border-gray-100 rounded-2xl p-5">
                    <h2 class="text-xs uppercase tracking-widest font-black text-gray-900 mb-4">Categorias</h2>
                    <div class="space-y-1">
                        @foreach($categoriasPost as $c)
                            <a href="{{$rota}}/{{$c->id}}/posts" class="flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-bold text-gray-600 hover:bg-gray-50 hover:text-main">
                                <span>{{$c->nome}}</span><span class="text-[10px] bg-gray-100 rounded-full px-2 py-1">{{sizeof($c->posts)}}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
                @endif

                @if(sizeof($postsRecentes) > 0)
                <div class="bg-white border border-gray-100 rounded-2xl p-5">
                    <h2 class="text-xs uppercase tracking-widest font-black text-gray-900 mb-4">Posts recentes</h2>
                    <div class="space-y-4">
                        @foreach($postsRecentes as $p)
                            <a href="{{$rota}}/{{$p->id}}/verPost" class="group flex gap-3">
                                <div class="w-16 h-16 rounded-xl overflow-hidden bg-gray-50 flex-shrink-0"><img src="{{$p->image}}" alt="{{$p->titulo}}" class="w-full h-full object-cover"></div>
                                <div class="min-w-0"><strong class="block text-xs text-gray-800 group-hover:text-main line-clamp-2">{{$p->titulo}}</strong><span class="block text-[9px] text-gray-400 mt-1">{{\Carbon\Carbon::parse($p->created_at)->format('d/m/Y')}}</span></div>
                            </a>
                        @endforeach
                    </div>
                </div>
                @endif
            </aside>
        </div>
    </div>
</section>
@endsection