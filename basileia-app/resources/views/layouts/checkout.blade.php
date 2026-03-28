<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Checkout Seguro - Basileia Vendas')</title>
    
    <!-- Design System: Basileia Vendas -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        :root {
            --primary: #4C1D95;
            --primary-gradient: linear-gradient(135deg, #4C1D95 0%, #6366F1 100%);
            --bg: #f4f5fa;
            --surface: #ffffff;
            --text-primary: #3b3b5c;
            --radius-lg: 12px;
            --shadow-xl: 0 12px 24px rgba(50, 50, 71, 0.14);
        }
        
        body {
            font-family: 'Inter', -apple-system, sans-serif;
            background-color: var(--bg);
            color: #4a4a6a;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .checkout-header {
            background: var(--primary-gradient);
            padding: 50px 0 100px;
            text-align: center;
            color: white;
            box-shadow: 0 4px 20px rgba(76, 29, 149, 0.2);
        }
        
        .checkout-header h1 {
            font-weight: 800;
            margin-bottom: 5px;
            letter-spacing: -1px;
        }
        
        .checkout-container {
            margin-top: -60px;
            flex: 1;
        }
        
        .card-checkout {
            border: none;
            border-radius: var(--radius-lg);
            background: var(--surface);
            box-shadow: var(--shadow-xl);
            overflow: hidden;
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        
        @keyframes slideUp {
            from { transform: translateY(30px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        .card-header-premium {
            background: #fff;
            padding: 30px;
            border-bottom: 1px solid #f0f0f5;
            text-align: center;
        }
        
        .price-tag {
            font-size: 2.8rem;
            font-weight: 800;
            color: var(--primary);
            letter-spacing: -1px;
            margin: 10px 0;
        }
        
        .btn-pay {
            background: var(--primary-gradient);
            border: none;
            color: white;
            padding: 16px;
            font-weight: 700;
            border-radius: 10px;
            font-size: 1.1rem;
            transition: all 0.2s;
            box-shadow: 0 8px 16px rgba(76, 29, 149, 0.25);
        }
        
        .btn-pay:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 20px rgba(76, 29, 149, 0.35);
            color: white;
        }
        
        .nav-pills-premium {
            background: #f0f0f5;
            padding: 6px;
            border-radius: 12px;
            gap: 5px;
        }
        
        .nav-pills-premium .nav-link {
            border-radius: 9px;
            color: #6e6b8b;
            font-weight: 600;
            padding: 10px;
            transition: all 0.2s;
        }
        
        .nav-pills-premium .nav-link.active {
            background: white;
            color: var(--primary);
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .form-control-premium {
            background: #f8f8fb;
            border: 1.5px solid #ededf2;
            border-radius: 10px;
            padding: 12px 15px;
            height: auto;
            font-weight: 500;
        }
        
        .form-control-premium:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(76, 29, 149, 0.1);
            background: white;
        }

        .footer-checkout {
            padding: 40px 0;
            text-align: center;
            opacity: 0.6;
        }
    </style>
</head>
<body>

    <div class="checkout-header">
        <div class="container">
            <h1>Basiléia <span style="font-weight: 400; opacity: 0.8;">Vendas</span></h1>
            <p class="mb-0 small"><i class="fas fa-shield-alt mr-1"></i> Ambiente 100% Seguro</p>
        </div>
    </div>

    <div class="checkout-container container">
        <div class="row justify-content-center">
            <div class="col-md-9 col-lg-7">
                @yield('content')
            </div>
        </div>
    </div>

    <footer class="footer-checkout container">
        <p class="small">&copy; {{ date('Y') }} Basiléia Vendas - Sistema de Gestão de Pagamentos</p>
    </footer>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
