<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    use HasFactory;

    protected $table = 'carga_horaria.rol';
    protected $primaryKey = 'id_rol';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    /**
     * Relación: Un rol tiene muchos usuarios
     */
    public function usuarios()
    {
        return $this->hasMany(Usuario::class, 'id_rol', 'id_rol');
    }

    /**
     * Relación: Un rol tiene muchos permisos (N:M)
     */
    public function permisos()
    {
        return $this->belongsToMany(
            Permiso::class,
            'carga_horaria.rol_permiso',
            'id_rol',
            'id_permiso',
            'id_rol',
            'id_permiso'
        );
    }

    /**
     * Método alternativo para verificar permisos de forma más robusta
     * Usa query builder directo para evitar problemas con esquemas
     */
    public function tienePermiso($nombrePermiso)
    {
        return \DB::table('carga_horaria.rol_permiso as rp')
            ->join('carga_horaria.permiso as p', 'rp.id_permiso', '=', 'p.id_permiso')
            ->where('rp.id_rol', $this->id_rol)
            ->where('p.nombre', $nombrePermiso)
            ->exists();
    }
}
