<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cobrança Pendente</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <h2>Olá {{ $clienteNome }}!</h2>
    <p>Você possui uma fatura pendente no valor de <strong>R$ {{ $valor }}</strong>, com vencimento em <strong>{{ $dataVencimento }}</strong>.</p>
    <p>Referência: <strong>{{ $referencia }}</strong></p>
    <p>Para evitar qualquer interrupção no seu acesso ao sistema, realize o pagamento o quanto antes:</p>
    <p><a href="{{ $linkPagamento }}" style="background-color: #4CAF50; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Pagar Agora</a></p>
    <p>Obrigado pela atenção!</p>
</body>
</html>
