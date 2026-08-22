<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alerta de Expiração do Plano</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 50px auto; background-color: #fff; border-radius: 10px; padding: 30px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h1 { color: #333; }
        p { font-size: 16px; color: #555; line-height: 1.5; }
        a.button { display: inline-block; padding: 12px 20px; margin-top: 20px; background-color: #007bff; color: #fff; text-decoration: none; border-radius: 5px; }
        a.button:hover { background-color: #0056b3; }
        .footer { margin-top: 30px; font-size: 12px; color: #999; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Olá {{ $empresa->nome_fantasia }}! 👋</h1>
        <p>
            Seu plano atual expira em <strong>{{ \Carbon\Carbon::parse($planoEmpresa->expiracao)->format('d/m/Y') }}</strong> 
            (faltam <strong>{{ $diasRestantes }} dias</strong>).
        </p>
        <p>
            Para garantir a continuidade do serviço, recomendamos renová-lo o quanto antes.
        </p>
        <a href="https://wa.me/5598984437313" class="button">Renovar Plano</a>
        <p class="footer">
            Caso já tenha renovado, ignore este email. Agradecemos a sua parceria! 😊
        </p>
    </div>
</body>
</html>
