<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
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
            'two_factor_secret' => 'encrypted',
            'recovery_codes' => 'encrypted',
        ];
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
