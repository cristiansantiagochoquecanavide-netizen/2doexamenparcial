<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bitacora extends Model
{
    use HasFactory;

    protected $table = 'bitacora';
    protected $primaryKey = 'id_bit';
    public $timestamps = false;
    
    const CREATED_AT = 'fecha_accion';
    const UPDATED_AT = null;

    protected $fillable = [
        'modulo',
        'accion',
        'fecha_accion',
        'id_usuario',
    ];

    protected $casts = [
        'fecha_accion' => 'datetime',
    ];

    /**
     * Relación: Una bitácora pertenece a un usuario
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    /**
     * Método estático para registrar acciones
     */
    public static function registrar($modulo, $accion, $idUsuario = null)
    {
        return self::create([
            'modulo' => $modulo,
            'accion' => $accion,
            'id_usuario' => $idUsuario ?? auth()->id(),
            'fecha_accion' => now(),
        ]);
    }
}
