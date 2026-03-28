<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento Aprovado!</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #6D28D9 0%, #4C1D95 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .card {
            background: white;
            border-radius: 20px;
            padding: 48px 40px;
            max-width: 500px;
            width: 100%;
            text-align: center;
            box-shadow: 0 25px 50px rgba(0,0,0,0.25);
        }
        .icon {
            width: 80px;
            height: 80px;
            background: #DCFCE7;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
        }
        .icon svg { width: 40px; height: 40px; color: #22C55E; }
        h1 { font-size: 1.75rem; color: #111827; margin-bottom: 8px; }
        .subtitle { color: #6B7280; margin-bottom: 32px; }
        .order-info {
            background: #F9FAFB;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 24px;
        }
        .order-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 0.95rem;
        }
        .order-row .label { color: #6B7280; }
        .order-row .value { font-weight: 700; color: #111827; }
        .order-row.total {
            border-top: 2px solid #E5E7EB;
            margin-top: 8px;
            padding-top: 16px;
        }
        .order-row.total .value { color: #6D28D9; font-size: 1.25rem; }
        .info-box {
            background: #EEF2FF;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 24px;
            font-size: 0.9rem;
            color: #4338CA;
        }
        .btn {
            display: block;
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #6D28D9 0%, #5B21B6 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            margin-bottom: 12px;
            transition: all 0.2s;
        }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(109,40,217,0.4); }
        .btn-secondary {
            background: #F3F4F6;
            color: #374151;
        }
        .btn-secondary:hover { background: #E5E7EB; box-shadow: none; transform: none; }
        .confetti {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            overflow: hidden;
            z-index: 9999;
        }
        .confetti-piece {
            position: absolute;
            width: 10px;
            height: 10px;
            background: #6D28D9;
            animation: confetti-fall 3s linear forwards;
        }
        @keyframes confetti-fall {
            0% { transform: translateY(-100px) rotate(0deg); opacity: 1; }
            100% { transform: translateY(100vh) rotate(720deg); opacity: 0; }
        }
    </style>
</head>
<body>
    <div class="confetti" id="confetti"></div>

    <div class="card">
        <div class="icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
        </div>

        <h1>Pagamento Aprovado!</h1>
        <p class="subtitle">Sua compra foi realizada com sucesso</p>

        <div class="order-info">
            <div class="order-row">
                <span class="label">Pedido</span>
                <span class="value">{{ $order->order_number }}</span>
            </div>
            <div class="order-row">
                <span class="label">Plano</span>
                <span class="value">{{ $order->offer->name ?? 'Plano' }}</span>
            </div>
            <div class="order-row">
                <span class="label">Método</span>
                <span class="value">{{ ucfirst($order->payment_method) }}</span>
            </div>
            <div class="order-row total">
                <span class="label">Total pago</span>
                <span class="value">R$ {{ number_format($order->total, 2, ',', '.') }}</span>
            </div>
        </div>

        <div class="info-box">
            <strong>📧 Acesso liberado!</strong><br>
            Você receberá um e-mail com instruções de acesso em instantes.
        </div>

        <a href="/" class="btn">Acessar minha conta</a>
        <a href="/" class="btn btn-secondary">Voltar para o início</a>
    </div>

    <script>
        // Confetti animation
        function createConfetti() {
            const container = document.getElementById('confetti');
            const colors = ['#6D28D9', '#22C55E', '#F59E0B', '#EF4444', '#3B82F6'];

            for (let i = 0; i < 100; i++) {
                const piece = document.createElement('div');
                piece.className = 'confetti-piece';
                piece.style.left = Math.random() * 100 + '%';
                piece.style.background = colors[Math.floor(Math.random() * colors.length)];
                piece.style.animationDelay = Math.random() * 2 + 's';
                piece.style.animationDuration = (Math.random() * 2 + 2) + 's';
                container.appendChild(piece);
            }

            setTimeout(() => container.remove(), 5000);
        }

        createConfetti();
    </script>
</body>
</html>
