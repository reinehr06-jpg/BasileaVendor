@extends('layouts.app')

@section('header_title', 'Centro de Comunicação')
@section('header_description', 'Gerencie seus atendimentos em tempo real com agilidade.')

@section('content')
<style>
    :root {
        --chat-sidebar-width: 360px;
        --chat-bubble-in: #ffffff;
        --chat-bubble-out: #f3e8ff; /* Roxo clarinho para outbound */
        --chat-bg: #f8fafc;
        --chat-sidebar-bg: #ffffff;
        --chat-border: #f1f5f9;
        --primary-gradient: linear-gradient(135deg, #3B0764 0%, #4C1D95 100%);
    }

    .chat-container {
        display: flex;
        height: calc(100vh - 280px); /* Ajustado para o banner global */
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 10px 40px rgba(0,0,0,0.05);
        border: 1px solid var(--chat-border);
    }

    /* Sidebar Styling */
    .chat-sidebar {
        width: var(--chat-sidebar-width);
        border-right: 1px solid var(--chat-border);
        display: flex;
        flex-direction: column;
        background: white;
    }

    .chat-sidebar-header {
        padding: 20px;
        border-bottom: 1px solid var(--chat-border);
        background: #fafafa;
    }

    .chat-sidebar-header h4 {
        margin: 0;
        font-weight: 800;
        color: var(--text-main);
        font-size: 1.1rem;
    }

    .chat-tabs {
        display: flex;
        padding: 10px;
        gap: 8px;
        background: white;
        border-bottom: 1px solid var(--chat-border);
    }

    .chat-tab-btn {
        flex: 1;
        padding: 10px;
        border-radius: 10px;
        border: none;
        background: #f8fafc;
        font-weight: 700;
        font-size: 0.8rem;
        color: #64748b;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }

    .chat-tab-btn.active {
        background: #f3e8ff;
        color: #6b21a8;
    }

    .chat-search-box {
        padding: 12px 15px;
    }

    .chat-search-input {
        width: 100%;
        padding: 10px 15px;
        border-radius: 12px;
        border: 1px solid var(--chat-border);
        background: #f8fafc;
        font-size: 0.9rem;
        outline: none;
    }

    .chat-list {
        flex: 1;
        overflow-y: auto;
    }

    .chat-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 15px;
        cursor: pointer;
        transition: 0.2s;
        border-bottom: 1px solid #f8fafc;
        text-decoration: none !important;
        color: inherit;
    }

    .chat-item:hover { background: #fdfaff; }
    .chat-item.active { background: #f5f3ff; border-right: 3px solid #7c3aed; }

    .chat-item-avatar {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        background: #7c3aed;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        flex-shrink: 0;
    }

    .chat-item-info { flex: 1; min-width: 0; }
    .chat-item-name { font-weight: 700; font-size: 0.95rem; display: flex; justify-content: space-between; margin-bottom: 4px; }
    .chat-item-time { font-size: 0.75rem; color: #94a3b8; font-weight: 500; }
    .chat-item-preview { font-size: 0.85rem; color: #64748b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

    /* Main Area */
    .chat-main {
        flex: 1;
        display: flex;
        flex-direction: column;
        background: #fff;
    }

    .chat-main-header {
        padding: 15px 25px;
        border-bottom: 1px solid var(--chat-border);
        display: flex;
        align-items: center;
        gap: 15px;
        background: white;
    }

    .chat-messages {
        flex: 1;
        padding: 25px;
        overflow-y: auto;
        background: #f8fafc;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .message { display: flex; flex-direction: column; max-width: 75%; }
    .message.inbound { align-self: flex-start; }
    .message.outbound { align-self: flex-end; }

    .message-bubble {
        padding: 12px 18px;
        border-radius: 18px;
        font-size: 0.95rem;
        line-height: 1.5;
        position: relative;
    }

    .inbound .message-bubble { background: white; border: 1px solid #e2e8f0; color: #1e293b; border-bottom-left-radius: 4px; }
    .outbound .message-bubble { background: #7c3aed; color: white; border-bottom-right-radius: 4px; }

    .message-time { font-size: 0.7rem; margin-top: 5px; opacity: 0.6; display: flex; align-items: center; gap: 4px; }
    .outbound .message-time { color: white; justify-content: flex-end; }

    .chat-input-area {
        padding: 20px;
        border-top: 1px solid var(--chat-border);
        background: white;
    }

    .chat-input-form {
        display: flex;
        gap: 12px;
        background: #f8fafc;
        padding: 8px;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
    }

    .chat-input-form input {
        flex: 1;
        border: none;
        background: transparent;
        padding: 10px 15px;
        outline: none;
        font-size: 0.95rem;
    }

    .btn-send {
        background: #7c3aed;
        color: white;
        border: none;
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: 0.2s;
    }

    .btn-send:hover { background: #6d28d9; transform: scale(1.05); }

    .empty-state {
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #94a3b8;
        padding: 40px;
        text-align: center;
    }
</style>

<div class="chat-container">
    @if(View::hasSection('chat-sidebar'))
    <aside class="chat-sidebar">
        @yield('chat-sidebar')
    </aside>
    <main class="chat-main">
        @yield('chat-content')
    </main>
    @else
        @yield('chat-content')
    @endif
</div>
@endsection