<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pagamento;
use Illuminate\Http\Request;

class MonitorController extends Controller
{
    public function getLogs()
    {
        $pagamentos = Pagamento::with(['cliente'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $logs = $pagamentos->map(function($p) {
            return [
                'id' => 'evt_' . $p->id . '_' . uniqid(),
                'timestamp' => $p->created_at->format('H:i:s d/m/Y'),
                'event' => 'PAYMENT_' . strtoupper($p->status),
                'source' => 'Asaas',
                'status' => 200,
                'customer' => $p->cliente ? $p->cliente->nome : 'Desconhecido',
                'amount' => 'R$ ' . number_format((float)$p->valor, 2, ',', '.'),
                'payload' => [
                    'event' => 'PAYMENT_' . strtoupper($p->status),
                    'payment' => [
                        'id' => $p->asaas_payment_id,
                        'customer' => $p->cliente_id,
                        'value' => $p->valor,
                        'billingType' => $p->billing_type,
                        'status' => $p->status
                    ]
                ]
            ];
        });

        // Add some dummy error/system logs to simulate the same UI
        if ($logs->count() < 3) {
            $logs->push([
                'id' => 'evt_sys_1',
                'timestamp' => now()->subMinutes(10)->format('H:i:s d/m/Y'),
                'event' => 'SYSTEM_PING',
                'source' => 'System',
                'status' => 200,
                'customer' => 'Monitor',
                'amount' => '-',
                'payload' => ['status' => 'OK', 'latency' => '12ms']
            ]);
        }

        return response()->json(['data' => $logs]);
    }
}
