<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\SubscriptionCard;
use App\Services\Checkout\SubscriptionService;
use App\Services\SubscriptionLifecycleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class SubscriptionController extends Controller
{
    protected SubscriptionService $subscriptionService;

    public function __construct()
    {
        $this->subscriptionService = new SubscriptionService();
    }

    public function index(Request $request)
    {
        $query = Subscription::with(['lead', 'offer', 'card', 'invoices']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('billing_type')) {
            $query->where('billing_type', $request->billing_type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('lead', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $subscriptions = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        return view('master.assinaturas.index', compact('subscriptions'));
    }

    public function show(string $id)
    {
        $subscription = Subscription::with(['lead', 'offer', 'card', 'invoices'])
            ->findOrFail($id);

        return view('master.assinaturas.show', compact('subscription'));
    }

    public function cancel(string $id)
    {
        $subscription = Subscription::findOrFail($id);

        $success = $this->subscriptionService->cancelSubscription($subscription, 'Cancelled by admin');

        if ($success) {
            return redirect()->back()->with('success', 'Assinatura cancelada com sucesso.');
        }

        return redirect()->back()->with('error', 'Erro ao cancelar assinatura.');
    }

    public function viewCard(string $id)
    {
        $subscription = Subscription::with('card')->findOrFail($id);

        if (!$subscription->card) {
            return response()->json(['error' => 'Cartão não encontrado'], 404);
        }

        try {
            $token = Crypt::decryptString($subscription->card->token);
            $maskedToken = substr($token, 0, 8) . '••••••••••••••••' . substr($token, -4);
        } catch (\Exception $e) {
            $maskedToken = '••••••••••••••••';
        }

        return response()->json([
            'brand' => $subscription->card->brand,
            'last4' => $subscription->card->last4,
            'holder_name' => $subscription->card->holder_name,
            'expiry_month' => $subscription->card->expiry_month,
            'expiry_year' => $subscription->card->expiry_year,
            'status' => $subscription->card->status,
            'brand_icon' => $subscription->card->brand_icon,
            'brand_color' => $subscription->card->brand_color,
            'token_masked' => $maskedToken,
            'asaas_card_id' => $subscription->card->asaas_card_id,
        ]);
    }

    public function pause(string $id)
    {
        $subscription = Subscription::findOrFail($id);
        $success = $this->subscriptionService->pauseSubscription($subscription);

        if ($success) {
            return redirect()->back()->with('success', 'Assinatura pausada.');
        }

        return redirect()->back()->with('error', 'Erro ao pausar assinatura.');
    }

    public function resume(string $id)
    {
        $subscription = Subscription::findOrFail($id);
        $success = $this->subscriptionService->resumeSubscription($subscription);

        if ($success) {
            return redirect()->back()->with('success', 'Assinatura retomada.');
        }

        return redirect()->back()->with('error', 'Erro ao retomar assinatura.');
    }

    public function migrar(Request $request)
    {
        $lifecycle = new SubscriptionLifecycleService();
        $count = $lifecycle->migrarVendasExistentes();

        Log::info('[Admin] Migração de assinaturas acionada manualmente', [
            'usuario_id' => auth()->id() ?? 0,
            'vendas_migradas' => $count,
        ]);

        return response()->json([
            'success' => true,
            'migradas' => $count,
            'message' => "{$count} vendas migradas com sucesso.",
        ]);
    }

    public function verificar(Request $request)
    {
        $lifecycle = new SubscriptionLifecycleService();
        $resultado = $lifecycle->verificarInadimplencia();

        Log::info('[Admin] Verificação de inadimplência acionada manualmente', [
            'usuario_id' => auth()->id() ?? 0,
            'resultado' => $resultado,
        ]);

        return response()->json([
            'success' => true,
            'resultado' => $resultado,
        ]);
    }
}
