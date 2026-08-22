<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Relatório Recall - Veículo {{ $placa }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #333; line-height: 1.4; }
        h2, h3, h4 { margin: 10px 0; color: #444; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { border: 1px solid #555; padding: 6px; text-align: left; }
        th { background-color: #f0f0f0; }
        .section { margin-bottom: 20px; }
        .highlight { color: #d9534f; font-weight: bold; }
    </style>
</head>
<body>

    <h2>Relatório Recall do Veículo</h2>
    <h3>Placa: {{ $placa }}</h3>


    {{-- Informações do veículo --}}
    @isset($veiculo)
    <div class="section">
        <h4>Informações do Veículo</h4>
        <table>
            <tr><th>Status</th><td>{{ $veiculo['status'] ? 'OK' : 'Nenhum recall' }}</td></tr>
            <tr><th>Mensagem</th><td>{{ $veiculo['msg'] ?? '-' }}</td></tr>
        </table>
    </div>
    @endisset

    <p>Relatório gerado em: {{ date('d/m/Y H:i') }}</p>

</body>
</html>
