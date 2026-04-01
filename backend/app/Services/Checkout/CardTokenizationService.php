<?php

namespace App\Services\Checkout;

use App\Models\Lead;
use App\Models\SubscriptionCard;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class CardTokenizationService
{
    protected AsaasService $asaasService;

    public function __construct()
    {
        $this->asaasService = new AsaasService();
    }

    public function tokenizeAndSave(Lead $lead, array $cardData): ?SubscriptionCard
    {
        try {
            $customer = $this->asaasService->findOrCreateCustomer([
                'name' => $lead->name,
                'email' => $lead->email,
                'cpfCnpj' => $lead->document ?? '',
                'phone' => $lead->phone ?? '',
            ]);

            if (!$customer || !isset($customer['id'])) {
                Log::error('CardTokenization: Failed to create/find customer', ['lead_id' => $lead->id]);
                return null;
            }

            $cardResponse = $this->asaasService->createCardToken($customer['id'], $cardData);

            if (!$cardResponse || !isset($cardResponse['id'])) {
                Log::error('CardTokenization: Failed to tokenize card', [
                    'lead_id' => $lead->id,
                    'customer_id' => $customer['id'],
                ]);
                return null;
            }

            $card = SubscriptionCard::create([
                'lead_id' => $lead->id,
                'asaas_card_id' => $cardResponse['id'],
                'brand' => $this->detectBrand($cardData['number'] ?? ''),
                'last4' => substr($cardData['number'] ?? '', -4),
                'holder_name' => strtoupper($cardData['name'] ?? ''),
                'expiry_month' => $this->extractMonth($cardData['expiry'] ?? ''),
                'expiry_year' => $this->extractYear($cardData['expiry'] ?? ''),
                'token' => Crypt::encryptString($cardResponse['creditCardToken']),
                'status' => 'active',
            ]);

            return $card;

        } catch (\Exception $e) {
            Log::error('CardTokenization: Exception', [
                'lead_id' => $lead->id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    public function getSavedCards(Lead $lead): \Illuminate\Database\Eloquent\Collection
    {
        return SubscriptionCard::where('lead_id', $lead->id)
            ->active()
            ->where(function ($query) {
                $query->where('expiry_year', '>', now()->format('Y'))
                    ->orWhere(function ($q) {
                        $q->where('expiry_year', now()->format('Y'))
                          ->where('expiry_month', '>=', now()->format('m'));
                    });
            })
            ->orderByDesc('created_at')
            ->get();
    }

    public function deleteCard(int $cardId, int $leadId): bool
    {
        return SubscriptionCard::where('id', $cardId)
            ->where('lead_id', $leadId)
            ->update(['status' => 'cancelled']);
    }

    public function getTokenForRenewal(SubscriptionCard $card): ?string
    {
        try {
            return Crypt::decryptString($card->token);
        } catch (\Exception $e) {
            Log::error('CardTokenization: Failed to decrypt token', [
                'card_id' => $card->id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    public function detectBrand(string $cardNumber): string
    {
        $number = preg_replace('/\D/', '', $cardNumber);
        $length = strlen($number);

        if ($length < 4) return 'unknown';

        $first2 = substr($number, 0, 2);
        $first4 = substr($number, 0, 4);
        $first6 = substr($number, 0, 6);

        // Diners Club
        if (preg_match('/^(30[0-5]|36|38)/', $number) && $length >= 14 && $length <= 19) {
            return 'diners';
        }

        // American Express
        if (preg_match('/^(34|37)/', $number) && $length === 15) {
            return 'amex';
        }

        // Hipercard
        if (preg_match('/^(606282|3841)/', $number)) {
            return 'hipercard';
        }

        // Elo
        $eloBins = ['4011', '4312', '4389', '4514', '4573', '4576', '5041', '5066', '5090', '6277', '6362', '6363', '6500', '6504', '6505', '6509', '6516', '6550'];
        foreach ($eloBins as $bin) {
            if (strpos($number, $bin) === 0) {
                return 'elo';
            }
        }

        // Discover
        if (preg_match('/^(6011|622[1-9]|64[4-9]|65)/', $number) && $length >= 16 && $length <= 19) {
            return 'discover';
        }

        // Visa
        if ($number[0] === '4' && ($length === 13 || $length === 16 || $length === 19)) {
            return 'visa';
        }

        // Mastercard
        if ((preg_match('/^(5[1-5])/', $number) || (int)$first4 >= 2221 && (int)$first4 <= 2720) && $length === 16) {
            return 'mastercard';
        }

        // JCB
        if (preg_match('/^(352[89]|35[3-8])/', $number) && $length >= 16 && $length <= 19) {
            return 'jcb';
        }

        return 'unknown';
    }

    public function validateCardNumber(string $cardNumber): bool
    {
        $number = preg_replace('/\D/', '', $cardNumber);

        if (strlen($number) < 13 || strlen($number) > 19) {
            return false;
        }

        return $this->luhnCheck($number);
    }

    protected function luhnCheck(string $number): bool
    {
        $sum = 0;
        $length = strlen($number);
        $parity = $length % 2;

        for ($i = $length - 1; $i >= 0; $i--) {
            $digit = (int)$number[$i];

            if ($i % 2 === $parity) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }

            $sum += $digit;
        }

        return $sum % 10 === 0;
    }

    protected function extractMonth(string $expiry): string
    {
        $parts = explode('/', $expiry);
        return str_pad($parts[0] ?? '01', 2, '0', STR_PAD_LEFT);
    }

    protected function extractYear(string $expiry): string
    {
        $parts = explode('/', $expiry);
        $year = $parts[1] ?? now()->format('Y');
        if (strlen($year) === 2) {
            $year = '20' . $year;
        }
        return $year;
    }
}
