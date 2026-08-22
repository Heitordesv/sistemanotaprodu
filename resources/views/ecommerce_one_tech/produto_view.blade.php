@extends('ecommerce_one_tech.default')
@section('content')
@php
    $estoqueControlado = (bool) $produto->controlar_estoque;
    $quantidadeDisponivel = $estoqueControlado ? max(0, (float) ($estoqueDisponivel ?? 0)) : null;
    $semEstoque = $estoqueControlado && $quantidadeDisponivel <= 0;
    $maxCompra = $estoqueControlado ? max(1, (int) floor($quantidadeDisponivel)) : 999;
@endphp

<div class="single_product">
    <div class="container">
        <div class="row">
            <div class="col-lg-2 order-lg-1 order-2">
                <ul class="image_list" aria-label="Galeria de imagens do produto">
                    @foreach($produto->galeria as $g)
                    <li data-image="{{$g->img}}"><img src="{{$g->img}}" alt="{{$produto->produto->nome}}"></li>
                    @endforeach
                </ul>
            </div>

            <div class="col-lg-5 order-lg-2 order-1">
                <div class="image_selected"><img src="{{$produto->galeria[0]->img}}" alt="{{$produto->produto->nome}}"></div>
            </div>

            <div class="col-lg-5 order-3">
                <div class="product_description">
                    <div class="product_category">{{$produto->categoria->nome}}</div>
                    <h1 class="product_name">{{$produto->produto->nome}}</h1>
                    <br>
                    <div class="text-truncate" style="height: 40px;">{{ trim(strip_tags($produto->descricao)) }}</div>

                    <div class="alert {{ $semEstoque ? 'alert-danger' : 'alert-success' }} mt-3" role="status">
                        @if($semEstoque)
                            Produto sem estoque no momento.
                        @elseif($estoqueControlado)
                            {{ (int) floor($quantidadeDisponivel) }} unidade(s) disponível(is).
                        @else
                            Produto disponível para compra.
                        @endif
                    </div>

                    <div class="order_info d-flex flex-row">
                        <form method="post" action="{{$rota}}/addProduto">
                            @csrf
                            <div class="clearfix" style="z-index: 1000;">
                                <input type="hidden" value="{{$produto->id}}" name="produto_id">
                                <div class="product_quantity clearfix {{ $semEstoque ? 'disabled' : '' }}">
                                    <span>Quantidade: </span>
                                    <input name="quantidade" id="quantity_input" type="number" min="1" max="{{$maxCompra}}" value="1" @disabled($semEstoque)>
                                    <div class="quantity_buttons">
                                        <button type="button" id="quantity_inc_button" class="quantity_inc quantity_control border-0 bg-transparent" aria-label="Aumentar quantidade" @disabled($semEstoque)><i class="fas fa-chevron-up"></i></button>
                                        <button type="button" id="quantity_dec_button" class="quantity_dec quantity_control border-0 bg-transparent" aria-label="Diminuir quantidade" @disabled($semEstoque)><i class="fas fa-chevron-down"></i></button>
                                    </div>
                                </div>
                            </div>

                            <div class="product_price">R$ {{number_format($produto->valor, 2, ',', '.')}}</div>
                            <div class="button_container">
                                <button type="submit" class="button cart_button" @disabled($semEstoque) style="{{$semEstoque ? 'opacity:.55;cursor:not-allowed;' : ''}}">
                                    {{$semEstoque ? 'Produto indisponível' : 'Adicionar ao carrinho'}}
                                </button>
                                <a href="{{$rota}}/{{$produto->id}}/curtirProduto" class="product_fav" aria-label="Adicionar ou remover dos favoritos"><i class="fas fa-heart"></i></a>
                            </div>
                        </form>
                    </div>
                    <p class="mt-3 text-muted"><small>Prazo de entrega e frete são calculados no carrinho.</small></p>
                </div>
            </div>
        </div>

        <br><br>
        <div class="row">
            <div class="col-12">
                <h2 class="h4">Descrição do produto</h2>
                {!! $produto->descricao !!}
            </div>
        </div>
    </div>
</div>

<div class="viewed">
    <div class="container">
        <div class="row">
            <div class="col">
                <div class="viewed_title_container">
                    <h3 class="viewed_title">Produtos da categoria</h3>
                    <div class="viewed_nav_container">
                        <div class="viewed_nav viewed_prev"><i class="fas fa-chevron-left ml-auto"></i></div>
                        <div class="viewed_nav viewed_next"><i class="fas fa-chevron-right ml-auto"></i></div>
                    </div>
                </div>

                <div class="viewed_slider_container">
                    <div class="owl-carousel owl-theme viewed_slider">
                        @foreach($categoria->produtos as $p)
                        @if($p->id != $produto->id && sizeof($p->galeria) > 0 && $p->status)
                        <div class="owl-item">
                            <div class="viewed_item discount d-flex flex-column align-items-center justify-content-center text-center">
                                <a href="{{$rota}}/{{$p->id}}/verProduto">
                                    <div class="viewed_image"><img src="{{$p->galeria[0]->img}}" alt="{{$p->produto->nome}}"></div>
                                    <div class="viewed_content text-center">
                                        <div class="viewed_price">R$ {{ number_format($p->valor, 2, ',', '.')}}
                                            @if($p->percentual_desconto_view > 0)
                                            <span>{{number_format($p->valor + ($p->valor *($p->percentual_desconto_view/100)), 2, ',', '.')}}</span>
                                            @endif
                                        </div>
                                        <div class="viewed_name">{{$p->produto->nome}}</div>
                                    </div>
                                    <ul class="item_marks">
                                        @if($p->isNovo())
                                        <li class="item_mark item_new">Novo</li>
                                        @endif
                                    </ul>
                                </a>
                            </div>
                        </div>
                        @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('javascript')
<script>
(function () {
    const input = document.getElementById('quantity_input');
    const inc = document.getElementById('quantity_inc_button');
    const dec = document.getElementById('quantity_dec_button');
    if (!input || !inc || !dec) return;

    inc.addEventListener('click', function () {
        const max = parseInt(input.max || '999', 10);
        input.value = Math.min(max, parseInt(input.value || '1', 10) + 1);
    });
    dec.addEventListener('click', function () {
        input.value = Math.max(1, parseInt(input.value || '1', 10) - 1);
    });
})();
</script>
@endsection