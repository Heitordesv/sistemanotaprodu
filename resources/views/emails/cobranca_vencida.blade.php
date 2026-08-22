<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>💰 Cobrança Pendente</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <h2>Olá {{ $clienteNome }}! 👋</h2>

    <p>Sua fatura está {{ $statusTexto ?? '' }}:</p>

    <ul>
        <li><strong>Valor:</strong> R$ {{ $valor }}</li>
        <li><strong>Vencimento:</strong> {{ $dataVencimento }}</li>
        <li><strong>Referência:</strong> {{ $referencia }}</li>
    </ul>

    <p>Para evitar qualquer interrupção no seu acesso ao sistema, você pode:</p>

    @if(!empty($boletoLink))
        <p>
            <a href="{{ $boletoLink }}" 
               style="background-color: #007bff; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;">
               📥 Baixar Boleto
            </a>
        </p>
    @endif

    <p>
        <a href="{{ $linkPagamento }}" 
           style="background-color: #28a745; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 5px;">
           💳 Pagar Agora
        </a>
    </p>

    <p>Obrigado por sua atenção! 🙏</p>
</body>
</html>
