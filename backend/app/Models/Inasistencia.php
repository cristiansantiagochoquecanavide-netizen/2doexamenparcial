<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Inasistencia extends Model
{
    use HasFactory;

    protected $table = 'inasistencias';
    protected $primaryKey = 'id_inasistencia';
    public $timestamps = false;

    protected $fillable = [
        'codigo_doc',
        'fecha',
        'motivo_aparente',
        'estado',
        'tipo_inasistencia',
        'fecha_registro',
    ];

    protected $casts = [
        'fecha' => 'date',
        'fecha_registro' => 'datetime',
    ];

    /**
     * Relación: Una inasistencia pertenece a un docente
     */
    public function docente()
    {
        return $this->belongsTo(Docente::class, 'codigo_doc', 'codigo_doc');
    }

    /**
     * Relación: Una inasistencia puede tener justificativos
     */
    public function justificativos()
    {
        return $this->hasMany(Justificativo::class, 'id_inasistencia', 'id_inasistencia');
    }

    /**
     * Relación: Una inasistencia puede tener resolución
     */
    public function resolucion()
    {
        return $this->hasOne(ResolucionInasistencia::class, 'id_inasistencia', 'id_inasistencia');
    }

    /**
     * Scope: Obtener inasistencias pendientes
     */
    public function scopePendientes($query)
    {
        return $query->where('estado', 'PENDIENTE');
    }

    /**
     * Scope: Obtener inasistencias resueltas
     */
    public function scopeResueltas($query)
    {
        return $query->where('estado', 'RESUELTA');
    }

    /**
     * Scope: Obtener inasistencias rechazadas
     */
    public function scopeRechazadas($query)
    {
        return $query->where('estado', 'RECHAZADA');
    }

    /**
     * Verificar si tiene justificativos en revisión
     */
    public function tieneJustificativosEnRevision()
    {
        return $this->justificativos()
            ->where('estado_revision', 'EN_REVISION')
            ->exists();
    }

    /**
     * Obtener el justificativo más reciente
     */
    public function obtenerUltimoJustificativo()
    {
        return $this->justificativos()
            ->orderByDesc('fecha_carga')
            ->first();
    }

    /**
     * Cambiar estado de la inasistencia
     */
    public function cambiarEstado($nuevoEstado)
    {
        $estadosValidos = ['PENDIENTE', 'EN_REVISION', 'RESUELTA', 'RECHAZADA', 'CANCELADA'];
        
        if (!in_array($nuevoEstado, $estadosValidos)) {
            throw new \Exception("Estado inválido: {$nuevoEstado}");
        }

        $this->update(['estado' => $nuevoEstado]);
    }
}
