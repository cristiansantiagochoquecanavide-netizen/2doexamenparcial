<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permiso extends Model
{
    use HasFactory;

    protected $table = 'permiso';
    protected $primaryKey = 'id_permiso';
    public $timestamps = true;

    protected $fillable = [
        'nombre',
        'descripcion',
        'modulo',
    ];

    /**
     * Relación: Un permiso pertenece a muchos roles
     */
    public function roles()
    {
        return $this->belongsToMany(
            Rol::class,
            'rol_permiso',
            'id_permiso',
            'id_rol'
        );
    }
}
