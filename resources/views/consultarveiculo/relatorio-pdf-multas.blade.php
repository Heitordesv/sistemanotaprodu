<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Multas do Veículo {{ $placa }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { border: 1px solid #000; padding: 5px; text-align: left; }
        h2, h4 { margin: 0; }
        hr { border: 0; border-top: 1px solid #000; margin: 10px 0; }
    </style>
</head>
<body>
    <h2>Consulta de Multas e Infrações</h2>
    <p><strong>Placa:</strong> {{ $placa }}</p>

    @if(!empty($multas))
        <table>
            <thead>
                <tr>
                    <th>ID Auto Infração</th>
                    <th>Descrição</th>
                    <th>Valor</th>
                    <th>Data Infração</th>
                    <th>Data Cadastro</th>
                    <th>Local</th>
                    <th>Órgão Autuador</th>
                    <th>Exigibilidade</th>
                </tr>
            </thead>
            <tbody>
                @foreach($multas as $multa)
                    <tr>
                        <td>{{ $multa['numeroautoinfracao'] ?? '-' }}</td>
                        <td>{{ $multa['detalhe_cod_infracao'] ?? '-' }}</td>
                        <td>{{ $multa['detalhe_valor_infracao'] ?? '-' }}</td>
                        <td>{{ $multa['detalhe_dt_infracao'] ?? '-' }}</td>
                        <td>{{ $multa['detalhe_cadastramento_infracao'] ?? '-' }}</td>
                        <td>{{ $multa['detalhe_local_infracao'] ?? '-' }}</td>
                        <td>{{ $multa['detalhe_orgao_autuador'] ?? '-' }}</td>
                        <td>{{ $multa['exigibilidade'] ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>Sem multas registradas.</p>
    @endif
</body>
</html>
