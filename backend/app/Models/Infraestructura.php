<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Infraestructura extends Model
{
    use HasFactory;

    protected $table = 'infraestructura';
    protected $primaryKey = 'id_infraestructura';
    public $timestamps = false;

    protected $fillable = [
        'nombre_infr',
        'ubicacion',
        'estado',
    ];

    /**
     * Relación: Una infraestructura tiene muchas aulas (Composición)
     */
    public function aulas()
    {
        return $this->hasMany(Aula::class, 'id_infraestructura', 'id_infraestructura');
    }
}
