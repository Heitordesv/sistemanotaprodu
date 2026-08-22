@extends('default.layout', ['title' => 'Consulta de Multas'])

@section('content')
<div class="page-content container mt-5">
    <h2 class="mb-4">Consulta de Multas</h2>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            Preencha os dados para consultar
        </div>
        <div class="card-body">
            <form action="{{ route('veiculo.multa.consultar') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="placa" class="form-label">Placa do Veículo</label>
                    <input type="text" name="placa" id="placa" class="form-control" placeholder="ABC1234" required maxlength="7">
                </div>
                <button type="submit" class="btn btn-success">Consultar Multas</button>
            </form>
        </div>
    </div>
</div>
@endsection
