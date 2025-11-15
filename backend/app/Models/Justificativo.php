<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Justificativo extends Model
{
    use HasFactory;

    protected $table = 'justificativos';
    protected $primaryKey = 'id_justificativo';
    public $timestamps = false;

    protected $fillable = [
        'id_inasistencia',
        'archivo_ruta',
        'archivo_nombre_original',
        'archivo_tipo',
        'archivo_tamaño',
        'motivo_justificacion',
        'estado_revision',
        'comentario_revision',
        'fecha_carga',
        'fecha_revision',
    ];

    protected $casts = [
        'fecha_carga' => 'datetime',
        'fecha_revision' => 'datetime',
    ];

    /**
     * Relación: Un justificativo pertenece a una inasistencia
     */
    public function inasistencia()
    {
        return $this->belongsTo(Inasistencia::class, 'id_inasistencia', 'id_inasistencia');
    }

    /**
     * Scope: Obtener justificativos en revisión
     */
    public function scopeEnRevision($query)
    {
        return $query->where('estado_revision', 'EN_REVISION');
    }

    /**
     * Scope: Obtener justificativos aprobados
     */
    public function scopeAprobados($query)
    {
        return $query->where('estado_revision', 'APROBADO');
    }

    /**
     * Scope: Obtener justificativos rechazados
     */
    public function scopeRechazados($query)
    {
        return $query->where('estado_revision', 'RECHAZADO');
    }

    /**
     * Validar que el archivo exista y sea accesible
     */
    public function archivoEsValido()
    {
        return file_exists(storage_path('app/justificativos/' . $this->archivo_ruta));
    }

    /**
     * Obtener URL de descarga del archivo
     */
    public function obtenerUrlDescarga()
    {
        return asset('storage/justificativos/' . $this->archivo_ruta);
    }

    /**
     * Cambiar estado de revisión
     */
    public function cambiarEstadoRevision($nuevoEstado, $comentario = null)
    {
        $estadosValidos = ['EN_REVISION', 'APROBADO', 'RECHAZADO', 'PENDIENTE_VALIDACION'];
        
        if (!in_array($nuevoEstado, $estadosValidos)) {
            throw new \Exception("Estado de revisión inválido: {$nuevoEstado}");
        }

        $this->update([
            'estado_revision' => $nuevoEstado,
            'comentario_revision' => $comentario,
            'fecha_revision' => Carbon::now()
        ]);
    }
}
