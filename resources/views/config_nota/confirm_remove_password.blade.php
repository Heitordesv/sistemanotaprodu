<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Confirmar remoção da senha</title>
</head>
<body>
    <main style="max-width: 620px; margin: 48px auto; padding: 24px; font-family: Arial, sans-serif;">
        <h1 style="font-size: 22px;">Remover senha de autorização?</h1>
        <p>A remoção será aplicada somente à configuração fiscal da empresa autenticada.</p>

        <form method="POST" action="{{ route('configNF.removeSenha.post', $configId) }}">
            @csrf
            <button type="submit" style="padding: 10px 16px; cursor: pointer;">Confirmar remoção</button>
            <a href="{{ route('configNF.index') }}" style="margin-left: 12px;">Cancelar</a>
        </form>
    </main>
</body>
</html>
