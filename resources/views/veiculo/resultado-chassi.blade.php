@extends('default.layout', ['title' => 'Resultado da Consulta - Chassi'])

@section('content')
<div class="container mt-5">
    <h2>Resultado da Consulta pelo Chassi</h2>

    <p><strong>Mensagem:</strong> {{ $mensagem }}</p>
    <p><strong>Saldo restante:</strong> {{ $balance }}</p>

    @if(!empty($veiculo))
        <table class="table table-bordered mt-3">
            <tr>
                <th>Placa</th>
                <td>{{ $veiculo['placa'] ?? '-' }}</td>
            </tr>
            <tr>
                <th>Marca / Modelo</th>
                <td>{{ $veiculo['modelo_marca'] ?? '-' }}</td>
            </tr>
            <tr>
                <th>Ano Fabricação / Modelo</th>
                <td>{{ $veiculo['ano_fabricacao'] ?? '-' }} / {{ $veiculo['ano_modelo'] ?? '-' }}</td>
            </tr>
            <tr>
                <th>Chassi</th>
                <td>{{ $veiculo['chassi'] ?? '-' }}</td>
            </tr>
            <tr>
                <th>Cor</th>
                <td>{{ $veiculo['cor'] ?? '-' }}</td>
            </tr>
            <tr>
                <th>Combustível</th>
                <td>{{ $veiculo['combustivel'] ?? '-' }}</td>
            </tr>
            <tr>
                <th>Proprietário</th>
                <td>{{ $veiculo['proprietario_nome'] ?? '-' }}</td>
            </tr>
            <tr>
                <th>Documento Proprietário</th>
                <td>{{ $veiculo['proprietario_documento'] ?? '-' }}</td>
            </tr>
        </table>

        <form action="{{ route('veiculo.pdf-chassi') }}" method="POST">
            @csrf
            <input type="hidden" name="veiculo" value='@json($veiculo)'>
            <input type="hidden" name="chassi" value="{{ $chassi }}">
            <button type="submit" class="btn btn-success mt-3">Gerar PDF</button>
        </form>
    @else
        <div class="alert alert-warning mt-3">Nenhum dado encontrado para o chassi informado.</div>
    @endif
</div>
@endsection
