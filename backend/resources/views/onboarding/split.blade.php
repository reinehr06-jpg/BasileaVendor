<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Split de Pagamento — Basileia Vendas</title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #059669;
            --primary-dark: #047857;
            --primary-light: #34D399;
            --surface: #FFFFFF;
            --surface-hover: #F8FAFC;
            --border: #E2E8F0;
            --text: #1E293B;
            --text-muted: #64748B;
            --warning: #F59E0B;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, var(--primary-dark) 0%, #059669 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .card {
            background: var(--surface);
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4);
            width: 100%;
            max-width: 560px;
            overflow: hidden;
            animation: slideUp 0.5s ease-out;
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .card-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            padding: 32px;
            color: white;
        }
        .card-header h1 { font-size: 1.5rem; font-weight: 800; margin-bottom: 4px; }
        .card-header p { opacity: 0.85; font-size: 0.9rem; }
        .card-body { padding: 32px; }
        
        .warning-box {
            display: flex;
            gap: 14px;
            padding: 18px;
            background: #FEF3C7;
            border: 1px solid #FCD34D;
            border-radius: 14px;
            margin-bottom: 28px;
        }
        .warning-box .icon { font-size: 1.5rem; }
        .warning-box .text { font-size: 0.9rem; color: #92400E; }
        
        .benefit-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 16px;
            background: var(--surface-hover);
            border-radius: 12px;
            margin-bottom: 12px;
            border: 1px solid var(--border);
        }
        .benefit-item .icon { 
            width: 42px; height: 42px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.1rem;
        }
        .benefit-item .label { font-weight: 600; color: var(--text); }
        .benefit-item .desc { font-size: 0.8rem; color: var(--text-muted); margin-top: 2px; }
        
        .checkbox-label {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            padding: 18px;
            border: 2px solid var(--border);
            border-radius: 14px;
            cursor: pointer;
            transition: all 0.2s;
            margin: 24px 0;
        }
        .checkbox-label:hover { border-color: var(--primary-light); background: rgba(5, 150, 105, 0.02); }
        .checkbox-label input { margin-top: 3px; width: 20px; height: 20px; accent-color: var(--primary); }
        .checkbox-label span { font-size: 0.9rem; color: var(--text); }
        
        .btn {
            width: 100%;
            padding: 16px 24px;
            border: none;
            border-radius: 14px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-bottom: 12px;
        }
        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
        }
        .btn-primary:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 10px 30px rgba(5, 150, 105, 0.3); }
        .btn-primary:disabled { background: #E2E8F0; color: #94A3B8; cursor: not-allowed; }
        
        .btn-secondary {
            background: transparent;
            color: var(--text-muted);
            font-weight: 600;
            padding: 12px;
        }
        .btn-secondary:hover { color: var(--text); }
    </style>
</head>
<body>
<div class="card">
    <div class="card-header">
        <h1>💳 Split de Pagamento</h1>
        <p>Configure o repasse automático de comissões</p>
    </div>
    
    <div class="card-body">
        <div class="warning-box">
            <span class="icon">🔒</span>
            <div class="text">
                <strong>Funcionalidades bloqueadas</strong> até que o Split de Pagamento seja configurado. Isso garante que seus repasses funcionem corretamente.
            </div>
        </div>
        
        <h3 style="margin-bottom: 16px; color: var(--text);">O que é o Split?</h3>
        
        <div class="benefit-item">
            <div class="icon">⚡</div>
            <div>
                <div class="label">Repasse Automático</div>
                <div class="desc">Comissões enviadas sem ação manual</div>
            </div>
        </div>
        
        <div class="benefit-item">
            <div class="icon">📊</div>
            <div>
                <div class="label">Rastreável</div>
                <div class="desc">Cada repasse fica registrado no histórico</div>
            </div>
        </div>
        
        <div class="benefit-item">
            <div class="icon">🔐</div>
            <div>
                <div class="label">Seguro</div>
                <div class="desc">Valores processados conforme regras do admin</div>
            </div>
        </div>
        
        <form action="{{ route('onboarding.split.ativar') }}" method="POST">
            @csrf
            <label class="checkbox-label">
                <input type="checkbox" name="confirmar_split" value="1" required>
                <span>Confirmo que desejo ativar o Split de Pagamento e concordo com as regras de repasse configuradas pelo administrador.</span>
            </label>
            
            <button type="submit" class="btn btn-primary">
                <span>Ativar Split e Acessar o Sistema</span>
                <i class="fas fa-check"></i>
            </button>
        </form>
        
        <form action="{{ route('onboarding.split.pular') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-secondary">
                Pular por agora (funcionalidades de repasse ficam inativas)
            </button>
        </form>
    </div>
</div>
</body>
</html>