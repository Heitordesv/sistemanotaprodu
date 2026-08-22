@extends('default.layout',['title' => 'cpf'])

@section('content')
<div class="page-content">
    <h2>Resultado da Consulta</h2>

    @if(isset($mensagem) && $mensagem)
        <div class="alert alert-info">{{ $mensagem }}</div>
    @endif

    <div class="card mt-3">
        <div class="card-body">
            <h5>CPF Consultado: <strong>{{ $cpf }}</strong></h5>
            
            <pre class="mt-3" style="background:#f8f9fa; padding:15px; border-radius:8px;">
{{ json_encode($dados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}
            </pre>
        </div>
    </div>

    {{-- Formulário para gerar PDF --}}
    <form action="{{ route('cpf.gerarPdf') }}" method="POST" class="mt-3">
        @csrf
        <input type="hidden" name="cpf" value="{{ $cpf }}">
        <input type="hidden" name="dados" value="{{ json_encode($dados) }}">
        <button type="submit" class="btn btn-danger">Gerar PDF</button>
    </form>

    <a href="{{ route('cpf.index') }}" class="btn btn-secondary mt-3">Nova consulta</a>
</div>
@endsection
