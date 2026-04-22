<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>2FA Bloqueado - Basileia Vendas</title>
    <link rel="icon" type="image/png" href="https://basileia.global/wp-content/uploads/2026/01/cropped-basileia-favicon-32x32.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: linear-gradient(135deg, #3B0764 0%, #4C1D95 50%, #7c3aed 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .locked-card { background: white; border-radius: 24px; padding: 48px 40px; max-width: 460px; width: 100%; text-align: center; box-shadow: 0 25px 60px rgba(0,0,0,0.5); animation: slideUp 0.4s ease-out; }
        @keyframes slideUp { from { opacity: 0; transform: translateY(30px) scale(0.95); } to { opacity: 1; transform: translateY(0) scale(1); } }
        .locked-icon { width: 88px; height: 88px; border-radius: 50%; background: linear-gradient(135deg, #fee2e2, #fecaca); display: flex; align-items: center; justify-content: center; margin: 0 auto 24px; font-size: 2.2rem; }
        .locked-card h2 { font-size: 1.4rem; font-weight: 800; color: #991b1b; margin-bottom: 12px; }
        .locked-card p { color: #6b7280; font-size: 0.95rem; line-height: 1.6; margin-bottom: 16px; }
        .timer { background: #fef2f2; border: 2px solid #fecaca; border-radius: 14px; padding: 20px; margin: 24px 0; font-size: 2.2rem; font-weight: 800; color: #dc2626; font-family: 'SF Mono', 'Fira Code', monospace; letter-spacing: 2px; }
        .btn-back { background: linear-gradient(135deg, #ef4444, #dc2626); color: white; border: none; padding: 16px 32px; border-radius: 14px; font-size: 1rem; font-weight: 700; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; transition: all 0.2s; }
        .btn-back:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(239,68,68,0.4); }
        @media (max-width: 480px) {
            .locked-card { padding: 36px 24px; }
        }
    </style>
</head>
<body>
    <div class="locked-card">
        <div class="locked-icon">🔒</div>
        <h2>Autenticação Bloqueada</h2>
        <p>Muitas tentativas incorretas de código 2FA. Sua conta foi temporariamente bloqueada por segurança.</p>
        <div class="timer" id="timer">{{ $minutes }}:00</div>
        <p style="font-size: 0.82rem; color: #a1a1b5;">Tente novamente após o tempo expirar.</p>
        <form method="POST" action="{{ route('logout') }}" style="margin-top: 20px;">
            @csrf
            <button type="submit" class="btn-back"><i class="fas fa-right-from-bracket"></i> Voltar ao Login</button>
        </form>
    </div>

    <script>
    (function() {
        var totalSeconds = {{ $minutes }} * 60;
        var timerEl = document.getElementById('timer');
        var interval = setInterval(function() {
            totalSeconds--;
            if (totalSeconds <= 0) {
                clearInterval(interval);
                window.location.reload();
                return;
            }
            var m = Math.floor(totalSeconds / 60);
            var s = totalSeconds % 60;
            timerEl.textContent = m + ':' + (s < 10 ? '0' : '') + s;
        }, 1000);
    })();
    </script>
</body>
</html>
