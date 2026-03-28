<?php

namespace App\Services\Checkout;

use App\Models\Lead;
use App\Models\Offer;
use App\Models\Subscription;
use App\Models\SubscriptionCard;
use App\Models\SubscriptionInvoice;
use Illuminate\Support\Facades\Log;

class SubscriptionService
{
    protected AsaasService $asaasService;

    public function __construct()
    {
        $this->asaasService = new AsaasService();
    }

    public function createSubscription(
        Lead $lead,
        Offer $offer,
        SubscriptionCard $card,
        string $billingType,
        float $amount,
        ?string $asaasSubscriptionId = null
    ): Subscription {
        $startDate = now();
        $nextBillingDate = $billingType === 'yearly'
            ? $startDate->copy()->addYear()
            : $startDate->copy()->addMonth();

        return Subscription::create([
            'lead_id' => $lead->id,
            'subscription_card_id' => $card->id,
            'offer_id' => $offer->id,
            'asaas_subscription_id' => $asaasSubscriptionId,
            'billing_type' => $billingType,
            'amount' => $amount,
            'start_date' => $startDate,
            'next_billing_date' => $nextBillingDate,
            'last_billing_date' => $startDate,
            'status' => 'active',
            'total_invoices_generated' => 1,
        ]);
    }

    public function shouldGenerateNewInvoice(Subscription $subscription): bool
    {
        if (!$subscription->isActive()) {
            return false;
        }

        $today = now();

        // Monthly: generate every month
        if ($subscription->isMonthly()) {
            return $subscription->next_billing_date <= $today;
        }

        // Yearly: check if 12 months passed since last billing
        if ($subscription->isYearly()) {
            $monthsSinceStart = $subscription->start_date->diffInMonths($today);
            return $monthsSinceStart >= 12 && $subscription->next_billing_date <= $today;
        }

        return false;
    }

    public function processRenewal(Subscription $subscription): ?SubscriptionInvoice
    {
        if (!$this->shouldGenerateNewInvoice($subscription)) {
            return null;
        }

        try {
            $card = $subscription->card;
            if (!$card || !$card->isActive()) {
                $this->cancelSubscription($subscription, 'Card expired or inactive');
                return null;
            }

            $invoice = SubscriptionInvoice::create([
                'subscription_id' => $subscription->id,
                'amount' => $subscription->amount,
                'due_date' => $subscription->next_billing_date,
                'status' => 'pending',
            ]);

            $nextDate = $subscription->isMonthly()
                ? $subscription->next_billing_date->copy()->addMonth()
                : $subscription->next_billing_date->copy()->addYear();

            $subscription->update([
                'next_billing_date' => $nextDate,
                'last_billing_date' => now(),
                'total_invoices_generated' => $subscription->total_invoices_generated + 1,
            ]);

            Log::info('SubscriptionService: Renewal processed', [
                'subscription_id' => $subscription->id,
                'invoice_id' => $invoice->id,
            ]);

            return $invoice;

        } catch (\Exception $e) {
            Log::error('SubscriptionService: Renewal failed', [
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    public function getSubscriptionsNeedingRenewal(): \Illuminate\Database\Eloquent\Collection
    {
        return Subscription::needsRenewal()->with(['lead', 'card', 'offer'])->get();
    }

    public function cancelSubscription(Subscription $subscription, string $reason = ''): bool
    {
        try {
            $subscription->update([
                'status' => 'cancelled',
            ]);

            Log::info('SubscriptionService: Subscription cancelled', [
                'subscription_id' => $subscription->id,
                'reason' => $reason,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('SubscriptionService: Failed to cancel subscription', [
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function pauseSubscription(Subscription $subscription): bool
    {
        try {
            $subscription->update(['status' => 'paused']);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function resumeSubscription(Subscription $subscription): bool
    {
        try {
            $subscription->update([
                'status' => 'active',
                'next_billing_date' => now(),
            ]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getActiveSubscriptionsForLead(Lead $lead): \Illuminate\Database\Eloquent\Collection
    {
        return Subscription::where('lead_id', $lead->id)
            ->active()
            ->with(['offer', 'card', 'invoices'])
            ->get();
    }

    public function hasActiveSubscription(Lead $lead, Offer $offer): bool
    {
        return Subscription::where('lead_id', $lead->id)
            ->where('offer_id', $offer->id)
            ->active()
            ->exists();
    }
}
