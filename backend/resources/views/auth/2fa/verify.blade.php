<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificação 2FA - Basileia Vendas</title>
    <link rel="icon" type="image/png" href="https://basileia.global/wp-content/uploads/2026/01/cropped-basileia-favicon-32x32.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: linear-gradient(135deg, #3B0764 0%, #4C1D95 50%, #7c3aed 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .twofa-card { background: white; border-radius: 24px; padding: 48px 40px; max-width: 460px; width: 100%; text-align: center; box-shadow: 0 25px 60px rgba(0,0,0,0.5); animation: slideUp 0.4s ease-out; }
        @keyframes slideUp { from { opacity: 0; transform: translateY(30px) scale(0.95); } to { opacity: 1; transform: translateY(0) scale(1); } }
        .twofa-icon { width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, #7c3aed, #4C1D95); color: white; display: flex; align-items: center; justify-content: center; font-size: 2rem; margin: 0 auto 24px; box-shadow: 0 8px 24px rgba(76,29,149,0.3); }
        .twofa-card h2 { font-size: 1.4rem; font-weight: 800; color: #1e1b4b; margin-bottom: 8px; }
        .twofa-card p { color: #6b7280; font-size: 0.92rem; margin-bottom: 28px; line-height: 1.6; }
        .twofa-input { width: 100%; max-width: 240px; padding: 16px; border: 2px solid #e5e7eb; border-radius: 12px; font-size: 1.6rem; font-weight: 700; text-align: center; letter-spacing: 10px; outline: none; transition: all 0.2s; display: block; margin: 0 auto 8px; }
        .twofa-input:focus { border-color: #7c3aed; box-shadow: 0 0 0 4px rgba(124,58,237,0.12); }
        .twofa-btn { width: 100%; max-width: 240px; padding: 16px; background: linear-gradient(135deg, #7c3aed, #4C1D95); color: white; border: none; border-radius: 12px; font-size: 1rem; font-weight: 700; cursor: pointer; margin: 16px auto 0; display: flex; align-items: center; justify-content: center; gap: 8px; transition: all 0.2s; }
        .twofa-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(76,29,149,0.4); }
        .error-msg { color: #ef4444; font-size: 0.82rem; margin-top: 8px; }
        .logout-btn { background: none; border: none; color: #a1a1b5; font-size: 0.85rem; cursor: pointer; text-decoration: underline; margin-top: 20px; padding: 8px; display: inline-flex; align-items: center; gap: 6px; transition: color 0.2s; }
        .logout-btn:hover { color: #6b7280; }
        @media (max-width: 480px) {
            .twofa-card { padding: 36px 24px; }
        }
    </style>
</head>
<body>
    <div class="twofa-card">
        <div class="twofa-icon"><i class="fas fa-shield-halved"></i></div>
        <h2>Verificação em Duas Etapas</h2>
        <p>Digite o código de 6 dígitos do seu aplicativo autenticador</p>

        <form method="POST" action="{{ route('2fa.verify.post') }}">
            @csrf
            <input type="text" name="code" class="twofa-input" maxlength="6" pattern="[0-9]{6}" inputmode="numeric" autofocus placeholder="000000" required>
            @error('code')
            <div class="error-msg"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
            @enderror
            <button type="submit" class="twofa-btn"><i class="fas fa-check"></i> Verificar</button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="logout-btn">
                <i class="fas fa-right-from-bracket"></i> Sair
            </button>
        </form>
    </div>
</body>
</html>
