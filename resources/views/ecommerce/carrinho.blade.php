@extends('ecommerce.default')
@section('content')
<section class="py-10 md:py-14 bg-gray-50/70 min-h-screen"><div class="container mx-auto px-4 max-w-7xl">
<div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-7"><div><span class="text-[10px] uppercase tracking-[.18em] font-black text-main">Seu pedido</span><h1 class="text-3xl md:text-4xl font-black text-gray-950 tracking-[-.03em] mt-2">Carrinho</h1><p class="text-sm text-gray-500 mt-2">{{ $default['carrinho'] ? count($default['carrinho']->itens) : 0 }} item(ns) adicionado(s).</p></div><a href="{{$rota}}" class="text-xs font-black text-main"><i class="fa fa-arrow-left mr-2"></i>Continuar comprando</a></div>
<div class="grid lg:grid-cols-[minmax(0,1fr)_360px] gap-7 items-start">
<div class="space-y-4">
@if($default['carrinho']&&count($default['carrinho']->itens)>0)
@foreach($default['carrinho']->itens as $i)
@php $imgUrl=(isset($i->produto->galeria)&&count($i->produto->galeria)>0)?$i->produto->galeria[0]->img:asset('img/no-image.png'); @endphp
<article class="item-row bg-white border border-gray-100 rounded-2xl md:rounded-3xl p-4 md:p-5 flex flex-col sm:flex-row gap-4 items-center" data-id="{{$i->id}}"><a href="{{$rota}}/{{$i->produto->id}}/verProduto" class="w-full sm:w-28 aspect-square rounded-2xl bg-gray-50 overflow-hidden flex-shrink-0"><img src="{{$imgUrl}}" alt="{{$i->produto->produto->nome}}" class="w-full h-full object-contain p-3"></a><div class="flex-1 w-full min-w-0"><h2 class="text-sm md:text-base font-black text-gray-900 line-clamp-2">{{$i->produto->produto->nome}}</h2>@if($i->produto->produto->grade)<span class="text-[9px] text-gray-400 font-black uppercase tracking-widest mt-1 block">{{$i->produto->produto->str_grade}}</span>@endif<div class="text-sm font-black text-main mt-2">R$ {{number_format($i->produto->valor,2,',','.')}} cada</div></div><div class="flex items-center gap-3 w-full sm:w-auto justify-between sm:justify-end"><div class="flex items-center rounded-xl border border-gray-200 bg-gray-50"><button type="button" class="btn-menos w-10 h-10 font-black text-gray-500">−</button><input id="{{$i->id}}" type="number" min="1" value="{{$i->quantidade}}" class="qtd w-10 text-center bg-transparent outline-none font-black pointer-events-none"><button type="button" class="btn-mais w-10 h-10 font-black text-gray-500">+</button></div><div class="text-right min-w-[105px]"><strong class="item-subtotal block text-base md:text-lg text-gray-950">R$ {{number_format($i->quantidade*$i->produto->valor,2,',','.')}}</strong><a href="{{$rota}}/{{$i->id}}/deleteItemCarrinho" class="text-[10px] font-black text-red-500 mt-1 inline-block"><i class="fa fa-trash mr-1"></i>Remover</a></div></div></article>
@endforeach
@else
<div class="bg-white border border-dashed border-gray-200 rounded-3xl py-20 text-center"><i class="fa fa-shopping-bag text-4xl text-gray-300 mb-4"></i><h2 class="text-xl font-black text-gray-900">Seu carrinho está vazio</h2><p class="text-sm text-gray-500 mt-2">Adicione produtos para continuar.</p><a href="{{$rota}}" class="inline-flex mt-6 px-6 py-3 rounded-xl bg-main text-white text-xs font-black">Explorar produtos</a></div>
@endif
</div>
<aside class="space-y-4 lg:sticky lg:top-28">
<div class="bg-white border border-gray-100 rounded-3xl p-5 md:p-6"><h2 class="text-lg font-black text-gray-950">Calcular frete</h2><p class="text-xs text-gray-500 mt-1 mb-4">Informe seu CEP para consultar PAC, SEDEX e outras opções disponíveis.</p>@if($default['carrinho'])<input type="hidden" id="pedido_id" value="{{$default['carrinho']->id}}">@endif<div class="flex gap-2"><input id="cep" data-mask="00000-000" placeholder="00000-000" inputmode="numeric" autocomplete="postal-code" class="flex-1 h-12 px-4 rounded-xl border border-gray-200 outline-none focus:border-main focus:ring-4 focus:ring-main/10"><button id="btn-calcular-frete" type="button" class="w-12 h-12 rounded-xl bg-main text-white disabled:opacity-50"><i class="fa fa-search"></i></button></div><div id="frete-feedback" class="hidden mt-3 rounded-xl px-3 py-2.5 text-xs font-semibold"></div><div class="frete space-y-2 mt-4"></div></div>
<div class="bg-white border border-gray-100 rounded-3xl p-5 md:p-6 shadow-sm"><h2 class="text-lg font-black text-gray-950">Resumo</h2><div class="mt-5 space-y-3 text-sm"><div class="flex justify-between text-gray-500"><span>Subtotal</span><strong class="val-subtotal text-gray-900" data-valor="{{$default['carrinho']?$default['carrinho']->somaItens():0}}">R$ {{$default['carrinho']?number_format($default['carrinho']->somaItens(),2,',','.'):'0,00'}}</strong></div><div id="display-frete-selecionado" class="hidden flex justify-between text-gray-500"><span>Frete <small id="txt-tipo-frete" class="text-main"></small></span><strong id="val-frete-selecionado" class="text-gray-900">R$ 0,00</strong></div><div class="pt-4 border-t border-gray-100 flex justify-between items-end"><span class="font-black text-gray-900">Total</span><strong id="total" class="text-2xl font-black text-main">R$ {{$default['carrinho']?number_format($default['carrinho']->somaItens(),2,',','.'):'0,00'}}</strong></div></div><form method="get" action="{{$rota}}/checkout" class="mt-6"><input type="hidden" id="inp_tipo_frete" name="tipo_frete" value=""><input type="hidden" id="inp_valor_frete" name="valor_frete" value="0"><button type="submit" @disabled(!$default['carrinho']||count($default['carrinho']->itens)===0) class="w-full py-4 rounded-2xl bg-main text-white text-xs font-black uppercase tracking-wider disabled:opacity-40">Continuar compra <i class="fa fa-arrow-right ml-2"></i></button></form><div class="grid grid-cols-2 gap-2 mt-4"><div class="rounded-xl bg-gray-50 p-3 text-center"><i class="fa fa-lock text-main"></i><span class="block text-[9px] font-black mt-1">Compra segura</span></div><div class="rounded-xl bg-gray-50 p-3 text-center"><i class="fa fa-credit-card text-main"></i><span class="block text-[9px] font-black mt-1">Pagamento protegido</span></div></div></div>
</aside></div></div></section>
<input type="hidden" value="{{csrf_token()}}" id="token">
@endsection
@section('javascript')
<script>
$(function(){
    function atualizarCarrinho(input){
        const id=input.attr('id'),q=input.val(),token=$('#token').val();
        if(!id||q<1)return;
        input.closest('.item-row').addClass('opacity-40 pointer-events-none');
        $.ajax({url:"{{$rota}}/atualizaItem",type:'POST',data:{_token:token,id:id,quantidade:q},success:function(){location.reload()},error:function(){location.reload()}})
    }

    function feedbackFrete(mensagem,tipo='erro'){
        const box=$('#frete-feedback');
        box.removeClass('hidden bg-red-50 text-red-700 border border-red-100 bg-amber-50 text-amber-700 border-amber-100 bg-green-50 text-green-700 border-green-100');
        if(!mensagem){box.addClass('hidden').text('');return;}
        if(tipo==='aviso') box.addClass('bg-amber-50 text-amber-700 border border-amber-100');
        else if(tipo==='sucesso') box.addClass('bg-green-50 text-green-700 border border-green-100');
        else box.addClass('bg-red-50 text-red-700 border border-red-100');
        box.text(mensagem);
    }

    function valorNumerico(valor){
        if(valor===undefined||valor===null||valor==='')return null;
        const numero=parseFloat(String(valor).replace(/\./g,'').replace(',','.'));
        return Number.isFinite(numero)?numero:null;
    }

    function opcaoFrete(type,price,time,labelClass){
        const numero=valorNumerico(price);
        if(numero===null)return '';
        return `<label class="flex items-center gap-3 p-3 rounded-xl border border-gray-200 bg-gray-50 cursor-pointer hover:border-main transition"><input type="radio" name="select_frete" value="${price}" data-type="${type}" class="w-4 h-4"><span class="flex-1"><strong class="block text-xs ${labelClass||'text-gray-900'}">${type}</strong><small class="text-[10px] text-gray-500">${time||''}</small></span><strong class="text-xs">${numero===0?'Grátis':'R$ '+price}</strong></label>`;
    }

    $(document).on('click','.btn-mais',function(){let i=$(this).siblings('.qtd');i.val(parseInt(i.val())+1);atualizarCarrinho(i)});
    $(document).on('click','.btn-menos',function(){let i=$(this).siblings('.qtd'),v=parseInt(i.val());if(v>1){i.val(v-1);atualizarCarrinho(i)}});

    $(document).on('click','#btn-calcular-frete',function(e){
        e.preventDefault();
        let cep=$('#cep').val().replace(/\D/g,''),pedido_id=$('#pedido_id').val();

        $('.frete').empty();
        feedbackFrete('');

        if(!pedido_id){
            feedbackFrete('Adicione um produto ao carrinho antes de calcular o frete.');
            return;
        }

        if(cep.length!==8){
            $('#cep').addClass('border-red-500');
            feedbackFrete('Informe um CEP com 8 números.');
            return;
        }

        $('#cep').removeClass('border-red-500');
        $('#btn-calcular-frete').prop('disabled',true).html('<i class="fa fa-spinner fa-spin"></i>');
        feedbackFrete('Consultando os Correios...','aviso');

        $.get("{{$rota}}/calcularFrete",{cep:cep,pedido_id:pedido_id})
            .done(function(res){
                let html='';

                if(res.frete_gratis){
                    html+=opcaoFrete('GRATIS','0,00','Frete grátis para este pedido','text-green-700');
                }

                if(res.preco!==null&&res.preco!==undefined&&valorNumerico(res.preco)>0){
                    html+=opcaoFrete('PAC',res.preco,(res.prazo&&res.prazo!=='0'?res.prazo+' dias úteis':'Prazo a confirmar'));
                }

                if(res.preco_sedex!==null&&res.preco_sedex!==undefined&&valorNumerico(res.preco_sedex)>0){
                    html+=opcaoFrete('SEDEX',res.preco_sedex,(res.prazo_sedex&&res.prazo_sedex!=='0'?res.prazo_sedex+' dias úteis':'Prazo a confirmar'));
                }

                if(res.habilitar_retirada==1){
                    html+=opcaoFrete('RETIRADA','0,00','Retire diretamente na loja','text-green-700');
                }

                $('.frete').html(html);

                if(!html){
                    feedbackFrete(res.aviso||'Nenhuma modalidade de entrega está disponível para este CEP.');
                }else if(res.aviso){
                    feedbackFrete(res.aviso,'aviso');
                }else{
                    feedbackFrete('Opções de entrega calculadas com sucesso.','sucesso');
                }
            })
            .fail(function(xhr){
                const res=xhr.responseJSON||{};
                feedbackFrete(res.error||res.message||'Não foi possível calcular o frete. Confira o CEP e tente novamente.');
            })
            .always(function(){
                $('#btn-calcular-frete').prop('disabled',false).html('<i class="fa fa-search"></i>');
            });
    });

    $('#cep').on('keydown',function(e){
        if(e.key==='Enter'){
            e.preventDefault();
            $('#btn-calcular-frete').trigger('click');
        }
    });

    $(document).on('change','input[name="select_frete"]',function(){
        let s=$(this).val(),n=valorNumerico(s)||0,t=String($(this).data('type')||''),subtotal=parseFloat($('.val-subtotal').data('valor'))||0;
        $('#display-frete-selecionado').removeClass('hidden');
        $('#txt-tipo-frete').text(t);
        $('#val-frete-selecionado').text(n===0?'Grátis':'R$ '+s);
        $('#total').text((subtotal+n).toLocaleString('pt-BR',{style:'currency',currency:'BRL'}));
        $('#inp_tipo_frete').val(t.toLowerCase());
        $('#inp_valor_frete').val(n.toFixed(2));
    });
});
</script>
@endsection