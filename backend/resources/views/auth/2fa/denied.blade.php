<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acesso Negado</title>
    <style>
        :root {
            --bg: #f4f6f8;
            --card: #ffffff;
            --text: #1f2937;
            --muted: #6b7280;
            --border: #e5e7eb;
            --danger: #b91c1c;
            --button: #111827;
            --button-hover: #000000;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: radial-gradient(circle at top, #ffffff 0%, var(--bg) 60%);
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text);
            padding: 20px;
        }

        .card {
            width: 100%;
            max-width: 460px;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 28px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);
            text-align: center;
        }

        .status {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 58px;
            height: 58px;
            border-radius: 999px;
            background: #fee2e2;
            color: var(--danger);
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 14px;
        }

        h1 {
            margin: 0;
            font-size: 1.5rem;
            letter-spacing: 0.2px;
        }

        p {
            margin: 12px 0 0;
            color: var(--muted);
            line-height: 1.5;
            font-size: 0.98rem;
        }

        .action {
            margin-top: 22px;
            display: inline-block;
            padding: 10px 18px;
            border-radius: 9px;
            color: #fff;
            background: var(--button);
            text-decoration: none;
            font-weight: 600;
            transition: background 0.2s ease;
        }

        .action:hover {
            background: var(--button-hover);
        }
    </style>
</head>
<body>
    <main class="card">
        <div class="status">!</div>
        <h1>Acesso negado</h1>
        <p>Esta area so pode ser acessada pelo fluxo de login com token valido.</p>
        <a class="action" href="{{ route('login.generate') }}">Ir para o login</a>
    </main>
</body>
</html>
