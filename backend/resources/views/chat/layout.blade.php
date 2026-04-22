@extends('layouts.app')
@section('title', 'Chat Hub')

@section('content')
<style>
    :root {
        --chat-sidebar-width: 380px;
        --chat-bubble-in: #ffffff;
        --chat-bubble-out: var(--primary);
        --chat-bg: #f4f5fa;
        --chat-sidebar-bg: #ffffff;
        --chat-header-grad: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        --chat-border: #eef0f7;
    }

    .chat-container {
        display: flex;
        height: calc(100vh - 100px);
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 20px 50px rgba(0,0,0,0.1);
        border: 1px solid rgba(255,255,255,0.4);
        margin: 0;
    }

    .chat-sidebar {
        width: var(--chat-sidebar-width);
        border-right: 1px solid var(--chat-border);
        display: flex;
        flex-direction: column;
        background: rgba(255, 255, 255, 0.5);
        z-index: 10;
    }

    .chat-sidebar-header {
        padding: 24px;
        background: var(--chat-header-grad);
        color: white;
        position: relative;
    }

    .chat-sidebar-header h4 { 
        margin: 0; 
        font-weight: 800; 
        letter-spacing: -0.5px;
        font-size: 1.25rem;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .chat-tabs {
        display: flex;
        background: #ffffff;
        padding: 8px;
        gap: 4px;
        border-bottom: 1px solid var(--chat-border);
    }

    .chat-tab-btn {
        flex: 1;
        padding: 10px;
        text-align: center;
        cursor: pointer;
        border: none;
        background: transparent;
        font-weight: 700;
        font-size: 0.8rem;
        color: var(--text-secondary);
        transition: 0.2s;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }

    .chat-tab-btn:hover { background: #f8fafc; color: var(--primary); }
    .chat-tab-btn.active {
        background: rgba(var(--primary-rgb), 0.08);
        color: var(--primary);
    }

    .chat-search-box {
        padding: 16px;
        background: white;
        border-bottom: 1px solid var(--chat-border);
    }

    .chat-search-input {
        width: 100%;
        padding: 12px 16px;
        border: 1px solid var(--chat-border);
        border-radius: 14px;
        background: #f8fafc;
        font-size: 0.9rem;
        transition: 0.2s;
        outline: none;
    }

    .chat-search-input:focus {
        background: white;
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(var(--primary-rgb), 0.1);
    }

    .chat-list {
        flex: 1;
        overflow-y: auto;
        background: white;
    }

    .chat-list::-webkit-scrollbar { width: 6px; }
    .chat-list::-webkit-scrollbar-track { background: transparent; }
    .chat-list::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }

    .chat-item {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 16px 20px;
        cursor: pointer;
        transition: 0.2s;
        border-bottom: 1px solid #f8fafc;
        text-decoration: none !important;
        position: relative;
    }

    .chat-item:hover { background: #f9fafb; }
    .chat-item.active { 
        background: rgba(var(--primary-rgb), 0.04);
    }
    .chat-item.active::after {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background: var(--primary);
    }

    .chat-item-avatar {
        width: 52px;
        height: 52px;
        border-radius: 16px;
        background: var(--primary-light);
        color: var(--primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 1.2rem;
        flex-shrink: 0;
        box-shadow: 0 4px 10px rgba(var(--primary-rgb), 0.1);
    }

    .chat-item-info { flex: 1; min-width: 0; }
    .chat-item-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px; }
    .chat-item-name { font-weight: 700; color: #1e293b; font-size: 0.95rem; }
    .chat-item-time { font-size: 0.75rem; color: #94a3b8; font-weight: 500; }
    .chat-item-preview { 
        font-size: 0.85rem; 
        color: #64748b; 
        white-space: nowrap; 
        overflow: hidden; 
        text-overflow: ellipsis; 
        line-height: 1.4;
    }

    .chat-main {
        flex: 1;
        display: flex;
        flex-direction: column;
        background: var(--chat-bg);
        position: relative;
        background-image: radial-gradient(var(--border) 1px, transparent 1px);
        background-size: 20px 20px;
    }

    .chat-main-header {
        padding: 16px 28px;
        background: white;
        border-bottom: 1px solid var(--chat-border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        z-index: 10;
        box-shadow: 0 2px 10px rgba(0,0,0,0.02);
    }

    .chat-empty-state {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #94a3b8;
        padding: 40px;
        text-align: center;
    }

    .chat-empty-state i { 
        font-size: 4rem; 
        margin-bottom: 24px; 
        background: linear-gradient(135deg, #e2e8f0 0%, #cbd5e1 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .chat-empty-state h3 { color: #475569; font-weight: 800; margin-bottom: 8px; }
    .chat-empty-state p { max-width: 300px; line-height: 1.5; font-size: 0.95rem; }

    /* Custom Scrollbar */
    .chat-container ::-webkit-scrollbar { width: 6px; }
    .chat-container ::-webkit-scrollbar-track { background: transparent; }
    .chat-container ::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 10px; }
</style>

<div class="chat-container">
    <div class="chat-sidebar">
        @yield('chat-sidebar')
    </div>
    <div class="chat-main">
        @yield('chat-content')
    </div>
</div>
@endsection