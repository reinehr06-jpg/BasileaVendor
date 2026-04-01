<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <style>
        body { margin: 0; padding: 0; background: #f4f4f8; font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, sans-serif; }
        .wrapper { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #dc2626, #991b1b); padding: 36px 40px; text-align: center; }
        .header h1 { color: #fff; margin: 0; font-size: 22px; font-weight: 800; }
        .header p { color: #fecaca; margin: 8px 0 0; font-size: 14px; }
        .badge { display: inline-block; background: #fff; color: #dc2626; padding: 6px 18px; border-radius: 999px; font-size: 13px; font-weight: 700; margin-top: 16px; }
        .body { padding: 36px 40px; }
        .body p { color: #374151; font-size: 15px; line-height: 1.7; margin: 0 0 16px; }
        .info-box { background: #fef2f2; border-left: 4px solid #dc2626; border-radius: 8px; padding: 16px 20px; margin: 20px 0; font-size: 14px; color: #7f1d1d; }
        .btn-group { display: flex; flex-direction: column; gap: 12px; margin-top: 8px; }
        .btn { display: block; text-align: center; text-decoration: none; padding: 16px 24px; border-radius: 10px; font-size: 15px; font-weight: 700; }
        .btn-pagar { background: linear-gradient(135deg, #16a34a, #15803d); color: #fff; }
        .btn-suporte { background: linear-gradient(135deg, #6d28d9, #4c1d95); color: #fff; }
        .footer { background: #f9fafb; border-top: 1px solid #e5e7eb; padding: 20px 40px; text-align: center; }
        .footer p { color: #9ca3af; font-size: 12px; margin: 0; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1>Basiléia Global</h1>
            <p>Aviso importante sobre sua conta</p>
            <span class="badge">⚠️ ACESSO SUSPENSO</span>
        </div>

        <div class="body">
            <p>Olá, <strong>{{ $cliente->nome_igreja ?? $cliente->nome ?? 'Cliente' }}</strong>!</p>
            <p>
                Identificamos que há um pagamento em aberto na sua conta.
                Por esse motivo, seu acesso ao Basiléia Global foi
                <strong>temporariamente suspenso</strong>.
            </p>

            <div class="info-box">
                <strong>O que acontece agora?</strong><br><br>
                Assim que o pagamento for confirmado, seu acesso será
                <strong>reativado automaticamente</strong> — sem precisar
                entrar em contato com o suporte.
            </div>

            <p>Para regularizar sua situação, use os botões abaixo:</p>

            <div class="btn-group">
                <a href="https://dash.basileia.global/login" class="btn btn-pagar">
                    💳 Acessar minha conta e pagar
                </a>
                <a href="https://wa.me/5511934924430?text=Olá,%20preciso%20de%20ajuda%20com%20minha%20conta%20suspensa" class="btn btn-suporte">
                    💬 Falar com o Suporte
                </a>
            </div>
        </div>

        <div class="footer">
            <p>Basiléia Global © {{ date('Y') }} — Este email foi gerado automaticamente.</p>
        </div>
    </div>
</body>
</html>
