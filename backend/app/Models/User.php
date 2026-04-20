<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'perfil',
        'status',
        'require_password_change',
        'two_factor_secret',
        'two_factor_enabled',
        'recovery_codes',
        'two_factor_rotated_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_rotated_at' => 'datetime',
        ];
    }

    protected function twoFactorSecret(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: function ($value) {
                if ($value === null) {
                    return null;
                }
                try {
                    return decrypt($value, false);
                } catch (\Exception $e) {
                    return null;
                }
            },
            set: function ($value) {
                return $value ? encrypt($value, false) : null;
            },
        );
    }

    protected function recoveryCodes(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: function ($value) {
                if ($value === null) {
                    return null;
                }
                try {
                    return decrypt($value, false);
                } catch (\Exception $e) {
                    return null;
                }
            },
            set: function ($value) {
                return $value ? encrypt($value, false) : null;
            },
        );
    }

    /**
     * Check if 2FA secret needs rotation (every 90 days)
     */
    public function needsTwoFactorRotation(): bool
    {
        if (!$this->two_factor_enabled) {
            return false;
        }
        if (!$this->two_factor_rotated_at) {
            return true;
        }
        return $this->two_factor_rotated_at->diffInDays(now()) >= 90;
    }

    /**
     * Rotate 2FA secret and disable until re-setup
     */
    public function rotateTwoFactorSecret(): string
    {
        $newSecret = \App\Services\TwoFactorAuthService::generateSecret();
        $this->two_factor_secret = $newSecret;
        $this->two_factor_enabled = false;
        $this->two_factor_rotated_at = now();
        $this->recovery_codes = null;
        $this->save();
        return $newSecret;
    }

    public function vendedor()
    {
        return $this->hasOne(Vendedor::class, 'usuario_id');
    }

    public function equipeLiderada()
    {
        return $this->hasOne(Equipe::class, 'gestor_id');
    }

    /**
     * Normalizar email para lowercase ao salvar
     */
    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = strtolower($value);
    }
}
