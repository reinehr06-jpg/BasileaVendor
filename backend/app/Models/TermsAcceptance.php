<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TermsAcceptance extends Model
{
    protected $fillable = [
        'user_id', 'terms_document_id', 'ip_address', 'user_agent', 'aceito_em',
    ];

    protected $casts = [
        'aceito_em' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function termsDocument(): BelongsTo
    {
        return $this->belongsTo(TermsDocument::class, 'terms_document_id');
    }

    public static function usuarioAceitouVersaoAtual(int $userId): bool
    {
        $termosAtivos = TermsDocument::ativos()->get();

        foreach ($termosAtivos as $termo) {
            $aceite = static::where('user_id', $userId)
                ->where('terms_document_id', $termo->id)
                ->first();

            if (!$aceite) {
                return false;
            }

            if ($aceite->termsDocument->versao !== $termo->versao) {
                return false;
            }
        }

        return true;
    }

    public static function registrar(int $userId, int $termsDocumentId, ?string $ip = null, ?string $userAgent = null): self
    {
        return static::updateOrCreate(
            ['user_id' => $userId, 'terms_document_id' => $termsDocumentId],
            [
                'ip_address' => $ip,
                'user_agent' => $userAgent,
                'aceito_em' => now(),
            ]
        );
    }
}