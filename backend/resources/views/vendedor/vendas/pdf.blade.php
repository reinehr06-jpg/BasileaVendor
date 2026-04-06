<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Vendas</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #333; font-size: 11px; margin: 0; padding: 20px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #4C1D95; padding-bottom: 15px; }
        .header h1 { color: #4C1D95; margin: 0; font-size: 22px; text-transform: uppercase; }
        .header p { margin: 5px 0 0; color: #666; font-size: 13px; }
        
        .summary { margin-bottom: 30px; display: table; width: 100%; border-collapse: collapse; }
        .summary-box { display: table-cell; width: 33.33%; padding: 15px; background: #f9fafb; border: 1px solid #e5e7eb; text-align: center; }
        .summary-box.highlight { background: #4C1D95; color: white; }
        .summary-label { font-size: 9px; text-transform: uppercase; margin-bottom: 5px; font-weight: bold; }
        .summary-value { font-size: 16px; font-weight: bold; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #4C1D95; color: white; padding: 10px; text-align: left; font-size: 10px; text-transform: uppercase; }
        td { padding: 8px 10px; border-bottom: 1px solid #eee; }
        .status { padding: 3px 8px; border-radius: 4px; font-size: 9px; font-weight: bold; text-transform: uppercase; }
        .pago { background-color: #dcfce7; color: #166534; }
        .pendente { background-color: #fef9c3; color: #854d0e; }
        .cancelado { background-color: #f1f5f9; color: #64748b; }
        
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #999; padding-top: 10px; border-top: 1px solid #eee; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Relatório de Vendas</h1>
        <p>Gerado em {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="summary">
        <div class="summary-box">
            <div class="summary-label">Total de Vendas</div>
            <div class="summary-value">{{ $resumo['count'] }}</div>
        </div>
        <div class="summary-box">
            <div class="summary-label">Vendas Pagas</div>
            <div class="summary-value" style="color: #16a34a;">{{ $resumo['pagas'] }}</div>
        </div>
        <div class="summary-box highlight">
            <div class="summary-label">Valor Total</div>
            <div class="summary-value">R$ {{ number_format($resumo['total'], 2, ',', '.') }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Cliente</th>
                <th>Vendedor</th>
                <th>Plano</th>
                <th>Valor</th>
                <th>Status</th>
                <th>Data</th>
            </tr>
        </thead>
        <tbody>
            @foreach($vendas as $v)
            <tr>
                <td style="font-weight: bold;">{{ $v->cliente?->nome_igreja ?? $v->cliente?->nome ?? 'N/A' }}</td>
                <td>{{ $v->vendedor->user->name ?? 'N/A' }}</td>
                <td>{{ strtoupper($v->plano) }}</td>
                <td style="font-weight: bold;">R$ {{ number_format($v->valor, 2, ',', '.') }}</td>
                <td>
                    @php
                        $s = strtolower($v->getStatusEfetivo());
                        $class = in_array($s, ['pago', 'received', 'confirmed']) ? 'pago' : 
                                (in_array($s, ['pendente', 'pending', 'aguardando pagamento']) ? 'pendente' : 'cancelado');
                    @endphp
                    <span class="status {{ $class }}">
                        {{ $v->getStatusEfetivo() }}
                    </span>
                </td>
                <td>{{ $v->created_at->format('d/m/Y') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Basileia Vendor - Sistema de Gestão Comercial • www.basileia.global
    </div>
</body>
</html>
