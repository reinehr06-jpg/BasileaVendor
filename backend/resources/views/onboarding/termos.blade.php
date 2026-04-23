<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Termos de Uso — Basileia Vendas</title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --primary-soft: #eef2ff;
            --surface: #ffffff;
            --bg: #F1F5F9;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --radius: 16px;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body { 
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, #4c1d95 0%, #7c3aed 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: var(--text-main);
        }

        .card {
            background: var(--surface);
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.3);
            width: 95%;
            max-width: 1100px;
            overflow: hidden;
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(40px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .card-header {
            background: linear-gradient(135deg, #4c1d95 0%, #7c3aed 100%);
            padding: 30px 40px;
            color: white;
            position: relative;
        }

        .card-header .badge {
            position: absolute;
            top: 30px;
            right: 40px;
            background: rgba(255,255,255,0.2);
            padding: 6px 16px;
            border-radius: 30px;
            font-size: 0.75rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
            backdrop-filter: blur(4px);
        }

        .card-header h1 { 
            font-size: 1.75rem; 
            font-weight: 800; 
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .card-header p { 
            opacity: 0.9; 
            font-size: 0.95rem; 
            max-width: 400px;
            line-height: 1.5;
        }

        .card-body { padding: 40px; }

        .terms-container {
            position: relative;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            background: #fafafa;
            margin-bottom: 20px;
        }

        .terms-box {
            height: 320px;
            overflow-y: auto;
            padding: 30px 45px;
            font-size: 1.05rem;
            line-height: 1.7;
            color: #334155;
        }

        /* Custom Scrollbar */
        .terms-box::-webkit-scrollbar { width: 6px; }
        .terms-box::-webkit-scrollbar-track { background: transparent; }
        .terms-box::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .terms-box::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        .terms-content h1, .terms-content h2, .terms-content h3 {
            color: var(--primary-dark);
            margin: 24px 0 12px 0;
            font-weight: 800;
        }
        
        .terms-content p { margin-bottom: 16px; }
        .terms-content ul { margin: 0 0 16px 20px; }

        /* Progress Bar Section */
        .progress-wrapper {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 24px;
            padding: 0 5px;
        }
        
        .progress-info {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--primary);
            white-space: nowrap;
        }

        .progress-bar-bg {
            flex-grow: 1;
            height: 8px;
            background: #f1f5f9;
            border-radius: 10px;
            overflow: hidden;
        }

        .progress-bar-fill {
            height: 100%;
            background: var(--primary);
            width: 0%;
            transition: width 0.3s ease;
            border-radius: 10px;
        }

        /* Checkbox Section */
        .checkbox-container {
            display: flex;
            align-items: flex-start;
            gap: 16px;
            padding: 24px;
            border: 2px solid var(--border);
            border-radius: var(--radius);
            cursor: pointer;
            transition: all 0.2s;
            margin-bottom: 24px;
            background: #fff;
        }

        .checkbox-container:hover:not(.disabled) {
            border-color: var(--primary);
            background: var(--primary-soft);
        }

        .checkbox-container.disabled {
            opacity: 0.5;
            cursor: not-allowed;
            background: #f8fafc;
        }

        .checkbox-container input {
            width: 24px;
            height: 24px;
            accent-color: var(--primary);
            margin-top: 2px;
            cursor: inherit;
        }

        .checkbox-container span {
            font-size: 0.95rem;
            font-weight: 600;
            line-height: 1.5;
            color: var(--text-main);
        }
        
        .checkbox-container span strong {
            color: var(--primary-dark);
        }

        /* Action Button */
        .btn-submit {
            width: 100%;
            padding: 18px;
            border-radius: var(--radius);
            border: none;
            background: #e2e8f0;
            color: #94a3b8;
            font-size: 1rem;
            font-weight: 700;
            cursor: not-allowed;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .btn-submit.active {
            background: var(--primary);
            color: white;
            cursor: pointer;
            box-shadow: 0 10px 20px -5px rgba(99, 102, 241, 0.4);
        }

        .btn-submit.active:hover {
            transform: translateY(-2px);
            background: var(--primary-dark);
        }

        .btn-submit.active:active {
            transform: translateY(0);
        }

        #scroll-warning {
            text-align: center;
            font-size: 0.8rem;
            color: #ef4444;
            font-weight: 700;
            margin-top: -10px;
            margin-bottom: 20px;
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
        <div class="badge">
            <i class="fas fa-shield-halved"></i>
            Segurança LGPD
        </div>
        <h1>
            <i class="fas fa-file-signature"></i>
            Contrato e Termos
        </h1>
        <p>Por favor, leia atentamente as condições de uso antes de acessar o painel administrativo.</p>
    </div>

    <div class="card-body">
        @if(session('error'))
        <div style="background: #fee2e2; border: 1px solid #ef4444; color: #b91c1c; padding: 16px; border-radius: 12px; margin-bottom: 24px; display: flex; align-items: center; gap: 12px; font-weight: 700; font-size: 0.9rem;">
            <i class="fas fa-exclamation-circle"></i>
            <span>{{ session('error') }}</span>
        </div>
        @endif

        @if($errors->any())
        <div style="background: #fee2e2; border: 1px solid #ef4444; color: #b91c1c; padding: 16px; border-radius: 12px; margin-bottom: 24px; font-weight: 700; font-size: 0.9rem;">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <i class="fas fa-exclamation-circle"></i>
                <span>Por favor, corrija os seguintes erros:</span>
            </div>
            <ul style="margin-left: 28px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif
        <div class="terms-container">
            <div class="terms-box" id="termsBox" onscroll="handleScroll(this)">
                <div class="terms-content">
                    {!! $termo->conteudo_html !!}
                </div>
            </div>
        </div>

        <div class="progress-wrapper">
            <div class="progress-info">
                <i class="fas fa-book-open"></i>
                <span id="progressText">0% lido</span>
            </div>
            <div class="progress-bar-bg">
                <div class="progress-bar-fill" id="progressFill"></div>
            </div>
        </div>

        <div id="scroll-warning">
            <i class="fas fa-arrow-down"></i>
            Role até o final para habilitar o aceite
        </div>

        <form action="{{ route('onboarding.termos.aceitar') }}" method="POST" id="termsForm">
            @csrf
            <input type="hidden" name="terms_document_id" value="{{ $termo->id }}">
            
            <label class="checkbox-container disabled" id="checkboxLabel">
                <input type="checkbox" name="termos_aceitos" value="1" id="termsCheckbox" disabled>
                <span>Li e concordo integralmente com os <strong>Termos de Uso</strong> e a <strong>Política de Privacidade</strong> descritos acima.</span>
            </label>

            <button type="submit" id="submitBtn" class="btn-submit" disabled>
                <i class="fas fa-lock"></i>
                <span>Aceitar e Acessar Sistema</span>
            </button>
        </form>
    </div>
</div>

<script>
    let hasScrolledToBottom = false;

    // Initial check in case content is small
    window.addEventListener('load', () => {
        handleScroll(document.getElementById('termsBox'));
    });

    function handleScroll(el) {
        const scrollTop = el.scrollTop;
        const scrollHeight = el.scrollHeight - el.clientHeight;
        let percentage = scrollHeight > 0 ? Math.round((scrollTop / scrollHeight) * 100) : 100;
        
        if (percentage > 100) percentage = 100;
        
        document.getElementById('progressFill').style.width = percentage + '%';
        document.getElementById('progressText').textContent = percentage + '% lido';

        if (percentage >= 98 && !hasScrolledToBottom) {
            enableAcceptance();
        }
    }

    function enableAcceptance() {
        hasScrolledToBottom = true;
        
        const label = document.getElementById('checkboxLabel');
        const checkbox = document.getElementById('termsCheckbox');
        const btn = document.getElementById('submitBtn');
        const warning = document.getElementById('scroll-warning');

        label.classList.remove('disabled');
        checkbox.disabled = false;
        warning.style.display = 'none';

        checkbox.addEventListener('change', function() {
            if (this.checked) {
                btn.disabled = false;
                btn.classList.add('active');
                btn.innerHTML = '<i class="fas fa-check-circle"></i> <span>Acessar Painel Agora</span>';
            } else {
                btn.disabled = true;
                btn.classList.remove('active');
                btn.innerHTML = '<i class="fas fa-lock"></i> <span>Aceite os termos para continuar</span>';
            }
        });
    }
</script>

</body>
</html>