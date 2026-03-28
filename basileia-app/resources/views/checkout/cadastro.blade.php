<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - Basiléa</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .cadastro-container {
            background: white;
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            max-width: 600px;
            width: 100%;
            overflow: hidden;
        }

        .cadastro-header {
            background: linear-gradient(135deg, var(--primary) 0%, #8b5cf6 100%);
            padding: 48px 32px;
            text-align: center;
            color: white;
        }

        :root {
            --primary: #6366f1;
            --primary-hover: #4f46e5;
            --primary-light: #e0e7ff;
            --success: #10b981;
            --danger: #ef4444;
            --gray-900: #0f172a;
            --gray-800: #1e293b;
            --gray-700: #334155;
            --gray-600: #475569;
            --gray-500: #64748b;
            --gray-400: #94a3b8;
            --gray-200: #e2e8f0;
            --gray-100: #f1f5f9;
            --white: #ffffff;
        }

        .vendedor-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255,255,255,0.2);
            padding: 8px 16px;
            border-radius: 20px;
            margin-bottom: 16px;
            font-size: 0.9rem;
        }

        .cadastro-title {
            font-size: 1.75rem;
            font-weight: 800;
            margin-bottom: 8px;
        }

        .cadastro-subtitle {
            font-size: 1rem;
            opacity: 0.9;
        }

        .cadastro-body {
            padding: 32px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--gray-600);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .form-input {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid var(--gray-200);
            border-radius: 12px;
            font-size: 1rem;
            font-family: inherit;
            transition: all 0.2s;
            background: var(--white);
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px var(--primary-light);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        @media (max-width: 480px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }

        .plano-selector {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 12px;
            margin-bottom: 24px;
        }

        .plano-card {
            padding: 16px;
            border: 2px solid var(--gray-200);
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s;
            text-align: center;
        }

        .plano-card:hover {
            border-color: var(--primary);
        }

        .plano-card.selected {
            border-color: var(--primary);
            background: var(--primary-light);
        }

        .plano-nome {
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 4px;
        }

        .plano-valor {
            font-size: 1.25rem;
            font-weight: 800;
            color: var(--primary);
        }

        .plano-membros {
            font-size: 0.8rem;
            color: var(--gray-500);
            margin-top: 4px;
        }

        .btn-primary {
            width: 100%;
            padding: 16px 24px;
            background: linear-gradient(135deg, var(--primary) 0%, #8b5cf6 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(99, 102, 241, 0.4);
        }

        .beneficios {
            margin-top: 24px;
            padding: 16px;
            background: var(--gray-100);
            border-radius: 12px;
        }

        .beneficio-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 0;
            font-size: 0.9rem;
            color: var(--gray-700);
        }

        .beneficio-item i {
            color: var(--success);
        }
    </style>
</head>
<body>
    <div class="cadastro-container">
        <div class="cadastro-header">
            <div class="vendedor-badge">
                <i class="fas fa-user-circle"></i>
                Indicado por {{ $vendedor->usuario->name ?? 'Vendedor' }}
            </div>
            <h1 class="cadastro-title">Cadastre-se agora</h1>
            <p class="cadastro-subtitle">Preencha seus dados para começar</p>
        </div>

        <div class="cadastro-body">
            <form action="{{ route('checkout.criar') }}" method="POST" id="cadastro-form">
                @csrf
                <input type="hidden" name="hash_indicacao" value="{{ $vendedor->hash_indicacao ?? $vendedor->id }}">
                <input type="hidden" name="vendedor_id" value="{{ $vendedor->id }}">

                <div class="form-group">
                    <label class="form-label">Nome completo *</label>
                    <input type="text" class="form-input" name="nome" required>
                </div>

                <div class="form-group">
                    <label class="form-label">E-mail *</label>
                    <input type="email" class="form-input" name="email" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">CPF *</label>
                        <input type="text" class="form-input" name="documento" id="cpf" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">WhatsApp</label>
                        <input type="text" class="form-input" name="telefone" id="telefone">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Nome da Igreja</label>
                        <input type="text" class="form-input" name="nome_igreja">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Membros</label>
                        <input type="number" class="form-input" name="quantidade_membros" min="1" value="100">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Escolha o plano *</label>
                    <div class="plano-selector">
                        @foreach($planos as $plano)
                        <div class="plano-card {{ $loop->first ? 'selected' : '' }}" onclick="selectPlano({{ $plano->id }}, this)">
                            <input type="radio" name="plano_id" value="{{ $plano->id }}" {{ $loop->first ? 'checked' : '' }} style="display: none;">
                            <div class="plano-nome">{{ $plano->nome }}</div>
                            <div class="plano-valor">R$ {{ number_format($plano->valor_mensal ?? 97, 2, ',', '.') }}</div>
                            <div class="plano-membros">até {{ $plano->faixa_max_membros ?? '∞' }} membros</div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Forma de pagamento *</label>
                    <select class="form-input" name="forma_pagamento" required>
                        <option value="pix">PIX (5% de desconto)</option>
                        <option value="cartao">Cartão de Crédito</option>
                        <option value="boleto">Boleto</option>
                    </select>
                </div>

                <button type="submit" class="btn-primary">
                    <i class="fas fa-lock"></i> Continuar para pagamento
                </button>
            </form>

            <div class="beneficios">
                <div class="beneficio-item">
                    <i class="fas fa-check-circle"></i>
                    <span>Acesso imediato ao sistema</span>
                </div>
                <div class="beneficio-item">
                    <i class="fas fa-check-circle"></i>
                    <span>Suporte prioritário</span>
                </div>
                <div class="beneficio-item">
                    <i class="fas fa-check-circle"></i>
                    <span>Garantia de 7 dias</span>
                </div>
            </div>
        </div>
    </div>

    <script>
        function selectPlano(planoId, el) {
            document.querySelectorAll('.plano-card').forEach(c => c.classList.remove('selected'));
            el.classList.add('selected');
            el.querySelector('input').checked = true;
        }

        // Masks
        document.getElementById('cpf').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 11) value = value.slice(0, 11);
            if (value.length > 9) {
                value = value.slice(0, 3) + '.' + value.slice(3, 6) + '.' + value.slice(6, 9) + '-' + value.slice(9);
            } else if (value.length > 6) {
                value = value.slice(0, 3) + '.' + value.slice(3, 6) + '.' + value.slice(6);
            } else if (value.length > 3) {
                value = value.slice(0, 3) + '.' + value.slice(3);
            }
            e.target.value = value;
        });

        document.getElementById('telefone').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 11) value = value.slice(0, 11);
            if (value.length > 7) {
                value = '(' + value.slice(0, 2) + ') ' + value.slice(2, 7) + '-' + value.slice(7);
            } else if (value.length > 2) {
                value = '(' + value.slice(0, 2) + ') ' + value.slice(2);
            } else if (value.length > 0) {
                value = '(' + value;
            }
            e.target.value = value;
        });
    </script>
</body>
</html>
