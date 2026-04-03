<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Vendas - Basiléia</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; margin: 0; padding: 0; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #4C1D95; padding-bottom: 10px; }
        .header h1 { color: #4C1D95; margin: 0; font-size: 24px; }
        .header p { margin: 5px 0 0; color: #666; }
        
        .filters { margin-bottom: 20px; background: #f9f9f9; padding: 15px; border-radius: 5px; }
        .filters span { font-weight: bold; margin-right: 15px; }
        
        .kpi-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .kpi-table td { padding: 15px; border: 1px solid #ddd; text-align: center; }
        .kpi-label { font-size: 10px; color: #991b1b; text-transform: uppercase; margin-bottom: 5px; font-weight: bold; }
        .kpi-value { font-size: 18px; font-weight: bold; }

        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th { background: #4C1D95; color: white; padding: 10px; text-align: left; font-size: 11px; }
        .data-table td { padding: 8px 10px; border-bottom: 1px solid #eee; font-size: 10px; }
        .data-table tr:nth-child(even) { background: #fcfcfc; }
        
        .footer { position: fixed; bottom: -20px; left: 0; right: 0; font-size: 10px; text-align: center; color: #999; border-top: 1px solid #eee; padding-top: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Basiléia Vendas</h1>
        <p>Relatório de Vendas e Operações</p>
    </div>

    <div class="filters">
        <span>Período: {{ \Carbon\Carbon::parse($filtros['data_inicio'])->format('d/m/Y') }} à {{ \Carbon\Carbon::parse($filtros['data_fim'])->format('d/m/Y') }}</span>
        @if(isset($filtros['status']) && $filtros['status'])
            <span>Status: {{ ucfirst($filtros['status']) }}</span>
        @endif
    </div>

    <table class="kpi-table">
        <tr>
            <td>
                <div class="kpi-label">Total de Vendas</div>
                <div class="kpi-value">{{ $resumo['totalVendas'] }}</div>
            </td>
            <td>
                <div class="kpi-label">Valor Vendido</div>
                <div class="kpi-value">R$ {{ number_format($resumo['valorVendido'], 2, ',', '.') }}</div>
            </td>
            <td>
                <div class="kpi-label">Valor Recebido</div>
                <div class="kpi-value">R$ {{ number_format($resumo['valorRecebido'], 2, ',', '.') }}</div>
            </td>
            <td>
                <div class="kpi-label">Comissões</div>
                <div class="kpi-value">R$ {{ number_format($resumo['totalComissoes'], 2, ',', '.') }}</div>
            </td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th>Data</th>
                <th>Igreja/Pastor</th>
                <th>Vendedor</th>
                <th>Plano</th>
                <th>Valor</th>
                <th>Status</th>
                <th>Forma Pgto</th>
            </tr>
        </thead>
        <tbody>
            @foreach($vendas as $v)
            <tr>
                <td>{{ $v->created_at->format('d/m/Y') }}</td>
                <td>
                    <strong>{{ $v->cliente->nome_igreja ?? $v->cliente->nome ?? '—' }}</strong><br>
                    <small>{{ $v->cliente->nome_pastor ?? '' }}</small>
                </td>
                <td>{{ $v->vendedor->user->name ?? 'N/A' }}</td>
                <td>{{ $v->plano ?? '—' }}</td>
                <td>R$ {{ number_format($v->valor, 2, ',', '.') }}</td>
                <td>{{ $v->status }}</td>
                <td>{{ $v->forma_pagamento ?? '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Gerado em {{ now()->format('d/m/Y H:i') }} - Basiléia Vendas &copy; {{ date('Y') }}
    </div>
</body>
</html>
