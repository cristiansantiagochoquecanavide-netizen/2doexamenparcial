<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grupo extends Model
{
    use HasFactory;

    protected $table = 'grupo';
    protected $primaryKey = 'codigo_grupo';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'codigo_grupo',
        'capacidad_de_grupo',
        'codigo_mat',
    ];

    protected $casts = [
        'capacidad_de_grupo' => 'integer',
    ];

    /**
     * Relación: Un grupo pertenece a una materia
     */
    public function materia()
    {
        return $this->belongsTo(Materia::class, 'codigo_mat', 'codigo_mat');
    }

    /**
     * Relación: Un grupo puede tener muchas materias (N:M)
     */
    public function materias()
    {
        return $this->belongsToMany(
            Materia::class,
            'grupo_materia',
            'codigo_grupo',
            'codigo_mat'
        );
    }

    /**
     * Relación: Un grupo tiene muchas asignaciones de horario
     */
    public function asignaciones()
    {
        return $this->hasMany(AsignacionHorario::class, 'codigo_grupo', 'codigo_grupo');
    }
}
