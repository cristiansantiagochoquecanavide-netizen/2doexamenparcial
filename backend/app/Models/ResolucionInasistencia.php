<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ResolucionInasistencia extends Model
{
    use HasFactory;

    protected $table = 'resoluciones_inasistencias';
    protected $primaryKey = 'id_resolucion';
    public $timestamps = false;

    protected $fillable = [
        'id_inasistencia',
        'decision_final',
        'tipo_accion',
        'descripcion_accion',
        'fecha_resolucion',
        'id_usuario_coordinador',
    ];

    protected $casts = [
        'fecha_resolucion' => 'datetime',
    ];

    /**
     * Relación: Una resolución pertenece a una inasistencia
     */
    public function inasistencia()
    {
        return $this->belongsTo(Inasistencia::class, 'id_inasistencia', 'id_inasistencia');
    }

    /**
     * Relación: Una resolución es creada por un coordinador (usuario)
     */
    public function coordinador()
    {
        return $this->belongsTo(User::class, 'id_usuario_coordinador', 'id');
    }

    /**
     * Scope: Obtener resoluciones aprobadas
     */
    public function scopeAprobadas($query)
    {
        return $query->where('decision_final', 'APROBADA');
    }

    /**
     * Scope: Obtener resoluciones rechazadas
     */
    public function scopeRechazadas($query)
    {
        return $query->where('decision_final', 'RECHAZADA');
    }
}
