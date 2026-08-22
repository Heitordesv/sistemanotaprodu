@extends('ecommerce.default')
@section('content')

@php
    $pedidosCliente = collect($cliente->pedidos());
    $favoritosCliente = \App\Models\CurtidaProdutoEcommerce::with(['produto.produto', 'produto.galeria'])
        ->where('cliente_id', $cliente->id)
        ->whereHas('produto', fn($q) => $q->where('empresa_id', $cliente->empresa_id)->where('status', 1))
        ->orderByDesc('id')
        ->get();

    $pedidosEmAndamento = $pedidosCliente->filter(function($pedido){
        $status = strtolower((string)($pedido->status ?? 'novo'));
        $pagamento = strtolower((string)($pedido->status_pagamento ?? 'pending'));
        return !in_array($status, ['entregue','cancelado'], true)
            && !in_array($pagamento, ['rejected','cancelled'], true);
    })->count();

    $pedidosEntregues = $pedidosCliente->filter(fn($p) => strtolower((string)($p->status ?? '')) === 'entregue')->count();
    $inicial = mb_strtoupper(mb_substr(trim((string)$cliente->nome) ?: 'C', 0, 1));
@endphp

<style>
    .account-page{max-width:1220px;margin:0 auto;padding:34px 18px 78px;color:#111827}
    .account-hero{position:relative;overflow:hidden;background:#fff;border:1px solid #eef2f7;border-radius:30px;padding:34px 36px;margin-bottom:18px;box-shadow:0 18px 50px rgba(15,23,42,.05)}
    .account-hero:before{content:'';position:absolute;width:320px;height:320px;right:-130px;top:-185px;border-radius:50%;background:color-mix(in srgb,var(--main-color) 11%,transparent)}
    .account-hero-inner{position:relative;z-index:1;display:flex;align-items:center;justify-content:space-between;gap:24px}
    .account-user{display:flex;align-items:center;gap:16px;min-width:0}.account-avatar{width:62px;height:62px;border-radius:20px;background:var(--main-color);color:#fff;display:flex;align-items:center;justify-content:center;font-size:25px;font-weight:900;box-shadow:0 10px 28px color-mix(in srgb,var(--main-color) 28%,transparent);flex:none}.account-eyebrow{font-size:10px;letter-spacing:.18em;text-transform:uppercase;color:var(--main-color);font-weight:900}.account-title{font-size:30px;line-height:1.1;margin:4px 0 5px;font-weight:900;color:#111827}.account-subtitle{font-size:13px;color:#64748b;margin:0}.account-hero-actions{display:flex;gap:10px;flex-wrap:wrap}
    .account-btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;border-radius:14px;padding:12px 16px;text-decoration:none!important;font-size:12px;font-weight:900;border:1px solid transparent;cursor:pointer;transition:.2s}.account-btn.primary{background:var(--main-color);color:#fff!important}.account-btn.primary:hover{filter:brightness(.95);transform:translateY(-1px)}.account-btn.light{background:#fff;border-color:#e5e7eb;color:#334155!important}.account-btn.light:hover{border-color:color-mix(in srgb,var(--main-color) 35%,#e5e7eb);color:var(--main-color)!important}
    .account-summary{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-bottom:18px}.summary-card{background:#fff;border:1px solid #eef2f7;border-radius:20px;padding:18px 20px;display:flex;align-items:center;justify-content:space-between;gap:12px}.summary-card .meta small{display:block;color:#94a3b8;font-size:10px;text-transform:uppercase;letter-spacing:.1em;font-weight:900}.summary-card .meta strong{display:block;font-size:24px;margin-top:4px;color:#111827}.summary-icon{width:42px;height:42px;border-radius:14px;background:color-mix(in srgb,var(--main-color) 8%,white);color:var(--main-color);display:flex;align-items:center;justify-content:center}
    .account-tabs-wrap{background:#fff;border:1px solid #eef2f7;border-radius:20px;padding:8px;margin-bottom:18px;overflow:auto;box-shadow:0 10px 24px rgba(15,23,42,.03)}.account-tabs{display:flex;gap:6px;min-width:max-content}.account-tab{border:0;background:transparent;color:#64748b;border-radius:13px;padding:11px 15px;font-size:12px;font-weight:900;display:flex;align-items:center;gap:8px;cursor:pointer;transition:.2s}.account-tab:hover,.account-tab.active{background:color-mix(in srgb,var(--main-color) 9%,white);color:var(--main-color)}.account-tab .badge{min-width:22px;height:22px;border-radius:999px;padding:0 7px;background:#f1f5f9;color:#64748b;display:inline-flex;align-items:center;justify-content:center;font-size:10px}.account-tab.active .badge{background:var(--main-color);color:#fff}
    .account-panel{display:none}.account-panel.active{display:block}.panel-card{background:#fff;border:1px solid #eef2f7;border-radius:24px;padding:24px;margin-bottom:18px;box-shadow:0 12px 28px rgba(15,23,42,.035)}.panel-head{display:flex;justify-content:space-between;align-items:flex-start;gap:16px;margin-bottom:20px}.panel-head h2{font-size:18px;font-weight:900;color:#111827;margin:0}.panel-head p{font-size:12px;color:#94a3b8;margin:4px 0 0}
    .orders-list{display:flex;flex-direction:column;gap:10px}.order-card{display:grid;grid-template-columns:minmax(0,1fr) auto;align-items:center;gap:16px;border:1px solid #eef2f7;border-radius:18px;padding:17px 18px;transition:.2s}.order-card:hover{border-color:color-mix(in srgb,var(--main-color) 30%,#eef2f7);box-shadow:0 8px 22px rgba(15,23,42,.045);transform:translateY(-1px)}.order-top{display:flex;align-items:center;gap:9px;flex-wrap:wrap}.order-id{font-size:14px;font-weight:900}.status-pill{display:inline-flex;align-items:center;padding:6px 9px;border-radius:999px;font-size:10px;font-weight:900}.status-ok{background:#ecfdf5;color:#166534}.status-wait{background:#fff7ed;color:#9a3412}.status-info{background:#eff6ff;color:#1d4ed8}.status-bad{background:#fef2f2;color:#991b1b}.status-neutral{background:#f1f5f9;color:#475569}.order-date{font-size:11px;color:#94a3b8;margin-top:5px}.order-meta{display:flex;gap:18px;flex-wrap:wrap;margin-top:8px;font-size:12px;color:#64748b}.order-meta strong{color:#111827}.order-link{color:var(--main-color)!important;font-size:12px;font-weight:900;text-decoration:none!important;white-space:nowrap}
    .favorites-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px}.favorite-card{border:1px solid #eef2f7;border-radius:20px;overflow:hidden;background:#fff;transition:.25s}.favorite-card:hover{transform:translateY(-3px);box-shadow:0 14px 30px rgba(15,23,42,.07)}.favorite-media{display:flex;align-items:center;justify-content:center;aspect-ratio:1/1;background:linear-gradient(180deg,#fbfdff,#f8fafc);overflow:hidden}.favorite-media img{width:100%;height:100%;object-fit:contain;padding:16px}.favorite-info{padding:15px}.favorite-name{font-size:13px;line-height:1.4;font-weight:900;color:#111827;min-height:38px;margin-bottom:9px}.favorite-price{font-size:18px;font-weight:900;color:#111827}.favorite-actions{display:flex;gap:8px;margin-top:12px}.favorite-actions a{flex:1;border-radius:11px;padding:10px;text-align:center;text-decoration:none!important;font-size:10px;font-weight:900;text-transform:uppercase;letter-spacing:.05em}.favorite-actions .go{background:var(--main-color);color:#fff!important}.favorite-actions .store{background:#f8fafc;color:#475569!important;border:1px solid #eef2f7}
    .profile-grid,.security-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}.field label{display:block;font-size:11px;font-weight:900;color:#64748b;margin-bottom:6px}.field input,.field select{width:100%;height:48px;border:1px solid #dfe5ec;border-radius:13px;padding:0 14px;background:#fff;color:#111827;outline:none;transition:.2s}.field input:focus,.field select:focus{border-color:var(--main-color);box-shadow:0 0 0 4px color-mix(in srgb,var(--main-color) 10%,transparent)}.field.full{grid-column:1/-1}.form-actions{margin-top:16px;display:flex;gap:10px;flex-wrap:wrap}
    .addresses-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}.address-card{position:relative;border:1px solid #eef2f7;border-radius:18px;padding:17px;background:#fcfdff}.address-card strong{display:block;font-size:13px;color:#111827;padding-right:34px;margin-bottom:5px}.address-card p{font-size:12px;color:#64748b;line-height:1.55;margin:0}.address-edit{position:absolute;right:10px;top:10px;width:34px;height:34px;border:0;border-radius:10px;background:#fff;color:#64748b;cursor:pointer}.address-edit:hover{color:var(--main-color);box-shadow:0 4px 12px rgba(15,23,42,.08)}.address-editor{display:none;border-top:1px solid #eef2f7;margin-top:20px;padding-top:20px}.address-editor.active{display:block}
    .security-note{background:linear-gradient(135deg,#f8fafc,#fff);border:1px solid #eef2f7;border-radius:18px;padding:20px}.security-note h3{font-size:14px;font-weight:900;margin:0 0 7px;color:#111827}.security-note p{font-size:12px;line-height:1.6;color:#64748b;margin:0}.empty-state{text-align:center;border:1px dashed #d8e0e9;border-radius:18px;padding:36px 20px;color:#64748b}.empty-state i{font-size:28px;color:#cbd5e1;margin-bottom:10px}.empty-state strong{display:block;color:#334155;margin-bottom:5px}.inline-alert{border-radius:14px;padding:12px 14px;margin-bottom:16px;font-size:13px;font-weight:800}.inline-alert.ok{background:#ecfdf5;border:1px solid #bbf7d0;color:#166534}.inline-alert.bad{background:#fef2f2;border:1px solid #fecaca;color:#991b1b}
    @media(max-width:980px){.account-hero-inner{align-items:flex-start}.account-summary{grid-template-columns:repeat(2,1fr)}.favorites-grid{grid-template-columns:repeat(2,1fr)}}
    @media(max-width:640px){.account-page{padding:20px 12px 60px}.account-hero{padding:22px;border-radius:24px}.account-hero-inner{display:block}.account-user{align-items:flex-start}.account-avatar{width:54px;height:54px;border-radius:17px}.account-title{font-size:24px}.account-hero-actions{margin-top:18px}.account-btn{width:100%}.account-summary{grid-template-columns:1fr 1fr}.summary-card{padding:14px}.summary-card .meta strong{font-size:20px}.summary-icon{width:36px;height:36px}.panel-card{padding:18px;border-radius:20px}.panel-head{display:block}.panel-head .account-btn{margin-top:12px}.favorites-grid,.profile-grid,.addresses-grid,.security-grid{grid-template-columns:1fr}.order-card{grid-template-columns:1fr}.order-link{display:inline-block;margin-top:3px}}
</style>

<div class="account-page">
    @if(session('flash_sucesso') || session('mensagem_sucesso'))<div class="inline-alert ok">{{ session('flash_sucesso') ?: session('mensagem_sucesso') }}</div>@endif
    @if(session('flash_erro') || session('mensagem_erro'))<div class="inline-alert bad">{{ session('flash_erro') ?: session('mensagem_erro') }}</div>@endif
    @if($errors->any())<div class="inline-alert bad">{{ $errors->first() }}</div>@endif

    <section class="account-hero">
        <div class="account-hero-inner">
            <div class="account-user">
                <div class="account-avatar">{{ $inicial }}</div>
                <div>
                    <div class="account-eyebrow">Minha conta</div>
                    <h1 class="account-title">Olá, {{ $cliente->nome }}</h1>
                    <p class="account-subtitle">Acompanhe seus pedidos, favoritos e dados pessoais.</p>
                </div>
            </div>
            <div class="account-hero-actions">
                <a href="{{ $rota }}/curtidas" class="account-btn light"><i class="fa fa-heart"></i> Favoritos</a>
                <a href="{{ $rota }}" class="account-btn primary"><i class="fa fa-shopping-bag"></i> Continuar comprando</a>
            </div>
        </div>
    </section>

    <section class="account-summary">
        <div class="summary-card"><div class="meta"><small>Pedidos</small><strong>{{ $pedidosCliente->count() }}</strong></div><div class="summary-icon"><i class="fa fa-shopping-bag"></i></div></div>
        <div class="summary-card"><div class="meta"><small>Em andamento</small><strong>{{ $pedidosEmAndamento }}</strong></div><div class="summary-icon"><i class="fa fa-clock-o"></i></div></div>
        <div class="summary-card"><div class="meta"><small>Entregues</small><strong>{{ $pedidosEntregues }}</strong></div><div class="summary-icon"><i class="fa fa-check"></i></div></div>
        <div class="summary-card"><div class="meta"><small>Favoritos</small><strong>{{ $favoritosCliente->count() }}</strong></div><div class="summary-icon"><i class="fa fa-heart"></i></div></div>
    </section>

    <div class="account-tabs-wrap">
        <nav class="account-tabs" aria-label="Minha conta">
            <button type="button" class="account-tab active" data-target="overview"><i class="fa fa-home"></i> Visão geral</button>
            <button type="button" class="account-tab" data-target="orders"><i class="fa fa-shopping-bag"></i> Pedidos <span class="badge">{{ $pedidosCliente->count() }}</span></button>
            <button type="button" class="account-tab" data-target="favorites"><i class="fa fa-heart"></i> Favoritos <span class="badge">{{ $favoritosCliente->count() }}</span></button>
            <button type="button" class="account-tab" data-target="profile"><i class="fa fa-user"></i> Meus dados</button>
            <button type="button" class="account-tab" data-target="addresses"><i class="fa fa-map-marker"></i> Endereços</button>
            <button type="button" class="account-tab" data-target="security"><i class="fa fa-lock"></i> Segurança</button>
            <a href="{{ $rota }}/logoff" class="account-tab" style="color:#b91c1c!important"><i class="fa fa-sign-out"></i> Sair</a>
        </nav>
    </div>

    <section id="panel-overview" class="account-panel active">
        <div class="panel-card">
            <div class="panel-head"><div><h2>Pedidos recentes</h2><p>Veja rapidamente o andamento das últimas compras.</p></div><button type="button" class="account-btn light account-tab" data-target="orders">Ver todos</button></div>
            <div class="orders-list">
                @forelse($pedidosCliente->take(3) as $p)
                    @php
                        $sp = strtolower((string)($p->status ?? 'novo')); $pg = strtolower((string)($p->status_pagamento ?? 'pending'));
                        if (in_array($sp,['cancelado'],true) || in_array($pg,['rejected','cancelled'],true)) { $sl='Cancelado'; $sc='status-bad'; }
                        elseif ($sp === 'entregue') { $sl='Entregue'; $sc='status-ok'; }
                        elseif ($sp === 'enviado') { $sl='Enviado'; $sc='status-info'; }
                        elseif ($sp === 'preparacao') { $sl='Preparando'; $sc='status-info'; }
                        elseif ($pg === 'approved') { $sl='Pagamento aprovado'; $sc='status-ok'; }
                        elseif (in_array($pg,['pending','in_process'],true)) { $sl='Aguardando pagamento'; $sc='status-wait'; }
                        else { $sl='Pedido recebido'; $sc='status-neutral'; }
                    @endphp
                    <article class="order-card"><div><div class="order-top"><span class="order-id">Pedido #{{ $p->id }}</span><span class="status-pill {{ $sc }}">{{ $sl }}</span></div><div class="order-date">{{ \Carbon\Carbon::parse($p->created_at)->format('d/m/Y \à\s H:i') }}</div><div class="order-meta"><span>Total <strong>R$ {{ number_format((float)$p->valor_total,2,',','.') }}</strong></span>@if($p->forma_pagamento)<span>Pagamento <strong>{{ ucfirst($p->forma_pagamento) }}</strong></span>@endif</div></div><a href="{{ $rota }}/pedido_detalhe/{{ $p->id }}" class="order-link">Acompanhar pedido →</a></article>
                @empty
                    <div class="empty-state"><i class="fa fa-shopping-bag"></i><strong>Nenhum pedido ainda</strong>Suas compras aparecerão aqui.</div>
                @endforelse
            </div>
        </div>

        <div class="panel-card">
            <div class="panel-head"><div><h2>Favoritos recentes</h2><p>Itens que você marcou para ver depois.</p></div><button type="button" class="account-btn light account-tab" data-target="favorites">Ver favoritos</button></div>
            @if($favoritosCliente->count())
                <div class="favorites-grid">
                    @foreach($favoritosCliente->take(4) as $fav)
                        @php $prod = $fav->produto; $img = optional($prod->galeria->first())->img; @endphp
                        <article class="favorite-card"><a href="{{ $rota }}/{{ $prod->id }}/verProduto" class="favorite-media">@if($img)<img src="{{ $img }}" alt="{{ $prod->produto->nome ?? 'Produto' }}">@else<i class="fa fa-image text-gray-300 text-3xl"></i>@endif</a><div class="favorite-info"><div class="favorite-name">{{ $prod->produto->nome ?? 'Produto' }}</div><div class="favorite-price">R$ {{ number_format((float)$prod->valor,2,',','.') }}</div><div class="favorite-actions"><a class="go" href="{{ $rota }}/{{ $prod->id }}/verProduto">Ver produto</a></div></div></article>
                    @endforeach
                </div>
            @else
                <div class="empty-state"><i class="fa fa-heart-o"></i><strong>Nenhum favorito</strong>Use o coração nos produtos que quiser salvar.</div>
            @endif
        </div>
    </section>

    <section id="panel-orders" class="account-panel">
        <div class="panel-card">
            <div class="panel-head"><div><h2>Meus pedidos</h2><p>Histórico completo de compras.</p></div></div>
            <div class="orders-list">
                @forelse($pedidosCliente as $p)
                    @php
                        $sp = strtolower((string)($p->status ?? 'novo')); $pg = strtolower((string)($p->status_pagamento ?? 'pending'));
                        if (in_array($sp,['cancelado'],true) || in_array($pg,['rejected','cancelled'],true)) { $sl='Cancelado'; $sc='status-bad'; }
                        elseif ($sp === 'entregue') { $sl='Entregue'; $sc='status-ok'; }
                        elseif ($sp === 'enviado') { $sl='Enviado'; $sc='status-info'; }
                        elseif ($sp === 'preparacao') { $sl='Preparando'; $sc='status-info'; }
                        elseif ($pg === 'approved') { $sl='Pagamento aprovado'; $sc='status-ok'; }
                        elseif (in_array($pg,['pending','in_process'],true)) { $sl='Aguardando pagamento'; $sc='status-wait'; }
                        else { $sl='Pedido recebido'; $sc='status-neutral'; }
                    @endphp
                    <article class="order-card"><div><div class="order-top"><span class="order-id">Pedido #{{ $p->id }}</span><span class="status-pill {{ $sc }}">{{ $sl }}</span></div><div class="order-date">{{ \Carbon\Carbon::parse($p->created_at)->format('d/m/Y \à\s H:i') }}</div><div class="order-meta"><span>Total <strong>R$ {{ number_format((float)$p->valor_total,2,',','.') }}</strong></span>@if($p->forma_pagamento)<span>Pagamento <strong>{{ ucfirst($p->forma_pagamento) }}</strong></span>@endif</div></div><a href="{{ $rota }}/pedido_detalhe/{{ $p->id }}" class="order-link">Acompanhar pedido →</a></article>
                @empty
                    <div class="empty-state"><i class="fa fa-shopping-bag"></i><strong>Nenhum pedido encontrado</strong>Quando fizer uma compra, ela aparecerá aqui.</div>
                @endforelse
            </div>
        </div>
    </section>

    <section id="panel-favorites" class="account-panel">
        <div class="panel-card">
            <div class="panel-head"><div><h2>Meus favoritos</h2><p>{{ $favoritosCliente->count() }} item(ns) salvo(s).</p></div><a href="{{ $rota }}/curtidas" class="account-btn light">Abrir página de favoritos</a></div>
            @if($favoritosCliente->count())
                <div class="favorites-grid">
                    @foreach($favoritosCliente as $fav)
                        @php $prod = $fav->produto; $img = optional($prod->galeria->first())->img; @endphp
                        <article class="favorite-card"><a href="{{ $rota }}/{{ $prod->id }}/verProduto" class="favorite-media">@if($img)<img src="{{ $img }}" alt="{{ $prod->produto->nome ?? 'Produto' }}">@else<i class="fa fa-image text-gray-300 text-3xl"></i>@endif</a><div class="favorite-info"><div class="favorite-name">{{ $prod->produto->nome ?? 'Produto' }}</div><div class="favorite-price">R$ {{ number_format((float)$prod->valor,2,',','.') }}</div><div class="favorite-actions"><a class="go" href="{{ $rota }}/{{ $prod->id }}/verProduto">Ver produto</a><a class="store" href="{{ $rota }}">Loja</a></div></div></article>
                    @endforeach
                </div>
            @else
                <div class="empty-state"><i class="fa fa-heart-o"></i><strong>Sua lista está vazia</strong>Explore a loja e favorite seus produtos preferidos.</div>
            @endif
        </div>
    </section>

    <section id="panel-profile" class="account-panel">
        <div class="panel-card">
            <div class="panel-head"><div><h2>Meus dados</h2><p>Informações usadas para identificação e contato.</p></div></div>
            <form method="post" action="{{ $rota }}/ecommerceUpdateCliente">@csrf
                <div class="profile-grid">
                    <div class="field"><label>Nome</label><input required type="text" name="nome" value="{{ old('nome',$cliente->nome) }}"></div>
                    <div class="field"><label>Sobrenome</label><input required type="text" name="sobre_nome" value="{{ old('sobre_nome',$cliente->sobre_nome) }}"></div>
                    <div class="field"><label>Telefone</label><input required type="tel" data-mask="(00) 00000-0000" name="telefone" value="{{ old('telefone',$cliente->telefone) }}"></div>
                    <div class="field"><label>E-mail</label><input required type="email" name="email" value="{{ old('email',$cliente->email) }}"></div>
                </div>
                <div class="form-actions"><button class="account-btn primary" type="submit">Salvar alterações</button></div>
            </form>
        </div>
    </section>

    <section id="panel-addresses" class="account-panel">
        <div class="panel-card">
            <div class="panel-head"><div><h2>Meus endereços</h2><p>Endereços disponíveis para suas entregas.</p></div><button id="new-address" type="button" class="account-btn primary"><i class="fa fa-plus"></i> Novo endereço</button></div>
            @if($cliente->enderecos->count())
                <div class="addresses-grid">
                    @foreach($cliente->enderecos as $e)
                        <article class="address-card"><strong>{{ $e->rua }}, {{ $e->numero }}</strong><p>{{ $e->bairro }}<br>{{ $e->cidade }}/{{ $e->uf }} · CEP {{ $e->cep }}@if($e->complemento)<br>{{ $e->complemento }}@endif</p><button type="button" class="address-edit" onclick='editAddress(@json($e))'><i class="fa fa-pencil"></i></button></article>
                    @endforeach
                </div>
            @else
                <div class="empty-state"><i class="fa fa-map-marker"></i><strong>Nenhum endereço cadastrado</strong>Adicione um endereço para agilizar o checkout.</div>
            @endif
            <div id="address-editor" class="address-editor">
                <div class="panel-head"><div><h2 id="address-title">Novo endereço</h2><p>Preencha os dados de entrega.</p></div></div>
                <form method="post" action="{{ $rota }}/ecommerceSaveEndereco">@csrf
                    <input type="hidden" id="endereco_id" name="endereco_id" value="0">
                    <div class="profile-grid">
                        <div class="field"><label>Rua</label><input required id="rua" name="rua" type="text"></div>
                        <div class="field"><label>Número</label><input required id="numero" name="numero" type="text"></div>
                        <div class="field"><label>Bairro</label><input required id="bairro" name="bairro" type="text"></div>
                        <div class="field"><label>CEP</label><input required id="cep" data-mask="00000-000" name="cep" type="text"></div>
                        <div class="field"><label>Cidade</label><input required id="cidade" name="cidade" type="text"></div>
                        <div class="field"><label>UF</label><select required id="uf" name="uf"><option value="">Selecione</option>@foreach(App\Models\EnderecoEcommerce::estados() as $u)<option value="{{ $u }}">{{ $u }}</option>@endforeach</select></div>
                        <div class="field full"><label>Complemento</label><input id="complemento" name="complemento" type="text"></div>
                    </div>
                    <div class="form-actions"><button type="submit" class="account-btn primary">Salvar endereço</button><button type="button" id="cancel-address" class="account-btn light">Cancelar</button></div>
                </form>
            </div>
        </div>
    </section>

    <section id="panel-security" class="account-panel">
        <div class="panel-card">
            <div class="panel-head"><div><h2>Segurança</h2><p>Altere sua senha de acesso.</p></div></div>
            <div class="security-grid">
                <div class="security-note"><h3><i class="fa fa-shield text-main"></i> Proteja sua conta</h3><p>Use uma senha exclusiva com pelo menos 8 caracteres. Evite repetir senhas de outros serviços.</p></div>
                <form method="post" action="{{ $rota }}/ecommerceUpdateSenha">@csrf
                    <div class="field"><label>Nova senha</label><input required minlength="8" autocomplete="new-password" type="password" name="senha"></div>
                    <div class="field" style="margin-top:12px"><label>Confirmar nova senha</label><input required minlength="8" autocomplete="new-password" type="password" name="repita_senha"></div>
                    <div class="form-actions"><button class="account-btn primary" type="submit">Alterar senha</button></div>
                </form>
            </div>
        </div>
    </section>
</div>

@section('javascript')
<script>
(function(){
    const tabs = document.querySelectorAll('.account-tab[data-target]');
    const panels = document.querySelectorAll('.account-panel');

    function activate(target){
        tabs.forEach(btn => btn.classList.toggle('active', btn.dataset.target === target));
        panels.forEach(panel => panel.classList.toggle('active', panel.id === 'panel-' + target));
        if(history.replaceState) history.replaceState(null, '', '#' + target);
    }

    tabs.forEach(btn => btn.addEventListener('click', () => activate(btn.dataset.target)));
    const initial = window.location.hash.replace('#','');
    if(['overview','orders','favorites','profile','addresses','security'].includes(initial)) activate(initial);

    const editor = document.getElementById('address-editor');
    const title = document.getElementById('address-title');
    const newBtn = document.getElementById('new-address');
    const cancelBtn = document.getElementById('cancel-address');

    window.editAddress = function(address){
        activate('addresses');
        document.getElementById('endereco_id').value = address.id || 0;
        document.getElementById('rua').value = address.rua || '';
        document.getElementById('numero').value = address.numero || '';
        document.getElementById('bairro').value = address.bairro || '';
        document.getElementById('cep').value = address.cep || '';
        document.getElementById('cidade').value = address.cidade || '';
        document.getElementById('uf').value = address.uf || '';
        document.getElementById('complemento').value = address.complemento || '';
        title.textContent = 'Editar endereço';
        editor.classList.add('active');
        editor.scrollIntoView({behavior:'smooth', block:'center'});
    };

    if(newBtn) newBtn.addEventListener('click', () => {
        document.getElementById('endereco_id').value = 0;
        ['rua','numero','bairro','cep','cidade','complemento'].forEach(id => document.getElementById(id).value = '');
        document.getElementById('uf').value = '';
        title.textContent = 'Novo endereço';
        editor.classList.add('active');
        editor.scrollIntoView({behavior:'smooth', block:'center'});
    });
    if(cancelBtn) cancelBtn.addEventListener('click', () => editor.classList.remove('active'));
})();
</script>
@endsection
@endsection