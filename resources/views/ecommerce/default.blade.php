<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{$title}}</title>

    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body{font-family:'Cairo',sans-serif;overflow-x:hidden;background:#f8fafc;color:#111827}
        :root{--main-color:{{$default['config']->cor_principal}}}
        .bg-main{background-color:var(--main-color)}.text-main{color:var(--main-color)}.border-main{border-color:var(--main-color)}
        .mobile-menu-shadow{background:rgba(15,23,42,.45)}.sidebar-transition{transition:transform .28s ease}
        .store-header{background:rgba(255,255,255,.97);backdrop-filter:blur(12px);border-bottom:1px solid #eef2f7;position:sticky;top:0;z-index:50}
        .header-action{display:flex;align-items:center;gap:10px;padding:8px 10px;border-radius:14px;text-decoration:none!important;color:#475569!important;transition:.2s}
        .header-action:hover{background:#f8fafc;color:var(--main-color)!important}
        .header-icon{width:40px;height:40px;border-radius:13px;background:#f8fafc;display:flex;align-items:center;justify-content:center;position:relative;flex:none}
        .header-action:hover .header-icon{background:color-mix(in srgb,var(--main-color) 10%,white);color:var(--main-color)}
        .header-label small{display:block;font-size:9px;line-height:1;text-transform:uppercase;letter-spacing:.1em;color:#94a3b8;font-weight:900}.header-label strong{display:block;font-size:12px;color:#334155;line-height:1.25;margin-top:4px;max-width:118px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
        .header-badge{position:absolute;right:-4px;top:-4px;min-width:18px;height:18px;border-radius:999px;padding:0 5px;background:var(--main-color);color:#fff;font-size:9px;font-weight:900;display:flex;align-items:center;justify-content:center;border:2px solid #fff}
        .desktop-nav a{font-size:11px;font-weight:900;text-transform:uppercase;letter-spacing:.12em;color:#475569;text-decoration:none!important}.desktop-nav a:hover{color:var(--main-color)}

        .smart-search{position:relative;flex:1;max-width:760px}
        .smart-search-form{display:flex;align-items:stretch;height:54px;background:#fff;border:2px solid #e5e7eb;border-radius:18px;transition:.2s;box-shadow:0 7px 24px rgba(15,23,42,.04);overflow:visible}
        .smart-search-form:focus-within{border-color:var(--main-color);box-shadow:0 0 0 4px color-mix(in srgb,var(--main-color) 9%,transparent),0 12px 30px rgba(15,23,42,.07)}
        .smart-search-category{width:155px;border:0;border-right:1px solid #eef2f7;background:#f8fafc;padding:0 13px;font-size:11px;font-weight:900;color:#475569;outline:none;border-radius:16px 0 0 16px}
        .smart-search-input-wrap{display:flex;align-items:center;flex:1;min-width:0;padding-left:15px;background:#fff}
        .smart-search-input-wrap i{color:#94a3b8;margin-right:10px}.smart-search-input{width:100%;height:100%;border:0;outline:none;background:transparent;font-size:14px;font-weight:700;color:#111827;min-width:0}.smart-search-input::placeholder{color:#94a3b8;font-weight:600}
        .smart-search-button{border:0;background:var(--main-color);color:#fff;padding:0 24px;font-size:11px;font-weight:900;text-transform:uppercase;letter-spacing:.08em;border-radius:0 15px 15px 0;cursor:pointer;transition:.2s}.smart-search-button:hover{filter:brightness(.95)}
        .search-suggestions{display:none;position:absolute;left:0;right:0;top:62px;background:#fff;border:1px solid #e5e7eb;border-radius:18px;box-shadow:0 22px 50px rgba(15,23,42,.13);overflow:hidden;z-index:80}.search-suggestions.open{display:block}.suggestion-head{padding:11px 15px;border-bottom:1px solid #f1f5f9;font-size:10px;font-weight:900;text-transform:uppercase;letter-spacing:.12em;color:#94a3b8}.suggestion-item{display:flex;align-items:center;gap:12px;padding:11px 14px;text-decoration:none!important;color:#334155!important;border-bottom:1px solid #f8fafc;transition:.15s}.suggestion-item:hover{background:#f8fafc}.suggestion-item:last-child{border-bottom:0}.suggestion-img{width:48px;height:48px;border-radius:12px;background:#f8fafc;object-fit:contain;padding:4px;flex:none}.suggestion-info{min-width:0;flex:1}.suggestion-info strong{display:block;font-size:12px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.suggestion-info small{display:block;font-size:10px;color:#94a3b8;margin-top:2px}.suggestion-price{font-size:12px;font-weight:900;color:var(--main-color);white-space:nowrap}.search-empty{padding:18px;text-align:center;font-size:12px;color:#64748b}.search-footer{display:block;padding:11px 14px;text-align:center;background:#f8fafc;color:var(--main-color)!important;text-decoration:none!important;font-size:11px;font-weight:900}
        .mobile-smart-search{padding:0 16px 14px}.mobile-smart-search .smart-search{max-width:none}.mobile-smart-search .smart-search-form{height:50px}.mobile-smart-search .smart-search-category{display:none}.mobile-smart-search .smart-search-button{padding:0 17px;font-size:0}.mobile-smart-search .smart-search-button i{font-size:14px;margin:0}.mobile-smart-search .search-suggestions{top:58px}

        @media(max-width:1180px){.smart-search-category{display:none}.header-label{display:none}.header-action{padding:4px}.header-icon{width:42px;height:42px}.desktop-actions{gap:2px}}
        @media(max-width:1024px){.desktop-smart-search{display:none!important}}
    </style>

    @if($default['config']->imagem != "")
        <link rel="shortcut icon" href="/ecommerce/logos/{{$default['config']->imagem}}" type="image/x-icon" />
    @else
        <link rel="shortcut icon" href="/ecommerce/logo.png" type="image/x-icon" />
    @endif

    <link rel="stylesheet" href="/ecommerce/assets/css/font-awesome.min.css" type="text/css">
    <link rel="stylesheet" href="/ecommerce/assets/css/owl.carousel.min.css" type="text/css">
</head>

@php
    $sessaoLoja = session('user_ecommerce');
    $usuarioLogado = false;
    $nomeUsuario = null;
    $emailUsuario = null;
    $empresaAtual = (int) ($default['config']->empresa_id ?? 0);

    if (is_array($sessaoLoja) && !empty($sessaoLoja['cliente_id'])) {
        $empresaSessao = (int) ($sessaoLoja['empresa_id'] ?? 0);
        if ($empresaSessao === $empresaAtual && $empresaAtual > 0) {
            $usuarioLogado = true;
            $nomeUsuario = $sessaoLoja['nome'] ?? null;
            $emailUsuario = $sessaoLoja['email'] ?? null;
        } elseif ($empresaSessao === 0 && $empresaAtual > 0) {
            $clienteSessao = \App\Models\ClienteEcommerce::where('id', (int)$sessaoLoja['cliente_id'])
                ->where('empresa_id',$empresaAtual)->where('status',1)->first();
            if($clienteSessao){$usuarioLogado=true;$nomeUsuario=$clienteSessao->nome;$emailUsuario=$clienteSessao->email;}
        }
    }

    $nomeUsuario = $nomeUsuario ?: 'Cliente';
    $qtdFavoritos = (int)($default['curtidas'] ?? 0);
    $qtdCarrinho = $default['carrinho'] ? sizeof($default['carrinho']->itens) : 0;
    $totalCarrinho = $default['carrinho'] ? number_format($default['carrinho']->somaItens(),2,',','.') : '0,00';
@endphp

<body>
    <div id="mobile-sidebar-overlay" class="fixed inset-0 z-[60] hidden mobile-menu-shadow opacity-0 transition-opacity duration-300"></div>
    <aside id="mobile-sidebar" class="fixed top-0 left-0 bottom-0 w-80 bg-white z-[70] sidebar-transition -translate-x-full shadow-2xl overflow-y-auto">
        <div class="p-6">
            <div class="flex justify-between items-center mb-7">
                <a href="{{$rota}}"><img class="h-11 w-auto object-contain" src="{{$default['config']->img}}" alt="Logo"></a>
                <button id="close-mobile-menu" class="w-10 h-10 rounded-xl bg-gray-50 text-gray-500"><i class="fa fa-times"></i></button>
            </div>

            <div class="rounded-2xl bg-gray-50 p-4 mb-6">
                @if($usuarioLogado)
                    <a href="{{$rota}}/login" class="flex items-center gap-3 no-underline">
                        <div class="w-11 h-11 rounded-full bg-main text-white flex items-center justify-center"><i class="fa fa-user"></i></div>
                        <div class="min-w-0"><small class="block text-[9px] uppercase tracking-widest text-gray-400 font-black">Minha conta</small><strong class="block text-sm text-gray-900 truncate">{{$nomeUsuario}}</strong>@if($emailUsuario)<span class="text-[10px] text-gray-400 truncate block">{{$emailUsuario}}</span>@endif</div>
                    </a>
                @else
                    <a href="{{$rota}}/login" class="flex items-center gap-3 no-underline"><div class="w-11 h-11 rounded-full bg-main text-white flex items-center justify-center"><i class="fa fa-user"></i></div><div><small class="block text-[9px] uppercase tracking-widest text-gray-400 font-black">Sua conta</small><strong class="text-sm text-gray-900">Entrar ou acessar cadastro</strong></div></a>
                @endif
            </div>

            <div class="grid grid-cols-2 gap-2 mb-7">
                <a href="{{$rota}}/curtidas" class="rounded-2xl border border-gray-100 p-4 no-underline text-gray-700"><div class="flex items-center justify-between"><i class="fa fa-heart text-main"></i><span class="text-xs font-black">{{$qtdFavoritos}}</span></div><strong class="block text-xs mt-2">Favoritos</strong></a>
                <a href="{{$rota}}/carrinho" class="rounded-2xl border border-gray-100 p-4 no-underline text-gray-700"><div class="flex items-center justify-between"><i class="fa fa-shopping-bag text-main"></i><span class="text-xs font-black">{{$qtdCarrinho}}</span></div><strong class="block text-xs mt-2">Carrinho</strong></a>
            </div>

            <nav class="space-y-1">
                <a href="{{$rota}}" class="block px-4 py-3 rounded-xl text-sm font-black text-gray-700 hover:bg-gray-50 hover:text-main no-underline">Início</a>
                <a href="{{$rota}}/categorias" class="block px-4 py-3 rounded-xl text-sm font-black text-gray-700 hover:bg-gray-50 hover:text-main no-underline">Categorias</a>
                @if($default['postBlogExists'] ?? false)<a href="{{$rota}}/blog" class="block px-4 py-3 rounded-xl text-sm font-black text-gray-700 hover:bg-gray-50 hover:text-main no-underline">Blog</a>@endif
                <a href="{{$rota}}/contato" class="block px-4 py-3 rounded-xl text-sm font-black text-gray-700 hover:bg-gray-50 hover:text-main no-underline">Contato</a>
                @if($usuarioLogado)<a href="{{$rota}}/logoff" class="block px-4 py-3 rounded-xl text-sm font-black text-red-600 hover:bg-red-50 no-underline">Sair da conta</a>@endif
            </nav>
        </div>
    </aside>

    <div class="hidden lg:block bg-white border-b border-gray-100">
        <div class="container mx-auto px-4 py-2 flex items-center justify-between text-[11px] text-gray-500">
            <div class="flex items-center gap-5"><span><i class="fa fa-envelope text-main mr-2"></i>{{$default['config']['email']}}</span>@if((float)($default['config']['frete_gratis_valor'] ?? 0)>0)<span class="font-bold">Frete grátis acima de R$ {{number_format($default['config']['frete_gratis_valor'],2,',','.')}}</span>@endif</div>
            <div class="flex items-center gap-4">@foreach(['facebook','twitter','instagram'] as $social)@if($default['config']["link_$social"] != "")<a href="{{$default['config']["link_$social"]}}" target="_blank" rel="noopener noreferrer" class="text-gray-500 hover:text-main"><i class="fa fa-{{$social}}"></i></a>@endif @endforeach</div>
        </div>
    </div>

    <header class="store-header">
        <div class="container mx-auto px-4 py-3 lg:py-4 flex items-center gap-4 lg:gap-6">
            <button id="open-mobile-menu" class="lg:hidden w-11 h-11 rounded-xl bg-gray-50 text-main flex items-center justify-center"><i class="fa fa-bars"></i></button>
            <a href="{{$rota}}" class="flex-shrink-0"><img class="h-11 lg:h-14 w-auto object-contain" src="{{$default['config']->img}}" alt="{{$default['config']->nome ?? 'Loja'}}"></a>

            <div class="smart-search desktop-smart-search hidden lg:block">
                <form action="{{$rota}}/pesquisa" method="get" class="smart-search-form js-smart-search-form">
                    <select name="categoria" class="smart-search-category" aria-label="Categoria da pesquisa">
                        <option value="">Todas categorias</option>
                        @foreach(($default['categorias'] ?? []) as $categoriaBusca)
                            <option value="{{$categoriaBusca->id}}" {{ (string)request('categoria') === (string)$categoriaBusca->id ? 'selected' : '' }}>{{$categoriaBusca->nome}}</option>
                        @endforeach
                    </select>
                    <div class="smart-search-input-wrap"><i class="fa fa-search"></i><input autocomplete="off" type="search" name="pesquisa" value="{{request('pesquisa')}}" class="smart-search-input js-smart-search-input" placeholder="Busque por produto, código, referência ou categoria"></div>
                    <button type="submit" class="smart-search-button">Buscar</button>
                </form>
                <div class="search-suggestions js-search-suggestions"></div>
            </div>

            <div class="ml-auto flex items-center desktop-actions">
                <a href="{{$rota}}/login" class="header-action"><span class="header-icon"><i class="fa fa-user"></i>@if($usuarioLogado)<span class="absolute -right-0.5 -bottom-0.5 w-3.5 h-3.5 rounded-full bg-green-500 border-2 border-white"></span>@endif</span><span class="header-label"><small>{{$usuarioLogado ? 'Minha conta' : 'Entrar'}}</small><strong>{{$usuarioLogado ? $nomeUsuario : 'Acessar conta'}}</strong></span></a>
                <a href="{{$rota}}/curtidas" class="header-action"><span class="header-icon"><i class="fa fa-heart"></i>@if($qtdFavoritos>0)<span class="header-badge">{{$qtdFavoritos}}</span>@endif</span><span class="header-label"><small>Minha lista</small><strong>Favoritos</strong></span></a>
                <a href="{{$rota}}/carrinho" class="header-action"><span class="header-icon"><i class="fa fa-shopping-bag"></i>@if($qtdCarrinho>0)<span class="header-badge">{{$qtdCarrinho}}</span>@endif</span><span class="header-label"><small>Carrinho</small><strong>R$ {{$totalCarrinho}}</strong></span></a>
            </div>
        </div>

        <div class="mobile-smart-search lg:hidden">
            <div class="smart-search">
                <form action="{{$rota}}/pesquisa" method="get" class="smart-search-form js-smart-search-form">
                    <div class="smart-search-input-wrap"><i class="fa fa-search"></i><input autocomplete="off" type="search" name="pesquisa" value="{{request('pesquisa')}}" class="smart-search-input js-smart-search-input" placeholder="Buscar produto ou código"></div>
                    <button type="submit" class="smart-search-button"><i class="fa fa-search"></i>Buscar</button>
                </form>
                <div class="search-suggestions js-search-suggestions"></div>
            </div>
        </div>

        <div class="hidden lg:block border-t border-gray-100">
            <div class="container mx-auto px-4 h-12 flex items-center gap-8">
                <nav class="desktop-nav flex items-center gap-7"><a href="{{$rota}}">Início</a><a href="{{$rota}}/categorias">Categorias</a>@if($usuarioLogado)<a href="{{$rota}}/login">Meus pedidos</a>@endif<a href="{{$rota}}/curtidas">Favoritos</a>@if($default['postBlogExists'] ?? false)<a href="{{$rota}}/blog">Blog</a>@endif<a href="{{$rota}}/contato">Contato</a></nav>
                <div class="ml-auto flex items-center gap-3 text-[11px] text-gray-500"><i class="fa fa-phone text-main"></i><strong class="text-gray-800">{{$default['config']->telefone}}</strong></div>
            </div>
        </div>
    </header>

    @php $flashSucesso=session('mensagem_sucesso')?:session('flash_sucesso');$flashErro=session('mensagem_erro')?:session('flash_erro'); @endphp
    <div class="container mx-auto px-4 mt-4">
        @if($flashSucesso)<div class="bg-green-50 border border-green-100 text-green-700 px-5 py-3 rounded-2xl mb-4 flex justify-between items-center"><span class="text-sm font-bold"><i class="fa fa-check-circle mr-2"></i>{{$flashSucesso}}</span><button onclick="this.parentElement.remove()"><i class="fa fa-times"></i></button></div>@endif
        @if($flashErro)<div class="bg-red-50 border border-red-100 text-red-700 px-5 py-3 rounded-2xl mb-4 flex justify-between items-center"><span class="text-sm font-bold"><i class="fa fa-exclamation-circle mr-2"></i>{{$flashErro}}</span><button onclick="this.parentElement.remove()"><i class="fa fa-times"></i></button></div>@endif
    </div>

    <main class="min-h-screen">@yield('content')</main>

    @include('ecommerce.partials.footer')

    <script src="/ecommerce/assets/js/jquery-3.3.1.min.js"></script>
    <script src="/ecommerce/assets/js/owl.carousel.min.js"></script>
    <script src="/js/jquery.mask.min.js"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script>
        $(document).ready(function(){
            function toggleMenu(){$('#mobile-sidebar').toggleClass('-translate-x-full');$('#mobile-sidebar-overlay').toggleClass('hidden').toggleClass('opacity-100');$('body').toggleClass('overflow-hidden')}
            $('#open-mobile-menu,#close-mobile-menu,#mobile-sidebar-overlay').on('click',toggleMenu);
            var cpfMascara=function(val){return val.replace(/\D/g,'').length>11?'00.000.000/0000-00':'000.000.000-009'};
            var cpfOptions={onKeyPress:function(val,e,field,options){field.mask(cpfMascara.apply({},arguments),options)}};
            $('.cpf_cnpj').mask(cpfMascara,cpfOptions);
        });

        (function(){
            const endpoint = @json($rota . '/pesquisa/sugestoes');
            const searchUrl = @json($rota . '/pesquisa');
            let timer = null;
            let controller = null;

            function escapeHtml(value){const d=document.createElement('div');d.textContent=value??'';return d.innerHTML;}

            document.querySelectorAll('.smart-search').forEach(function(wrapper){
                const input = wrapper.querySelector('.js-smart-search-input');
                const box = wrapper.querySelector('.js-search-suggestions');
                const form = wrapper.querySelector('.js-smart-search-form');
                const category = form ? form.querySelector('[name="categoria"]') : null;
                if(!input || !box || !form) return;

                function close(){box.classList.remove('open');box.innerHTML='';}
                function render(items, term){
                    if(!items.length){box.innerHTML='<div class="search-empty">Nenhum produto encontrado para <strong>'+escapeHtml(term)+'</strong>.</div><a class="search-footer" href="'+searchUrl+'?pesquisa='+encodeURIComponent(term)+'">Ver resultados da busca</a>';box.classList.add('open');return;}
                    let html='<div class="suggestion-head">Sugestões de produtos</div>';
                    items.forEach(function(item){html+='<a class="suggestion-item" href="'+escapeHtml(item.url)+'">'+(item.imagem?'<img class="suggestion-img" src="'+escapeHtml(item.imagem)+'" alt="">':'<span class="suggestion-img flex items-center justify-center"><i class="fa fa-image text-gray-300"></i></span>')+'<span class="suggestion-info"><strong>'+escapeHtml(item.nome)+'</strong><small>'+escapeHtml(item.categoria||item.codigo||'Produto')+'</small></span><span class="suggestion-price">'+escapeHtml(item.preco)+'</span></a>';});
                    html+='<a class="search-footer" href="'+searchUrl+'?pesquisa='+encodeURIComponent(term)+(category&&category.value?'&categoria='+encodeURIComponent(category.value):'')+'">Ver todos os resultados</a>';
                    box.innerHTML=html;box.classList.add('open');
                }

                input.addEventListener('input',function(){
                    clearTimeout(timer);const term=input.value.trim();if(term.length<2){close();return;}
                    timer=setTimeout(async function(){
                        try{
                            if(controller) controller.abort();controller=new AbortController();
                            const url=new URL(endpoint,window.location.origin);url.searchParams.set('pesquisa',term);if(category&&category.value)url.searchParams.set('categoria',category.value);
                            const response=await fetch(url.toString(),{headers:{'Accept':'application/json'},signal:controller.signal});if(!response.ok)return close();const data=await response.json();render(data.items||[],term);
                        }catch(e){if(e.name!=='AbortError')close();}
                    },250);
                });

                input.addEventListener('focus',function(){if(input.value.trim().length>=2)input.dispatchEvent(new Event('input'));});
                form.addEventListener('submit',function(e){if(input.value.trim().length<2){e.preventDefault();input.focus();}});
                document.addEventListener('click',function(e){if(!wrapper.contains(e.target))close();});
            });
        })();
    </script>
    @isset($payJs)<script src="https://secure.mlstatic.com/sdk/javascript/v1/mercadopago.js"></script><script>window.Mercadopago.setPublishableKey('{{$default['config']->mercadopago_public_key}}')</script><script src="/ecommerce/assets/js/pay.js"></script>@endisset
    @yield('javascript')
</body>
</html>