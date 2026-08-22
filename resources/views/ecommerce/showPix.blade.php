@extends('ecommerce.default')

@section('content')
<div class="container mt-5">
    <div class="card text-center">
        <div class="card-body">
            <h3>{{ $title }}</h3>

            @if($pedido_pix->qr_code_base64)
                <img src="data:image/png;base64,{{ $pedido_pix->qr_code_base64 }}" width="250" class="my-3">
                <textarea class="form-control" rows="3" readonly>{{ $pedido_pix->qr_code }}</textarea>
            @else
                <p class="text-danger">Não foi possível gerar o QR Code PIX.</p>
            @endif

            <p class="mt-3 text-warning">Aguardando confirmação do pagamento...</p>

            <a href="{{ url('/loja/'.$link.'/carrinho') }}" class="btn btn-secondary mt-3">Voltar ao Carrinho</a>
        </div>
    </div>
</div>
@endsection

@section('javascript')
<script>
    // Consulta automática do status do PIX a cada 10 segundos
    setInterval(() => {
        fetch("{{ url('/loja/'.$link.'/consulta-pix/'.$pedido_pix->transacao_id) }}")
        .then(res => res.json())
        .then(data => {
            if(data === 'approved'){
                alert('Pagamento aprovado!');
                location.reload();
            }
        })
        .catch(err => console.error(err));
    }, 10000);
</script>
@endsection