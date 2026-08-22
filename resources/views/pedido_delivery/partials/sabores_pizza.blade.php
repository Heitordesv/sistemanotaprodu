
<style>
    .sabor:hover {
        cursor: pointer;
    }

</style>

<div class="row mt-3">
    @foreach($data as $item)

    <div class="card col-md-3 bd2 sabor sabores_pizza_{{ $item->id }} sabores" onclick="tamanho_pizza('{{$item->id}}', '{{$item->produto->nome}}')">
        <div class="card-header">
            @if($item->img != null)
            <img class="card-img-top" src="{{$item->img}}">
            @else
            <img style="width: auto; height: 200px;" class="m-2" src="/imgs/no_image.png" alt="image">
            @endif
        </div>
        <div class="card-body">
            <h5 class="card-title">{{ $item->produto->nome }}</h5>
            <p class="card-text">
                @if(!$item->categoria->tipo_pizza)
                R$ {{ __moeda($item->valor) }}
                @else R$
                @foreach($item->pizza as $key => $pz)
                @if($pz->tamanho_id == $tamanho)
                {{ __moeda($pz->valor) }}
                <input type="" name="" class="valor_tamanho_{{$pz->produto->id}}" id="" value="{{$pz->valor}}">
                @endif 
                @endforeach
                @endif
            </p>
            
        </div>
    </div>
    @endforeach
    <div class="mt-3">
        <h5>Sabores Selecionados:
            <strong class="lbl-sabores"></strong>
        </h5>
    </div>
</div>
