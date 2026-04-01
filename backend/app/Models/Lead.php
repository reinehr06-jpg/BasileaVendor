<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lead extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'uuid',
        'name',
        'email',
        'phone',
        'document',
        'church_name',
        'members_count',
        'ip',
        'country_code',
        'country_name',
        'currency',
        'language',
        'source',
        'campaign',
        'referrer',
        'seller_id',
        'seller_name',
        'status',
    ];

    protected $casts = [
        'members_count' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = \Illuminate\Support\Str::uuid();
            }
        });
    }

    public function checkoutSessions(): HasMany
    {
        return $this->hasMany(CheckoutSession::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function trackingEvents(): HasMany
    {
        return $this->hasMany(TrackingEvent::class);
    }

    public function markAsConverted(): void
    {
        $this->update(['status' => 'converted']);
    }

    public function markAsAbandoned(): void
    {
        $this->update(['status' => 'abandoned']);
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
