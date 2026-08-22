@extends('ecommerce.default')
@section('content')
@php
    $estoqueControlado=(bool)$produto->controlar_estoque;
    $quantidadeDisponivel=$estoqueControlado?max(0,(float)($estoqueDisponivel??0)):null;
    $semEstoque=$estoqueControlado&&$quantidadeDisponivel<=0;
    $maxCompra=$estoqueControlado?max(1,(int)floor($quantidadeDisponivel)):999;
@endphp
<section class="py-8 md:py-12 bg-gray-50/70">
<div class="container mx-auto px-4 max-w-7xl">
<nav class="flex items-center gap-2 text-[10px] uppercase tracking-widest font-black text-gray-400 mb-6"><a href="{{$rota}}" class="hover:text-main">Início</a><span>/</span>@if($produto->categoria)<a href="{{$rota}}/{{$produto->categoria->id}}/categorias" class="hover:text-main">{{$produto->categoria->nome}}</a><span>/</span>@endif<span class="truncate">{{$produto->produto->nome}}</span></nav>
<div class="grid lg:grid-cols-2 gap-7 lg:gap-12 items-start">
<div class="lg:sticky lg:top-28">
<div class="bg-white border border-gray-100 rounded-3xl overflow-hidden aspect-square flex items-center justify-center"><img id="main-product-image" src="{{$produto->galeria[0]->img}}" alt="{{$produto->produto->nome}}" class="w-full h-full object-contain p-5 md:p-8"></div>
@if(sizeof($produto->galeria)>1)<div class="grid grid-cols-5 gap-2 mt-3">@foreach($produto->galeria as $g)<button type="button" class="thumb-item bg-white border border-gray-100 rounded-xl aspect-square overflow-hidden hover:border-main"><img data-imgbigurl="{{$g->img}}" src="{{$g->img}}" class="w-full h-full object-contain p-2"></button>@endforeach</div>@endif
</div>
<div class="bg-white border border-gray-100 rounded-3xl p-5 md:p-8 shadow-sm">
@if($produto->categoria)<span class="text-[10px] uppercase tracking-[.16em] font-black text-main">{{$produto->categoria->nome}}</span>@endif
<h1 class="text-2xl md:text-4xl font-black text-gray-950 leading-tight tracking-[-.03em] mt-2">{{$produto->produto->nome}}</h1>
@if($produto->produto->referencia)<div class="text-xs text-gray-400 mt-2">Ref. {{$produto->produto->referencia}}</div>@endif
<div class="mt-6"><div class="text-3xl md:text-5xl font-black text-gray-950">R$ {{number_format($produto->valor,2,',','.')}}</div>@if($produto->valor_pix>0)<div class="text-sm font-black text-green-600 mt-2">R$ {{number_format($produto->valor_pix,2,',','.')}} no PIX</div>@endif @if($produto->valor_cartao>0)<div class="text-sm text-gray-500 mt-1">ou 12x de R$ {{number_format($produto->valor_cartao/12,2,',','.')}}</div>@endif</div>
<div class="mt-6 text-sm leading-6 text-gray-600">{{ \Illuminate\Support\Str::limit(trim(strip_tags($produto->descricao)),220) }}</div>
<div class="mt-6 rounded-2xl p-4 flex items-center gap-3 {{$semEstoque?'bg-red-50 text-red-700 border border-red-100':'bg-green-50 text-green-700 border border-green-100'}}"><i class="fa {{$semEstoque?'fa-times-circle':'fa-check-circle'}}"></i><strong class="text-sm">{{$semEstoque?'Produto indisponível no momento':($estoqueControlado?((int)floor($quantidadeDisponivel)).' unidade(s) disponível(is)':'Produto disponível para compra')}}</strong></div>
<form method="post" action="{{$rota}}/addProduto" class="mt-6">@csrf<input type="hidden" name="produto_id" value="{{$produto->id}}"><div class="flex flex-col sm:flex-row gap-3"><div class="h-14 flex items-center rounded-2xl border border-gray-200 bg-gray-50"><button type="button" class="btn-qtd-menos w-12 h-full text-xl font-black" @disabled($semEstoque)>−</button><input id="quantidade-produto" name="quantidade" type="number" value="1" min="1" max="{{$maxCompra}}" class="w-14 text-center bg-transparent outline-none font-black" @disabled($semEstoque)><button type="button" class="btn-qtd-mais w-12 h-full text-xl font-black" @disabled($semEstoque)>+</button></div><button @disabled($semEstoque) class="flex-1 h-14 rounded-2xl {{$semEstoque?'bg-gray-200 text-gray-500 cursor-not-allowed':'bg-main text-white hover:opacity-90'}} font-black text-xs uppercase tracking-widest"><i class="fa fa-shopping-bag mr-2"></i>{{$semEstoque?'Indisponível':'Adicionar ao carrinho'}}</button><a href="{{$rota}}/{{$produto->id}}/curtirProduto" class="h-14 w-14 rounded-2xl border border-gray-200 flex items-center justify-center {{$curtida?'text-red-500 bg-red-50 border-red-100':'text-gray-400 hover:text-red-500'}}"><i class="fa fa-heart"></i></a></div></form>
<div class="grid sm:grid-cols-3 gap-3 mt-7 pt-6 border-t border-gray-100"><div class="rounded-2xl bg-gray-50 p-4"><i class="fa fa-lock text-main"></i><strong class="block text-xs mt-2">Compra segura</strong><span class="text-[10px] text-gray-500">Dados protegidos</span></div><div class="rounded-2xl bg-gray-50 p-4"><i class="fa fa-truck text-main"></i><strong class="block text-xs mt-2">Entrega</strong><span class="text-[10px] text-gray-500">Calcule no carrinho</span></div><div class="rounded-2xl bg-gray-50 p-4"><i class="fa fa-credit-card text-main"></i><strong class="block text-xs mt-2">Pagamento</strong><span class="text-[10px] text-gray-500">PIX e cartão</span></div></div>
</div>
</div>
<div class="bg-white border border-gray-100 rounded-3xl p-5 md:p-8 mt-7"><h2 class="text-xl font-black text-gray-950 mb-5">Descrição do produto</h2><div class="prose max-w-none text-gray-600 leading-7">{!! $produto->descricao !!}</div></div>
</div></section>
@endsection
@section('javascript')
<script>$('.thumb-item').on('click',function(){const n=$(this).find('img').data('imgbigurl');$('#main-product-image').attr('src',n);$('.thumb-item').removeClass('border-main');$(this).addClass('border-main')});$('.btn-qtd-mais').on('click',function(){const i=$('#quantidade-produto'),m=parseInt(i.attr('max')||999);i.val(Math.min(m,parseInt(i.val()||1)+1))});$('.btn-qtd-menos').on('click',function(){const i=$('#quantidade-produto');i.val(Math.max(1,parseInt(i.val()||1)-1))});</script>
@endsection