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
            transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
        }
        /* ── Collapsed State ── */
        .sidebar.collapsed { width: 68px; }
        .sidebar.collapsed .sidebar-brand-logo span,
        .sidebar.collapsed .sidebar-user,
        .sidebar.collapsed .menu-section,
        .sidebar.collapsed .menu-item-text,
        .sidebar.collapsed .btn-logout-text { display: none; }
        .sidebar.collapsed .sidebar-brand-logo { justify-content: center; padding: 16px 8px; }
        .sidebar.collapsed .sidebar-brand-logo img { max-height: 28px; }
        .sidebar.collapsed .sidebar-collapse-btn { position: static; margin: 0 auto; }
        .sidebar.collapsed .menu-item { justify-content: center; padding: 14px 0; gap: 0; border-left: none; }
        .sidebar.collapsed .menu-item i { margin: 0; font-size: 1.2rem; }
        .sidebar.collapsed .menu-item.active { border-left: none; background: rgba(255,255,255,0.2); border-radius: 10px; margin: 2px 8px; }
        .sidebar.collapsed .btn-logout { justify-content: center; padding: 12px; }
        .sidebar.collapsed .sidebar-footer { padding: 12px 8px; }

        .sidebar-brand-logo {
            padding: 18px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.12);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            min-height: 68px;
        }
        .sidebar-brand-logo img {
            max-height: 32px;
            width: auto;
            transition: all 0.3s;
        }
        .sidebar-brand-logo span {
            font-size: 1.1rem;
            font-weight: 800;
            letter-spacing: -0.5px;
            color: white;
            white-space: nowrap;
        }
        .sidebar-brand-logo .brand-icon {
            width: 40px;
            height: 40px;
            background: white;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            font-size: 1.8rem;
            font-weight: 900;
            flex-shrink: 0;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .sidebar-brand-logo .brand-name {
            font-size: 1.6rem;
            font-weight: 800;
            color: white;
            font-style: italic;
            letter-spacing: -1px;
            margin-left: 5px;
        }
        .sidebar-brand-logo .brand-subtext {
            font-size: 1rem;
            letter-spacing: 5px;
            opacity: 1;
            font-weight: 400;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
            width: 100%;
            margin-top: 2px;
            color: white;
            text-transform: uppercase;
            font-family: 'Inter', sans-serif;
        }
        .sidebar-brand-logo .brand-subtext::before,
        .sidebar-brand-logo .brand-subtext::after {
            content: "";
            height: 1.5px;
            background: rgba(255,255,255,0.5);
            flex: 1;
            display: block;
        }
        .sidebar-collapse-btn {
            width: 26px;
            height: 26px;
            border-radius: 50%;
            border: 2px solid rgba(255,255,255,0.3);
            background: rgba(255,255,255,0.08);
            color: rgba(255,255,255,0.7);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.55rem;
            transition: all 0.2s;
            flex-shrink: 0;
        }
        .sidebar-collapse-btn:hover {
            background: rgba(255,255,255,0.2);
            border-color: rgba(255,255,255,0.6);
            color: white;
            transform: scale(1.15);
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
            white-space: nowrap;
            overflow: hidden;
            transition: opacity 0.2s;
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
            white-space: nowrap;
            overflow: hidden;
        }
        .menu-item i {
            font-size: 1.1rem;
            width: 22px;
            text-align: center;
            color: rgba(255,255,255,0.8);
            font-weight: 900;
            flex-shrink: 0;
        }
        .menu-item-text { transition: opacity 0.2s; }
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
            overflow-x: hidden;
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .sidebar.collapsed ~ .main-content { margin-left: 68px; }

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
            .sidebar { transform: translateX(-100%); transition: transform 0.3s ease, width 0.3s ease; width: 260px !important; }
            .sidebar.open { transform: translateX(0); }
            .sidebar.collapsed { width: 260px !important; }
            .main-content { margin-left: 0 !important; }
            .sidebar-toggle { display: block; }
            .content-area { padding: 20px 16px; }
            .topbar { padding: 12px 16px; }
        }
    </style>
</head>
<body>
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand-logo">
            <div style="display:flex;flex-direction:column;align-items:center;width:100%;overflow:hidden;padding: 10px 0;">
                <div style="display:flex;align-items:center;gap:4px;">
                    <div class="brand-icon">B</div>
                    <span class="brand-name">Basiléia</span>
                </div>
                <div class="brand-subtext">Sales</div>
            </div>
            <button class="sidebar-collapse-btn" id="sidebarCollapseBtn" onclick="toggleSidebar()" title="Recolher menu">
                <i class="fas fa-circle" style="font-size:0.5rem;"></i>
            </button>
        </div>
        @php $perfil = Auth::user()->perfil; @endphp
        <div class="sidebar-user">
            <h3>{{ Auth::user()->name }}</h3>
            <span>{{ $perfil === 'master' ? 'Administrador' : ($perfil === 'gestor' ? 'Gestor' : 'Vendedor') }}</span>
        </div>
        <nav class="sidebar-menu">
            @if($perfil === 'master' || $perfil === 'admin')
                {{-- ADMIN MASTER --}}
                <div class="menu-section">Painel</div>
                <a href="{{ route('master.dashboard') }}" class="menu-item {{ request()->routeIs('master.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-chart-pie"></i> <span class="menu-item-text">Painel Principal</span>
                </a>

                <div class="menu-section">Gestão Comercial</div>
                <a href="{{ route('master.vendedores') }}" class="menu-item {{ request()->routeIs('master.vendedores') ? 'active' : '' }}">
                    <i class="fas fa-user-tie"></i> <span class="menu-item-text">Vendedores</span>
                </a>
                <a href="{{ route('master.equipes') }}" class="menu-item {{ request()->routeIs('master.equipes') ? 'active' : '' }}">
                    <i class="fas fa-users"></i> <span class="menu-item-text">Equipes</span>
                </a>
                <a href="{{ route('master.vendas') }}" class="menu-item {{ request()->routeIs('master.vendas') ? 'active' : '' }}">
                    <i class="fas fa-shopping-cart"></i> <span class="menu-item-text">Todas as Vendas</span>
                </a>
                <a href="{{ route('master.clientes') }}" class="menu-item {{ request()->routeIs('master.clientes') ? 'active' : '' }}">
                    <i class="fas fa-user-friends"></i> <span class="menu-item-text">Clientes</span>
                </a>
                <a href="{{ route('master.aprovacoes') }}" class="menu-item {{ request()->routeIs('master.aprovacoes') ? 'active' : '' }}">
                    <i class="fas fa-check-double"></i> <span class="menu-item-text">Aprovações</span>
                </a>

                <div class="menu-section">Marketing</div>
                <a href="{{ route('admin.campanhas.index') }}" class="menu-item {{ request()->routeIs('admin.campanhas*') ? 'active' : '' }}">
                    <i class="fas fa-bullhorn"></i> <span class="menu-item-text">Campanhas</span>
                </a>
                <a href="{{ route('admin.contatos.index') }}" class="menu-item {{ request()->routeIs('admin.contatos*') ? 'active' : '' }}">
                    <i class="fas fa-address-book"></i> <span class="menu-item-text">Leads</span>
                </a>
                <a href="{{ route('admin.calendario.index') }}" class="menu-item {{ request()->routeIs('admin.calendario*') ? 'active' : '' }}">
                    <i class="fas fa-calendar-alt"></i> <span class="menu-item-text">Calendário</span>
                </a>

                <div class="menu-section">Comunicação</div>
                <a href="{{ route('admin.chat.index') }}" class="menu-item {{ request()->routeIs('admin.chat.index') ? 'active' : '' }}">
                    <i class="fab fa-whatsapp"></i> <span class="menu-item-text">Chat Conversas</span>
                </a>
                <a href="{{ route('admin.chat.contatos') }}" class="menu-item {{ request()->routeIs('admin.chat.contatos') ? 'active' : '' }}">
                    <i class="fas fa-id-card"></i> <span class="menu-item-text">Chat Contatos</span>
                </a>

                <div class="menu-section">Financeiro</div>
                <a href="{{ route('master.pagamentos') }}" class="menu-item {{ request()->routeIs('master.pagamentos') ? 'active' : '' }}">
                    <i class="fas fa-credit-card"></i> <span class="menu-item-text">Pagamentos</span>
                </a>
                <a href="{{ route('master.comissoes') }}" class="menu-item {{ request()->routeIs('master.comissoes') ? 'active' : '' }}">
                    <i class="fas fa-percentage"></i> <span class="menu-item-text">Comissões</span>
                </a>
                <a href="{{ route('master.metas') }}" class="menu-item {{ request()->routeIs('master.metas') ? 'active' : '' }}">
                    <i class="fas fa-target-slash"></i> <span class="menu-item-text">Metas</span>
                </a>

                <div class="menu-section">Ia Lab</div>
                <a href="{{ route('admin.ia.prompts.index') }}" class="menu-item {{ request()->routeIs('admin.ia.prompts*') ? 'active' : '' }}">
                    <i class="fas fa-keyboard"></i> <span class="menu-item-text">Novo Prompt</span>
                </a>
                <a href="{{ route('master.ia') }}" class="menu-item {{ request()->routeIs('master.ia') ? 'active' : '' }}">
                    <i class="fas fa-terminal"></i> <span class="menu-item-text">Ia Lab</span>
                </a>

                <div class="menu-section">Sistema</div>
                <a href="{{ route('master.configuracoes') }}" class="menu-item {{ request()->is('master/configuracoes*') ? 'active' : '' }}">
                    <i class="fas fa-cog"></i> <span class="menu-item-text">Configurações</span>
                </a>
                <a href="{{ route('admin.termos.index') }}" class="menu-item {{ request()->routeIs('admin.termos*') ? 'active' : '' }}">
                    <i class="fas fa-file-contract"></i> <span class="menu-item-text">Termos de Uso</span>
                </a>
                <a href="{{ route('admin.importar.index') }}" class="menu-item {{ request()->routeIs('admin.importar*') ? 'active' : '' }}">
                    <i class="fas fa-file-import"></i> <span class="menu-item-text">Importar</span>
                </a>

            @elseif(Auth::user()->perfil === 'gestor')
                {{-- GESTOR --}}
                <div class="menu-section">Painel</div>
                <a href="{{ route('vendedor.dashboard') }}" class="menu-item {{ request()->routeIs('vendedor.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-chart-pie"></i> Meu Painel
                </a>

                <div class="menu-section">Gestão</div>
                <a href="{{ route('vendedor.equipe') }}" class="menu-item {{ request()->routeIs('vendedor.equipe*') ? 'active' : '' }}">
                    <i class="fas fa-users-cog"></i> Minha Equipe
                </a>

                <div class="menu-section">Marketing</div>
                <a href="{{ route('gestor.contatos.index') }}" class="menu-item {{ request()->routeIs('gestor.contatos*') ? 'active' : '' }}">
                    <i class="fas fa-address-book"></i> Leads
                </a>
                <a href="{{ route('gestor.calendario.index') }}" class="menu-item {{ request()->routeIs('gestor.calendario*') ? 'active' : '' }}">
                    <i class="fas fa-calendar-alt"></i> Calendário
                </a>
                <a href="{{ route('gestor.aprovar-mensagem') }}" class="menu-item {{ request()->routeIs('gestor.aprovar-mensagem*') ? 'active' : '' }}">
                    <i class="fas fa-comment-check"></i> Aprovar Mensagens
                </a>

                <div class="menu-section">Comunicação</div>
                <a href="{{ route('gestor.chat.index') }}" class="menu-item {{ request()->routeIs('gestor.chat.index') ? 'active' : '' }}">
                    <i class="fab fa-whatsapp"></i> Conversas
                </a>
                <a href="{{ route('gestor.chat.distribuicao') }}" class="menu-item {{ request()->routeIs('gestor.chat.distribuicao') ? 'active' : '' }}">
                    <i class="fas fa-random"></i> Distribuição
                </a>
                <a href="{{ route('gestor.chat.config') }}" class="menu-item {{ request()->routeIs('gestor.chat.config') ? 'active' : '' }}">
                    <i class="fas fa-phone-alt"></i> WhatsApp
                </a>

                <div class="menu-section">Financeiro</div>
                <a href="{{ route('vendedor.comissoes') }}" class="menu-item {{ request()->routeIs('vendedor.comissoes*') ? 'active' : '' }}">
                    <i class="fas fa-percentage"></i> Minhas Comissões
                </a>
                <a href="{{ route('vendedor.configuracoes') }}" class="menu-item {{ request()->routeIs('vendedor.configuracoes*') ? 'active' : '' }}">
                    <i class="fas fa-cog"></i> Split e Repasse
                </a>

                <div class="menu-section">IA Lab</div>
                <a href="{{ route('master.ia') }}" class="menu-item {{ request()->routeIs('master.ia*') ? 'active' : '' }}">
                    <i class="fas fa-chart-line"></i> Métricas de IA
                </a>

            @else
                {{-- VENDEDOR --}}
                <div class="menu-section">Painel</div>
                <a href="{{ route('vendedor.dashboard') }}" class="menu-item {{ request()->routeIs('vendedor.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-chart-pie"></i> Meu Painel
                </a>

                <div class="menu-section">Vendas</div>
                <a href="{{ route('vendedor.vendas') }}" class="menu-item {{ request()->routeIs('vendedor.vendas*') ? 'active' : '' }}">
                    <i class="fas fa-shopping-bag"></i> Minhas Vendas
                </a>
                <a href="{{ route('vendedor.clientes') }}" class="menu-item {{ request()->routeIs('vendedor.clientes*') ? 'active' : '' }}">
                    <i class="fas fa-user-friends"></i> Meus Clientes
                </a>
                <a href="{{ route('vendedor.pagamentos') }}" class="menu-item {{ request()->routeIs('vendedor.pagamentos') ? 'active' : '' }}">
                    <i class="fas fa-credit-card"></i> Pagamentos
                </a>

                <div class="menu-section">Comunicação</div>
                <a href="{{ route('chat.index') }}" class="menu-item {{ request()->routeIs('chat.index') || request()->routeIs('chat.conversa') ? 'active' : '' }}">
                    <i class="fab fa-whatsapp"></i> Chat WhatsApp
                </a>

                <div class="menu-section">Marketing</div>
                <a href="{{ route('vendedor.contatos.index') }}" class="menu-item {{ request()->routeIs('vendedor.contatos*') ? 'active' : '' }}">
                    <i class="fas fa-address-book"></i> Meus Leads
                </a>
                <a href="{{ route('vendedor.calendario.index') }}" class="menu-item {{ request()->routeIs('vendedor.calendario*') ? 'active' : '' }}">
                    <i class="fas fa-calendar-alt"></i> Meu Calendário
                </a>
                <a href="{{ route('configuracoes.primeira-mensagem') }}" class="menu-item {{ request()->routeIs('configuracoes.primeira-mensagem*') ? 'active' : '' }}">
                    <i class="fas fa-comment-dots"></i> Primeira Mensagem
                </a>

                <div class="menu-section">Financeiro</div>
                <a href="{{ route('vendedor.comissoes') }}" class="menu-item {{ request()->routeIs('vendedor.comissoes*') ? 'active' : '' }}">
                    <i class="fas fa-percentage"></i> Comissões
                </a>
                <a href="{{ route('vendedor.configuracoes') }}" class="menu-item {{ request()->routeIs('vendedor.configuracoes*') ? 'active' : '' }}">
                    <i class="fas fa-cog"></i> Configurações
                </a>

                <div class="menu-section">IA Lab</div>
                <a href="{{ route('vendedor.dashboard') }}" class="menu-item {{ request()->routeIs('vendedor.dashboard*') ? 'active' : '' }}">
                    <i class="fas fa-chart-line"></i> IA Métricas
                </a>
            @endif
        </nav>
        <div class="sidebar-footer">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn-logout">
                    <i class="fas fa-right-from-bracket"></i> <span class="btn-logout-text">Sair do Sistema</span>
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
                @php
                    $user2fa = Auth::user();
                @endphp
                @if(!$user2fa->two_factor_enabled)
                <a href="{{ route('2fa.setup') }}" style="display:inline-flex; align-items:center; gap:6px; padding:8px 14px; background:linear-gradient(135deg,#fef3c7,#fde68a); color:#92400e; border-radius:8px; font-size:0.78rem; font-weight:700; text-decoration:none; border:1px solid #fbbf24;">
                    <i class="fas fa-shield-halved"></i> Ativar 2FA
                </a>
                @else
                <span style="display:inline-flex; align-items:center; gap:6px; padding:8px 14px; background:#dcfce7; color:#166534; border-radius:8px; font-size:0.78rem; font-weight:700; border:1px solid #86efac;">
                    <i class="fas fa-shield-halved"></i> 2FA Ativo
                </span>
                @endif
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
                            <div class="notif-item {{ $notif->lida ? '' : 'unread' }}" onclick="verNotificacao({{ $notif->id }}, '{{ $notif->tipo }}', {{ $notif->dados['venda_id'] ?? null }})">
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
            @if(session('warning'))
            <div class="alert" style="background: #fef3c7; border: 1px solid #f59e0b; color: #92400e; padding: 14px 18px; border-radius: 10px; margin-bottom: 16px; display: flex; align-items: center; gap: 10px; font-weight: 600;">
                <i class="fas fa-exclamation-triangle" style="color: #f59e0b; font-size: 1.1rem;"></i>
                <span>{{ session('warning') }}</span>
            </div>
            @endif
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
    // ── Sidebar Collapse Toggle ──
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        sidebar.classList.toggle('collapsed');
        localStorage.setItem('sidebar_collapsed', sidebar.classList.contains('collapsed'));
    }
    // Restore sidebar state from localStorage
    (function() {
        const collapsed = localStorage.getItem('sidebar_collapsed') === 'true';
        if (collapsed) {
            document.getElementById('sidebar').classList.add('collapsed');
        }
    })();

    function toggleNotif() {
        document.getElementById('notifDropdown').classList.toggle('show');
    }
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.notif-wrapper')) {
            var dd = document.getElementById('notifDropdown');
            if (dd) dd.classList.remove('show');
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
    <script src="/js/custom-selects.js"></script>
    
    @if(session('iniciar_tour') && !Auth::user()->tour_completo)
    <script type="module">
        import { iniciarTour } from '/resources/js/tour.js';
        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(iniciarTour, 1200);
        });
    </script>
    @endif
</body>
</html>
