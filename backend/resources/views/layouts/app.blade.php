<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Basiléia Vendas - @yield('title')</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="/css/basileia.css">
    <style>
        body { display: flex; min-height: 100vh; }

        .sidebar {
            width: 260px;
            background: linear-gradient(180deg, #3B0764 0%, #4C1D95 50%, #5B21B6 100%);
            color: white;
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
            z-index: 50;
            box-shadow: 4px 0 30px rgba(59, 7, 100, 0.4);
        }
        .sidebar-brand {
            padding: 22px 20px;
            font-size: 1.35rem;
            font-weight: 800;
            border-bottom: 1px solid rgba(255,255,255,0.12);
            text-align: center;
            letter-spacing: -0.5px;
            color: white;
        }
        .sidebar-brand span {
            opacity: 0.6;
            font-weight: 400;
            font-size: 0.8rem;
            display: block;
            margin-top: 2px;
        }
        .sidebar-user {
            padding: 18px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.12);
            text-align: center;
        }
        .sidebar-user h3 {
            font-size: 0.95rem;
            margin-bottom: 6px;
            font-weight: 600;
            color: white;
        }
        .sidebar-user span {
            font-size: 0.68rem;
            background: rgba(255,255,255,0.15);
            padding: 4px 14px;
            border-radius: 20px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .sidebar-menu {
            padding: 12px 0;
            flex-grow: 1;
            overflow-y: auto;
        }
        .sidebar-menu::-webkit-scrollbar { width: 4px; }
        .sidebar-menu::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 2px; }

        .menu-section {
            padding: 12px 24px 6px;
            font-size: 0.6rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: rgba(255,255,255,0.35);
            font-weight: 700;
        }
        .menu-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 24px;
            color: rgba(255,255,255,0.75);
            text-decoration: none;
            transition: all 0.2s;
            font-weight: 500;
            font-size: 0.88rem;
            border-left: 3px solid transparent;
            margin: 1px 0;
        }
        .menu-item i {
            font-size: 1.1rem;
            width: 22px;
            text-align: center;
            color: rgba(255,255,255,0.8);
            font-weight: 900;
        }
        .menu-item:hover {
            background: rgba(255,255,255,0.1);
            color: white;
            border-left-color: rgba(255,255,255,0.6);
        }
        .menu-item:hover i { color: white; }
        .menu-item.active {
            background: rgba(255,255,255,0.15);
            color: white;
            border-left-color: white;
            font-weight: 700;
        }
        .menu-item.active i { color: white; font-weight: 900; }

        .sidebar-footer {
            padding: 16px 20px;
            border-top: 1px solid rgba(255,255,255,0.12);
        }
        .btn-logout {
            width: 100%;
            padding: 11px;
            background: rgba(255,255,255,0.08);
            color: rgba(255,255,255,0.8);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
            transition: all 0.2s;
            font-size: 0.85rem;
            font-family: var(--font);
        }
        .btn-logout:hover { background: rgba(239, 68, 68, 0.3); border-color: rgba(239, 68, 68, 0.5); color: white; }

        .main-content {
            margin-left: 260px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .topbar {
            background: var(--surface);
            padding: 14px 32px;
            border-bottom: 1px solid var(--border-light);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 40;
            box-shadow: var(--shadow-xs);
        }
        .topbar h1 { font-size: 1.2rem; font-weight: 700; color: var(--text-primary); }
        .topbar-actions { display: flex; align-items: center; gap: 12px; }
        .content-area { padding: 28px 32px; flex-grow: 1; }

        .notif-wrapper { position: relative; }
        .notif-btn {
            background: var(--surface);
            border: 1.5px solid var(--border);
            border-radius: 10px;
            padding: 10px;
            cursor: pointer;
            position: relative;
            font-size: 1.1rem;
            transition: all 0.2s;
            color: var(--text-secondary);
            display: flex;
            align-items: center;
            justify-content: center;
            width: 42px;
            height: 42px;
        }
        .notif-btn:hover { border-color: var(--primary); color: var(--primary); }
        .notif-badge {
            position: absolute;
            top: -4px;
            right: -4px;
            background: var(--danger);
            color: white;
            font-size: 0.6rem;
            font-weight: 700;
            min-width: 18px;
            height: 18px;
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 4px;
            border: 2px solid var(--surface);
        }
        .notif-dropdown {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            width: 380px;
            background: var(--surface);
            border: 1px solid var(--border-light);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-xl);
            z-index: 100;
            display: none;
            max-height: 420px;
            overflow-y: auto;
        }
        .notif-dropdown.show { display: block; animation: scaleIn 0.15s ease; }
        .notif-header {
            padding: 16px 18px;
            border-bottom: 1px solid var(--border-light);
            font-weight: 700;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.9rem;
            color: var(--text-primary);
        }
        .notif-item { padding: 14px 18px; border-bottom: 1px solid var(--border-light); cursor: pointer; transition: all 0.15s; }
        .notif-item:hover { background: var(--surface-hover); }
        .notif-item.unread { background: rgba(var(--primary-rgb), 0.04); border-left: 3px solid var(--primary); }
        .notif-item .notif-title { font-weight: 600; font-size: 0.875rem; margin-bottom: 4px; color: var(--text-primary); }
        .notif-item .notif-msg { font-size: 0.8rem; color: var(--text-secondary); white-space: pre-line; line-height: 1.4; }
        .notif-item .notif-time { font-size: 0.7rem; color: var(--text-muted); margin-top: 6px; }
        .notif-empty { padding: 40px; text-align: center; color: var(--text-muted); }

        .sidebar-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.4rem;
            color: var(--text);
            cursor: pointer;
            padding: 4px;
        }

        @media (max-width: 900px) {
            .sidebar { transform: translateX(-100%); transition: transform 0.3s ease; }
            .sidebar.open { transform: translateX(0); }
            .main-content { margin-left: 0; }
            .sidebar-toggle { display: block; }
            .content-area { padding: 20px 16px; }
            .topbar { padding: 12px 16px; }
        }
    </style>
</head>
<body>
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            Basiléia Vendas
            <span>Sistema de Gestão</span>
        </div>
        <div class="sidebar-user">
            <h3>{{ Auth::user()->name }}</h3>
            <span>{{ Auth::user()->perfil === 'master' ? 'Administrador' : (Auth::user()->perfil === 'gestor' ? 'Gestor' : 'Vendedor') }}</span>
        </div>
        <nav class="sidebar-menu">
            @if(Auth::user()->perfil === 'master')
                <div class="menu-section">Visão Geral</div>
                <a href="{{ route('master.dashboard') }}" class="menu-item {{ request()->routeIs('master.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-chart-pie"></i> Painel Principal
                </a>

                <div class="menu-section">Gestão Comercial</div>
                <a href="{{ route('master.vendedores') }}" class="menu-item {{ request()->routeIs('master.vendedores') ? 'active' : '' }}">
                    <i class="fas fa-users"></i> Vendedores
                </a>
                <a href="{{ route('master.equipes') }}" class="menu-item {{ request()->routeIs('master.equipes') ? 'active' : '' }}">
                    <i class="fas fa-people-group"></i> Equipes
                </a>
                <a href="{{ route('master.vendas') }}" class="menu-item {{ request()->routeIs('master.vendas') ? 'active' : '' }}">
                    <i class="fas fa-shopping-bag"></i> Todas as Vendas
                </a>
                <a href="{{ route('master.clientes') }}" class="menu-item {{ request()->routeIs('master.clientes') ? 'active' : '' }}">
                    <i class="fas fa-building"></i> Clientes
                </a>
                <a href="{{ route('master.aprovacoes') }}" class="menu-item {{ request()->routeIs('master.aprovacoes') ? 'active' : '' }}">
                    <i class="fas fa-check-double"></i> Aprovações Pendentes
                </a>



                <div class="menu-section">Financeiro</div>
                <a href="{{ route('master.pagamentos') }}" class="menu-item {{ request()->routeIs('master.pagamentos') ? 'active' : '' }}">
                    <i class="fas fa-credit-card"></i> Controle de Pagamentos
                </a>
                <a href="{{ route('master.comissoes') }}" class="menu-item {{ request()->routeIs('master.comissoes') ? 'active' : '' }}">
                    <i class="fas fa-hand-holding-dollar"></i> Comissões
                </a>
                <a href="{{ route('master.metas') }}" class="menu-item {{ request()->routeIs('master.metas') ? 'active' : '' }}">
                    <i class="fas fa-bullseye"></i> Metas e Objetivos
                </a>

                <div class="menu-section">Relatórios</div>
                <a href="{{ route('master.relatorios') }}" class="menu-item {{ request()->routeIs('master.relatorios') ? 'active' : '' }}">
                    <i class="fas fa-chart-bar"></i> Relatórios Gerenciais
                </a>

                <div class="menu-section">Sistema</div>
                <a href="{{ route('master.configuracoes') }}" class="menu-item {{ request()->is('master/configuracoes*') ? 'active' : '' }}">
                    <i class="fas fa-gear"></i> Configurações
                </a>
            @else
                <div class="menu-section">Visão Geral</div>
                <a href="{{ route('vendedor.dashboard') }}" class="menu-item {{ request()->routeIs('vendedor.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-chart-pie"></i> Meu Painel
                </a>

                <div class="menu-section">Minhas Vendas</div>
                <a href="{{ route('vendedor.vendas') }}" class="menu-item {{ request()->routeIs('vendedor.vendas*') ? 'active' : '' }}">
                    <i class="fas fa-shopping-bag"></i> Vendas Realizadas
                </a>
                <a href="{{ route('vendedor.clientes') }}" class="menu-item {{ request()->routeIs('vendedor.clientes*') ? 'active' : '' }}">
                    <i class="fas fa-building"></i> Meus Clientes
                </a>
                <a href="{{ route('vendedor.pagamentos') }}" class="menu-item {{ request()->routeIs('vendedor.pagamentos') ? 'active' : '' }}">
                    <i class="fas fa-credit-card"></i> Pagamentos Recebidos
                </a>

                <div class="menu-section">Financeiro</div>
                <a href="{{ route('vendedor.comissoes') }}" class="menu-item {{ request()->routeIs('vendedor.comissoes*') ? 'active' : '' }}">
                    <i class="fas fa-hand-holding-dollar"></i> Minhas Comissões
                </a>
                <a href="{{ route('vendedor.configuracoes') }}" class="menu-item {{ request()->routeIs('vendedor.configuracoes*') ? 'active' : '' }}">
                    <i class="fas fa-wallet"></i> Split e Repasse
                </a>

                @if(Auth::user()->perfil === 'gestor')
                <div class="menu-section">Gestão</div>
                <a href="{{ route('vendedor.equipe') }}" class="menu-item {{ request()->routeIs('vendedor.equipe*') ? 'active' : '' }}">
                    <i class="fas fa-people-group"></i> Minha Equipe
                </a>
                @endif
            @endif
        </nav>
        <div class="sidebar-footer">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn-logout">
                    <i class="fas fa-right-from-bracket"></i> Sair do Sistema
                </button>
            </form>
        </div>
    </aside>

    <main class="main-content">
        <header class="topbar">
            <div class="d-flex align-center gap-2">
                <button class="sidebar-toggle" onclick="document.getElementById('sidebar').classList.toggle('open')">
                    <i class="fas fa-bars"></i>
                </button>
                <h1>@yield('title')</h1>
            </div>
            <div class="topbar-actions">
                @if(Auth::user()->perfil === 'master')
                @php
                    $notifNaoLidas = \App\Models\Notificacao::where('user_id', Auth::id())->where('lida', false)->count();
                    $notificacoes = \App\Models\Notificacao::where('user_id', Auth::id())->orderByDesc('created_at')->limit(10)->get();
                @endphp
                <div class="notif-wrapper">
                    <button class="notif-btn" onclick="toggleNotif()">
                        <i class="fas fa-bell"></i>
                        @if($notifNaoLidas > 0)
                            <span class="notif-badge">{{ $notifNaoLidas > 9 ? '9+' : $notifNaoLidas }}</span>
                        @endif
                    </button>
                    <div class="notif-dropdown" id="notifDropdown">
                        <div class="notif-header">
                            <span>Notificações</span>
                            @if($notifNaoLidas > 0)
                                <a href="#" onclick="marcarTodasLidas()" style="font-size: 0.8rem; color: var(--primary); font-weight: 600;">Marcar todas como lidas</a>
                            @endif
                        </div>
                        @if($notificacoes->count() > 0)
                            @foreach($notificacoes as $notif)
                            <div class="notif-item {{ $notif->lida ? '' : 'unread' }}" onclick="verNotificacao({{ $notif->id }}, '{{ $notif->tipo }}', {{ $notif->dados['venda_id'] ?? 'null' }})">
                                <div class="notif-title">{{ $notif->titulo }}</div>
                                <div class="notif-msg">{{ \Str::limit($notif->mensagem, 150) }}</div>
                                <div class="notif-time">{{ $notif->created_at->diffForHumans() }}</div>
                            </div>
                            @endforeach
                        @else
                            <div class="notif-empty">
                                <i class="fas fa-bell-slash" style="font-size: 2rem; display: block; margin-bottom: 8px;"></i>
                                Nenhuma notificação
                            </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </header>
        <section class="content-area">
            @if(session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <span>{{ session('success') }}</span>
            </div>
            @endif
            @if(session('error'))
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <span>{{ session('error') }}</span>
            </div>
            @endif
            @if($errors->any())
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <div>
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            </div>
            @endif
            @yield('content')
        </section>
    </main>

    <script src="/js/basileia.js"></script>
    <script>
    function toggleNotif() {
        document.getElementById('notifDropdown').classList.toggle('show');
    }
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.notif-wrapper')) {
            document.getElementById('notifDropdown').classList.remove('show');
        }
    });
    function verNotificacao(id, tipo, vendaId) {
        fetch('/master/notificacoes/' + id + '/marcar-lida', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            }
        });
        if (tipo === 'renovacao_anual' && vendaId) {
            window.location.href = '/master/vendas';
        } else if (tipo === 'venda_aprovacao' && vendaId) {
            window.location.href = '/master/aprovacoes';
        }
    }
    function marcarTodasLidas() {
        fetch('/master/notificacoes/marcar-todas-lidas', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            }
        }).then(() => location.reload());
    }
    </script>
    @yield('scripts')

    {{-- Desabilitar autofill/autocomplete em TODO o sistema (exceto campos de senha/email) --}}
    <script>
    (function() {
        function disableAutofill(root) {
            (root.querySelectorAll ? root.querySelectorAll('input, select, textarea') : []).forEach(function(el) {
                if (el.type === 'password') {
                    // Navegadores exigem valor válido para campos de senha
                    el.setAttribute('autocomplete', 'new-password');
                } else if (el.type === 'email' || el.name === 'email' || el.name === 'email_cliente' || el.name === 'username') {
                    // Navegadores exigem valor válido para campos de email
                    el.setAttribute('autocomplete', 'email');
                } else if (el.type === 'hidden' || el.type === 'submit' || el.type === 'button') {
                    // Ignorar campos hidden/submit
                } else {
                    el.setAttribute('autocomplete', 'off');
                    el.setAttribute('autocorrect', 'off');
                    el.setAttribute('autocapitalize', 'off');
                    el.setAttribute('spellcheck', 'false');
                }
            });
        }

        disableAutofill(document);

        var observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1) disableAutofill(node);
                });
            });
        });
        observer.observe(document.body, { childList: true, subtree: true });
    })();
    </script>
</body>
</html>
