<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Finalizar Pagamento - Basileia Vendas')</title>
    
    <!-- Design System: Basileia Vendas Identity Edition -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        :root {
            --primary: #4C1D95;
            --primary-light: #6D28D9;
            --primary-gradient: linear-gradient(135deg, #4C1D95 0%, #6366F1 100%);
            --surface: #ffffff;
            --radius-xl: 18px;
            --shadow-premium: 0 20px 60px rgba(0,0,0,0.15);
        }
        
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            /* Restauração da Identidade: Fundo com Gradiente Oficial */
            background: var(--primary-gradient);
            background-attachment: fixed;
            -webkit-font-smoothing: antialiased;
        }
        
        .checkout-card-container {
            width: 100%;
            max-width: 1000px;
            background: var(--surface);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-premium);
            overflow: hidden;
            border: none;
            position: relative;
            animation: slideUpFade 0.7s cubic-bezier(0.16, 1, 0.3, 1);
        }
        
        @keyframes slideUpFade {
            from { transform: translateY(40px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        .logo-header {
            text-align: center;
            padding: 35px 0 15px;
            position: relative;
        }
        
        .logo-official {
            max-width: 240px;
            height: auto;
            transform: scale(1.05);
            filter: drop-shadow(0 4px 8px rgba(0,0,0,0.08));
        }

        .btn-back-minimal {
            position: absolute;
            top: 25px;
            left: 25px;
            color: #d1d5db;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            font-weight: 800;
            text-decoration: none;
            transition: all 0.3s;
            z-index: 10;
        }
        .btn-back-minimal:hover { color: white; transform: translateX(-5px); text-decoration: none; }

        @media (max-width: 992px) {
            body { padding: 0; display: block; background: #fff; }
            .checkout-card-container { border-radius: 0; box-shadow: none; max-width: 100%; border: none; }
            .btn-back-minimal { color: var(--primary); top: 15px; left: 15px; }
            .btn-back-minimal:hover { color: var(--primary-light); }
        }
    </style>
</head>
<body>

    <a href="javascript:history.back()" class="btn-back-minimal d-none d-lg-inline-block">
        <i class="fas fa-arrow-left mr-1"></i> Voltar
    </a>

    <div class="checkout-card-container">
        <div class="logo-header">
            <img src="/assets/img/logo_oficial.png" alt="Basiléia Vendas" class="logo-official" 
                 onerror="this.src='https://i.imgur.com/uRjE87c.png';">
        </div>

        @yield('content')
        
        <div class="text-center py-4 border-top" style="background: #fafafb;">
            <p class="small text-muted mb-0 font-weight-500">&copy; {{ date('Y') }} Basiléia Vendas - Gestão Segura de Assinaturas</p>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
