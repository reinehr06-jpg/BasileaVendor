@extends('layouts.app')
@section('title', 'Chat')

@section('content')
<style>
        :root {
            --chat-bg: #f4f5fa;
            --chat-sidebar-bg: #ffffff;
            --chat-header-grad: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            --chat-bubble-in: #ffffff;
            --chat-bubble-out: var(--primary);
            --chat-bubble-out-text: #ffffff;
            --chat-border: #eef0f7;
        }

        .chat-container {
            display: flex;
            height: calc(100vh - 70px);
            background: var(--chat-bg);
            border-radius: var(--radius-xl);
            overflow: hidden;
            box-shadow: var(--shadow-lg);
            margin: 0;
            border: 1px solid var(--border-light);
            animate: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .chat-sidebar {
            width: 380px;
            border-right: 1px solid var(--chat-border);
            display: flex;
            flex-direction: column;
            background: var(--chat-sidebar-bg);
            z-index: 10;
        }

        .chat-header {
            padding: 24px;
            background: var(--chat-header-grad);
            color: white;
            position: relative;
        }

        .chat-header h4 {
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

        .chat-tab {
            flex: 1;
            padding: 10px;
            text-align: center;
            cursor: pointer;
            border: none;
            background: transparent;
            font-weight: 600;
            font-size: 0.8rem;
            color: var(--text-secondary);
            transition: var(--transition);
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .chat-tab:hover {
            background: var(--bg);
            color: var(--primary);
        }

        .chat-tab.active {
            background: rgba(var(--primary-rgb), 0.08);
            color: var(--primary);
        }

        .chat-tab .badge {
            background: var(--primary);
            color: white;
            padding: 2px 6px;
            border-radius: 6px;
            font-size: 10px;
        }

        .chat-search {
            padding: 16px;
            background: white;
            border-bottom: 1px solid var(--chat-border);
        }

        .chat-search input {
            width: 100%;
            padding: 12px 16px;
            border: 1.5px solid var(--border-light);
            border-radius: var(--radius-lg);
            font-size: 0.875rem;
            outline: none;
            transition: var(--transition);
            background: var(--bg);
        }

        .chat-search input:focus {
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 4px rgba(var(--primary-rgb), 0.1);
        }

        .chat-list {
            flex: 1;
            overflow-y: auto;
            background: white;
        }

        .chat-item {
            padding: 16px 20px;
            border-bottom: 1px solid var(--chat-border);
            cursor: pointer;
            display: flex;
            align-items: center;
            transition: var(--transition);
            text-decoration: none !important;
            color: inherit;
        }

        .chat-item:hover {
            background: var(--surface-hover);
        }

        .chat-item.active {
            background: rgba(var(--primary-rgb), 0.04);
            border-left: 4px solid var(--primary);
            padding-left: 16px;
        }

        .chat-item-avatar {
            width: 52px;
            height: 52px;
            border-radius: 16px;
            background: var(--primary-gradient);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.2rem;
            margin-right: 14px;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(var(--primary-rgb), 0.2);
        }

        .chat-item-info {
            flex: 1;
            min-width: 0;
        }

        .chat-item-name {
            font-weight: 700;
            color: var(--text-primary);
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 4px;
        }

        .chat-item-time {
            font-size: 0.7rem;
            color: var(--text-muted);
            font-weight: 500;
        }

        .chat-item-preview {
            font-size: 0.8125rem;
            color: var(--text-secondary);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1.2;
        }

        .chat-item-badge {
            background: var(--danger);
            color: white;
            min-width: 20px;
            height: 20px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: 800;
            margin-left: 8px;
            box-shadow: 0 2px 6px rgba(239, 68, 68, 0.4);
        }

        .chat-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: var(--chat-bg);
            position: relative;
        }

        .chat-main-header {
            padding: 16px 24px;
            border-bottom: 1px solid var(--chat-border);
            display: flex;
            align-items: center;
            background: white;
            z-index: 5;
            box-shadow: var(--shadow-xs);
        }

        .chat-main-header .avatar {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: var(--primary-gradient);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            margin-right: 14px;
        }

        .chat-main-header .info h5 {
            margin: 0;
            font-weight: 700;
            font-size: 1rem;
            color: var(--text-primary);
        }

        .chat-main-header .info p {
            margin: 0;
            font-size: 0.75rem;
            color: var(--success);
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 30px 24px;
            background: var(--chat-bg);
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .message {
            display: flex;
            flex-direction: column;
            max-width: 75%;
            animate: fadeIn 0.4s ease;
        }

        .message.inbound {
            align-self: flex-start;
        }

        .message.outbound {
            align-self: flex-end;
        }

        .message-bubble {
            padding: 12px 18px;
            font-size: 0.9375rem;
            line-height: 1.5;
            position: relative;
            box-shadow: var(--shadow-xs);
        }

        .message.inbound .message-bubble {
            background: white;
            color: var(--text-primary);
            border-radius: 4px 18px 18px 18px;
            border: 1px solid var(--chat-border);
        }

        .message.outbound .message-bubble {
            background: var(--primary-gradient);
            color: white;
            border-radius: 18px 18px 4px 18px;
            box-shadow: 0 4px 15px rgba(var(--primary-rgb), 0.2);
        }

        .message-time {
            font-size: 0.65rem;
            color: var(--text-muted);
            margin-top: 5px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .message.outbound .message-time {
            color: rgba(255,255,255,0.7);
            text-align: right;
            margin-top: 8px;
        }

        .chat-input {
            padding: 20px 24px;
            background: white;
            border-top: 1px solid var(--chat-border);
        }

        .chat-input form {
            display: flex;
            align-items: center;
            gap: 12px;
            background: var(--bg);
            padding: 6px 6px 6px 18px;
            border-radius: 30px;
            border: 1.5px solid var(--border-light);
            transition: var(--transition);
        }

        .chat-input form:focus-within {
            background: white;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(var(--primary-rgb), 0.1);
        }

        .chat-input input {
            flex: 1;
            border: none;
            background: transparent;
            font-size: 0.9375rem;
            outline: none;
            padding: 8px 0;
            color: var(--text-primary);
        }

        .chat-input button {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            border: none;
            background: var(--primary-gradient);
            color: white;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
            box-shadow: 0 4px 10px rgba(var(--primary-rgb), 0.3);
        }

        .chat-input button:hover {
            transform: scale(1.05) rotate(-5deg);
            box-shadow: 0 6px 15px rgba(var(--primary-rgb), 0.4);
        }

        .empty-state {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: var(--text-muted);
            padding: 40px;
            text-align: center;
        }

        .empty-state i {
            font-size: 80px;
            margin-bottom: 24px;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            opacity: 0.3;
        }

        .empty-state h4 {
            color: var(--text-primary);
            font-weight: 800;
            margin-bottom: 10px;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
<div class="chat-container">
    @yield('chat-content')
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
        function scrollToBottom() {
            const messagesContainer = document.querySelector('.chat-messages');
            if (messagesContainer) {
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }
        }

        function sendMessage(formId) {
            const form = document.getElementById(formId);
            if (!form) return;

            const input = form.querySelector('input[name="mensagem"]');
            const button = form.querySelector('button[type="submit"]');
            
            if (!input.value.trim()) return;

            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

            fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ mensagem: input.value })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    input.value = '';
                    location.reload();
                } else {
                    alert('Erro ao enviar mensagem');
                }
            })
            .catch(err => {
                alert('Erro ao enviar mensagem');
            })
            .finally(() => {
                button.disabled = false;
                button.innerHTML = '<i class="fas fa-paper-plane"></i>';
            });
        }

        document.addEventListener('DOMContentLoaded', scrollToBottom);
</script>
@endsection