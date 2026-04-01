<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\IntegracaoAsaasLog;
use App\Models\Pagamento;
use App\Models\Setting;
use App\Models\Venda;
use Illuminate\Http\Request;

class IntegracaoVendasController extends Controller
{
    public function index()
    {
        $asaasStatus = Setting::get('asaas_environment', 'sandbox');
        $asaasApiKey = Setting::get('asaas_api_key', '');
        $splitAtivo = Setting::get('asaas_split_global_ativo', false);

        $ultimasCobrancas = Pagamento::with('venda.cliente')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $ultimosWebhooks = IntegracaoAsaasLog::orderByDesc('created_at')
            ->limit(10)
            ->get();

        $totalVendas = Venda::where('status', 'pago')->count();
        $totalPendentes = Venda::where('status', 'pendente')->count();
        $totalFaturado = Venda::where('status', 'pago')->sum('valor_final');

        return view('master.integracoes.vendas', compact(
            'asaasStatus', 'asaasApiKey', 'splitAtivo',
            'ultimasCobrancas', 'ultimosWebhooks',
            'totalVendas', 'totalPendentes', 'totalFaturado'
        ));
    }
}
