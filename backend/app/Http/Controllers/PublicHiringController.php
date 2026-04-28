<?php

namespace App\Http\Controllers;

use App\Models\Plano;
use App\Models\Cliente;
use App\Models\Venda;
use App\Models\Setting;
use App\Services\AsaasService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PublicHiringController extends Controller
{
    protected AsaasService $asaasService;

    public function __construct()
    {
        $this->asaasService = new AsaasService();
    }

    /**
     * Exibir tela de contratação pública
     */
    public function index()
    {
        $planos = Plano::where('status', true)->get();
        
        $labels = [
            'church' => Setting::get('label_church', 'Igreja'),
            'organization' => Setting::get('label_organization', 'Igreja/Organização'),
            'pastor' => Setting::get('label_pastor', 'Pastor'),
            'member' => Setting::get('label_member', 'Membro'),
        ];

        return view('public.hiring', compact('planos', 'labels'));
    }

    /**
     * Processar contratação
     */
    public function store(Request $request)
    {
        $request->validate([
            'nome_igreja' => 'required|string|max:255',
            'documento' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'whatsapp' => 'required|string|max:20',
            'plano_id' => 'required|exists:planos,id',
            'forma_pagamento' => 'required|in:PIX,BOLETO,CREDIT_CARD',
        ]);

        try {
            // 1. Criar ou buscar cliente
            $documento = preg_replace('/\D/', '', $request->documento);
            $cliente = Cliente::where('documento', $documento)->first();
            
            if (!$cliente) {
                $cliente = Cliente::create([
                    'nome_igreja' => $request->nome_igreja,
                    'documento' => $documento,
                    'email' => $request->email,
                    'whatsapp' => preg_replace('/\D/', '', $request->whatsapp),
                    'status' => 'Lead',
                    'origem' => 'Auto-Contratação'
                ]);
            }

            // 2. Criar no Asaas
            $asaasCustomer = $this->asaasService->createCustomer(
                $cliente->nome_igreja,
                $cliente->documento,
                $cliente->whatsapp,
                $cliente->email
            );

            $plano = Plano::find($request->plano_id);

            // 3. Criar Venda
            $venda = Venda::create([
                'cliente_id' => $cliente->id,
                'plano_id' => $plano->id,
                'plano' => $plano->nome,
                'valor' => $plano->valor_mensal,
                'status' => 'Aguardando pagamento',
                'forma_pagamento' => $request->forma_pagamento,
                'tipo_negociacao' => 'assinatura',
                'origem' => 'Self-Service'
            ]);

            // 4. Criar Assinatura no Asaas
            $subscription = $this->asaasService->createSubscription([
                'customer' => $asaasCustomer['id'],
                'billingType' => $request->forma_pagamento,
                'value' => $plano->valor_mensal,
                'nextDueDate' => now()->addDays(3)->format('Y-m-d'),
                'cycle' => 'MONTHLY',
                'description' => "Assinatura - {$plano->nome}",
                'externalReference' => (string) $venda->id
            ]);

            $venda->update([
                'asaas_subscription_id' => $subscription['id']
            ]);

            // Se for Pix ou Boleto, redireciona para a fatura do Asaas ou uma tela de sucesso local
            if (isset($subscription['invoiceUrl'])) {
                return redirect($subscription['invoiceUrl']);
            }

            return redirect()->route('checkout.success', ['orderNumber' => $venda->id]);

        } catch (\Exception $e) {
            Log::error('Erro na contratação pública', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Ocorreu um erro ao processar sua contratação. Por favor, tente novamente.');
        }
    }
}
