<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Consulta CRLV - {{ $veiculo['placa'] ?? '' }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; margin: 20px; }
        h1, h2 { text-align: center; }
        h2 { font-size: 16px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
        th { background-color: #f5f5f5; }
        .section-title { margin-top: 20px; font-size: 14px; text-decoration: underline; }
        .footer { text-align: center; margin-top: 30px; font-size: 10px; color: #777; }
        .crlv-img { margin-top: 10px; text-align: center; }
        .crlv-img img { max-width: 100%; height: auto; }
    </style>
</head>
<body>

<h1>Consulta CRLV</h1>
<h2>Veículo: {{ $veiculo['placa'] ?? '' }} - {{ $veiculo['uf'] ?? '' }}</h2>

<p><strong>Mensagem da API:</strong> {{ $mensagem ?? '' }}</p>
<p><strong>Saldo restante:</strong> R$ {{ $balance ?? 0 }}</p>

@if(!empty($veiculo))
    <div class="section-title">Dados do Veículo</div>
    <table>
        <tr><th>Marca / Modelo</th><td>{{ $veiculo['marca_modelo'] ?? '' }}</td></tr>
        <tr><th>Ano Fabricação</th><td>{{ $veiculo['ano_fabricacao'] ?? '' }}</td></tr>
        <tr><th>Ano Modelo</th><td>{{ $veiculo['ano_modelo'] ?? '' }}</td></tr>
        <tr><th>Chassi</th><td>{{ $veiculo['chassi'] ?? '' }}</td></tr>
        <tr><th>Motor</th><td>{{ $veiculo['motor'] ?? '' }}</td></tr>
        <tr><th>Combustível</th><td>{{ $veiculo['combustivel'] ?? '' }}</td></tr>
        <tr><th>Cor</th><td>{{ $veiculo['cor_veiculo'] ?? '' }}</td></tr>
        <tr><th>Renavam</th><td>{{ $veiculo['renavam'] ?? '' }}</td></tr>
        <tr><th>Município</th><td>{{ $veiculo['municipio'] ?? '' }}</td></tr>
        <tr><th>UF</th><td>{{ $veiculo['uf'] ?? '' }}</td></tr>
    </table>

    <div class="section-title">Proprietário</div>
    <table>
        <tr><th>Nome</th><td>{{ $veiculo['proprietario_nome'] ?? '' }}</td></tr>
        <tr><th>Documento (CPF/CNPJ)</th><td>{{ $veiculo['proprietario_documento'] ?? '' }}</td></tr>
    </table>

    @if(!empty($veiculo['crlv_image_base64']))
        <div class="section-title">CRLV</div>
        <div class="crlv-img">
            <img src="data:image/png;base64,{{ $veiculo['crlv_image_base64'] }}" alt="CRLV">
        </div>
    @elseif(!empty($veiculo['crlv_pdf_base64']))
        <p>PDF do CRLV disponível para download via API.</p>
    @endif

@endif

<div class="footer">
    Consulta gerada via API Brasil - Sistema de Veículos
</div>

</body>
</html>
