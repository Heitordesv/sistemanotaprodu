@extends('ecommerce_one_tech.default')
@section('content')

<style type="text/css">
    .img-prod { width: 100px; height: 100px; object-fit: contain; }
    .stock-badge { position:absolute; left:8px; top:8px; z-index:4; padding:4px 8px; border-radius:12px; font-size:10px; font-weight:700; }
    .stock-out { background:#dc3545; color:#fff; }
    @media (max-width: 767px) {
        .shop_sidebar { display:none; }
        .product_item { width:50% !important; }
    }
</style>

<div class="super_container">
    <div class="shop">
        <div class="container">
            <div class="row">
                <aside class="col-lg-3">
                    <div class="shop_sidebar">
                        <div class="sidebar_section">
                            <div class="sidebar_title">Categorias</div>
                            <ul class="sidebar_categories">
                                @foreach($default['categorias'] as $c)
                                <li><a href="{{$rota}}/{{$c->id}}/categorias">{{$c->nome}}</a></li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </aside>

                <div class="col-lg-9">
                    <div class="shop_content">
                        <div class="shop_bar clearfix">
                            <div class="shop_product_count">
                                <span>{{sizeof($produtos)}}</span> produtos encontrados
                                @isset($pesquisa)<small class="d-block text-muted">Busca: “{{$pesquisa}}”</small>@endisset
                            </div>
                        </div>

                        @if(sizeof($produtos) > 0)
                        <div class="product_grid">
                            <div class="product_grid_border"></div>
                            @php $max = 0; @endphp

                            @foreach($produtos as $p)
                            @if(sizeof($p->galeria) > 0)
                            @php
                                $semEstoque = $p->controlar_estoque && (($p->estoque_disponivel ?? 0) <= 0);
                                if($p->valor > $max) $max = $p->valor;
                            @endphp
                            <article class="product_item is_new" style="position:relative;">
                                <div class="product_border"></div>
                                @if($semEstoque)
                                    <span class="stock-badge stock-out">SEM ESTOQUE</span>
                                @endif

                                <a href="{{$rota}}/{{$p->id}}/verProduto" aria-label="Ver {{$p->produto->nome}}">
                                    <div class="product_image d-flex flex-column align-items-center justify-content-center">
                                        <img class="img-prod" src="{{$p->galeria[0]->img}}" alt="{{$p->produto->nome}}">
                                    </div>
                                </a>

                                <div class="product_content">
                                    <div class="product_price">R$ {{number_format($p->valor, 2, ',', '.')}}</div>
                                    <div class="product_name"><div><a href="{{$rota}}/{{$p->id}}/verProduto" tabindex="0">{{$p->produto->nome}}</a></div></div>
                                    @if($semEstoque)
                                    <div class="text-danger mt-1"><small>Indisponível no momento</small></div>
                                    @elseif($p->controlar_estoque)
                                    <div class="text-success mt-1"><small>{{(int) floor($p->estoque_disponivel)}} disponível(is)</small></div>
                                    @endif
                                </div>

                                <a href="{{$rota}}/{{$p->id}}/curtirProduto" aria-label="Favoritar {{$p->produto->nome}}">
                                    <div class="product_fav"><i class="fas fa-heart"></i></div>
                                </a>

                                <ul class="product_marks">
                                    @if($p->isNovo())
                                    <li class="product_mark product_new">novo</li>
                                    @endif
                                </ul>
                            </article>
                            @endif
                            @endforeach
                        </div>
                        <input type="hidden" id="max" value="{{$max}}">
                        @else
                        <div class="text-center py-5 my-5">
                            <h3>Nenhum produto encontrado.</h3>
                            <p class="text-muted">Tente outra busca ou navegue pelas categorias.</p>
                            <a href="{{$rota}}" class="btn btn-primary">Voltar para a loja</a>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection