@extends('default.layout',['title' => 'cpf'])

@section('content')
<div class="container mt-5">
    <h2>Consulta de Veículo</h2>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $erro)
                    <li>{{ $erro }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('consulta.veiculo.consultar') }}" method="POST" class="mt-3">
        @csrf
        <div class="mb-3">
            <label for="placa" class="form-label">Placa</label>
            <input type="text" name="placa" id="placa" class="form-control" placeholder="ABC1234" required>
        </div>

        <button type="submit" class="btn btn-primary">Consultar</button>
    </form>
</div>
@endsection
