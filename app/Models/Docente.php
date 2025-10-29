<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Docente extends Model
{
    use HasFactory;

    protected $table = 'docente';
    protected $primaryKey = 'codigo_doc';
    public $timestamps = false;

    protected $fillable = [
        'titulo',
        'correo_institucional',
        'carga_horaria_max',
        'id_usuario',
    ];

    protected $casts = [
        'carga_horaria_max' => 'integer',
    ];

    /**
     * Relación: Un docente pertenece a un usuario (Agregación)
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    /**
     * Relación: Un docente tiene muchas asignaciones de horario
     */
    public function asignaciones()
    {
        return $this->hasMany(AsignacionHorario::class, 'codigo_doc', 'codigo_doc');
    }

    /**
     * Accessor: Obtener datos de persona a través del usuario
     */
    public function getPersonaAttribute()
    {
        return $this->usuario?->persona;
    }
}
