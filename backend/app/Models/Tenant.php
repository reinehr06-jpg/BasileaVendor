<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    protected $fillable = ['name', 'slug', 'active', 'settings'];

    protected $casts = [
        'active' => 'boolean',
        'settings' => 'array',
    ];
}
