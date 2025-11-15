<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Materia extends Model
{
    use HasFactory;

    protected $table = 'materia';
    protected $primaryKey = 'codigo_mat';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'codigo_mat',
        'nombre_mat',
        'nivel',
        'horas_semanales',
        'tipo',
    ];

    protected $casts = [
        'nivel' => 'integer',
        'horas_semanales' => 'integer',
    ];

    /**
     * Relación: Una materia tiene muchos grupos
     */
    public function grupos()
    {
        return $this->hasMany(Grupo::class, 'codigo_mat', 'codigo_mat');
    }

    /**
     * Relación: Una materia puede pertenecer a muchos grupos (N:M)
     */
    public function gruposRelacion()
    {
        return $this->belongsToMany(
            Grupo::class,
            'grupo_materia',
            'codigo_mat',
            'codigo_grupo'
        );
    }
}
