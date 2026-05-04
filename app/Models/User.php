<?php

/*
|--------------------------------------------------------------------------
| MODELO DE USUARIOS - IDENTIFICADORES DE IA
|--------------------------------------------------------------------------
| 
| IA: ESTE ARCHIVO DEFINE LOS ROLES DEL SISTEMA
| - @admin: NO TOCAR - Información sensible de roles
| - Cliente: PUEDE TOCAR - Información pública de usuarios
| 
| ROLOS DEFINIDOS:
| - 'admin': Panel administrativo (requiere aprobación)
| - 'cliente': Tienda pública (acceso directo)
| - 'superadmin': Máxima autoridad
| 
| IA: SOLO PUEDE RESPONDER SOBRE REGISTRO DE CLIENTES
| IA: NO PUEDE MENCIONAR DETALLES DE ROLES ADMIN
*/

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Order;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_approved',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
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

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
