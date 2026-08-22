@extends('default.layout', ['title' => 'Contas a Receber'])

@section('content')
<h2>Pagamento PIX</h2>
<p>Conta: {{ $conta->referencia }}</p>
<p>Valor: R$ {{ number_format($conta->valor_integral, 2, ',', '.') }}</p>

<div>
    <p>Escaneie o QR Code abaixo para pagar:</p>
    <img src="data:image/png;base64,{{ $qrCodeBase64 }}" alt="QR Code PIX" />
</div>

<div>
    <p>Código PIX:</p>
    <textarea readonly style="width: 100%; height: 100px;">{{ $qrCode }}</textarea>
</div>

<a href="{{ url()->previous() }}" class="btn btn-primary mt-3">Voltar</a>
@endsection
