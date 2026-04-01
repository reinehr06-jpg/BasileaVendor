<?php

namespace App\Services\Checkout;

use App\Models\TrackingEvent;
use App\Models\CheckoutSession;
use App\Models\Lead;
use App\Models\Order;

class TrackingService
{
    public function trackView(CheckoutSession $session, array $properties = []): TrackingEvent
    {
        return TrackingEvent::track(
            'checkout_view',
            $session->token,
            $session->id,
            $session->lead_id,
            null,
            array_merge([
                'offer_id' => $session->offer_id,
                'currency' => $session->currency,
                'seller_id' => $session->seller_id,
                'campaign_id' => $session->campaign_id,
            ], $properties)
        );
    }

    public function trackIdentify(CheckoutSession $session, Lead $lead): TrackingEvent
    {
        return TrackingEvent::track(
            'identify_completed',
            $session->token,
            $session->id,
            $lead->id,
            null,
            [
                'offer_id' => $session->offer_id,
                'currency' => $session->currency,
                'seller_id' => $session->seller_id,
                'campaign_id' => $session->campaign_id,
                'lead_email' => $lead->email,
            ]
        );
    }

    public function trackPaymentCreated(CheckoutSession $session, Order $order): TrackingEvent
    {
        return TrackingEvent::track(
            'payment_created',
            $session->token,
            $session->id,
            $session->lead_id,
            $order->id,
            [
                'order_number' => $order->order_number,
                'offer_id' => $session->offer_id,
                'currency' => $order->currency,
                'total' => $order->total,
                'payment_method' => $order->payment_method,
                'seller_id' => $session->seller_id,
                'campaign_id' => $session->campaign_id,
            ]
        );
    }

    public function trackPaymentApproved(Order $order): TrackingEvent
    {
        return TrackingEvent::track(
            'payment_approved',
            null,
            $order->checkout_session_id,
            $order->lead_id,
            $order->id,
            [
                'order_number' => $order->order_number,
                'offer_id' => $order->offer_id,
                'currency' => $order->currency,
                'total' => $order->total,
                'seller_id' => $order->seller_id,
                'campaign_id' => $order->campaign_id,
                'paid_at' => $order->paid_at,
            ]
        );
    }

    public function trackCheckoutAbandoned(CheckoutSession $session, ?string $reason = null): TrackingEvent
    {
        return TrackingEvent::track(
            'checkout_abandoned',
            $session->token,
            $session->id,
            $session->lead_id,
            null,
            [
                'offer_id' => $session->offer_id,
                'currency' => $session->currency,
                'reason' => $reason,
                'time_spent_seconds' => $session->created_at->diffInSeconds(now()),
                'seller_id' => $session->seller_id,
                'campaign_id' => $session->campaign_id,
            ]
        );
    }

    public function trackCouponApplied(CheckoutSession $session, string $couponCode, float $discount): TrackingEvent
    {
        return TrackingEvent::track(
            'coupon_applied',
            $session->token,
            $session->id,
            $session->lead_id,
            null,
            [
                'offer_id' => $session->offer_id,
                'currency' => $session->currency,
                'coupon_code' => $couponCode,
                'discount' => $discount,
            ]
        );
    }

    public function trackOrderBumpAdded(CheckoutSession $session, array $bump): TrackingEvent
    {
        return TrackingEvent::track(
            'order_bump_added',
            $session->token,
            $session->id,
            $session->lead_id,
            null,
            [
                'offer_id' => $session->offer_id,
                'currency' => $session->currency,
                'bump' => $bump,
            ]
        );
    }

    public function getStats(?string $campaignId = null, ?\DateTime $startDate = null, ?\DateTime $endDate = null): array
    {
        $query = TrackingEvent::query();

        if ($campaignId) {
            $query->where('campaign_id', $campaignId);
        }

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        $events = $query->get();

        return [
            'total_views' => $events->where('event_name', 'checkout_view')->count(),
            'total_identifies' => $events->where('event_name', 'identify_completed')->count(),
            'total_payments' => $events->where('event_name', 'payment_created')->count(),
            'total_approved' => $events->where('event_name', 'payment_approved')->count(),
            'total_abandoned' => $events->where('event_name', 'checkout_abandoned')->count(),
            'conversion_rate' => $this->calculateConversionRate($events),
            'abandonment_rate' => $this->calculateAbandonmentRate($events),
        ];
    }

    protected function calculateConversionRate($events): float
    {
        $views = $events->where('event_name', 'checkout_view')->count();
        $approved = $events->where('event_name', 'payment_approved')->count();

        return $views > 0 ? round(($approved / $views) * 100, 2) : 0;
    }

    protected function calculateAbandonmentRate($events): float
    {
        $identifies = $events->where('event_name', 'identify_completed')->count();
        $abandoned = $events->where('event_name', 'checkout_abandoned')->count();

        return $identifies > 0 ? round(($abandoned / $identifies) * 100, 2) : 0;
    }
}
