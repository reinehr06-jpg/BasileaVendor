<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat - BasileaVendor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4F46E5;
            --secondary-color: #6366F1;
            --bg-light: #F8FAFC;
            --border-color: #E2E8F0;
            --text-primary: #1E293B;
            --text-secondary: #64748B;
            --success-color: #10B981;
            --warning-color: #F59E0B;
            --danger-color: #EF4444;
            --chat-inbound: #E8F5E9;
            --chat-outbound: #EDE9FE;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--bg-light);
            margin: 0;
            padding: 0;
            height: 100vh;
            overflow: hidden;
        }

        .chat-container {
            display: flex;
            height: 100vh;
            background: white;
        }

        .chat-sidebar {
            width: 400px;
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            background: white;
        }

        .chat-header {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border-color);
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
        }

        .chat-header h4 {
            margin: 0;
            font-weight: 600;
        }

        .chat-tabs {
            display: flex;
            border-bottom: 1px solid var(--border-color);
            background: #F8FAFC;
        }

        .chat-tab {
            flex: 1;
            padding: 12px;
            text-align: center;
            cursor: pointer;
            border: none;
            background: none;
            font-weight: 500;
            color: var(--text-secondary);
            transition: all 0.2s;
            border-bottom: 2px solid transparent;
        }

        .chat-tab:hover {
            background: #EFF6FF;
        }

        .chat-tab.active {
            color: var(--primary-color);
            border-bottom-color: var(--primary-color);
        }

        .chat-tab .badge {
            background: var(--danger-color);
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 11px;
            margin-left: 6px;
        }

        .chat-search {
            padding: 12px;
            border-bottom: 1px solid var(--border-color);
        }

        .chat-search input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 14px;
            outline: none;
        }

        .chat-search input:focus {
            border-color: var(--primary-color);
        }

        .chat-list {
            flex: 1;
            overflow-y: auto;
        }

        .chat-item {
            padding: 12px 16px;
            border-bottom: 1px solid var(--border-color);
            cursor: pointer;
            display: flex;
            align-items: center;
            transition: background 0.2s;
        }

        .chat-item:hover {
            background: #F8FAFC;
        }

        .chat-item.active {
            background: #EEF2FF;
        }

        .chat-item.pinned {
            background: #FFFBEB;
        }

        .chat-item-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 18px;
            margin-right: 12px;
            flex-shrink: 0;
        }

        .chat-item-info {
            flex: 1;
            min-width: 0;
        }

        .chat-item-name {
            font-weight: 600;
            color: var(--text-primary);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .chat-item-time {
            font-size: 12px;
            color: var(--text-secondary);
        }

        .chat-item-preview {
            font-size: 13px;
            color: var(--text-secondary);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .chat-item-badge {
            background: var(--danger-color);
            color: white;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 600;
        }

        .chat-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: white;
        }

        .chat-main-header {
            padding: 12px 20px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            background: white;
        }

        .chat-main-header .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            margin-right: 12px;
        }

        .chat-main-header .info h5 {
            margin: 0;
            font-weight: 600;
        }

        .chat-main-header .info p {
            margin: 0;
            font-size: 12px;
            color: var(--text-secondary);
        }

        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            background: #F8FAFC;
        }

        .message {
            display: flex;
            margin-bottom: 16px;
            align-items: flex-end;
        }

        .message.inbound {
            justify-content: flex-start;
        }

        .message.outbound {
            justify-content: flex-end;
        }

        .message-bubble {
            max-width: 70%;
            padding: 10px 14px;
            border-radius: 12px;
            font-size: 14px;
            line-height: 1.4;
        }

        .message.inbound .message-bubble {
            background: white;
            border: 1px solid var(--border-color);
            border-bottom-left-radius: 4px;
        }

        .message.outbound .message-bubble {
            background: var(--primary-color);
            color: white;
            border-bottom-right-radius: 4px;
        }

        .message-time {
            font-size: 10px;
            color: var(--text-secondary);
            margin-top: 4px;
        }

        .message.outbound .message-time {
            color: rgba(255,255,255,0.7);
            text-align: right;
        }

        .message-status {
            font-size: 12px;
            color: rgba(255,255,255,0.8);
            margin-top: 2px;
        }

        .chat-input {
            padding: 16px 20px;
            border-top: 1px solid var(--border-color);
            background: white;
        }

        .chat-input form {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .chat-input input {
            flex: 1;
            padding: 12px 16px;
            border: 1px solid var(--border-color);
            border-radius: 24px;
            font-size: 14px;
            outline: none;
        }

        .chat-input input:focus {
            border-color: var(--primary-color);
        }

        .chat-input button {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            border: none;
            background: var(--primary-color);
            color: white;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s;
        }

        .chat-input button:hover {
            background: var(--secondary-color);
        }

        .empty-state {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: var(--text-secondary);
            padding: 40px;
            text-align: center;
        }

        .empty-state i {
            font-size: 64px;
            margin-bottom: 16px;
            color: var(--border-color);
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 500;
        }

        .status-aberta {
            background: #DBEAFE;
            color: #1D4ED8;
        }

        .status-pendente {
            background: #FEF3C7;
            color: #B45309;
        }

        .status-resolvida {
            background: #D1FAE5;
            color: #047857;
        }

        .pin-icon {
            color: var(--warning-color);
            margin-right: 8px;
        }

        .atendido-badge {
            font-size: 10px;
            padding: 2px 6px;
            border-radius: 4px;
            background: #D1FAE5;
            color: #047857;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .message {
            animation: fadeIn 0.3s ease;
        }
    </style>
</head>
<body>
    <div class="chat-container">
        @yield('content')
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
</body>
</html>