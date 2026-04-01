<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Evento extends Model
{
    protected $fillable = [
        'slug', 'titulo', 'descricao', 'valor', 'moeda',
        'vagas_total', 'vagas_ocupadas', 'whatsapp_vendedor',
        'telefone_vendedor', 'data_inicio', 'data_fim',
        'status', 'asaas_payment_link_id', 'checkout_url', 'created_by',
        'billing_type', 'charge_type', 'due_date_limit_days',
        'notification_enabled', 'is_address_required',
        'max_allowed_usage', 'end_date', 'max_installments',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'vagas_total' => 'integer',
        'vagas_ocupadas' => 'integer',
        'data_inicio' => 'datetime',
        'data_fim' => 'datetime',
        'notification_enabled' => 'boolean',
        'is_address_required' => 'boolean',
        'max_allowed_usage' => 'integer',
        'end_date' => 'date',
        'max_installments' => 'integer',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isDisponivel(): bool
    {
        if ($this->status !== 'ativo') {
            return false;
        }
        if ($this->data_fim && now()->gt($this->data_fim)) {
            return false;
        }
        if ($this->vagas_ocupadas >= $this->vagas_total) {
            return false;
        }
        return true;
    }

    public function vagasRestantes(): int
    {
        return max(0, $this->vagas_total - $this->vagas_ocupadas);
    }

    public function incrementarVagas(): void
    {
        $this->increment('vagas_ocupadas');
        if ($this->vagas_ocupadas >= $this->vagas_total) {
            $this->update(['status' => 'esgotado']);
        }
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($evento) {
            if (empty($evento->slug)) {
                $evento->slug = self::generateUniqueSlug($evento->titulo);
            }
        });
    }

    private static function generateUniqueSlug(string $titulo): string
    {
        $base = \Illuminate\Support\Str::slug($titulo);
        $slug = $base;
        $i = 1;

        while (self::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}
