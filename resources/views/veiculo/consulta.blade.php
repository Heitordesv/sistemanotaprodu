@extends('default.layout', ['title' => 'Consulta de Veículo - API Brasil'])

@section('content')
<div class="page-content container mt-5">
    <h2>Veículo – (DG)</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            {{ $errors->first() }}
        </div>
    @endif

    <p>Escolha o tipo de consulta:</p>
    <div class="mb-4">
        <button type="button" class="btn btn-primary" onclick="showForm('placa')">Por Placa</button>
        <button type="button" class="btn btn-secondary" onclick="showForm('chassi')">Por Chassi</button>
    </div>

    <!-- Formulário por Placa -->
    <form id="form-placa" action="{{ route('veiculo.consultar') }}" method="POST" style="display:none;">
        @csrf
        <div class="mb-3">
            <label for="placa" class="form-label">Placa do Veículo</label>
            <input type="text" name="placa" id="placa" class="form-control" placeholder="AAA1234" required>
        </div>
        <button type="submit" class="btn btn-primary">Consultar</button>
    </form>

    <!-- Formulário por Chassi -->
    <form id="form-chassi" action="{{ route('veiculo.consultar-chassi') }}" method="POST" style="display:none;">
        @csrf
        <div class="mb-3">
            <label for="chassi" class="form-label">Número do Chassi</label>
            <input type="text" name="chassi" id="chassi" class="form-control" placeholder="XXXXXXXXXXXXXXX" required>
        </div>
        <button type="submit" class="btn btn-secondary">Consultar</button>
    </form>
</div>

<script>
function showForm(tipo) {
    document.getElementById('form-placa').style.display = tipo === 'placa' ? 'block' : 'none';
    document.getElementById('form-chassi').style.display = tipo === 'chassi' ? 'block' : 'none';
}
</script>
@endsection
