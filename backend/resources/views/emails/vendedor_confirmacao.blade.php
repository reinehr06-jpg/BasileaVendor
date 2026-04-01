<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <style>
        body { margin: 0; padding: 0; background: #f4f4f8; font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, sans-serif; }
        .wrapper { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #6d28d9, #4c1d95); padding: 36px 40px; text-align: center; }
        .header h1 { color: #fff; margin: 0; font-size: 22px; }
        .header p { color: #ddd6fe; margin: 8px 0 0; font-size: 14px; }
        .badge { display: inline-block; background: #22c55e; color: #fff; padding: 6px 18px; border-radius: 999px; font-size: 13px; font-weight: 600; margin-top: 16px; }
        .body { padding: 36px 40px; }
        .body p { color: #374151; font-size: 15px; line-height: 1.6; margin: 0 0 16px; }
        .info-box { background: #f5f3ff; border-left: 4px solid #7c3aed; border-radius: 8px; padding: 20px 24px; margin: 24px 0; }
        .row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #ede9fe; font-size: 14px; color: #374151; }
        .row:last-child { border-bottom: none; }
        .row span:first-child { font-weight: 600; color: #6d28d9; }
        .footer { background: #f9fafb; border-top: 1px solid #e5e7eb; padding: 20px 40px; text-align: center; }
        .footer p { color: #9ca3af; font-size: 12px; margin: 0; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1>Basiléia Vendas</h1>
            <p>Notificação de Pagamento</p>
            <span class="badge">✅ PAGAMENTO CONFIRMADO</span>
        </div>
        <div class="body">
            <p>Olá, <strong>{{ $venda->vendedor->user->name ?? ($venda->vendedor->nome ?? 'Vendedor') }}</strong>!</p>
            <p>Um pagamento foi confirmado. Confira os detalhes:</p>

            <div class="info-box">
                <div class="row">
                    <span>Igreja</span>
                    <span>{{ $venda->cliente->nome_igreja ?? $venda->cliente->nome ?? 'N/A' }}</span>
                </div>
                <div class="row">
                    <span>Responsável</span>
                    <span>{{ $venda->cliente->nome_pastor ?? $venda->cliente->nome_responsavel ?? 'N/A' }}</span>
                </div>
                <div class="row">
                    <span>Plano</span>
                    <span>{{ $venda->plano ?? 'N/A' }}</span>
                </div>
                <div class="row">
                    <span>Valor</span>
                    <span>R$ {{ number_format($venda->valor_final ?? $venda->valor, 2, ',', '.') }}</span>
                </div>
                @if($venda->forma_pagamento)
                <div class="row">
                    <span>Forma de Pagamento</span>
                    <span>{{ $venda->forma_pagamento }}</span>
                </div>
                @endif
                <div class="row">
                    <span>Sua Comissão</span>
                    <span style="color: #22c55e; font-weight: 700;">R$ {{ number_format($venda->comissao_gerada ?? 0, 2, ',', '.') }}</span>
                </div>
                <div class="row">
                    <span>Status</span>
                    <span style="color: #22c55e; font-weight: 600;">Pago ✅</span>
                </div>
                <div class="row">
                    <span>Data</span>
                    <span>{{ $venda->updated_at->format('d/m/Y H:i') }}</span>
                </div>
            </div>

            <p>Continue assim! 🚀</p>
        </div>
        <div class="footer">
            <p>Basiléia Global — Sistema de Vendas © {{ date('Y') }}</p>
        </div>
    </div>
</body>
</html>
