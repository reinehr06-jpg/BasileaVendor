<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $evento->titulo }} — Checkout</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --primary: #581c87;
            --primary-hover: #4c1d95;
            --bg: #f8fafc;
            --surface: #ffffff;
            --text: #1e293b;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --success: #10b981;
            --danger: #ef4444;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
        body { background: var(--bg); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .container { max-width: 500px; width: 100%; }
        .card { background: var(--surface); border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.08); overflow: hidden; }
        .card-header { background: linear-gradient(135deg, var(--primary), #3b0764); color: white; padding: 28px; text-align: center; }
        .card-header h1 { font-size: 1.3rem; font-weight: 700; margin-bottom: 8px; }
        .card-header p { font-size: 0.85rem; opacity: 0.8; }
        .card-body { padding: 28px; }
        .price { text-align: center; margin-bottom: 24px; }
        .price .amount { font-size: 2.2rem; font-weight: 800; color: var(--primary); }
        .price .label { font-size: 0.8rem; color: var(--text-muted); margin-top: 4px; }
        .vagas { display: flex; align-items: center; justify-content: center; gap: 8px; margin-bottom: 24px; padding: 12px; background: #f0fdf4; border-radius: 8px; font-size: 0.85rem; color: #166534; font-weight: 600; }
        .vagas i { font-size: 1rem; }
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 4px; color: var(--text); }
        .form-control { width: 100%; padding: 12px 14px; border: 1.5px solid var(--border); border-radius: 8px; font-size: 0.9rem; transition: border-color 0.2s; }
        .form-control:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(88,28,135,0.1); }
        .payment-options { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 8px; margin-bottom: 20px; }
        .payment-option { text-align: center; padding: 14px 8px; border: 1.5px solid var(--border); border-radius: 10px; cursor: pointer; transition: all 0.2s; }
        .payment-option:hover { border-color: var(--primary); }
        .payment-option.selected { border-color: var(--primary); background: rgba(88,28,135,0.05); }
        .payment-option input { display: none; }
        .payment-option i { font-size: 1.3rem; color: var(--primary); display: block; margin-bottom: 6px; }
        .payment-option span { font-size: 0.75rem; font-weight: 600; color: var(--text); }
        .btn-pay { width: 100%; padding: 14px; background: var(--primary); color: white; border: none; border-radius: 10px; font-size: 1rem; font-weight: 700; cursor: pointer; transition: background 0.2s; }
        .btn-pay:hover { background: var(--primary-hover); }
        .error { background: #fef2f2; color: #991b1b; padding: 12px; border-radius: 8px; font-size: 0.85rem; margin-bottom: 16px; }
        .error i { margin-right: 6px; }
        .branding { text-align: center; margin-top: 16px; font-size: 0.75rem; color: var(--text-muted); }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h1>{{ $evento->titulo }}</h1>
                @if($evento->descricao)
                    <p>{{ $evento->descricao }}</p>
                @endif
            </div>
            <div class="card-body">
                <div class="price">
                    <div class="amount">R$ {{ number_format($evento->valor, 2, ',', '.') }}</div>
                    <div class="label">Valor do evento</div>
                </div>

                <div class="vagas">
                    <i class="fas fa-ticket"></i>
                    {{ $evento->vagasRestantes() }} {{ $evento->vagasRestantes() === 1 ? 'vaga restante' : 'vagas restantes' }}
                </div>

                @if($errors->any())
                <div class="error">
                    <i class="fas fa-exclamation-circle"></i>
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
                @endif

                <form action="{{ url("/co/evento/{$evento->slug}/pay") }}" method="POST" id="checkoutForm">
                    @csrf
                    <div class="form-group">
                        <label>Nome completo</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required placeholder="Seu nome">
                    </div>
                    <div class="form-group">
                        <label>E-mail</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" required placeholder="seu@email.com">
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        <div class="form-group">
                            <label>CPF/CNPJ</label>
                            <input type="text" name="document" class="form-control" value="{{ old('document') }}" required placeholder="000.000.000-00" id="docInput">
                        </div>
                        <div class="form-group">
                            <label>Telefone</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="(00) 00000-0000">
                        </div>
                    </div>

                    <label style="font-size:0.8rem; font-weight:600; display:block; margin-bottom:8px;">Forma de Pagamento</label>
                    <div class="payment-options">
                        <label class="payment-option selected" onclick="selectPayment(this)">
                            <input type="radio" name="billing_type" value="PIX" checked>
                            <i class="fas fa-qrcode"></i>
                            <span>PIX</span>
                        </label>
                        <label class="payment-option" onclick="selectPayment(this)">
                            <input type="radio" name="billing_type" value="BOLETO">
                            <i class="fas fa-barcode"></i>
                            <span>Boleto</span>
                        </label>
                        <label class="payment-option" onclick="selectPayment(this)">
                            <input type="radio" name="billing_type" value="CREDIT_CARD">
                            <i class="fas fa-credit-card"></i>
                            <span>Cartão</span>
                        </label>
                    </div>

                    <button type="submit" class="btn-pay">
                        <i class="fas fa-lock"></i> Pagar Agora
                    </button>
                </form>

                <div class="branding">
                    Pagamento processado com segurança via Asaas
                </div>
            </div>
        </div>
    </div>

    <script>
        function selectPayment(el) {
            document.querySelectorAll('.payment-option').forEach(o => o.classList.remove('selected'));
            el.classList.add('selected');
            el.querySelector('input').checked = true;
        }

        document.getElementById('docInput').addEventListener('input', function(e) {
            let v = e.target.value.replace(/\D/g, '');
            if (v.length <= 11) {
                v = v.replace(/(\d{3})(\d)/, '$1.$2');
                v = v.replace(/(\d{3})(\d)/, '$1.$2');
                v = v.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            } else {
                v = v.replace(/^(\d{2})(\d)/, '$1.$2');
                v = v.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
                v = v.replace(/\.(\d{3})(\d)/, '.$1/$2');
                v = v.replace(/(\d{4})(\d)/, '$1-$2');
            }
            e.target.value = v;
        });
    </script>
</body>
</html>
