<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Relatório Débitos</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h2 { text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table, th, td { border: 1px solid #000; }
        th, td { padding: 8px; text-align: left; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; }
    </style>
</head>
<body>
    <h2>Consulta de Débitos por CPF</h2>

    @if(isset($data['error']) && $data['error'])
        <p><strong>Erro:</strong> {{ $data['message'] ?? 'Erro na consulta' }}</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>Campo</th>
                    <th>Valor</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $key => $value)
                    <tr>
                        <td>{{ $key }}</td>
                        <td>
                            @if(is_array($value))
                                {{ json_encode($value, JSON_UNESCAPED_UNICODE) }}
                            @else
                                {{ $value }}
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">
        Relatório gerado automaticamente em {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>
