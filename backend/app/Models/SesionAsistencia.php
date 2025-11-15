<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SesionAsistencia extends Model
{
    use HasFactory;

    protected $table = 'sesiones_asistencia';
    protected $primaryKey = 'id_sesion';
    public $timestamps = false;

    protected $fillable = [
        'token',
        'id_asignacion',
        'fecha_creacion',
        'fecha_expiracion',
        'estado',
        'url_registro',
        'qr_data'
    ];

    protected $casts = [
        'fecha_creacion' => 'datetime',
        'fecha_expiracion' => 'datetime',
    ];

    /**
     * Relación: Una sesión de asistencia pertenece a una asignación
     */
    public function asignacion()
    {
        return $this->belongsTo(AsignacionHorario::class, 'id_asignacion', 'id_asignacion');
    }

    /**
     * Scope: Obtener sesiones activas
     */
    public function scopeActivas($query)
    {
        return $query->where('estado', 'ACTIVA')
            ->where('fecha_expiracion', '>', Carbon::now());
    }

    /**
     * Scope: Obtener sesiones expiradas
     */
    public function scopeExpiradas($query)
    {
        return $query->where(function ($q) {
            $q->where('estado', 'CERRADA')
              ->orWhere('fecha_expiracion', '<=', Carbon::now());
        });
    }

    /**
     * Verificar si la sesión es válida
     */
    public function esValida()
    {
        return $this->estado === 'ACTIVA' && $this->fecha_expiracion > Carbon::now();
    }

    /**
     * Cerrar la sesión
     */
    public function cerrar()
    {
        $this->update([
            'estado' => 'CERRADA',
            'fecha_expiracion' => Carbon::now()
        ]);
    }

    /**
     * Obtener la hora de inicio de clase desde la asignación
     */
    public function obtenerHoraInicio()
    {
        try {
            $asignacion = $this->asignacion()->with('horario')->first();
            if ($asignacion && $asignacion->horario) {
                return $asignacion->horario->hora_inicio;
            }
        } catch (\Exception $e) {
            \Log::error('Error obteniendo hora de inicio: ' . $e->getMessage());
        }
        return null;
    }

    /**
     * Calcular minutos transcurridos desde el inicio de la clase
     */
    public function obtenerMinutosTranscurridos()
    {
        try {
            // Obtener la hora de inicio de la clase
            $horaInicio = $this->obtenerHoraInicio();
            if (!$horaInicio) {
                return 0;
            }

            // Crear un DateTime para hoy a la hora de inicio
            $ahora = Carbon::now();
            $inicioClaseHoy = Carbon::createFromTimeString($horaInicio);
            
            // Si ya pasó la clase de hoy, calcular desde hace 24 horas
            if ($ahora < $inicioClaseHoy) {
                $inicioClaseHoy->subDay();
            }

            // Calcular minutos transcurridos
            $minutosTranscurridos = $inicioClaseHoy->diffInMinutes($ahora);
            
            return max(0, $minutosTranscurridos);
        } catch (\Exception $e) {
            \Log::error('Error calculando minutos transcurridos: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Determinar el estado de asistencia según el tiempo transcurrido
     * 
     * @return array ['estado' => string, 'mensaje' => string, 'permitido' => bool]
     */
    public function determinarEstadoAsistencia()
    {
        $minutosTranscurridos = $this->obtenerMinutosTranscurridos();

        if ($minutosTranscurridos <= 15) {
            return [
                'estado' => 'ASISTIO',
                'minutos' => $minutosTranscurridos,
                'mensaje' => 'Presente - A tiempo',
                'permitido' => true
            ];
        } elseif ($minutosTranscurridos <= 25) {
            return [
                'estado' => 'RETRASO',
                'minutos' => $minutosTranscurridos,
                'mensaje' => 'Con retraso',
                'permitido' => true
            ];
        } else {
            return [
                'estado' => 'FALTA',
                'minutos' => $minutosTranscurridos,
                'mensaje' => 'Fuera de tiempo - QR expirado',
                'permitido' => false
            ];
        }
    }
}
