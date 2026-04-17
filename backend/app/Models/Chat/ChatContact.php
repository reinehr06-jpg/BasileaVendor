<?php

namespace App\Models\Chat;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatContact extends Model
{
    protected $table = 'chat_contacts';

    protected $fillable = [
        'tenant_id',
        'phone',
        'name',
        'email',
        'avatar_url',
        'source',
        'external_id',
        'is_contact_admin',
    ];

    protected function casts(): array
    {
        return [
            'is_contact_admin' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(ChatConversation::class, 'contact_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'contact_id');
    }

    public static function normalizePhone(string $phone): string
    {
        $clean = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($clean) === 10) {
            $clean = '55' . $clean;
        }
        if (strlen($clean) === 11 && $clean[0] === '0') {
            $clean = '55' . substr($clean, 1);
        }
        if (strlen($clean) === 12 && substr($clean, 0, 2) !== '55') {
            $clean = '55' . substr($clean, 2);
        }
        return $clean;
    }

    public static function findOrCreateByPhone(int $tenantId, string $phone, array $data = []): self
    {
        $normalized = self::normalizePhone($phone);
        $contact = self::where('tenant_id', $tenantId)
            ->where('phone', $normalized)
            ->first();

        if (!$contact) {
            $contact = self::create([
                'tenant_id' => $tenantId,
                'phone' => $normalized,
                'name' => $data['name'] ?? null,
                'email' => $data['email'] ?? null,
                'source' => $data['source'] ?? 'whatsapp',
                'external_id' => $data['external_id'] ?? null,
                'is_contact_admin' => $data['is_contact_admin'] ?? false,
            ]);
        } else {
            if (!empty($data['name']) && !$contact->name) {
                $contact->name = $data['name'];
                $contact->save();
            }
        }

        return $contact;
    }
}