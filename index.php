<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estamos em manutenção - NFENotas</title>
    <style>
        body {
            font-family: "Poppins", sans-serif;
            background: linear-gradient(135deg, #007bff, #00b4d8);
            color: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            text-align: center;
        }

        h1 {
            font-size: 3em;
            margin-bottom: 0.3em;
        }

        p {
            font-size: 1.2em;
            max-width: 400px;
        }

        .loader {
            border: 5px solid rgba(255, 255, 255, 0.3);
            border-top: 5px solid #fff;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            animation: spin 1s linear infinite;
            margin-top: 30px;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        footer {
            position: absolute;
            bottom: 15px;
            font-size: 0.9em;
            opacity: 0.8;
        }
    </style>
</head>
<body>
    <h1>🚧 Estamos em manutenção</h1>
    <p>Estamos realizando algumas melhorias para oferecer um serviço ainda melhor.<br>
       Voltaremos em breve!</p>

    <div class="loader"></div>

    <footer>© 2025 NFENotas - Todos os direitos reservados</footer>
</body>
</html>
