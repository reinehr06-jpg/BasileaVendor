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
            height: 320px;
            overflow-y: auto;
            padding: 24px;
            background: var(--surface-hover);
            font-size: 0.9rem;
            line-height: 1.7;
            color: var(--text);
            transition: all 0.3s;
        }
        .terms-box::-webkit-scrollbar { width: 6px; }
        .terms-box::-webkit-scrollbar-track { background: transparent; }
        .terms-box::-webkit-scrollbar-thumb { background: #CBD5E1; border-radius: 3px; }
        
        .progress-container {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 20px 0;
        }
        .progress-bar {
            flex: 1;
            height: 6px;
            background: var(--border);
            border-radius: 3px;
            overflow: hidden;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--primary) 0%, var(--primary-light) 100%);
            border-radius: 3px;
            transition: width 0.3s;
            width: 0%;
        }
        .progress-text {
            font-size: 0.8rem;
            color: var(--text-muted);
            min-width: 45px;
            text-align: right;
        }
        
        .checkbox-label {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            padding: 18px;
            border: 2px solid var(--border);
            border-radius: 14px;
            cursor: pointer;
            transition: all 0.2s;
            margin-bottom: 24px;
        }
        .checkbox-label:hover { border-color: var(--primary-light); background: rgba(76, 29, 149, 0.02); }
        .checkbox-label.disabled { opacity: 0.5; cursor: not-allowed; }
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
        }
        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
        }
        .btn-primary:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 10px 30px rgba(76, 29, 149, 0.3); }
        .btn-primary:disabled { background: #E2E8F0; color: #94A3B8; cursor: not-allowed; }
        
        .terms-content h2 { color: var(--primary); font-size: 1.1rem; margin: 20px 0 10px; }
        .terms-content h2:first-child { margin-top: 0; }
        .terms-content p { margin-bottom: 12px; }
        .terms-content ul { margin-left: 20px; margin-bottom: 12px; }
        .terms-content li { margin-bottom: 6px; }
    </style>
</head>
<body>
<div class="card">
    <div class="card-header">
        <h1>📋 Termos de Uso</h1>
        <p>Leia até o final e aceite para acessar o sistema</p>
    </div>
    
    <div class="card-body">
        <div class="version-badge">
            <i class="fas fa-file-contract"></i>
            Versão {{ $termo->versao }}
        </div>
        
        <div class="terms-box" id="termsBox" onscroll="onScroll(this)">
            <div class="terms-content">
                {!! $termo->conteudo_html !!}
            </div>
        </div>
        
        <div class="progress-container">
            <div class="progress-bar">
                <div class="progress-fill" id="progressFill"></div>
            </div>
            <span class="progress-text" id="progressText">0%</span>
        </div>
        
        <form action="{{ route('onboarding.termos.aceitar') }}" method="POST" id="aceiteForm">
            @csrf
            <input type="hidden" name="terms_document_id" value="{{ $termo->id }}">
            
            <label class="checkbox-label disabled" id="checkboxLabel">
                <input type="checkbox" name="termos_aceitos" value="1" id="cbTermos" disabled>
                <span>Li integralmente e aceito os Termos de Uso e o Contrato de Utilização do Basileia Vendas. Estou ciente de que este aceite fica registrado com meu IP e data/hora.</span>
            </label>
            
            <button type="submit" id="btnAceitar" class="btn btn-primary" disabled>
                <span>Aceitar e Continuar</span>
                <i class="fas fa-arrow-right"></i>
            </button>
        </form>
    </div>
</div>

<script>
let podeAceitar = false;

function onScroll(element) {
    const scrollTop = element.scrollTop;
    const scrollHeight = element.scrollHeight - element.clientHeight;
    const percentage = scrollHeight > 0 ? Math.round((scrollTop / scrollHeight) * 100) : 100;
    
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
    
    label.classList.remove('disabled');
    checkbox.disabled = false;
    
    checkbox.addEventListener('change', function() {
        botao.disabled = !this.checked;
        if (this.checked) {
            botao.innerHTML = '<span>Aceitar e Continuar</span> <i class="fas fa-arrow-right"></i>';
        } else {
            botao.innerHTML = '<span>Aceite os termos para continuar</span>';
        }
    });
}
</script>
</body>
</html>