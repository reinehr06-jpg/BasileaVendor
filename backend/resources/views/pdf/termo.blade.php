<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $titulo }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.5;
            padding: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #4f46e5;
            margin-bottom: 30px;
            padding-bottom: 10px;
        }
        .header h1 {
            color: #4f46e5;
            margin: 0;
            font-size: 24px;
        }
        .meta {
            text-align: right;
            font-size: 10px;
            color: #666;
            margin-bottom: 20px;
        }
        .content {
            text-align: justify;
        }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 10px;
            color: #999;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $titulo }}</h1>
    </div>

    <div class="meta">
        Versão: {{ $versao }} | Atualizado em: {{ $data }}
    </div>

    <div class="content">
        {!! $conteudo !!}
    </div>

    <div class="footer">
        © {{ date('Y') }} Basiléia Vendas - Todos os direitos reservados.
    </div>
</body>
</html>
