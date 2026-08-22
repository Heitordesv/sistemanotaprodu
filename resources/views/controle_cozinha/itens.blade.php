@foreach ($itens as $item)
<div class="col-xl-4 col-12">
    <div class="card">
        <div class="card-header bg-dark text-white">
            <div class="col-4">
                <h5 class="card-title text-white">Pedido: {{ $item->pedido->id }}</h5>
            </div>
        </div>
        <div class="card-body">
            <h5 class="">Produto: {{$item->produto->nome}}</h5>
            <h5>Valor: {{ __moeda($item->valor) }}</h5>
            <h5 class="text-info">Quantidade: {{ $item->quantidade }}</h5>
            <hr>
            <h5 class="text-danger">Observação: {{ $item->observacao }}</h5>
        </div>
        <div class="">
            <a class="btn btn-info w-100" href="{{ route('pedidos.alterarStatus' , $item->id) }}">Finalizar</a>
        </div>
    </div>
</div>

@endforeach
