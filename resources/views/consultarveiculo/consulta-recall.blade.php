@extends('default.layout', ['title' => 'Consulta de Veículo - Recall'])

@section('content')
<div class="page-content container mt-5">
    <h2>Consulta Recall do Veículo</h2>
{{-- Botão de pagamento --}}
<a href="https://checkout.mixksolutions.com.br/pg/?item=11" 
   target="_blank" 
   class="btn btn-success mt-3 d-inline-block">
   💳 COBRAR NO PIX 
</a> 
    <form action="{{ route('consultarveiculo.consultarRecall') }}" method="POST">
        @csrf
        <input type="text" name="placa" placeholder="Digite a placa" required>
        <button type="submit" class="btn btn-primary">Consultar</button>
    </form>

    @isset($veiculo)
    <hr>
    <h4>Dados do Veículo - Placa: {{ $placa }}</h4>

   <form action="{{ route('consultarveiculo.gerarPdfRecall') }}" method="POST">
    @csrf
    <input type="hidden" name="placa" value="{{ $placa }}">
    <input type="hidden" name="veiculo" value="{{ json_encode($veiculo) }}">
    <button type="submit">Gerar PDF</button>
</form>


    <pre>{{ print_r($veiculo, true) }}</pre>
    @endisset
</div>
@endsection
