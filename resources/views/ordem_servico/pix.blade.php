<h1>Pagamento PIX - Ordem #{{ $ordem->id }}</h1>

<p>Valor: R$ {{ number_format($ordem->valor, 2, ',', '.') }}</p>

@if(isset($preference->init_point))
    <p><a href="{{ $preference->init_point }}" target="_blank" class="btn btn-success">Pagar com Mercado Pago (PIX)</a></p>
@endif

@if(isset($preference->point_of_interaction->transaction_data->qr_code))
    <img src="{{ $preference->point_of_interaction->transaction_data->qr_code }}" alt="QR Code PIX" />
@endif
