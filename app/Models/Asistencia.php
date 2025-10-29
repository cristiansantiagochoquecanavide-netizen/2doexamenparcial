<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asistencia extends Model
{
    use HasFactory;

    protected $table = 'asistencias';
    protected $primaryKey = 'id_asistencias';
    public $timestamps = false;

    protected $fillable = [
        'fecha',
        'hora_de_registro',
        'tipo_registro',
        'estado',
        'id_asignacion',
    ];

    protected $casts = [
        'fecha' => 'date',
        'hora_de_registro' => 'datetime:H:i',
    ];

    /**
     * Relación: Una asistencia pertenece a una asignación de horario
     */
    public function asignacion()
    {
        return $this->belongsTo(AsignacionHorario::class, 'id_asignacion', 'id_asignacion');
    }

    /**
     * Scope: Filtrar por fecha
     */
    public function scopePorFecha($query, $fecha)
    {
        return $query->whereDate('fecha', $fecha);
    }

    /**
     * Scope: Filtrar por rango de fechas
     */
    public function scopePorRangoFechas($query, $fechaInicio, $fechaFin)
    {
        return $query->whereBetween('fecha', [$fechaInicio, $fechaFin]);
    }

    /**
     * Scope: Filtrar por estado
     */
    public function scopePorEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }
}
