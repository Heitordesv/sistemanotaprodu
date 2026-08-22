<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Relatório Veicular - {{ $placa }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h2 { text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
        th { background-color: #f0f0f0; }
    </style>
</head>
<body>
    <h2>Relatório do Veículo - {{ $placa }}</h2>
    <table>
        <tr><th>Campo</th><th>Valor</th></tr>

        @foreach($veiculo as $campo => $valor)
            @if(!is_array($valor))
                <tr>
                    <td>{{ ucfirst(str_replace('_',' ',$campo)) }}</td>
                    <td>{{ $valor ?: '-' }}</td>
                </tr>
            @elseif(is_array($valor) && !empty($valor))
                <tr>
                    <td>{{ ucfirst(str_replace('_',' ',$campo)) }}</td>
                    <td>{{ json_encode($valor, JSON_UNESCAPED_UNICODE) }}</td>
                </tr>
            @endif
        @endforeach
    </table>
</body>
</html>
