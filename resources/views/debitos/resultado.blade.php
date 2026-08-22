@extends('default.layout', ['title' => 'Resultado Débitos'])

@section('content')
<div class="container mt-5">
    <h2>Resultado da Consulta</h2>

    @if(isset($data['error']) && $data['error'])
        <div class="alert alert-danger">{{ $data['message'] ?? 'Erro na consulta' }}</div>
    @else
        <pre>{{ json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
    @endif

    <a href="{{ route('debitos.index') }}" class="btn btn-secondary mt-3">Nova Consulta</a>
</div>
@endsection
