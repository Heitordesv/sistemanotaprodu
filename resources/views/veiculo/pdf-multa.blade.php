<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Multas - Placa {{ $data['placa'] ?? '-' }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h2, h3 { text-align: center; margin-bottom: 10px; }
        .section { margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 5px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>

    <h2>Consulta de Multas</h2>
    <h3>Placa: {{ $data['placa'] ?? '-' }}</h3>

    {{-- Usuário --}}
    <div class="section">
        <strong>Usuário:</strong><br>
        Nome: {{ $user['first_name'] ?? '-' }}<br>
        Email: {{ $user['email'] ?? '-' }}<br>
        Celular: {{ $user['cellphone'] ?? '-' }}<br>
        Notificação ativa: {{ ($user['notification'] ?? '') == 'yes' ? 'Sim' : 'Não' }}<br>
    </div>

    {{-- Info da API --}}
    <div class="section">
        <strong>Informações da API:</strong><br>
        Saldo: {{ $balance ?? '0' }}<br>
        Mensagem: {{ $message ?? '' }}<br>
        Homolog: {{ $homolog ? 'Sim' : 'Não' }}<br>
        Status Retorno: {{ $data['status_retorno']['descricao'] ?? '-' }}<br>
    </div>

    {{-- Ocorrências / Multas --}}
    <div class="section">
        <strong>Ocorrências ({{ $data['quantidade_ocorrencias'] ?? 0 }}):</strong>
        @if(!empty($data['ocorrencias']))
            <table>
                <thead>
                    <tr>
                        <th>Descrição</th>
                        <th>Vencimento</th>
                        <th>Valor (R$)</th>
                        <th>Status</th>
                        <th>Boleto / PDF</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['ocorrencias'] as $ocorrencia)
                        <tr>
                            <td>{{ $ocorrencia['descricao'] ?? '-' }}</td>
                            <td>{{ $ocorrencia['data_vencimento'] ?? '-' }}</td>
                            <td>{{ $ocorrencia['total'] ?? '0,00' }}</td>
                            <td>{{ $ocorrencia['status_pgto'] ?? '-' }}</td>
                            <td>
                                @if(!empty($data['pdf']))
                                    <a href="{{ $data['pdf'] }}" target="_blank">Abrir PDF</a>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>Nenhuma ocorrência encontrada.</p>
        @endif
    </div>

</body>
</html>
