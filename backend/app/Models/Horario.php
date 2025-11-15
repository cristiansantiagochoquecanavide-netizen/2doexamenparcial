<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Horario extends Model
{
    use HasFactory;

    protected $table = 'horario';
    protected $primaryKey = 'id_horario';
    public $timestamps = false;

    protected $fillable = [
        'dias_semana',
        'hora_inicio',
        'hora_fin',
        'turno',
    ];

    protected $casts = [
        'hora_inicio' => 'datetime:H:i',
        'hora_fin' => 'datetime:H:i',
    ];

    /**
     * Relación: Un horario tiene muchas asignaciones
     */
    public function asignaciones()
    {
        return $this->hasMany(AsignacionHorario::class, 'id_horario', 'id_horario');
    }

    /**
     * Scope: Filtrar por día de la semana
     */
    public function scopePorDia($query, $dia)
    {
        return $query->where('dias_semana', $dia);
    }

    /**
     * Scope: Filtrar por turno
     */
    public function scopePorTurno($query, $turno)
    {
        return $query->where('turno', $turno);
    }
}
