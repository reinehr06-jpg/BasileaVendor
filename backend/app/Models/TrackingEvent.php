<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class TrackingEvent extends Model
{
    protected $table = 'tracking_events';

    public $timestamps = false;

    protected $fillable = [
        'event_id',
        'event_name',
        'session_token',
        'checkout_session_id',
        'lead_id',
        'order_id',
        'seller_id',
        'campaign_id',
        'properties',
        'ip',
        'user_agent',
        'referrer',
        'landing_page',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_content',
        'utm_term',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->event_id)) {
                $model->event_id = Str::uuid();
            }
            if (empty($model->created_at)) {
                $model->created_at = now();
            }
        });
    }

    public function checkoutSession(): BelongsTo
    {
        return $this->belongsTo(CheckoutSession::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public static function track(
        string $eventName,
        ?string $sessionToken = null,
        ?int $checkoutSessionId = null,
        ?int $leadId = null,
        ?int $orderId = null,
        array $properties = []
    ): static {
        $request = request();

        $now = now();
        $eventId = \Illuminate\Support\Str::uuid();

        return static::create([
            'event_name' => $eventName,
            'event_id' => $eventId,
            'session_token' => $sessionToken,
            'checkout_session_id' => $checkoutSessionId,
            'lead_id' => $leadId,
            'order_id' => $orderId,
            'seller_id' => $properties['seller_id'] ?? null,
            'campaign_id' => $properties['campaign_id'] ?? null,
            'properties' => json_encode($properties),
            'ip' => $request->ip(),
            'user_agent' => substr($request->userAgent() ?? '', 0, 500),
            'referrer' => $request->header('Referer'),
            'landing_page' => $properties['landing_page'] ?? null,
            'utm_source' => $request->get('utm_source'),
            'utm_medium' => $request->get('utm_medium'),
            'utm_campaign' => $request->get('utm_campaign'),
            'utm_content' => $request->get('utm_content'),
            'utm_term' => $request->get('utm_term'),
            'created_at' => $now,
        ]);
    }
}
