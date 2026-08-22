@extends('default.layout', ['title' => 'Consulta CRLV - API Brasil'])

@section('content')
<div class="page-content container mt-5">
    <h2>Veículo – CRLV</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            {{ $errors->first() }}
        </div>
    @endif

    <form action="{{ route('veiculo.consultar-crlv') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="placa" class="form-label">Placa do Veículo</label>
            <input type="text" name="placa" id="placa" class="form-control" placeholder="AAA1234" required>
        </div>

        <div class="mb-3">
            <label for="uf" class="form-label">Estado (UF)</label>
            <input type="text" name="uf" id="uf" class="form-control" placeholder="SP" maxlength="2" required>
        </div>

        <button type="submit" class="btn btn-primary">Consultar</button>
    </form>
</div>
@endsection
