<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Basiléia Vendas - Atualizar Senha</title>
    <style>
        :root {
            --primary: #581c87;
            --primary-hover: #4c1d95;
            --background: #f8fafc;
            --surface: #ffffff;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --error: #ef4444;
            --warning: #f59e0b;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
        }

        body {
            background-color: var(--background);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            color: var(--text-main);
        }

        .wrapper {
            display: flex;
            width: 100%;
            max-width: 900px;
            min-height: 550px;
            background: var(--surface);
            border-radius: 20px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            margin: 20px;
        }

        .brand {
            flex: 1;
            background: linear-gradient(135deg, var(--primary), #3b0764);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px;
            color: white;
            text-align: center;
        }

        .form-container {
            flex: 1.2;
            padding: 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: white;
        }

        .header {
            margin-bottom: 25px;
        }

        .header h2 {
            font-size: 1.6rem;
            margin-bottom: 8px;
        }

        .alert-warning {
            background-color: #fef3c7;
            border-left: 4px solid var(--warning);
            color: #92400e;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 0.9rem;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .form-input {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 1rem;
            outline: none;
            background: var(--background);
            transition: all 0.2s;
        }

        .form-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(88, 28, 135, 0.1);
            background: white;
        }

        .error-message {
            color: var(--error);
            font-size: 0.8rem;
            margin-top: 5px;
            display: block;
        }

        .btn-submit {
            width: 100%;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 14px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
            margin-top: 10px;
        }

        .btn-submit:hover {
            background: var(--primary-hover);
        }

        .password-requirements {
            font-size: 0.8rem;
            color: var(--text-muted);
            margin-top: 8px;
            line-height: 1.4;
        }

        .logo-b {
            width: 80px;
            height: 80px;
            background: rgba(255,255,255,0.1);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            font-size: 2.5rem;
            font-weight: bold;
        }

        @media (max-width: 768px) {
            .wrapper {
                flex-direction: column;
                margin: 0;
                border-radius: 0;
            }
            .brand {
                padding: 40px 20px;
            }
            .form-container {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>

    <div class="wrapper">
        <div class="brand">
            <div class="logo-b">B</div>
            <h2>Segurança de Acesso</h2>
            <p style="opacity: 0.8; margin-top: 10px;">Para garantir a proteção dos dados comerciais, solicitamos a atualização da sua senha.</p>
        </div>
        
        <div class="form-container">
            <div class="header">
                <h2>Atualizar Senha</h2>
            </div>

            @if(session('warning'))
                <div class="alert-warning">
                    {{ session('warning') }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                
                <div class="form-group">
                    <label class="form-label" for="current_password">Senha Atual</label>
                    <input type="password" id="current_password" name="current_password" class="form-input" required placeholder="Sua senha atual">
                    @error('current_password')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Nova Senha</label>
                    <input type="password" id="password" name="password" class="form-input" required placeholder="Nova senha segura">
                    <div class="password-requirements">
                        Mínimo 8 caracteres, incluindo Letras (Maiúsculas e Minúsculas), Números e Símbolos.
                    </div>
                    @error('password')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="password_confirmation">Confirmar Nova Senha</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-input" required placeholder="Repita a nova senha">
                </div>

                <button type="submit" class="btn-submit">
                    Atualizar e Acessar Painel
                </button>
            </form>
        </div>
    </div>

</body>
</html>
