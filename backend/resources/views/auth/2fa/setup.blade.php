<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurar 2FA - Basileia Vendas</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: linear-gradient(135deg, #3B0764 0%, #4C1D95 50%, #7c3aed 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }

        .overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.9); z-index: 1000; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(8px); }
        .overlay.hidden { display: none; }

        .modal { background: white; border-radius: 24px; padding: 48px 40px; max-width: 460px; width: 100%; text-align: center; box-shadow: 0 25px 60px rgba(0,0,0,0.5); animation: slideUp 0.4s ease-out; }
        @keyframes slideUp { from { opacity: 0; transform: translateY(30px) scale(0.95); } to { opacity: 1; transform: translateY(0) scale(1); } }

        .modal .shield { width: 88px; height: 88px; border-radius: 50%; background: linear-gradient(135deg, #fef3c7, #fde68a); display: flex; align-items: center; justify-content: center; margin: 0 auto 24px; font-size: 2.2rem; box-shadow: 0 8px 24px rgba(251,191,36,0.3); }
        .modal h1 { font-size: 1.5rem; font-weight: 800; color: #1e1b4b; margin-bottom: 12px; }
        .modal .msg { color: #6b7280; font-size: 0.95rem; line-height: 1.7; margin-bottom: 20px; }
        .modal .msg strong { color: #4C1D95; }
        .modal .info-box { background: #fef3c7; border: 1px solid #fde68a; border-radius: 12px; padding: 14px 18px; font-size: 0.85rem; color: #92400e; margin-bottom: 28px; display: flex; align-items: center; gap: 8px; text-align: left; }
        .modal .info-box i { color: #f59e0b; font-size: 1rem; flex-shrink: 0; }

        .btn-primary { background: linear-gradient(135deg, #7c3aed, #4C1D95); color: white; border: none; padding: 16px 40px; border-radius: 14px; font-size: 1rem; font-weight: 700; cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center; gap: 8px; width: 100%; justify-content: center; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(76,29,149,0.4); }

        /* Form section */
        .setup-section { display: none; max-width: 480px; width: 100%; animation: slideUp 0.4s ease-out; }
        .setup-section.show { display: block; }

        .setup-card { background: white; border-radius: 20px; padding: 36px; box-shadow: 0 20px 50px rgba(0,0,0,0.3); }
        .setup-card .header { text-align: center; margin-bottom: 28px; }
        .setup-card .header .shield-sm { width: 56px; height: 56px; border-radius: 50%; background: linear-gradient(135deg, #ede9fe, #ddd6fe); display: flex; align-items: center; justify-content: center; margin: 0 auto 14px; font-size: 1.4rem; }
        .setup-card .header h2 { font-size: 1.3rem; font-weight: 800; color: #1e1b4b; margin-bottom: 6px; }
        .setup-card .header p { color: #a1a1b5; font-size: 0.88rem; }

        .step { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 18px; padding: 14px; background: #f8f7ff; border-radius: 12px; }
        .step .num { width: 28px; height: 28px; border-radius: 50%; background: linear-gradient(135deg, #7c3aed, #4C1D95); color: white; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 700; flex-shrink: 0; }
        .step .content { flex: 1; }
        .step .content strong { display: block; color: #1e1b4b; font-size: 0.88rem; margin-bottom: 4px; }
        .step .content span { color: #a1a1b5; font-size: 0.82rem; line-height: 1.5; }

        .secret-box { background: #f8f7ff; border: 2px solid #7c3aed; border-radius: 12px; padding: 16px; font-family: 'SF Mono', 'Fira Code', monospace; font-size: 1.15rem; font-weight: 700; letter-spacing: 3px; text-align: center; color: #4C1D95; margin: 8px 0; word-break: break-all; user-select: all; }

        .code-input { width: 100%; max-width: 220px; padding: 16px; border: 2px solid #e5e7eb; border-radius: 12px; font-size: 1.6rem; font-weight: 700; text-align: center; letter-spacing: 10px; outline: none; transition: all 0.2s; box-sizing: border-box; display: block; margin: 0 auto 16px; }
        .code-input:focus { border-color: #7c3aed; box-shadow: 0 0 0 4px rgba(124,58,237,0.12); }

        .error-msg { color: #ef4444; font-size: 0.82rem; text-align: center; margin-bottom: 12px; }

        @media (max-width: 480px) {
            .modal { padding: 36px 24px; }
            .setup-card { padding: 28px 20px; }
        }
    </style>
</head>
<body>

{{-- OVERLAY DE BLOQUEIO --}}
<div class="overlay" id="overlay">
    <div class="modal">
        <div class="shield">🔒</div>
        <h1>Acesso Bloqueado</h1>
        <p class="msg">Para proteger sua conta e os dados do sistema, é <strong>obrigatório</strong> configurar a autenticação em duas etapas (2FA). Sem isso, você não poderá acessar nenhuma funcionalidade.</p>
        <div class="info-box">
            <i class="fas fa-info-circle"></i>
            <span>Você precisará de um app autenticador no celular, como Google Authenticator, Authy ou Microsoft Authenticator.</span>
        </div>
        <button class="btn-primary" onclick="document.getElementById('overlay').classList.add('hidden'); document.getElementById('setup').classList.add('show');">
            <i class="fas fa-check"></i> Entendi, vou configurar agora
        </button>
    </div>
</div>

{{-- FORMULÁRIO DE SETUP --}}
<div class="setup-section" id="setup">
    <div class="setup-card">
        <div class="header">
            <div class="shield-sm"><i class="fas fa-shield-halved" style="color: #4C1D95;"></i></div>
            <h2>Configurar 2FA</h2>
            <p>Siga os passos abaixo para ativar a autenticação em duas etapas</p>
        </div>

        <div class="step">
            <span class="num">1</span>
            <div class="content">
                <strong>Instale um app autenticador</strong>
                <span>Google Authenticator, Authy, Microsoft Authenticator, etc.</span>
            </div>
        </div>

        <div class="step">
            <span class="num">2</span>
            <div class="content">
                <strong>Adicione a chave manualmente no app</strong>
                <div class="secret-box">{{ $user->two_factor_secret }}</div>
                <span style="color: #a1a1b5; font-size: 0.78rem;">Copie esta chave e cole no app como "entrada manual"</span>
            </div>
        </div>

        <div class="step">
            <span class="num">3</span>
            <div class="content">
                <strong>Digite o código de 6 dígitos gerado pelo app</strong>
            </div>
        </div>

        <form method="POST" action="{{ $enableRoute }}">
            @csrf
            <input type="text" name="code" class="code-input" maxlength="6" pattern="[0-9]{6}" inputmode="numeric" autofocus placeholder="000000" required>
            @error('code')
            <div class="error-msg"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
            @enderror
            <button type="submit" class="btn-primary"><i class="fas fa-check"></i> Ativar 2FA</button>
        </form>
    </div>
</div>

</body>
</html>
