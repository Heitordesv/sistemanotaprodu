@extends('ecommerce.default')
@section('content')
<section class="py-12 md:py-20 bg-gray-50/70 min-h-screen">
<div class="container mx-auto px-4 max-w-xl">
<div class="bg-white border border-gray-100 rounded-3xl p-6 md:p-8 shadow-sm">
<div class="w-14 h-14 rounded-2xl bg-main/10 text-main flex items-center justify-center mb-5"><i class="fa fa-key text-xl"></i></div>
<span class="text-[10px] uppercase tracking-[.18em] font-black text-main">Recuperação de acesso</span>
<h1 class="text-3xl font-black text-gray-950 tracking-[-.03em] mt-2">Esqueci minha senha</h1>
<p class="text-sm text-gray-500 leading-6 mt-3 mb-6">Informe o e-mail cadastrado nesta loja. Se ele existir, enviaremos uma senha temporária para você acessar sua conta.</p>
@if(session('flash_erro')||session('mensagem_erro'))<div class="mb-5 rounded-xl bg-red-50 border border-red-100 p-3 text-sm font-bold text-red-700">{{session('flash_erro')?:session('mensagem_erro')}}</div>@endif
@if(session('flash_sucesso')||session('mensagem_sucesso'))<div class="mb-5 rounded-xl bg-green-50 border border-green-100 p-3 text-sm font-bold text-green-700">{{session('flash_sucesso')?:session('mensagem_sucesso')}}</div>@endif
@if($errors->any())<div class="mb-5 rounded-xl bg-red-50 border border-red-100 p-3 text-sm font-bold text-red-700">{{$errors->first()}}</div>@endif
<form method="post">@csrf<label class="block text-xs font-black text-gray-600 mb-2">E-mail</label><input autofocus autocomplete="email" required name="email" value="{{old('email')}}" type="email" placeholder="voce@exemplo.com" class="w-full h-12 px-4 border border-gray-200 rounded-xl outline-none focus:border-main focus:ring-4 focus:ring-main/10"><button type="submit" class="w-full mt-4 h-12 rounded-xl bg-main text-white font-black text-xs uppercase tracking-wider hover:opacity-90"><i class="fa fa-envelope mr-2"></i>Enviar recuperação</button></form>
<div class="mt-6 pt-5 border-t border-gray-100 flex items-center justify-between gap-3 flex-wrap"><a href="{{$rota}}/login" class="text-xs font-black text-main"><i class="fa fa-arrow-left mr-2"></i>Voltar ao login</a><a href="{{$rota}}" class="text-xs font-black text-gray-500 hover:text-main">Voltar à loja</a></div>
</div></div></section>
@endsection