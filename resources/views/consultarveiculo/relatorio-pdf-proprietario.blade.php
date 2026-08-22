<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Consulta Proprietário - {{ $placa }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        h2 { text-align: center; margin-bottom: 20px; }
    </style>
</head>
<body>
    <h2>Consulta de Veículo e Proprietário</h2>

    <h3>Dados do Veículo</h3>
    <table>
        <tbody>
        @foreach($veiculo as $campo => $valor)
            @if(!is_array($valor))
                <tr>
                    <th>{{ ucfirst(str_replace('_',' ',$campo)) }}</th>
                    <td>{{ $valor ?: '-' }}</td>
                </tr>
            @endif
        @endforeach
        </tbody>
    </table>

    <h3>Dados do Proprietário</h3>
    <table>
        <tbody>
        @foreach($proprietario as $campo => $valor)
            @if(!is_array($valor))
                <tr>
                    <th>{{ ucfirst(str_replace('_',' ',$campo)) }}</th>
                    <td>{{ $valor ?: '-' }}</td>
                </tr>
            @endif
        @endforeach
        </tbody>
    </table>
</body>
</html>
