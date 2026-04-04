<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Comissões - {{ $mes }}</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; color: #333; margin: 0; padding: 0; }
        .header { text-align: center; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid #eee; }
        .header h1 { margin: 0; font-size: 18px; color: #1e293b; }
        .header p { margin: 5px 0 0; color: #64748b; }
        .summary { margin-bottom: 20px; display: table; width: 100%; }
        .summary-item { display: table-cell; width: 33%; padding: 10px; background: #f8fafc; border-radius: 4px; border: 1px solid #e2e8f0; }
        .summary-label { font-size: 8px; color: #64748b; text-transform: uppercase; font-weight: bold; }
        .summary-value { font-size: 14px; font-weight: bold; color: #0f172a; margin-top: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #f1f5f9; color: #475569; text-align: left; padding: 8px; border-bottom: 2px solid #e2e8f0; text-transform: uppercase; font-size: 8px; }
        td { padding: 8px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .badge { padding: 2px 6px; border-radius: 10px; font-size: 7px; font-weight: bold; text-transform: uppercase; }
        .badge-pendente { background: #fef9c3; color: #854d0e; }
        .badge-confirmada { background: #dcfce7; color: #15803d; }
        .badge-paga { background: #dbeafe; color: #1d4ed8; }
        .footer { position: fixed; bottom: -20px; left: 0; right: 0; text-align: center; font-size: 8px; color: #94a3b8; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Relatório de Comissões</h1>
        <p>Mês de Referência: {{ \Carbon\Carbon::parse($mes.'-01')->translatedFormat('F/Y') }}</p>
        <p>Vendedor: {{ $resumo['vendedor'] }} | Gerado em: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="summary">
        <div class="summary-item">
            <div class="summary-label">Total de Comissões</div>
            <div class="summary-value">R$ {{ number_format($resumo['total'], 2, ',', '.') }}</div>
        </div>
        <div class="summary-item" style="border-left: none; border-right: none;">
            <div class="summary-label">Total de Vendas</div>
            <div class="summary-value">{{ $comissoes->count() }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Status Principal</div>
            <div class="summary-value">{{ $comissoes->count() > 0 ? ucfirst($comissoes->first()->status) : '-' }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Cliente / Igreja</th>
                <th class="text-center">Venda</th>
                <th class="text-right">V. Venda</th>
                <th class="text-center">%</th>
                <th class="text-right">V. Comis.</th>
                <th class="text-center">Status</th>
                <th class="text-center">Data</th>
            </tr>
        </thead>
        <tbody>
            @foreach($comissoes as $c)
            <tr>
                <td>{{ $c->id }}</td>
                <td>
                    <strong>{{ $c->cliente?->nome ?? 'N/A' }}</strong><br>
                    <small>{{ $c->cliente?->nome_igreja ?? '-' }}</small>
                </td>
                <td class="text-center">#{{ $c->venda_id }}</td>
                <td class="text-right">R$ {{ number_format($c->valor_venda, 2, ',', '.') }}</td>
                <td class="text-center">{{ number_format($c->percentual_aplicado, 1) }}%</td>
                <td class="text-right"><strong>R$ {{ number_format($c->valor_comissao, 2, ',', '.') }}</strong></td>
                <td class="text-center">
                    <span class="badge badge-{{ $c->status }}">{{ ucfirst($c->status) }}</span>
                </td>
                <td class="text-center">{{ $c->created_at->format('d/m/Y') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Basilea Vendor - Painel do Vendedor - Página 1 de 1
    </div>
</body>
</html>
