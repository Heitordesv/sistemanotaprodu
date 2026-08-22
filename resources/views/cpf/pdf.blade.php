<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Consulta CPF - {{ $cpf }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h2 { text-align: center; }
        .box { border:1px solid #000; padding:10px; margin-top:10px; }
    </style>
</head>
<body>
    <h2>Resultado da Consulta de CPF</h2>
    <p><strong>CPF:</strong> {{ $cpf }}</p>

    <div class="box">
        <pre>{{ json_encode($dados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
    </div>
</body>
</html>
