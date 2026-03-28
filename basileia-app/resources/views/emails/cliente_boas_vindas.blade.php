<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <style>
        body { margin: 0; padding: 0; background: #f4f4f8; font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, sans-serif; }
        .wrapper { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #6d28d9, #4c1d95); padding: 44px 40px; text-align: center; }
        .header h1 { color: #fff; margin: 0; font-size: 24px; font-weight: 800; }
        .header p { color: #ddd6fe; margin: 10px 0 0; font-size: 15px; }
        .body { padding: 36px 40px; }
        .body p { color: #374151; font-size: 15px; line-height: 1.7; margin: 0 0 16px; }
        .divider { border: none; border-top: 1px solid #e5e7eb; margin: 28px 0; }
        .buttons-title { font-size: 16px; font-weight: 700; color: #1f2937; margin-bottom: 20px; text-align: center; }
        .btn-group { display: flex; flex-direction: column; gap: 14px; }
        .btn { display: block; text-align: center; text-decoration: none; padding: 16px 24px; border-radius: 10px; font-size: 15px; font-weight: 700; }
        .btn-login { background: linear-gradient(135deg, #7c3aed, #6d28d9); color: #fff; }
        .btn-videos { background: linear-gradient(135deg, #2563eb, #1d4ed8); color: #fff; }
        .btn-suporte { background: linear-gradient(135deg, #16a34a, #15803d); color: #fff; }
        .tip { background: #fefce8; border: 1px solid #fde68a; border-radius: 8px; padding: 14px 18px; margin-top: 24px; font-size: 13px; color: #92400e; }
        .info-box { background: #f5f3ff; border-left: 4px solid #7c3aed; border-radius: 8px; padding: 16px 20px; margin: 20px 0; }
        .info-row { display: flex; justify-content: space-between; padding: 6px 0; font-size: 14px; color: #374151; }
        .info-row span:first-child { font-weight: 600; color: #6d28d9; }
        .footer { background: #f9fafb; border-top: 1px solid #e5e7eb; padding: 20px 40px; text-align: center; }
        .footer p { color: #9ca3af; font-size: 12px; margin: 0; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1>🎉 Bem-vindo(a) ao Basiléia Global!</h1>
            <p>Sua conta foi criada automaticamente.</p>
        </div>
        <div class="body">
            <p>Olá, <strong>{{ $cliente->nome_igreja ?? $cliente->nome ?? 'Cliente' }}</strong>!</p>
            <p>Seu pagamento foi confirmado e sua conta no Basiléia Church já está ativa e pronta para uso. Confira os dados da sua compra:</p>

            <div class="info-box">
                @php
                    $ultimaVenda = $cliente->vendas()->where('status', 'PAGO')->latest()->first();
                @endphp
                @if($ultimaVenda)
                <div class="info-row">
                    <span>Plano</span>
                    <span>{{ $ultimaVenda->plano ?? 'N/A' }}</span>
                </div>
                <div class="info-row">
                    <span>Valor Pago</span>
                    <span>R$ {{ number_format($ultimaVenda->valor_final ?? $ultimaVenda->valor, 2, ',', '.') }}</span>
                </div>
                @endif
                <div class="info-row">
                    <span>Status</span>
                    <span style="color: #22c55e; font-weight: 600;">Ativo ✅</span>
                </div>
            </div>

            <hr class="divider" />

            <p class="buttons-title">🚀 Acesse agora mesmo</p>

            <div class="btn-group">
                <a href="https://dash.basileia.global/login" class="btn btn-login">
                    🔐 Acessar Minha Conta
                </a>
                <a href="#" class="btn btn-videos">
                    🎬 Vídeos de Implementação
                </a>
                <a href="https://wa.me/5511934924430?text=Olá,%20preciso%20de%20suporte%20-%20sou%20cliente%20Basiléia%20Global" class="btn btn-suporte">
                    💬 Falar com o Suporte
                </a>
            </div>

            <div class="tip">
                💡 <strong>Dica:</strong> Sua conta já foi criada com os dados cadastrados. Acesse com o e-mail <strong>{{ $cliente->email }}</strong> e a senha provisória que foi enviada para você.
            </div>
        </div>
        <div class="footer">
            <p>Basiléia Global © {{ date('Y') }} — Todos os direitos reservados.</p>
        </div>
    </div>
</body>
</html>
