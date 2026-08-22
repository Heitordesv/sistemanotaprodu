<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>{{ $empresa->nome_fantasia ?? 'NFeNotas System' }}</title>
</head>
<body style="font-family: Arial, sans-serif; background-color:#f0f2f5; padding:20px;">

    <div style="max-width:600px; margin:auto; background:white; border-radius:10px; overflow:hidden; box-shadow:0 0 10px rgba(0,0,0,0.1);">

        <!-- Cabeçalho com logo -->
        <div style="background-color:#fff; padding:20px; text-align:center;">
            <img src="https://sistema.nfenotas.com.br/logos/logonfenotas.png" alt="Logo" style="max-height:60px;">
        </div>

        <!-- Conteúdo -->
        <div style="padding:25px; color:#333;">
            <h2 style="color:#1a73e8;">Olá, {{ $empresa->razao_social }}!</h2>

            <p style="font-size:16px; line-height:1.6;">
                {!! nl2br(e($mensagem)) !!}
            </p>

            <!-- Botão com WhatsApp da nossa empresa -->
            <div style="text-align:center; margin:30px 0;">
                <a href="https://wa.me/5598984437313" target="_blank"
                   style="background-color:#25D366; color:white; text-decoration:none; padding:12px 25px; border-radius:5px; font-weight:bold;">
                    Suporte via WhatsApp
                </a>
            </div>

            <!-- Dados do usuário que enviou -->
            <div style="margin-top:20px; padding:15px; background:#f7f7f7; border-radius:5px; font-size:14px; color:#555;">
                <p><strong>Enviado por:</strong> Heitor Bezerra</p>
            </div>

        </div>

        <!-- Rodapé -->
        <div style="background-color:#f0f2f5; padding:15px; text-align:center; font-size:12px; color:#777;">
            Enviado automaticamente por <strong>{{ $empresa->nome_fantasia ?? 'NFeNotas System' }}</strong><br>
            Não responda este e-mail diretamente.
        </div>

    </div>

</body>
</html>
