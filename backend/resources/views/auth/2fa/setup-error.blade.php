<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erro 2FA - Basileia Vendas</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: linear-gradient(135deg, #3B0764 0%, #4C1D95 50%, #7c3aed 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .card { background: white; border-radius: 24px; padding: 48px 40px; max-width: 460px; width: 100%; text-align: center; box-shadow: 0 25px 60px rgba(0,0,0,0.5); }
        .icon { width: 88px; height: 88px; border-radius: 50%; background: linear-gradient(135deg, #fee2e2, #fecaca); display: flex; align-items: center; justify-content: center; margin: 0 auto 24px; font-size: 2.2rem; }
        h1 { font-size: 1.5rem; font-weight: 800; color: #1e1b4b; margin-bottom: 12px; }
        .msg { color: #6b7280; font-size: 0.95rem; line-height: 1.7; margin-bottom: 20px; }
        .debug-box { background: #fef2f2; border: 1px solid #fecaca; border-radius: 12px; padding: 14px 18px; font-size: 0.82rem; color: #991b1b; margin-bottom: 20px; text-align: left; word-break: break-all; font-family: monospace; }
        .btn { display: inline-flex; align-items: center; gap: 8px; padding: 14px 32px; border-radius: 14px; font-size: 1rem; font-weight: 700; cursor: pointer; transition: all 0.2s; text-decoration: none; border: none; }
        .btn-primary { background: linear-gradient(135deg, #7c3aed, #4C1D95); color: white; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(76,29,149,0.4); }
        .btn-secondary { background: #f3f4f6; color: #374151; margin-left: 8px; }
        .btn-secondary:hover { background: #e5e7eb; }
        .actions { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">⚠️</div>
        <h1>Erro ao Configurar 2FA</h1>
        <p class="msg">{{ $message }}</p>

        @if($debug)
        <div class="debug-box">
            <strong>Debug:</strong> {{ $debug }}
        </div>
        @endif

        <div class="actions">
            <a href="{{ route('2fa.setup') }}" class="btn btn-primary">
                <i class="fas fa-redo"></i> Tentar Novamente
            </a>
            <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                @csrf
                <button type="submit" class="btn btn-secondary">
                    <i class="fas fa-right-from-bracket"></i> Sair
                </button>
            </form>
        </div>
    </div>
</body>
</html>
