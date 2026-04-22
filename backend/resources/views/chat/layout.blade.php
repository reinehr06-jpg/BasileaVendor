@extends('layouts.app')
@section('title', 'Chat Hub')

@section('content')
<style>
    :root {
        --chat-sidebar-width: 360px;
        --chat-bubble-in: #f1f5f9;
        --chat-bubble-out: var(--primary);
        --chat-bg: #f8fafc;
    }

    .chat-container {
        display: flex;
        height: calc(100vh - 100px);
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: var(--shadow-lg);
        border: 1px solid var(--border-light);
    }

    .chat-sidebar {
        width: var(--chat-sidebar-width);
        border-right: 1px solid var(--border-light);
        display: flex;
        flex-direction: column;
        background: #fff;
    }

    .chat-sidebar-header {
        padding: 20px;
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        color: white;
    }

    .chat-sidebar-header h4 { margin: 0; font-weight: 800; font-size: 1.2rem; }

    .chat-tabs {
        display: flex;
        padding: 10px;
        gap: 8px;
        border-bottom: 1px solid var(--border-light);
        background: #f8fafc;
    }

    .chat-tab-btn {
        flex: 1;
        padding: 8px;
        border-radius: 10px;
        border: 1px solid transparent;
        background: transparent;
        font-size: 0.8rem;
        font-weight: 700;
        color: var(--text-muted);
        cursor: pointer;
        transition: 0.2s;
    }

    .chat-tab-btn.active {
        background: white;
        color: var(--primary);
        border-color: var(--border-light);
        box-shadow: var(--shadow-sm);
    }

    .chat-list {
        flex: 1;
        overflow-y: auto;
    }

    .chat-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 15px 20px;
        cursor: pointer;
        transition: 0.2s;
        border-bottom: 1px solid #f1f5f9;
    }

    .chat-item:hover { background: #f8fafc; }
    .chat-item.active { background: rgba(var(--primary-rgb), 0.05); border-left: 4px solid var(--primary); }

    .chat-item-avatar {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        background: var(--primary-light);
        color: var(--primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 1.1rem;
        flex-shrink: 0;
    }

    .chat-item-info { flex: 1; min-width: 0; }
    .chat-item-name { font-weight: 700; color: var(--text-primary); font-size: 0.95rem; display: flex; justify-content: space-between; }
    .chat-item-time { font-size: 0.7rem; color: var(--text-muted); font-weight: 400; }
    .chat-item-preview { font-size: 0.85rem; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 2px; }

    .chat-main {
        flex: 1;
        display: flex;
        flex-direction: column;
        background: var(--chat-bg);
        position: relative;
    }

    .chat-main-header {
        padding: 15px 25px;
        background: white;
        border-bottom: 1px solid var(--border-light);
        display: flex;
        align-items: center;
        justify-content: space-between;
        z-index: 10;
    }

    .chat-messages {
        flex: 1;
        overflow-y: auto;
        padding: 25px;
        display: flex;
        flex-direction: column;
        gap: 15px;
        background-image: url('https://user-images.githubusercontent.com/15075759/28719144-86dc0f70-73b1-11e7-911d-60d70fcded21.png');
        background-blend-mode: overlay;
        background-color: rgba(248, 250, 252, 0.95);
    }

    .message-bubble {
        max-width: 70%;
        padding: 12px 16px;
        border-radius: 18px;
        font-size: 0.95rem;
        line-height: 1.4;
        position: relative;
        box-shadow: var(--shadow-sm);
    }

    .message-in {
        align-self: flex-start;
        background: white;
        color: var(--text-primary);
        border-bottom-left-radius: 4px;
    }

    .message-out {
        align-self: flex-end;
        background: var(--primary);
        color: white;
        border-bottom-right-radius: 4px;
    }

    .message-time {
        font-size: 0.65rem;
        margin-top: 4px;
        opacity: 0.7;
        text-align: right;
    }

    .chat-input-area {
        padding: 20px 25px;
        background: white;
        border-top: 1px solid var(--border-light);
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .chat-input {
        flex: 1;
        background: #f1f5f9;
        border: 1px solid transparent;
        padding: 12px 20px;
        border-radius: 12px;
        outline: none;
        transition: 0.2s;
    }

    .chat-input:focus { background: white; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.1); }

    .btn-send {
        width: 45px;
        height: 45px;
        border-radius: 12px;
        background: var(--primary);
        color: white;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        cursor: pointer;
        transition: 0.2s;
    }

    .btn-send:hover { transform: scale(1.05); background: var(--primary-dark); }
</style>

<div class="chat-container">
    <!-- Sidebar de Conversas -->
    <div class="chat-sidebar">
        <div class="chat-sidebar-header">
            <h4>Chat Hub</h4>
            <div style="font-size: 0.75rem; opacity: 0.8; margin-top: 4px;">Monitoramento Global de Mensagens</div>
        </div>
        
        <div class="chat-tabs">
            <button class="chat-tab-btn active">Pendentes</button>
            <button class="chat-tab-btn">Em Atendimento</button>
            <button class="chat-tab-btn">Todos</button>
        </div>

        <div class="chat-search" style="padding: 12px;">
            <input type="text" class="form-control form-control-sm" placeholder="Buscar conversa...">
        </div>

        <div class="chat-list">
            @yield('chat-sidebar-items')
        </div>
    </div>

    <!-- Área Principal do Chat -->
    <div class="chat-main">
        @yield('chat-content')
    </div>
</div>
@endsection