@extends('default.layout',['title' => 'cpf'])

@section('content')
<div class="container mt-5">
    <h2>Resultado da Consulta</h2>

    @if($mensagem)
        <div class="alert alert-info">{{ $mensagem }}</div>
    @endif

   <div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white">
            <h4 class="mb-0">Dados do Veículo</h4>
        </div>
        <div class="card-body">
            <div class="row">

                <div class="col-md-4 text-center">
                    <img src="{{ $dados['response']['logo'] ?? '' }}" 
                         alt="Logo Marca" class="img-fluid mb-3" style="max-height:80px;">
                    <h5>{{ $dados['response']['MARCA'] ?? '-' }}</h5>
                    <p class="text-muted">{{ $dados['response']['MODELO'] ?? '-' }} - {{ $dados['response']['VERSAO'] ?? '-' }}</p>
                </div>

                <div class="col-md-8">
                    <table class="table table-sm table-bordered">
                        <tr>
                            <th>Placa</th>
                            <td>{{ $dados['response']['placa'] ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Ano / Modelo</th>
                            <td>{{ $dados['response']['ano'] ?? '-' }} / {{ $dados['response']['anoModelo'] ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Cor</th>
                            <td>{{ $dados['response']['cor'] ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Chassi</th>
                            <td>{{ $dados['response']['chassi'] ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Município / UF</th>
                            <td>{{ $dados['response']['municipio'] ?? '-' }} / {{ $dados['response']['uf'] ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Combustível</th>
                            <td>{{ $dados['response']['combustivel'] ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Potência</th>
                            <td>{{ $dados['response']['potencia'] ?? '-' }} cv</td>
                        </tr>
                        <tr>
                            <th>Passageiros</th>
                            <td>{{ $dados['response']['quantidade_passageiro'] ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Última atualização</th>
                            <td>{{ $dados['response']['ultima_atualizacao'] ?? '-' }}</td>
                        </tr>
                    </table>
                </div>

            </div>
        </div>
    </div>

    {{-- Debug opcional para ver JSON completo --}}
    <div class="card mt-3">
        <div class="card-body">
            <h6 class="mb-2">JSON Completo</h6>
            <pre style="background:#f8f9fa; padding:10px; border-radius:6px; max-height:300px; overflow:auto;">
{{ json_encode($dados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}
            </pre>
        </div>
    </div>
</div>

    <form action="{{ route('consulta.veiculo.pdf') }}" method="POST" class="mt-3">
        @csrf
        <input type="hidden" name="placa" value="{{ $placa }}">
        <input type="hidden" name="dados" value="{{ json_encode($dados) }}">
        <button type="submit" class="btn btn-danger">Gerar PDF</button>
    </form>

    <a href="{{ route('consulta.veiculo.index') }}" class="btn btn-secondary mt-3">Nova consulta</a>
</div>
@endsection
