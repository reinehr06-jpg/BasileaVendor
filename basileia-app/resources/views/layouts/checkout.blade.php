<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Checkout Seguro - Basileia Vendas')</title>
    
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 4.6 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f4f7f6;
            color: #333;
        }
        
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05) !important;
        }
        
        .bg-primary {
            background-color: #6248ff !important;
        }
        
        .btn-primary {
            background-color: #6248ff;
            border-color: #6248ff;
            font-weight: 600;
            padding: 12px 25px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background-color: #4e36d6;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(98, 72, 255, 0.4);
        }
        
        .form-control {
            border-radius: 8px;
            padding: 12px 15px;
            border: 1px solid #e0e0e0;
            height: auto;
        }
        
        .form-control:focus {
            box-shadow: 0 0 0 3px rgba(98, 72, 255, 0.1);
            border-color: #6248ff;
        }
        
        .btn-outline-primary {
            color: #6248ff;
            border-color: #6248ff;
            border-radius: 8px;
            padding: 10px;
        }
        
        .btn-outline-primary:not(:disabled):not(.disabled).active, 
        .btn-outline-primary:not(:disabled):not(.disabled):active, 
        .show>.btn-outline-primary.dropdown-toggle {
            background-color: #6248ff;
            border-color: #6248ff;
        }
        
        .display-4 {
            font-size: 2.5rem;
            color: #2d3436;
        }
        
        .badge-info {
            background-color: rgba(98, 72, 255, 0.1);
            color: #6248ff;
            font-weight: 600;
        }
        
        .checkout-header {
            padding: 40px 0;
            text-align: center;
        }
        
        .checkout-header h1 {
            font-weight: 800;
            color: #2d3436;
            letter-spacing: -1px;
        }

        .opacity-75 { opacity: 0.75; }
    </style>
</head>
<body>

    <div class="checkout-header">
        <h1>Basileia <span class="text-primary">Vendas</span></h1>
    </div>

    @yield('content')

    <footer class="text-center py-5">
        <p class="text-muted small">&copy; {{ date('Y') }} Basileia Vendas - Todos os direitos reservados.</p>
    </footer>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
