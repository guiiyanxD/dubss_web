<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',
        'apellido',
        'ci',
        'email',
        'password',
        'telefono',
        'rol',
        'email_verified_at',
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
        ];
    }

    // Relaciones
    public function tramites(): HasMany
    {
        return $this->hasMany(Tramite::class, 'usuario_id');
    }

    public function turnos(): HasMany
    {
        return $this->hasMany(Turno::class, 'usuario_id');
    }

    public function notificaciones(): HasMany
    {
        return $this->hasMany(Notificacion::class, 'usuario_id');
    }

    public function tramitesAsignados(): HasMany
    {
        return $this->hasMany(Tramite::class, 'operador_asignado_id');
    }

    // Scopes
    public function scopeEstudiantes($query)
    {
        return $query->where('rol', 'estudiante');
    }

    public function scopeOperadores($query)
    {
        return $query->where('rol', 'operador');
    }

    /*// Helpers
    public function esOperador(): bool
    {
        return $this->rol === 'operador' || $this->rol === 'admin';
    }
    */
    public function esOperador(): bool
    {
    return in_array($this->rol, ['operador', 'admin']);
    }

    public function nombreCompleto(): string
    {
        return "{$this->nombre} {$this->apellido}";
    }
}
