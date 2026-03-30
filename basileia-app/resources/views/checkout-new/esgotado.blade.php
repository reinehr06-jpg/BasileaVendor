<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Promoção Esgotada</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root { --primary: #581c87; --bg: #f8fafc; --surface: #ffffff; --text: #1e293b; --text-muted: #64748b; --danger: #ef4444; }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
        body { background: var(--bg); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .container { max-width: 480px; width: 100%; text-align: center; }
        .card { background: var(--surface); border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.08); padding: 48px 32px; }
        .icon { font-size: 4rem; color: var(--danger); margin-bottom: 20px; }
        h1 { font-size: 1.5rem; font-weight: 800; color: var(--text); margin-bottom: 12px; }
        .desc { font-size: 0.95rem; color: var(--text-muted); line-height: 1.5; margin-bottom: 28px; }
        .evento-name { font-weight: 700; color: var(--primary); }
        .btn-whatsapp { display: inline-flex; align-items: center; gap: 10px; padding: 14px 28px; background: #25d366; color: white; text-decoration: none; border-radius: 10px; font-size: 1rem; font-weight: 700; transition: background 0.2s; }
        .btn-whatsapp:hover { background: #128c7e; }
        .branding { margin-top: 24px; font-size: 0.75rem; color: var(--text-muted); }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="icon">
                <i class="fas fa-clock-rotate-left"></i>
            </div>
            <h1>Promoção Esgotada</h1>
            <p class="desc">
                O evento <strong class="evento-name">{{ $evento->titulo }}</strong> atingiu o limite de vagas.
                @if($evento->whatsapp_vendedor)
                    Entre em contato com nosso vendedor para verificar se há vagas extras.
                @endif
            </p>

            @if($evento->whatsapp_vendedor)
            <a href="https://wa.me/{{ $evento->whatsapp_vendedor }}?text=Olá! Tenho interesse no evento '{{ $evento->titulo }}' mas as vagas esgotaram. Há possibilidade de conseguir uma vaga?" target="_blank" class="btn-whatsapp">
                <i class="fab fa-whatsapp" style="font-size:1.3rem;"></i>
                Falar com Vendedor
            </a>
            @endif

            <div class="branding">Basiléia Checkout</div>
        </div>
    </div>
</body>
</html>
