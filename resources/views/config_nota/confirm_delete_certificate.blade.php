<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Confirmar remoção do certificado</title>
</head>
<body>
    <main style="max-width: 620px; margin: 48px auto; padding: 24px; font-family: Arial, sans-serif;">
        <h1 style="font-size: 22px;">Remover certificado digital?</h1>
        <p>Esta ação remove o certificado configurado desta empresa. Nenhuma outra empresa será alterada.</p>

        <form method="POST" action="{{ route('configNF.deleteCertificado.post') }}">
            @csrf
            <button type="submit" style="padding: 10px 16px; cursor: pointer;">Confirmar remoção</button>
            <a href="{{ route('configNF.index') }}" style="margin-left: 12px;">Cancelar</a>
        </form>
    </main>
</body>
</html>
