@extends('default.layout', ['title' => 'Consulta de Veículo por Chassi'])

@section('content')
<div class="container mt-5">
    <h2 class="mb-4">Consulta de Veículo (Chassi)</h2>

    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <form action="{{ route('veiculo.consultar-chassi') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="chassi" class="form-label">Número do Chassi</label>
            <input type="text" name="chassi" id="chassi" class="form-control" placeholder="Digite o chassi" required>
        </div>
        <button type="submit" class="btn btn-primary">Consultar</button>
    </form>
</div>
@endsection
