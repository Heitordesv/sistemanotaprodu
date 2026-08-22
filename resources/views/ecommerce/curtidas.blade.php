@extends('ecommerce.default')
@section('content')
<section class="py-10 md:py-14 bg-gray-50/70 min-h-screen">
<div class="container mx-auto px-4 max-w-7xl">
<div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8"><div><nav class="flex items-center gap-2 text-[10px] uppercase tracking-widest font-black text-gray-400 mb-3"><a href="{{$rota}}" class="hover:text-main">Início</a><span>/</span><span>Favoritos</span></nav><h1 class="text-3xl md:text-4xl font-black text-gray-950 tracking-[-.03em]">Meus favoritos</h1><p class="text-sm text-gray-500 mt-2">Produtos salvos para você consultar depois.</p></div><div class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-xs font-black"><i class="fa fa-heart text-main"></i>{{sizeof($curtidas)}} item(ns)</div></div>
@if(sizeof($curtidas)>0)
<div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-3 md:gap-5">
@foreach($curtidas as $i)
@php $img=(isset($i->produto->galeria)&&count($i->produto->galeria)>0)?$i->produto->galeria[0]->img:null; @endphp
<article class="group bg-white border border-gray-100 rounded-2xl md:rounded-3xl overflow-hidden hover:shadow-xl hover:-translate-y-1 transition-all flex flex-col">
<a href="{{$rota}}/{{$i->produto->id}}/verProduto" class="aspect-square bg-gray-50 flex items-center justify-center overflow-hidden">@if($img)<img src="{{$img}}" alt="{{$i->produto->produto->nome}}" class="w-full h-full object-contain p-4 md:p-6 group-hover:scale-105 transition-transform">@else<i class="fa fa-image text-4xl text-gray-300"></i>@endif</a>
<div class="p-4 md:p-5 flex flex-col flex-1"><span class="text-[9px] uppercase tracking-widest font-black text-gray-400">Favoritado em {{\Carbon\Carbon::parse($i->created_at)->format('d/m/Y')}}</span><h2 class="text-sm md:text-base font-black text-gray-900 mt-2 line-clamp-2 min-h-[40px]"><a href="{{$rota}}/{{$i->produto->id}}/verProduto" class="hover:text-main">{{$i->produto->produto->nome}}</a></h2><div class="mt-auto pt-4"><div class="text-xl md:text-2xl font-black text-gray-950">R$ {{number_format($i->produto->valor,2,',','.')}}</div><a href="{{$rota}}/{{$i->produto->id}}/verProduto" class="mt-4 w-full inline-flex items-center justify-center gap-2 bg-main text-white rounded-xl py-3 text-[10px] md:text-xs uppercase tracking-wider font-black hover:opacity-90">Ver produto <i class="fa fa-arrow-right"></i></a></div></div>
</article>
@endforeach
</div>
@else
<div class="bg-white border border-dashed border-gray-200 rounded-3xl py-20 text-center"><div class="w-16 h-16 rounded-full bg-gray-50 flex items-center justify-center mx-auto mb-5"><i class="fa fa-heart-o text-2xl text-gray-300"></i></div><h2 class="text-xl font-black text-gray-900">Sua lista está vazia</h2><p class="text-sm text-gray-500 mt-2">Toque no coração dos produtos que quiser salvar.</p><a href="{{$rota}}" class="inline-flex mt-6 px-6 py-3 rounded-xl bg-main text-white text-xs font-black">Explorar produtos</a></div>
@endif
</div></section>
@endsection