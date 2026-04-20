<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IAEvaluation extends Model
{
    protected $table = 'ia_evaluations';

    protected $fillable = [
        'user_id',
        'ia_model',
        'prompt',
        'response',
        'approved',
        'disapproval_reason',
        'metadata',
    ];

    protected $casts = [
        'approved' => 'boolean',
        'metadata' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
