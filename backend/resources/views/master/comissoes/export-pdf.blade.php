<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
body { font-family: DejaVu Sans, sans-serif; font-size: 9px; }
table { width: 100%; border-collapse: collapse; margin-top: 10px; }
th { background: #581c87; color: white; padding: 6px 4px; text-align: left; font-size: 8px; }
td { padding: 5px 4px; border-bottom: 1px solid #e5e7eb; font-size: 8px; }
h2 { color: #581c87; margin: 0 0 4px 0; font-size: 14px; }
p { color: #6b7280; margin: 0 0 12px 0; font-size: 9px; }
.total { text-align: right; margin-top: 10px; font-weight: bold; font-size: 10px; }
</style>
</head>
<body>
<h2>Comissões - {{ $mes }}</h2>
<p>Exportado em {{ now()->format('d/m/Y H:i') }} | {{ $comissoes->count() }} registros</p>
<table>
<thead>
<tr>
<th>Vendedor</th><th>Cliente</th><th>CPF/CNPJ</th><th>Valor</th><th>%</th><th>Comissão</th><th>Tipo</th><th>Status</th><th>Data</th>
</tr>
</thead>
<tbody>
@php $total = 0; @endphp
@foreach($comissoes as $c)
@php $total += $c->valor_comissao; @endphp
<tr>
<td>{{ $c->vendedor->user->name ?? 'N/A' }}</td>
<td>{{ $c->cliente->nome_igreja ?? 'N/A' }}</td>
<td>{{ $c->cliente->documento ?? '-' }}</td>
<td>R$ {{ number_format($c->valor_venda, 2, ',', '.') }}</td>
<td>{{ $c->percentual_aplicado }}%</td>
<td>R$ {{ number_format($c->valor_comissao, 2, ',', '.') }}</td>
<td>{{ ucfirst($c->tipo_comissao) }}</td>
<td>{{ ucfirst($c->status) }}</td>
<td>{{ $c->data_pagamento ? $c->data_pagamento->format('d/m/Y') : '-' }}</td>
</tr>
@endforeach
</tbody>
</table>
<div class="total">Total: R$ {{ number_format($total, 2, ',', '.') }}</div>
</body>
</html>
