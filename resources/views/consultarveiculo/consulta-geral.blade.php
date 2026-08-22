@extends('default.layout', ['title' => 'Consulta de Veículo - API Brasil'])

@section('content')
<div class="page-content container mt-5">
    <h2>Consulta de Veículo</h2>
{{-- Botão de pagamento --}}
<a href="https://checkout.mixksolutions.com.br/pg/?item=11" 
   target="_blank" 
   class="btn btn-success mt-3 d-inline-block">
   💳 COBRAR NO PIX 
</a> 
    {{-- Formulário de consulta --}}
<form action="{{ route('consultarveiculo.consultarVeiculog') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="placa">Placa:</label>
            <input type="text" name="placa" id="placa" class="form-control" required placeholder="Ex: ABC1234" value="{{ old('placa', $placa ?? '') }}">
        </div>
        <button type="submit" class="btn btn-primary">Consultar</button>
    </form>

    {{-- Exibir JSON completo do veículo --}}
    @isset($veiculo)
    <div class="card mt-4">
        <div class="card-header">JSON Completo do Veículo</div>
        <div class="card-body">
            <pre>{{ json_encode($veiculo, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        </div>
    </div>

    {{-- Botão gerar PDF --}}
    <form action="{{ route('consulta-geral.pdfVeiculo') }}" method="POST" target="_blank" class="mt-3">
        @csrf
        <input type="hidden" name="placa" value="{{ $placa }}">
        <input type="hidden" name="veiculo" value="{{ json_encode($veiculo) }}">
        <button type="submit" class="btn btn-danger">Gerar PDF</button>
    </form>
    @endisset
</div>
@endsection
