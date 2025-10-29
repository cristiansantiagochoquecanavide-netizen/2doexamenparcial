<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsignacionHorario extends Model
{
    use HasFactory;

    protected $table = 'asignacion_horario';
    protected $primaryKey = 'id_asignacion';
    public $timestamps = false;

    protected $fillable = [
        'periodo_academico',
        'estado',
        'codigo_doc',
        'codigo_grupo',
        'nro_aula',
        'id_horario',
    ];

    /**
     * Relación: Una asignación pertenece a un docente
     */
    public function docente()
    {
        return $this->belongsTo(Docente::class, 'codigo_doc', 'codigo_doc');
    }

    /**
     * Relación: Una asignación pertenece a un grupo
     */
    public function grupo()
    {
        return $this->belongsTo(Grupo::class, 'codigo_grupo', 'codigo_grupo');
    }

    /**
     * Relación: Una asignación pertenece a un aula
     */
    public function aula()
    {
        return $this->belongsTo(Aula::class, 'nro_aula', 'nro_aula');
    }

    /**
     * Relación: Una asignación pertenece a un horario
     */
    public function horario()
    {
        return $this->belongsTo(Horario::class, 'id_horario', 'id_horario');
    }

    /**
     * Relación: Una asignación tiene muchas asistencias
     */
    public function asistencias()
    {
        return $this->hasMany(Asistencia::class, 'id_asignacion', 'id_asignacion');
    }

    /**
     * Scope: Filtrar por período académico
     */
    public function scopePorPeriodo($query, $periodo)
    {
        return $query->where('periodo_academico', $periodo);
    }

    /**
     * Scope: Filtrar por estado
     */
    public function scopeActivas($query)
    {
        return $query->where('estado', 'ACTIVO');
    }
}
