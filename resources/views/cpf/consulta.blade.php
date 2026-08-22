@extends('default.layout',['title' => 'cpf'])

@section('content')
<div class="page-content">
    <h2>Consulta de CPF</h2>

    {{-- Exibir erros --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $erro)
                    <li>{{ $erro }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Formulário --}}
    <form action="{{ route('cpf.consultar') }}" method="POST" class="mt-3">
        @csrf
        <div class="mb-3">
            <label for="cpf" class="form-label">Digite o CPF</label>
            <input type="text" name="cpf" id="cpf" class="form-control" placeholder="000.000.000-00" required>
        </div>

        <button type="submit" class="btn btn-primary">Consultar</button>
    </form>
</div>
@endsection
