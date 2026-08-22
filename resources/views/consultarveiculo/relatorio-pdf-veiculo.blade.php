<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Relatório Veículo {{ $placa }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #333; }
        h2, h3, h4 { margin: 8px 0; color: #222; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { border: 1px solid #555; padding: 6px; text-align: left; vertical-align: top; }
        th { background-color: #f0f0f0; }
        .section { margin-bottom: 25px; }
        hr { border: 0; border-top: 1px solid #ccc; margin: 15px 0; }
    </style>
</head>
<body>

<h2>Relatório Completo do Veículo</h2>
<h3>Placa: {{ $placa }}</h3>



{{-- Informações do Veículo --}}
@isset($veiculo)
<div class="section">
    <h4>Informações do Veículo</h4>
    <table>
        <tr><th>Placa</th><td>{{ $veiculo['placa'] ?? '-' }}</td></tr>
        <tr><th>Alerta</th><td>{{ $veiculo['alerta'] ?? '-' }}</td></tr>
        <tr><th>Quantidade de Ocorrências</th><td>{{ $veiculo['quantidade_ocorrencias'] ?? '0' }}</td></tr>
        <tr><th>Total de Ocorrências</th><td>{{ $veiculo['quantidade_ocorrencias_total'] ?? '0' }}</td></tr>
    </table>
</div>

{{-- Registros detalhados --}}
@if(!empty($veiculo['registros']))
<div class="section">
    <h4>Registros Detalhados</h4>
    @foreach($veiculo['registros'] as $i => $registro)
    <div class="registro">
        <h5>Registro #{{ $i+1 }}</h5>
        <table>
            @foreach($registro as $chave => $valor)
            <tr>
                <th>{{ ucfirst(str_replace('_', ' ', $chave)) }}</th>
                <td>{{ $valor !== '' ? $valor : '-' }}</td>
            </tr>
            @endforeach
        </table>
    </div>
    <hr>
    @endforeach
</div>
@endif
@endisset

<p>Relatório gerado em: {{ date('d/m/Y H:i') }}</p>

</body>
</html>
