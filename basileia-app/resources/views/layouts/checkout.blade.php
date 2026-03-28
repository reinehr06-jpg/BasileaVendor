<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Finalizar Registro - Basileia Vendas')</title>
    
    <!-- Design System: Basileia Vendas Elite Edition -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        :root {
            --primary: #4C1D95;
            --primary-light: #6D28D9;
            --surface: #ffffff;
            --radius-elite: 24px;
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
            /* Fundo Animado Elite: Suave e Imersivo */
            background: linear-gradient(-45deg, #4B0082, #8A2BE2, #9400D3, #A020F0);
            background-size: 400% 400%;
            animation: gradientElite 20s ease infinite;
            -webkit-font-smoothing: antialiased;
        }

        @keyframes gradientElite {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        .checkout-elite-wrapper {
            width: 100%;
            max-width: 1080px;
            background: var(--surface);
            border-radius: var(--radius-elite);
            box-shadow: 0 50px 120px rgba(0,0,0,0.3);
            overflow: hidden;
            animation: fadeInScale 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            display: block;
        }
        
        @keyframes fadeInScale {
            from { transform: scale(0.96); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        @media (max-width: 992px) {
            body { padding: 0; }
            .checkout-elite-wrapper { border-radius: 0; }
        }
    </style>
</head>
<body>

    <div class="checkout-elite-wrapper">
        @yield('content')
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
