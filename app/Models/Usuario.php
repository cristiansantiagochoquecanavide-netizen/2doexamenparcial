<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Usuario extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $table = 'usuario';
    protected $primaryKey = 'id_usuario';
    public $timestamps = false;

    protected $fillable = [
        'contrasena',
        'estado',
        'ci_persona',
        'id_rol',
    ];

    protected $hidden = [
        'contrasena',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];

    /**
     * Relación: Un usuario pertenece a una persona (Composición)
     */
    public function persona()
    {
        return $this->belongsTo(Persona::class, 'ci_persona', 'ci');
    }

    /**
     * Relación: Un usuario tiene un rol
     */
    public function rol()
    {
        return $this->belongsTo(Rol::class, 'id_rol', 'id_rol');
    }

    /**
     * Relación: Un usuario puede ser un docente
     */
    public function docente()
    {
        return $this->hasOne(Docente::class, 'id_usuario', 'id_usuario');
    }

    /**
     * Relación: Un usuario tiene muchos registros en la bitácora
     */
    public function bitacoras()
    {
        return $this->hasMany(Bitacora::class, 'id_usuario', 'id_usuario');
    }

    /**
     * Override del método getAuthPassword para usar 'contrasena'
     */
    public function getAuthPassword()
    {
        return $this->contrasena;
    }
}
