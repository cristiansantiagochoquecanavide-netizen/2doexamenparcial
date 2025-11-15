<?php

namespace App\Http\Controllers\Planificación_Académica;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DisponibilidadAulasController extends Controller
{
    /**
     * 1. Consultar aulas (filtros)
     */
    public function consultarAulas(Request $request)
    {
        // Lógica para consultar aulas según filtros
        $filtros = $request->only(['periodo', 'dia', 'franja', 'capacidad', 'sede']);

        // Buscar aulas que cumplan con los filtros básicos y de horario/asignación
        $query = \App\Models\Aula::query()->with(['infraestructura', 'asignaciones.horario']);
        if (!empty($filtros['capacidad'])) {
            $query->where('capacidad', $filtros['capacidad']);
        }
        if (!empty($filtros['sede'])) {
            $query->whereHas('infraestructura', function($q) use ($filtros) {
                $q->where('nombre_infr', $filtros['sede']);
            });
        }

        // Filtrar por disponibilidad en el horario (día y franja)
        if (!empty($filtros['dia']) && !empty($filtros['franja'])) {
            // Extraer hora_inicio y hora_fin de la franja seleccionada
            $franja = explode(' - ', $filtros['franja']);
            $horaInicio = $franja[0] ?? null;
            $horaFin = $franja[1] ?? null;
            $dia = $filtros['dia'];
            // Mapear nombre de día a número si es necesario
            $diasMap = [
                'Lunes' => '1', 'Martes' => '2', 'Miércoles' => '3', 'Jueves' => '4', 'Viernes' => '5', 'Sábado' => '6', 'Domingo' => '7'
            ];
            $diaNum = $diasMap[$dia] ?? $dia;
            // Solo aulas que NO tengan asignaciones en ese horario y día
            $query->whereDoesntHave('asignaciones', function($q) use ($diaNum, $horaInicio, $horaFin, $filtros) {
                $q->whereHas('horario', function($qh) use ($diaNum, $horaInicio, $horaFin) {
                    $qh->where('dias_semana', $diaNum)
                        ->where('hora_inicio', '<', $horaFin)
                        ->where('hora_fin', '>', $horaInicio);
                });
                // Si se quiere filtrar por periodo académico
                if (!empty($filtros['periodo'])) {
                    $q->where('periodo_academico', $filtros['periodo']);
                }
            });
        }

        $aulas = $query->get();

        return response()->json(['success' => true, 'aulas' => $aulas]);
    }

    /**
     * 1.1 Solicitar disponibilidad de aulas
     */
    public function solicitarDisponibilidad(Request $request)
    {
        // Lógica real para solicitar disponibilidad (igual a consultarAulas)
        $filtros = $request->only(['periodo', 'dia', 'franja', 'capacidad', 'sede']);

        $query = \App\Models\Aula::query()->with(['infraestructura', 'asignaciones.horario']);
        if (!empty($filtros['capacidad'])) {
            $query->where('capacidad', $filtros['capacidad']);
        }
        if (!empty($filtros['sede'])) {
            $query->whereHas('infraestructura', function($q) use ($filtros) {
                $q->where('nombre_infr', $filtros['sede']);
            });
        }
        if (!empty($filtros['dia']) && !empty($filtros['franja'])) {
            $franja = explode(' - ', $filtros['franja']);
            $horaInicio = $franja[0] ?? null;
            $horaFin = $franja[1] ?? null;
            $dia = $filtros['dia'];
            $diasMap = [
                'Lunes' => '1', 'Martes' => '2', 'Miércoles' => '3', 'Jueves' => '4', 'Viernes' => '5', 'Sábado' => '6', 'Domingo' => '7'
            ];
            $diaNum = $diasMap[$dia] ?? $dia;
            $query->whereDoesntHave('asignaciones', function($q) use ($diaNum, $horaInicio, $horaFin, $filtros) {
                $q->whereHas('horario', function($qh) use ($diaNum, $horaInicio, $horaFin) {
                    $qh->where('dias_semana', $diaNum)
                        ->where('hora_inicio', '<', $horaFin)
                        ->where('hora_fin', '>', $horaInicio);
                });
                if (!empty($filtros['periodo'])) {
                    $q->where('periodo_academico', $filtros['periodo']);
                }
            });
        }

        $aulas = $query->get();
        return response()->json(['success' => true, 'disponibilidad' => $aulas]);
    }

    /**
     * 1.2 Consultar estado de aulas
     */
    public function consultarEstadoAulas(Request $request)
    {
        // Lógica real para consultar estado de aulas
        $filtros = $request->only(['periodo', 'dia', 'franja', 'capacidad', 'sede']);

        $query = \App\Models\Aula::query()->with(['infraestructura', 'asignaciones.horario']);
        if (!empty($filtros['capacidad'])) {
            $query->where('capacidad', $filtros['capacidad']);
        }
        if (!empty($filtros['sede'])) {
            $query->whereHas('infraestructura', function($q) use ($filtros) {
                $q->where('nombre_infr', $filtros['sede']);
            });
        }

        $aulas = $query->get();

        // Para cada aula, determinar su estado en el horario filtrado
        $estados = $aulas->map(function($aula) use ($filtros) {
            // Si el estado del aula NO es 'Disponible', mostrarlo tal cual
            if (strtolower($aula->estado) !== 'disponible') {
                $estado = $aula->estado;
            } else {
                $estado = 'Disponible';
                if (!empty($filtros['dia']) && !empty($filtros['franja'])) {
                    $franja = explode(' - ', $filtros['franja']);
                    $horaInicio = $franja[0] ?? null;
                    $horaFin = $franja[1] ?? null;
                    $dia = $filtros['dia'];
                    $diasMap = [
                        'Lunes' => '1', 'Martes' => '2', 'Miércoles' => '3', 'Jueves' => '4', 'Viernes' => '5', 'Sábado' => '6', 'Domingo' => '7'
                    ];
                    $diaNum = $diasMap[$dia] ?? $dia;
                    $ocupada = $aula->asignaciones->contains(function($asig) use ($diaNum, $horaInicio, $horaFin, $filtros) {
                        // Solo considerar asignaciones activas
                        if (strtoupper($asig->estado) !== 'ACTIVO') return false;
                        if (!empty($filtros['periodo']) && $asig->periodo_academico != $filtros['periodo']) return false;
                        if (!$asig->horario) return false;
                        return $asig->horario->dias_semana == $diaNum
                            && $asig->horario->hora_inicio < $horaFin
                            && $asig->horario->hora_fin > $horaInicio;
                    });
                    if ($ocupada) $estado = 'Ocupada';
                }
            }
            return [
                'nro_aula' => $aula->nro_aula,
                'estado' => $estado,
                'capacidad' => $aula->capacidad,
                'sede' => $aula->infraestructura->nombre_infr ?? null,
            ];
        });

        return response()->json(['success' => true, 'estados' => $estados]);
    }

    /**
     * 1.3 Obtener aulas disponibles
     */
    public function aulasDisponibles(Request $request)
    {
        $filtros = $request->only(['periodo', 'dia', 'franja', 'capacidad', 'sede']);

        $query = \App\Models\Aula::query()->with(['infraestructura']);
        $query->whereRaw('LOWER(estado) = ?', ['disponible']);
        if (!empty($filtros['capacidad'])) {
            $query->where('capacidad', $filtros['capacidad']);
        }
        if (!empty($filtros['sede'])) {
            $query->whereHas('infraestructura', function($q) use ($filtros) {
                $q->where('nombre_infr', $filtros['sede']);
            });
        }

        $aulas = $query->get()->map(function($aula) {
            return [
                'nro_aula' => $aula->nro_aula,
                'estado' => 'Disponible',
                'ocupacion' => $aula->tipo ?? '',
                'capacidad' => $aula->capacidad,
                'sede' => $aula->infraestructura->nombre_infr ?? null,
            ];
        });

        return response()->json(['success' => true, 'aulas_disponibles' => $aulas]);
    }

    /**
     * 1.4 Registrar consulta en bitácora
     */
    public function registrarConsulta(Request $request)
    {
        // Lógica para registrar la consulta en la bitácora
        return response()->json(['success' => true, 'registrado' => true]);
    }

    /**
     * 1.5 Confirmar registro en bitácora
     */
    public function confirmarRegistro(Request $request)
    {
        // Lógica para confirmar el registro en la bitácora
        return response()->json(['success' => true, 'confirmado' => true]);
    }

    /**
     * 1.6 Actualizar disponibilidad de aulas
     */
    public function actualizarDisponibilidad(Request $request)
    {
        // Lógica para actualizar la disponibilidad de aulas
        return response()->json(['success' => true, 'actualizado' => true]);
    }

    /**
     * 1.7 Mostrar resultados al usuario
     */
    public function mostrarResultados(Request $request)
    {
        // Lógica para mostrar resultados al usuario
        return response()->json(['success' => true, 'resultados' => []]);
    }
}
