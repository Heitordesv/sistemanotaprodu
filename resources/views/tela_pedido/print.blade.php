<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Pedido #{{ $pedido->id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 0;
            text-align: center;
        }
        h1 {
            margin-bottom: 10px;
        }
        p {
            font-size: 16px;
            margin: 5px 0;
        }
        .container {
            width: 80%;
            margin: auto;
            padding: 20px;
            border: 1px solid #000;
            text-align: left;
        }
        .highlight {
            font-weight: bold;
        }
        .print-button {
            display: none;
        }
        @media print {
            .print-button {
                display: none;
            }
        }
    </style>
</head>
<body onload="autoPrint()">
    <div class="container">
        <h1>Detalhes do Pedido #{{ $pedido->id }}</h1>
        
        @if($pedido && $config)
            <p><span class="highlight">Status:</span> {{ $pedido->status }}</p>
            <p><span class="highlight">Cliente:</span> {{ $pedido->cliente->nome }}</p>
            <p><span class="highlight">Total:</span> R$ {{ number_format($pedido->total, 2, ',', '.') }}</p>
        @else
            <p style="color: red; font-weight: bold;">Erro: Pedido ou configuração não encontrados.</p>
        @endif

        <!-- Botão de Impressão manual (caso precise) -->
        <button class="print-button" onclick="window.print()">🖨️ Imprimir</button>
    </div>

    <script>
        function autoPrint() {
            if ({{ $pedido ? 'true' : 'false' }} && {{ $config ? 'true' : 'false' }}) {
                window.print();
                setTimeout(() => {
                    window.close(); // Fecha a aba após a impressão
                }, 1000);
            }
        }
    </script>
</body>
</html>
