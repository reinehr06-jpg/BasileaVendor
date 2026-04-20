<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurar 2FA - Basileia Vendor</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #1e1b4b 0%, #4C1D95 50%, #7c3aed 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 24px;
            padding: 40px;
            max-width: 480px;
            width: 100%;
            box-shadow: 0 25px 60px rgba(0,0,0,0.4);
            animation: slideUp 0.4s ease-out;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .header { text-align: center; margin-bottom: 32px; }
        .header .shield {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #fef3c7, #fde68a);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2rem;
            box-shadow: 0 8px 24px rgba(251,191,36,0.3);
        }
        .header h1 { font-size: 1.5rem; font-weight: 800; color: #1e1b4b; margin-bottom: 8px; }
        .header p { color: #6b7280; font-size: 0.9rem; line-height: 1.6; }

        .step {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 20px;
            padding: 16px;
            background: #f8f7ff;
            border-radius: 12px;
        }
        .step .num {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: linear-gradient(135deg, #7c3aed, #4C1D95);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 700;
            flex-shrink: 0;
        }
        .step .content { flex: 1; }
        .step .content strong { display: block; color: #1e1b4b; font-size: 0.9rem; margin-bottom: 4px; }
        .step .content p { color: #6b7280; font-size: 0.85rem; }

        .qr-container {
            text-align: center;
            margin: 16px 0;
            padding: 16px;
            background: white;
            border-radius: 12px;
            display: inline-block;
        }
        .secret-box {
            background: #f8f7ff;
            border: 2px solid #7c3aed;
            border-radius: 12px;
            padding: 14px;
            font-family: 'SF Mono', 'Fira Code', monospace;
            font-size: 1.1rem;
            font-weight: 700;
            letter-spacing: 2px;
            text-align: center;
            color: #4C1D95;
            cursor: pointer;
            word-break: break-all;
        }
        .secret-box:hover { background: #ede9fe; }

        .code-input {
            width: 100%;
            max-width: 200px;
            padding: 14px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 1.4rem;
            font-weight: 700;
            text-align: center;
            letter-spacing: 8px;
            outline: none;
            transition: all 0.2s;
            display: block;
            margin: 0 auto 16px;
        }
        .code-input:focus { border-color: #7c3aed; box-shadow: 0 0 0 4px rgba(124,58,237,0.12); }

        .btn-primary {
            background: linear-gradient(135deg, #7c3aed, #4C1D95);
            color: white;
            border: none;
            padding: 16px 40px;
            border-radius: 14px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            width: 100%;
            justify-content: center;
        }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(76,29,149,0.4); }

        .error-msg {
            color: #ef4444;
            font-size: 0.85rem;
            text-align: center;
            margin-bottom: 16px;
            padding: 12px;
            background: #fee2e2;
            border-radius: 8px;
        }

        .success-msg {
            color: #16a34a;
            font-size: 0.85rem;
            text-align: center;
            margin-bottom: 16px;
            padding: 12px;
            background: #dcfce7;
            border-radius: 8px;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #6b7280;
            font-size: 0.85rem;
            text-decoration: none;
        }
        .back-link:hover { color: #4C1D95; }

        .device-info {
            background: #fef3c7;
            border: 1px solid #fde68a;
            border-radius: 10px;
            padding: 12px 16px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 0.9rem;
            color: #92400e;
        }

        @media (max-width: 520px) {
            .container { padding: 28px 20px; }
            .qr-container img { width: 160px; height: 160px; }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <div class="shield"><i class="fas fa-shield-halved" style="color: #4C1D95;"></i></div>
        <h1>Configurar 2FA</h1>
        <p>Dispositivo: <strong>{{ $deviceName }}</strong><br>Escaneie o QR code ou insira a chave manualmente</p>
    </div>

    <div class="device-info">
        <i class="fas fa-info-circle"></i> Usuário: {{ $user->name }} ({{ $user->email }})
    </div>

    <div class="step">
        <span class="num">1</span>
        <div class="content">
            <strong>Instale um app autenticador</strong>
            <p>Google Authenticator, Authy, Microsoft Authenticator, etc.</p>
        </div>
    </div>

    <div class="step">
        <span class="num">2</span>
        <div class="content">
            <strong>Escaneie o QR code</strong>
            <div class="qr-container">
                {!! $qrCode !!}
            </div>
            <p style="text-align: center; font-size: 0.78rem; color: #9ca3af; margin-bottom: 12px;">Ou use a chave manual:</p>
            <div class="secret-box" onclick="copySecret()" title="Clique para copiar">
                {{ $secret }}
            </div>
        </div>
    </div>

    <div class="step">
        <span class="num">3</span>
        <div class="content">
            <strong>Digite o código de 6 dígitos</strong>
            <p>O código muda a cada 30 segundos</p>
        </div>
    </div>

    @if(session('error'))
    <div class="error-msg"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
    @endif

    @if($errors->any())
    <div class="error-msg">
        <i class="fas fa-exclamation-circle"></i> {{ $errors->first() }}
    </div>
    @endif

    @if(session('success'))
    <div class="success-msg"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
    @endif

    @if($isSetup)
    <form method="POST" action="{{ route('master.configuracoes.seguranca.2fa.enable') }}">
        @csrf
        <input type="hidden" name="user_id" value="{{ $user->id }}">
        <input type="text" name="code" class="code-input" maxlength="6" pattern="[0-9]{6}" inputmode="numeric" autofocus placeholder="000000" required>
        <button type="submit" class="btn-primary">
            <i class="fas fa-check"></i> Ativar 2FA
        </button>
    </form>
    @else
    <div class="success-msg">
        <i class="fas fa-check-circle"></i> Dispositivo adicionado com sucesso! O usuário pode usar este código no login.
    </div>
    @endif

    <a href="{{ route('master.configuracoes', ['tab' => 'seguranca']) }}" class="back-link">
        <i class="fas fa-arrow-left"></i> Voltar para Segurança
    </a>
</div>

<script>
function copySecret() {
    const secret = '{{ $secret }}';
    navigator.clipboard.writeText(secret).then(() => {
        alert('Chave copiada!');
    }).catch(() => {
        alert('Erro ao copiar. Selecione e copie manualmente.');
    });
}
</script>

</body>
</html>
