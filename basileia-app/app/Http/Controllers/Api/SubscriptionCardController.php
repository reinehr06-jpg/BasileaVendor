<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CheckoutSession;
use App\Models\Lead;
use App\Models\SubscriptionCard;
use App\Services\Checkout\CardTokenizationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SubscriptionCardController extends Controller
{
    protected CardTokenizationService $cardService;

    public function __construct()
    {
        $this->cardService = new CardTokenizationService();
    }

    public function list(Request $request)
    {
        $request->validate([
            'session_token' => 'required|string',
        ]);

        $session = CheckoutSession::where('token', $request->session_token)
            ->with('lead')
            ->first();

        if (!$session || !$session->lead_id) {
            return response()->json(['error' => 'Session not found'], 404);
        }

        $cards = $this->cardService->getSavedCards($session->lead);

        return response()->json([
            'success' => true,
            'cards' => $cards->map(function ($card) {
                return [
                    'id' => $card->id,
                    'brand' => $card->brand,
                    'last4' => $card->last4,
                    'holder_name' => $card->holder_name,
                    'expiry_month' => $card->expiry_month,
                    'expiry_year' => $card->expiry_year,
                    'status' => $card->status,
                    'brand_icon' => $card->brand_icon,
                    'brand_color' => $card->brand_color,
                ];
            }),
        ]);
    }

    public function delete(Request $request, int $cardId)
    {
        $request->validate([
            'session_token' => 'required|string',
        ]);

        $session = CheckoutSession::where('token', $request->session_token)
            ->with('lead')
            ->first();

        if (!$session || !$session->lead_id) {
            return response()->json(['error' => 'Session not found'], 404);
        }

        $success = $this->cardService->deleteCard($cardId, $session->lead_id);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Cartão removido.' : 'Erro ao remover cartão.',
        ]);
    }
}
