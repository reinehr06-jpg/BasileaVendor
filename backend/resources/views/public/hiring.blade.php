<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contratação - {{ $labels['organization'] }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6366f1;
            --primary-hover: #4f46e5;
            --bg: #f8fafc;
            --card-bg: #ffffff;
            --text: #1e293b;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --radius: 12px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg);
            color: var(--text);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 900px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            background: var(--card-bg);
            padding: 40px;
            border-radius: var(--radius);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        }

        @media (max-width: 768px) {
            .container { grid-template-columns: 1fr; }
        }

        .header { margin-bottom: 30px; }
        .header h1 { font-size: 1.8rem; font-weight: 700; color: var(--text); margin-bottom: 8px; }
        .header p { color: var(--text-muted); font-size: 0.95rem; }

        .form-section h3 { font-size: 1.1rem; margin-bottom: 20px; color: var(--text); display: flex; align-items: center; gap: 8px; }

        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px; color: var(--text); }
        .form-group input, .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.2s;
        }
        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .plans-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
            margin-bottom: 20px;
        }

        .plan-card {
            border: 2px solid var(--border);
            padding: 15px;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.2s;
            position: relative;
        }
        .plan-card:hover { border-color: var(--primary); }
        .plan-card.active { border-color: var(--primary); background: rgba(99, 102, 241, 0.03); }
        .plan-card input { display: none; }
        .plan-card .name { font-weight: 700; font-size: 1rem; }
        .plan-card .price { color: var(--primary); font-weight: 700; font-size: 1.1rem; margin-top: 4px; }
        .plan-card .details { font-size: 0.8rem; color: var(--text-muted); margin-top: 2px; }

        .btn-submit {
            width: 100%;
            padding: 14px;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
            margin-top: 10px;
        }
        .btn-submit:hover { background-color: var(--primary-hover); }

        .info-panel {
            background: linear-gradient(135deg, #6366f1 0%, #4338ca 100%);
            color: white;
            padding: 40px;
            border-radius: var(--radius);
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .info-panel h2 { font-size: 2rem; margin-bottom: 20px; }
        .info-panel ul { list-style: none; }
        .info-panel li { margin-bottom: 15px; display: flex; align-items: center; gap: 12px; font-size: 1rem; opacity: 0.9; }
        .info-panel li svg { width: 20px; height: 20px; flex-shrink: 0; }

        .alert {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }
        .alert-danger { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
    </style>
</head>
<body>

<div class="container">
    <div class="form-section">
        <div class="header">
            <h1>Contratação Digital</h1>
            <p>Preencha os dados abaixo para ativar sua conta na <strong>{{ $labels['organization'] }}</strong>.</p>
        </div>

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <form action="/contratar" method="POST">
            @csrf
            
            <div class="form-group">
                <label for="nome_igreja">Nome da {{ $labels['church'] }}</label>
                <input type="text" name="nome_igreja" id="nome_igreja" value="{{ old('nome_igreja') }}" required placeholder="Ex: {{ $labels['church'] }} Central">
            </div>

            <div class="form-group">
                <label for="documento">CPF ou CNPJ</label>
                <input type="text" name="documento" id="documento" value="{{ old('documento') }}" required placeholder="00.000.000/0000-00">
            </div>

            <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label for="email">E-mail</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required placeholder="seu@email.com">
                </div>
                <div class="form-group">
                    <label for="whatsapp">WhatsApp</label>
                    <input type="text" name="whatsapp" id="whatsapp" value="{{ old('whatsapp') }}" required placeholder="(00) 00000-0000">
                </div>
            </div>

            <h3 style="margin-top: 25px;">Selecione o Plano</h3>
            <div class="plans-grid">
                @foreach($planos as $plano)
                <label class="plan-card" onclick="selectPlan(this)">
                    <input type="radio" name="plano_id" value="{{ $plano->id }}" {{ $loop->first ? 'checked' : '' }}>
                    <div class="name">{{ $plano->nome }}</div>
                    <div class="price">R$ {{ number_format($plano->valor_mensal, 2, ',', '.') }} <span style="font-size: 0.7rem; color: var(--text-muted)">/mês</span></div>
                    <div class="details">Até {{ $plano->faixa_max_membros }} {{ Str::plural($labels['member'], $plano->faixa_max_membros) }}</div>
                </label>
                @endforeach
            </div>

            <div class="form-group">
                <label for="forma_pagamento">Forma de Pagamento</label>
                <select name="forma_pagamento" id="forma_pagamento" required>
                    <option value="PIX">Pix (Ativação Imediata)</option>
                    <option value="BOLETO">Boleto Bancário</option>
                    <option value="CREDIT_CARD">Cartão de Crédito</option>
                </select>
            </div>

            <button type="submit" class="btn-submit">Finalizar e Pagar</button>
        </form>
    </div>

    <div class="info-panel">
        <h2>Por que escolher nossa plataforma?</h2>
        <ul>
            <li>
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                Gestão completa de {{ $labels['member'] }}s
            </li>
            <li>
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                Controle financeiro integrado
            </li>
            <li>
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                Relatórios em tempo real
            </li>
            <li>
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                Suporte especializado
            </li>
        </ul>
        <div style="margin-top: 40px; font-size: 0.85rem; opacity: 0.7;">
            Ao clicar em Finalizar, você concorda com nossos Termos de Uso e Política de Privacidade.
        </div>
    </div>
</div>

<script>
    function selectPlan(el) {
        document.querySelectorAll('.plan-card').forEach(c => c.classList.remove('active'));
        el.classList.add('active');
    }
    
    // Auto-select first plan on load
    window.onload = () => {
        const firstPlan = document.querySelector('.plan-card');
        if (firstPlan) firstPlan.classList.add('active');
    };
</script>

</body>
</html>
