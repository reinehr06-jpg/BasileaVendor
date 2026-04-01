<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento — {{ $evento->titulo }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root { --primary: #581c87; --bg: #f8fafc; --surface: #ffffff; --text: #1e293b; --text-muted: #64748b; --border: #e2e8f0; --success: #10b981; }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
        body { background: var(--bg); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .container { max-width: 500px; width: 100%; }
        .card { background: var(--surface); border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.08); overflow: hidden; }
        .card-header { background: linear-gradient(135deg, var(--primary), #3b0764); color: white; padding: 28px; text-align: center; }
        .card-header h1 { font-size: 1.2rem; font-weight: 700; }
        .card-header .subtitle { font-size: 0.85rem; opacity: 0.8; margin-top: 4px; }
        .card-body { padding: 28px; text-align: center; }
        .success-icon { font-size: 3rem; color: var(--success); margin-bottom: 16px; }
        .amount { font-size: 2rem; font-weight: 800; color: var(--primary); margin-bottom: 20px; }
        .pix-area { margin: 20px 0; }
        .pix-area img { max-width: 250px; border-radius: 8px; }
        .pix-code { background: #f1f5f9; padding: 12px; border-radius: 8px; font-family: monospace; font-size: 0.8rem; word-break: break-all; margin: 12px 0; }
        .btn-copy { padding: 10px 20px; background: var(--primary); color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 0.9rem; }
        .btn-copy:hover { background: var(--primary-hover); }
        .boleto-link { display: inline-block; padding: 12px 24px; background: var(--primary); color: white; text-decoration: none; border-radius: 8px; font-weight: 600; margin: 12px 0; }
        .info { font-size: 0.85rem; color: var(--text-muted); margin-top: 16px; line-height: 1.5; }
        .branding { text-align: center; margin-top: 16px; font-size: 0.75rem; color: var(--text-muted); }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h1>{{ $evento->titulo }}</h1>
                <div class="subtitle">Pagamento gerado com sucesso</div>
            </div>
            <div class="card-body">
                <div class="success-icon"><i class="fas fa-check-circle"></i></div>
                <div class="amount">R$ {{ number_format($evento->valor, 2, ',', '.') }}</div>

                @if($billing_type === 'PIX')
                    <div class="pix-area">
                        @if(isset($payment['pixQrCodeImage']) || isset($payment['encodedImage']))
                            <img src="data:image/png;base64,{{ $payment['encodedImage'] ?? '' }}" alt="QR Code PIX">
                        @endif
                        @if(isset($payment['payload']))
                            <div class="pix-code">{{ $payment['payload'] }}</div>
                            <button class="btn-copy" onclick="navigator.clipboard.writeText('{{ $payment['payload'] }}'); this.textContent='Copiado!'">
                                <i class="fas fa-copy"></i> Copiar código PIX
                            </button>
                        @endif
                    </div>
                    <p class="info">Escaneie o QR Code ou copie o código acima para pagar via PIX.<br>O pagamento é confirmado em segundos.</p>

                @elseif($billing_type === 'BOLETO')
                    @if(isset($payment['bankSlipUrl']))
                        <a href="{{ $payment['bankSlipUrl'] }}" target="_blank" class="boleto-link">
                            <i class="fas fa-barcode"></i> Visualizar Boleto
                        </a>
                    @endif
                    <p class="info">O boleto vence em 1 dia. Após o pagamento, a confirmação pode levar até 3 dias úteis.</p>

                @elseif($billing_type === 'CREDIT_CARD')
                    @if(isset($payment['invoiceUrl']))
                        <a href="{{ $payment['invoiceUrl'] }}" target="_blank" class="boleto-link">
                            <i class="fas fa-credit-card"></i> Finalizar Pagamento
                        </a>
                    @endif
                    <p class="info">Você será redirecionado para finalizar o pagamento com cartão de crédito.</p>
                @endif

                <div class="branding">Basiléia Checkout — Pagamento Seguro</div>
            </div>
        </div>
    </div>
</body>
</html>
