<?php

namespace App\Services;

use App\Models\AsignacionHoraria;
use App\Models\Bitacora;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DetectorConflictosHorarios
{
    /**
     * Detectar todos los conflictos de horarios en las asignaciones actuales
     * Retorna array con conflictos encontrados sin usar tabla separada
     */
    public static function detectarConflictos()
    {
        $conflictosEncontrados = [];

        // Obtener todas las asignaciones horarias activas
        $asignaciones = AsignacionHorario::with([
            'docente',
            'grupo',
            'materia',
            'aula',
            'horario'
        ])->where('estado', 'Activo')->get();

        if ($asignaciones->isEmpty()) {
            return $conflictosEncontrados;
        }

        // Detectar conflictos comparando pares de asignaciones
        for ($i = 0; $i < count($asignaciones); $i++) {
            for ($j = $i + 1; $j < count($asignaciones); $j++) {
                $asig1 = $asignaciones[$i];
                $asig2 = $asignaciones[$j];

                // Verificar si los horarios se solapan
                if (self::horariosSesolapan($asig1, $asig2)) {
                    // Detectar diferentes tipos de conflictos
                    self::detectarConflictosHorario($asig1, $asig2, $conflictosEncontrados);
                }
            }
        }

        // Detectar conflictos de carga horaria de docentes
        self::detectarCargaHoraria($asignaciones, $conflictosEncontrados);

        // Detectar asignaciones duplicadas (grupo-materia)
        self::detectarDuplicados($asignaciones, $conflictosEncontrados);

        return $conflictosEncontrados;
    }

    /**
     * Verificar si dos horarios se solapan
     */
    private static function horariosSesolapan($asig1, $asig2)
    {
        if (!$asig1->horario || !$asig2->horario) {
            return false;
        }

        $inicio1 = $asig1->horario->hora_inicio;
        $fin1 = $asig1->horario->hora_fin;
        $inicio2 = $asig2->horario->hora_inicio;
        $fin2 = $asig2->horario->hora_fin;

        // Los horarios se solapan si: inicio1 < fin2 AND fin1 > inicio2
        return ($inicio1 < $fin2 && $fin1 > $inicio2);
    }

    /**
     * Detectar conflictos específicos de horario entre dos asignaciones
     */
    private static function detectarConflictosHorario($asig1, $asig2, &$conflictos)
    {
        // Conflicto 1: Mismo docente, diferentes materias en mismo horario
        if ($asig1->codigo_doc === $asig2->codigo_doc && 
            $asig1->codigo_mat !== $asig2->codigo_mat) {
            
            self::agregarConflicto([
                'tipo' => 'DOCENTE_HORARIO_DUPLICADO',
                'severidad' => 'CRÍTICO',
                'codigo_doc' => $asig1->codigo_doc,
                'id_asignacion_1' => $asig1->id_asignacion,
                'id_asignacion_2' => $asig2->id_asignacion,
                'descripcion' => "El docente {$asig1->codigo_doc} tiene múltiples materias en el mismo horario",
                'solucion' => 'Cambiar el horario de una de las materias',
            ], $conflictos);
        }

        // Conflicto 2: Misma aula, diferentes asignaciones en mismo horario
        if ($asig1->nro_aula === $asig2->nro_aula && 
            $asig1->nro_aula !== null) {
            
            self::agregarConflicto([
                'tipo' => 'AULA_HORARIO_DUPLICADO',
                'severidad' => 'CRÍTICO',
                'nro_aula' => $asig1->nro_aula,
                'id_asignacion_1' => $asig1->id_asignacion,
                'id_asignacion_2' => $asig2->id_asignacion,
                'descripcion' => "El aula {$asig1->nro_aula} está asignada a múltiples docentes en el mismo horario",
                'solucion' => 'Reasignar una de las materias a otra aula',
            ], $conflictos);
        }

        // Conflicto 3: Docente en diferentes aulas mismo horario
        if ($asig1->codigo_doc === $asig2->codigo_doc && 
            $asig1->nro_aula !== $asig2->nro_aula &&
            $asig1->nro_aula !== null && $asig2->nro_aula !== null) {
            
            self::agregarConflicto([
                'tipo' => 'DOCENTE_AULA_HORARIO_CONFLICTO',
                'severidad' => 'CRÍTICO',
                'codigo_doc' => $asig1->codigo_doc,
                'id_asignacion_1' => $asig1->id_asignacion,
                'id_asignacion_2' => $asig2->id_asignacion,
                'descripcion' => "El docente {$asig1->codigo_doc} está en múltiples aulas al mismo tiempo",
                'solucion' => 'Cambiar el horario o el aula de una de las asignaciones',
            ], $conflictos);
        }

        // Conflicto 4: Mismo grupo, diferentes materias en mismo horario
        if ($asig1->codigo_grupo === $asig2->codigo_grupo && 
            $asig1->codigo_mat !== $asig2->codigo_mat) {
            
            self::agregarConflicto([
                'tipo' => 'GRUPO_MATERIAS_HORARIO_CONFLICTO',
                'severidad' => 'CRÍTICO',
                'codigo_grupo' => $asig1->codigo_grupo,
                'id_asignacion_1' => $asig1->id_asignacion,
                'id_asignacion_2' => $asig2->id_asignacion,
                'descripcion' => "El grupo {$asig1->codigo_grupo} tiene múltiples materias en el mismo horario",
                'solucion' => 'Cambiar el horario de una de las materias para este grupo',
            ], $conflictos);
        }

        // Conflicto 5: Diferentes grupos, misma materia en mismo horario
        if ($asig1->codigo_grupo !== $asig2->codigo_grupo && 
            $asig1->codigo_mat === $asig2->codigo_mat) {
            
            self::agregarConflicto([
                'tipo' => 'GRUPO_MATERIA_HORARIO_DUPLICADO',
                'severidad' => 'MEDIO',
                'codigo_mat' => $asig1->codigo_mat,
                'id_asignacion_1' => $asig1->id_asignacion,
                'id_asignacion_2' => $asig2->id_asignacion,
                'descripcion' => "La materia {$asig1->codigo_mat} tiene múltiples grupos en el mismo horario",
                'solucion' => 'Cambiar el horario de uno de los grupos para esta materia',
            ], $conflictos);
        }

        // Conflicto 6: Misma materia, aula y horario, diferentes docentes
        if ($asig1->codigo_mat === $asig2->codigo_mat && 
            $asig1->nro_aula === $asig2->nro_aula && 
            $asig1->codigo_doc !== $asig2->codigo_doc &&
            $asig1->nro_aula !== null) {
            
            self::agregarConflicto([
                'tipo' => 'MATERIA_AULA_HORARIO_MULTIPLES_DOCENTES',
                'severidad' => 'CRÍTICO',
                'codigo_mat' => $asig1->codigo_mat,
                'nro_aula' => $asig1->nro_aula,
                'id_asignacion_1' => $asig1->id_asignacion,
                'id_asignacion_2' => $asig2->id_asignacion,
                'descripcion' => "La materia {$asig1->codigo_mat} en el aula {$asig1->nro_aula} tiene múltiples docentes asignados",
                'solucion' => 'Asignar un solo docente responsable para esta combinación',
            ], $conflictos);
        }
    }

    /**
     * Detectar si un docente excede su carga horaria máxima
     */
    private static function detectarCargaHoraria($asignaciones, &$conflictos)
    {
        $docentes = [];

        // Agrupar asignaciones por docente
        foreach ($asignaciones as $asignacion) {
            $codigo_doc = $asignacion->codigo_doc;
            if (!isset($docentes[$codigo_doc])) {
                $docentes[$codigo_doc] = [];
            }
            $docentes[$codigo_doc][] = $asignacion;
        }

        // Validar carga horaria de cada docente
        foreach ($docentes as $codigo_doc => $asigs) {
            $horasTotales = 0;

            foreach ($asigs as $asig) {
                if ($asig->horario) {
                    // Calcular duración en horas
                    $inicio = Carbon::createFromFormat('H:i:s', $asig->horario->hora_inicio);
                    $fin = Carbon::createFromFormat('H:i:s', $asig->horario->hora_fin);
                    $duracion = $fin->diffInMinutes($inicio) / 60;
                    $horasTotales += $duracion;
                }
            }

            // Verificar contra carga máxima del docente
            $docente = $asigs[0]->docente;
            $cargaMaxima = $docente->carga_horaria_max ?? 40; // Valor por defecto

            if ($horasTotales > $cargaMaxima) {
                self::agregarConflicto([
                    'tipo' => 'DOCENTE_CARGA_HORARIA_EXCEDIDA',
                    'severidad' => 'MEDIO',
                    'codigo_doc' => $codigo_doc,
                    'id_asignacion_1' => $asigs[0]->id_asignacion,
                    'descripcion' => "El docente {$codigo_doc} tiene {$horasTotales}h asignadas, máximo permitido: {$cargaMaxima}h",
                    'solucion' => 'Reasignar algunas materias a otros docentes',
                ], $conflictos);
            }
        }
    }

    /**
     * Detectar asignaciones duplicadas (mismo grupo y materia)
     */
    private static function detectarDuplicados($asignaciones, &$conflictos)
    {
        $registrados = [];

        foreach ($asignaciones as $asig) {
            $clave = $asig->codigo_grupo . '-' . $asig->codigo_mat;

            if (isset($registrados[$clave])) {
                // Ya existe otra asignación del mismo grupo-materia
                $asigAnterior = $registrados[$clave];
                
                self::agregarConflicto([
                    'tipo' => 'GRUPO_MATERIA_DUPLICADA',
                    'severidad' => 'CRÍTICO',
                    'codigo_grupo' => $asig->codigo_grupo,
                    'codigo_mat' => $asig->codigo_mat,
                    'id_asignacion_1' => $asigAnterior->id_asignacion,
                    'id_asignacion_2' => $asig->id_asignacion,
                    'descripcion' => "El grupo {$asig->codigo_grupo} tiene múltiples asignaciones para la materia {$asig->codigo_mat}",
                    'solucion' => 'Eliminar la asignación duplicada o modificar una de ellas',
                ], $conflictos);
            } else {
                $registrados[$clave] = $asig;
            }
        }
    }

    /**
     * Agregar un conflicto encontrado a la lista de conflictos
     * Evita duplicados
     */
    private static function agregarConflicto($datos, &$conflictos)
    {
        // Verificar si el conflicto ya está registrado
        foreach ($conflictos as $conflicto) {
            if ($conflicto['tipo'] === $datos['tipo']) {
                if (isset($conflicto['id_asignacion_1']) && isset($datos['id_asignacion_1'])) {
                    if ($conflicto['id_asignacion_1'] === $datos['id_asignacion_1'] &&
                        $conflicto['id_asignacion_2'] === $datos['id_asignacion_2']) {
                        // Conflicto ya registrado
                        return;
                    }
                }
            }
        }

        // Agregar timestamp de detección
        $datos['fecha_deteccion'] = now();

        // Agregar a la lista
        $conflictos[] = $datos;

        // Registrar en bitácora
        Bitacora::registrar(
            'Gestión de Conflictos',
            "Conflicto detectado: {$datos['tipo']} (Severidad: {$datos['severidad']})"
        );
    }
}
