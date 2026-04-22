<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Termos de Uso — Basileia Vendas</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --primary: #4C1D95;
            --primary-dark: #3B0764;
            --primary-light: #8B5CF6;
            --surface: #FFFFFF;
            --surface-hover: #F8FAFC;
            --border: #E2E8F0;
            --text: #1E293B;
            --text-muted: #64748B;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, var(--primary-dark) 0%, #5B21B6 100%);
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
            max-width: 680px;
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
        
        .version-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(76, 29, 149, 0.1);
            color: var(--primary);
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-bottom: 20px;
        }
        
        .terms-box {
            border: 2px solid var(--border);
            border-radius: 16px;
            height: 380px;
            overflow-y: auto;
            padding: 30px;
            background: #fdfdfd;
            font-size: 0.95rem;
            line-height: 1.8;
            color: var(--text);
            transition: all 0.3s;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);
        }
        .terms-box::-webkit-scrollbar { width: 8px; }
        .terms-box::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
        .terms-box::-webkit-scrollbar-thumb { background: var(--primary-light); border-radius: 10px; }
        
        .legal-info {
            background: #F1F5F9;
            padding: 12px 20px;
            border-radius: 10px;
            margin-top: 15px;
            font-size: 0.75rem;
            color: var(--text-muted);
            display: flex;
            justify-content: space-between;
            border: 1px solid var(--border);
        }

        .legal-item { display: flex; align-items: center; gap: 6px; }
        .legal-item i { color: var(--primary); }

        .checkbox-label {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            padding: 20px;
            border: 2px solid var(--border);
            border-radius: 14px;
            cursor: pointer;
            transition: all 0.2s;
            margin: 24px 0;
            background: white;
        }
        .checkbox-label:hover:not(.disabled) { border-color: var(--primary); background: rgba(76, 29, 149, 0.02); }
        .checkbox-label.disabled { opacity: 0.5; cursor: not-allowed; background: #f8fafc; }
        .checkbox-label input { margin-top: 3px; width: 22px; height: 22px; accent-color: var(--primary); cursor: inherit; }
        .checkbox-label span { font-size: 0.9rem; color: var(--text); font-weight: 500; }
        
        .scroll-warning {
            text-align: center;
            font-size: 0.85rem;
            color: #ef4444;
            font-weight: 600;
            margin-top: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
    </style>
</head>
<body>
<div class="card">
    <div class="card-header">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
            <h1 style="margin:0;">📋 Contrato e Termos</h1>
            <div style="background: rgba(255,255,255,0.2); padding: 4px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 700;">
                Segurança LGPD
            </div>
        </div>
        <p>Por favor, leia atentamente as condições de uso do sistema.</p>
    </div>
    
    <div class="card-body">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div class="version-badge" style="margin:0;">
                <i class="fas fa-shield-check"></i>
                Versão {{ $termo->versao }}
            </div>
            <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">
                ID Doc: #{{ str_pad($termo->id, 5, '0', STR_PAD_LEFT) }}
            </div>
        </div>
        
        <div class="terms-box" id="termsBox" onscroll="onScroll(this)">
            <div class="terms-content">
                {!! $termo->conteudo_html !!}
            </div>
        </div>
        
        <div id="scrollWarning" class="scroll-warning">
            <i class="fas fa-mouse-pointer"></i>
            <span>Por favor, role até o final para habilitar o aceite.</span>
        </div>

        <div class="progress-container">
            <div class="progress-bar">
                <div class="progress-fill" id="progressFill"></div>
            </div>
            <span class="progress-text" id="progressText">0%</span>
        </div>

        <div class="legal-info">
            <div class="legal-item">
                <i class="fas fa-network-wired"></i>
                <strong>Seu IP:</strong> {{ request()->ip() }}
            </div>
            <div class="legal-item">
                <i class="fas fa-clock"></i>
                <strong>Data/Hora:</strong> {{ now()->format('d/m/Y H:i') }}
            </div>
        </div>
        
        <form action="{{ route('onboarding.termos.aceitar') }}" method="POST" id="aceiteForm">
            @csrf
            <input type="hidden" name="terms_document_id" value="{{ $termo->id }}">
            
            <label class="checkbox-label disabled" id="checkboxLabel">
                <input type="checkbox" name="termos_aceitos" value="1" id="cbTermos" disabled>
                <span>Li e concordo integralmente com os Termos de Uso e a Política de Privacidade descritos acima.</span>
            </label>
            
            <button type="submit" id="btnAceitar" class="btn btn-primary" disabled>
                <span>Aceitar e Acessar Sistema</span>
                <i class="fas fa-lock-open"></i>
            </button>
        </form>
    </div>
</div>

<script>
let podeAceitar = false;

// Trigger scroll check on load in case content is small
window.addEventListener('load', () => {
    onScroll(document.getElementById('termsBox'));
});

function onScroll(element) {
    const scrollTop = element.scrollTop;
    const scrollHeight = element.scrollHeight - element.clientHeight;
    let percentage = scrollHeight > 0 ? Math.round((scrollTop / scrollHeight) * 100) : 100;
    
    if (percentage > 100) percentage = 100;

    document.getElementById('progressFill').style.width = percentage + '%';
    document.getElementById('progressText').textContent = percentage + '%';
    
    if (percentage >= 95 && !podeAceitar) {
        podeAceitar = true;
        liberarAceite();
    }
}

function liberarAceite() {
    const label = document.getElementById('checkboxLabel');
    const checkbox = document.getElementById('cbTermos');
    const botao = document.getElementById('btnAceitar');
    const warning = document.getElementById('scrollWarning');
    
    label.classList.remove('disabled');
    checkbox.disabled = false;
    warning.style.display = 'none';
    
    checkbox.addEventListener('change', function() {
        botao.disabled = !this.checked;
        if (this.checked) {
            botao.innerHTML = '<span>Acessar Dashboard</span> <i class="fas fa-check-circle"></i>';
            botao.style.transform = 'scale(1.02)';
        } else {
            botao.innerHTML = '<span>Aceite os termos para continuar</span>';
            botao.style.transform = 'scale(1)';
        }
    });
}
</script>
</body>
</html>