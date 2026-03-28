<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Finalizar Cadastro - Basileia Vendas')</title>
    
    <!-- Design System: Basileia Vendas Premium -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        :root {
            --primary: #4C1D95;
            --primary-light: #6D28D9;
            --primary-gradient: linear-gradient(135deg, #4C1D95 0%, #6366F1 100%);
            --bg: #ffffff;
            --bg-muted: #f9fafb;
            --text-primary: #111827;
            --text-secondary: #4b5563;
            --border: #e5e7eb;
            --radius: 12px;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg);
            color: var(--text-primary);
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }
        
        .checkout-wrapper {
            display: flex;
            min-height: 100vh;
        }
        
        /* Lateral Esquerda (Summary) */
        .summary-side {
            width: 45%;
            background-color: var(--bg-muted);
            padding: 60px 80px;
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            animation: fadeIn 0.8s ease;
        }
        
        /* Lateral Direita (Payment) */
        .payment-side {
            width: 55%;
            padding: 60px 100px;
            display: flex;
            flex-direction: column;
            animation: slideLeft 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes slideLeft { from { transform: translateX(20px); opacity: 0; } to { transform: translateX(0); opacity: 1; } }

        .btn-back {
            color: var(--text-secondary);
            font-size: 0.9rem;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 40px;
            transition: color 0.2s;
        }
        .btn-back:hover { color: var(--primary); text-decoration: none; }

        .brand-logo {
            font-weight: 800;
            font-size: 1.5rem;
            color: var(--primary);
            margin-bottom: 20px;
            letter-spacing: -0.5px;
        }

        .plan-price {
            font-size: 3.5rem;
            font-weight: 800;
            color: var(--text-primary);
            margin: 15px 0;
            letter-spacing: -2px;
        }
        .plan-price small {
            font-size: 1rem;
            color: var(--text-secondary);
            font-weight: 400;
            letter-spacing: 0;
        }

        .feature-list {
            margin: 40px 0;
            padding: 0;
            list-style: none;
        }
        .feature-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 18px;
            font-size: 1rem;
            color: var(--text-secondary);
            font-weight: 500;
        }
        .feature-item i {
            color: var(--primary);
            font-size: 1.1rem;
            margin-top: 4px;
        }

        .total-row {
            margin-top: auto;
            border-top: 1px solid var(--border);
            padding-top: 30px;
        }

        /* Formulário */
        .form-section-title {
            font-weight: 700;
            font-size: 1.25rem;
            margin-bottom: 25px;
            color: var(--text-primary);
        }

        .custom-control-input:checked ~ .custom-control-label::before {
            background-color: var(--primary);
            border-color: var(--primary);
        }

        .form-control-premium {
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 14px 16px;
            font-family: inherit;
            font-size: 1rem;
            height: auto;
            background: #fff;
            transition: all 0.2s;
        }
        .form-control-premium:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(76, 29, 149, 0.1);
        }

        .btn-subscribe {
            background: var(--primary-gradient);
            color: white;
            padding: 18px;
            font-weight: 700;
            border-radius: 10px;
            border: none;
            width: 100%;
            font-size: 1.1rem;
            margin-top: 30px;
            box-shadow: 0 10px 20px rgba(76, 29, 149, 0.2);
            transition: all 0.3s;
        }
        .btn-subscribe:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 25px rgba(76, 29, 149, 0.3);
            color: white;
        }

        /* Mobile Adjustments */
        @media (max-width: 992px) {
            .checkout-wrapper { flex-direction: column; }
            .summary-side, .payment-side { width: 100%; padding: 40px 20px; }
            .summary-side { order: 2; border-right: none; border-top: 1px solid var(--border); }
            .payment-side { order: 1; }
        }
    </style>
</head>
<body>

    <div class="checkout-wrapper">
        @yield('content')
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
