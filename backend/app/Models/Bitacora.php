<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bitacora extends Model
{
    use HasFactory;

    protected $table = 'carga_horaria.bitacora';
    protected $primaryKey = 'id_bit';
    public $timestamps = false;
    
    const CREATED_AT = 'fecha_accion';
    const UPDATED_AT = null;

    protected $fillable = [
        'modulo',
        'accion',
        'fecha_accion',
        'id_usuario',
        'descripcion',
        'detalles_json',
        'ip_address',
        'user_agent',
        'tabla_afectada',
        'registro_id',
    ];

    protected $casts = [
        'fecha_accion' => 'datetime',
        'detalles_json' => 'json',
    ];

    /**
     * Relación: Una bitácora pertenece a un usuario
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    /**
     * Método estático para registrar acciones con detalles completos
     */
    public static function registrar($modulo, $accion, $idUsuario = null, $detalles = [], $tablaAfectada = null, $registroId = null)
    {
        return self::create([
            'modulo' => $modulo,
            'accion' => $accion,
            'id_usuario' => $idUsuario ?? auth()->id(),
            'fecha_accion' => now(),
            'descripcion' => json_encode($detalles),
            'detalles_json' => $detalles,
            'ip_address' => request()->ip(),
            'user_agent' => request()->header('User-Agent'),
            'tabla_afectada' => $tablaAfectada,
            'registro_id' => $registroId,
        ]);
    }

    /**
     * Scopes para filtros comunes
     */
    public function scopePorUsuario($query, $idUsuario)
    {
        return $query->where('id_usuario', $idUsuario);
    }

    public function scopePorModulo($query, $modulo)
    {
        return $query->where('modulo', $modulo);
    }

    public function scopePorAccion($query, $accion)
    {
        return $query->where('accion', $accion);
    }

    public function scopeEntreFechas($query, $desde, $hasta)
    {
        return $query->whereBetween('fecha_accion', [$desde, $hasta]);
    }

    public function scopeUltimas($query, $cantidad = 50)
    {
        return $query->latest('fecha_accion')->limit($cantidad);
    }
}
