<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Folha de Pagamento</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10pt; margin:0; padding:0; }
        .container { padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #ccc; padding-bottom: 10px; }
        .header img { max-width: 150px; display:block; margin:0 auto 10px auto; }
        .header h1 { font-size: 16pt; margin:0; color:#333; }
        .header p { margin:0; font-size: 10pt; color:#666; }
        .filters { margin-bottom: 20px; font-size:9pt; color:#555; border-bottom:1px solid #eee; padding-bottom:10px; }
        table { width:100%; border-collapse:collapse; margin-bottom:20px; }
        th, td { border:1px solid #ddd; padding:6px; text-align:left; }
        th { background-color:#f4f4f4; text-transform:uppercase; font-size:9pt; }
        td { font-size:9pt; }
        .text-right { text-align:right; }
        .grand-total { margin-top:20px; text-align:right; font-size:12pt; font-weight:bold; padding:10px; border-top:2px solid #333; }
    </style>
</head>
<body>
<div class="container">

    <!-- CABEÇALHO -->
    <div class="header">
        @php 
            $config = App\Models\ConfigNota::configStatic();
            $logoPath = public_path('uploads/configEmitente/' . ($config->logo ?? ''));
            $logoDefault = public_path('logos/logonfenotas.png');
        @endphp

        @if(!empty($config->logo) && file_exists($logoPath))
            <img src="data:image/png;base64,{{ base64_encode(file_get_contents($logoPath)) }}">
        @else
            <img src="data:image/png;base64,{{ base64_encode(file_get_contents($logoDefault)) }}">
        @endif

        <h1>Relatório de Folha de Pagamento - Resumo Mensal</h1>
        <p>{{ $empresa->nome_fantasia ?? 'Nome da Empresa' }} | CNPJ: {{ $empresa->cpf_cnpj ?? 'Não Informado' }}</p>
        <p>Gerado em: {{ date('d/m/Y H:i:s') }}</p>
    </div>

    <!-- FILTROS -->
    <div class="filters">
        Período de Apuração:
        @if($dt_inicio && $dt_fim)
            De {{ \Carbon\Carbon::parse($dt_inicio)->format('d/m/Y') }}
            a {{ \Carbon\Carbon::parse($dt_fim)->format('d/m/Y') }}
        @elseif($dt_inicio)
            A partir de {{ \Carbon\Carbon::parse($dt_inicio)->format('d/m/Y') }}
        @elseif($dt_fim)
            Até {{ \Carbon\Carbon::parse($dt_fim)->format('d/m/Y') }}
        @else
            Todas as Apurações
        @endif
    </div>

    <!-- TABELA -->
    <table>
        <thead>
            <tr>
                <th>Funcionário</th>
                <th>Mês/Ano</th>
                <th class="text-right">Salário Base</th>
                <th class="text-right">Proventos</th>
                <th class="text-right">Descontos</th>
                <th class="text-right">Valor Final</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $item)
                <tr>
                    <td>{{ $item->funcionario->nome ?? 'Funcionário Excluído' }}</td>
                    <td>{{ str_pad($item->mes,2,'0',STR_PAD_LEFT) }}/{{ $item->ano }}</td>
                    <td class="text-right">R$ {{ number_format($item->funcionario->salario ?? 0,2,',','.') }}</td>
                    <td class="text-right">R$ {{ number_format($item->proventos ?? 0,2,',','.') }}</td>
                    <td class="text-right">R$ {{ number_format($item->descontos ?? 0,2,',','.') }}</td>
                    <td class="text-right"><strong>R$ {{ number_format($item->valor_final ?? 0,2,',','.') }}</strong></td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align:center;">Nenhuma apuração encontrada.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- TOTAL GERAL -->
    <div class="grand-total">
        TOTAL APURADO: R$ {{ number_format($totalPagar, 2, ',', '.') }}
    </div>

</div>
</body>
</html>