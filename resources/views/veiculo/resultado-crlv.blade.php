@extends('default.layout', ['title' => 'Resultado CRLV - API Brasil'])

@section('content')
<div class="container mt-5">
    <h2 class="mb-4">Resultado da Consulta CRLV</h2>

    <p><strong>Mensagem:</strong> {{ $mensagem }}</p>
    <p><strong>Saldo restante:</strong> {{ $balance }}</p>

    @if(!empty($veiculo))
        <div class="card mb-3">
            <div class="card-header"><strong>Dados do Veículo</strong></div>
            <div class="card-body">
                <table class="table table-bordered table-sm">
                    <tr>
                        <th>Placa</th>
                        <td>{{ $veiculo['placa'] ?? '' }}</td>
                        <th>UF</th>
                        <td>{{ $uf ?? '' }}</td>
                    </tr>
                    <tr>
                        <th>Marca / Modelo</th>
                        <td>{{ $veiculo['marca_modelo'] ?? '' }}</td>
                        <th>Ano / Modelo</th>
                        <td>{{ $veiculo['ano_fabricacao'] ?? '' }} / {{ $veiculo['ano_modelo'] ?? '' }}</td>
                    </tr>
                    <tr>
                        <th>Chassi</th>
                        <td>{{ $veiculo['chassi'] ?? '' }}</td>
                        <th>Motor</th>
                        <td>{{ $veiculo['motor'] ?? '' }}</td>
                    </tr>
                    <tr>
                        <th>Cor</th>
                        <td>{{ $veiculo['cor_veiculo'] ?? '' }}</td>
                        <th>Combustível</th>
                        <td>{{ $veiculo['combustivel'] ?? '' }}</td>
                    </tr>
                    <tr>
                        <th>Renavam</th>
                        <td>{{ $veiculo['renavam'] ?? '' }}</td>
                        <th>Município</th>
                        <td>{{ $veiculo['municipio'] ?? '' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header"><strong>Proprietário</strong></div>
            <div class="card-body">
                <table class="table table-bordered table-sm">
                    <tr>
                        <th>Nome</th>
                        <td>{{ $veiculo['proprietario_nome'] ?? '' }}</td>
                    </tr>
                    <tr>
                        <th>Documento (CPF/CNPJ)</th>
                        <td>{{ $veiculo['proprietario_documento'] ?? '' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        @if(!empty($veiculo['crlv_image_base64']))
            <div class="card mb-3">
                <div class="card-header"><strong>CRLV</strong></div>
                <div class="card-body text-center">
                    <img src="data:image/png;base64,{{ $veiculo['crlv_image_base64'] }}" class="img-fluid" alt="CRLV">
                </div>
            </div>
        @endif

        <form action="{{ route('veiculo.pdf-crlv') }}" method="POST">
            @csrf
            <input type="hidden" name="veiculo" value='@json($veiculo)'>
            <input type="hidden" name="placa" value="{{ $placa }}">
            <input type="hidden" name="uf" value="{{ $uf }}">
            <button type="submit" class="btn btn-success mt-3">Gerar PDF</button>
        </form>

    @else
        <div class="alert alert-warning">Nenhum dado encontrado.</div>
    @endif
</div>
@endsection
