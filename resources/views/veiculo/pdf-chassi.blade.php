<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Consulta Veículo - Chassi {{ $chassi }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        h2 { text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background: #f4f4f4; }
    </style>
</head>
<body>
    <h2>Consulta Oficial do Veículo</h2>

    <table>
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
</body>
</html>
