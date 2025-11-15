<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConflictoHorario extends Model
{
    protected $table = 'conflictos_horarios';
    protected $primaryKey = 'codigo';
    public $timestamps = false;
    
    protected $fillable = [
        'codigo',
        'codigo_doc',
        'codigo_materia',
        'codigo_grupo',
        'periodo_academico',
        'tipo_conflicto',
        'descripcion',
        'estado',
        'fecha_deteccion',
        'fecha_resolucion'
    ];
}
