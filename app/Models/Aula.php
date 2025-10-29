<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Aula extends Model
{
    use HasFactory;

    protected $table = 'aula';
    protected $primaryKey = 'nro_aula';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'nro_aula',
        'tipo',
        'capacidad',
        'estado',
        'id_infraestructura',
    ];

    protected $casts = [
        'capacidad' => 'integer',
    ];

    /**
     * Relación: Un aula pertenece a una infraestructura (Composición)
     */
    public function infraestructura()
    {
        return $this->belongsTo(Infraestructura::class, 'id_infraestructura', 'id_infraestructura');
    }

    /**
     * Relación: Un aula tiene muchas asignaciones de horario
     */
    public function asignaciones()
    {
        return $this->hasMany(AsignacionHorario::class, 'nro_aula', 'nro_aula');
    }
}
