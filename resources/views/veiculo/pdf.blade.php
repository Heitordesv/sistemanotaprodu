<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Consulta Veículo - {{ $veiculo['placa'] ?? '' }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; margin: 20px; }
        h2 { text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f0f0f0; }
        .section-title { background-color: #007bff; color: white; padding: 5px; margin-top: 20px; }
    </style>
</head>
<body>
    <h2>Relatório de Veículo - {{ $veiculo['placa'] ?? '' }}</h2>

    {{-- Dados do Veículo --}}
    <div class="section-title">Dados do Veículo</div>
    <table>
        <tr>
            <th>Placa</th>
            <td>{{ $veiculo['placa'] ?? '' }}</td>
            <th>Renavam</th>
            <td>{{ $veiculo['renavam'] ?? '' }}</td>
        </tr>
        <tr>
            <th>Marca / Modelo</th>
            <td>{{ $veiculo['modelo_marca'] ?? '' }}</td>
            <th>Cor</th>
            <td>{{ $veiculo['cor'] ?? '' }}</td>
        </tr>
        <tr>
            <th>Ano Fabricação</th>
            <td>{{ $veiculo['ano_fabricacao'] ?? '' }}</td>
            <th>Ano Modelo</th>
            <td>{{ $veiculo['ano_modelo'] ?? '' }}</td>
        </tr>
        <tr>
            <th>Combustível</th>
            <td>{{ $veiculo['combustivel'] ?? '' }}</td>
            <th>Potência</th>
            <td>{{ $veiculo['potencia'] ?? '' }}</td>
        </tr>
        <tr>
            <th>Tipo de Veículo</th>
            <td>{{ $veiculo['tipo_veiculo'] ?? '' }}</td>
            <th>Espécie</th>
            <td>{{ $veiculo['especie'] ?? '' }}</td>
        </tr>
        <tr>
            <th>Capacidade Passageiros</th>
            <td>{{ $veiculo['capacidade_passageiros'] ?? '' }}</td>
            <th>Capacidade de Carga (kg)</th>
            <td>{{ $veiculo['capacidade_carga_kg'] ?? '' }}</td>
        </tr>
        <tr>
            <th>Eixos</th>
            <td>{{ $veiculo['eixos'] ?? '' }}</td>
            <th>Tanque (L)</th>
            <td>{{ $veiculo['tanque_litros'] ?? '' }}</td>
        </tr>
        <tr>
            <th>Cidade Placa</th>
            <td>{{ $veiculo['cidade_placa'] ?? '' }}</td>
            <th>Estado Placa</th>
            <td>{{ $veiculo['estado_placa'] ?? '' }}</td>
        </tr>
        <tr>
            <th>Origem</th>
            <td>{{ $veiculo['origem'] ?? '' }}</td>
            <th>Chassi</th>
            <td>{{ $veiculo['chassi'] ?? '' }}</td>
        </tr>
    </table>

    {{-- Dados do Proprietário --}}
    <div class="section-title">Proprietário / Cliente</div>
    @if(isset($veiculo['cliente']))
    <table>
        <tr>
            <th>Nome</th>
            <td>{{ $veiculo['cliente']['nome'] ?? '' }}</td>
            <th>CPF / CNPJ</th>
            <td>{{ $veiculo['cliente']['documento'] ?? '' }}</td>
        </tr>
        <tr>
            <th>Telefone</th>
            <td>{{ implode(', ', $veiculo['cliente']['telefones'] ?? []) }}</td>
            <th>E-mails</th>
            <td>{{ implode(', ', $veiculo['cliente']['emails'] ?? []) }}</td>
        </tr>
        <tr>
            <th>Endereço</th>
            <td colspan="3">
                @if(isset($veiculo['cliente']['enderecos'][0]))
                    {{ $veiculo['cliente']['enderecos'][0]['rua'] ?? '' }},
                    {{ $veiculo['cliente']['enderecos'][0]['numero'] ?? '' }},
                    {{ $veiculo['cliente']['enderecos'][0]['bairro'] ?? '' }},
                    {{ $veiculo['cliente']['enderecos'][0]['cidade'] ?? '' }} -
                    {{ $veiculo['cliente']['enderecos'][0]['estado'] ?? '' }},
                    CEP: {{ $veiculo['cliente']['enderecos'][0]['cep'] ?? '' }}
                @endif
            </td>
        </tr>
    </table>
    @endif

</body>
</html>
