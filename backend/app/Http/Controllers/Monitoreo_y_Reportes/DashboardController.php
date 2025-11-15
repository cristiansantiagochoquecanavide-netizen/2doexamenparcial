<?php

namespace App\Http\Controllers\Monitoreo_y_Reportes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AsignacionHorario;
use App\Models\Asistencia;
use App\Models\ConflictoHorario;
use App\Models\Bitacora;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * CU16: Visualizar Dashboard - Obtener indicadores de planificación, asistencia y conflictos
     */
    public function index(Request $request)
    {
        try {
            $usuario = auth('sanctum')->user();
            
            // Validar que solo Administrador y Coordinador Académico accedan
            $rol = $usuario->rol->nombre ?? null;
            if (!in_array($rol, ['Administrador', 'Coordinador Académico'])) {
                return response()->json(['message' => 'No tienes permiso para acceder al dashboard'], 403);
            }

            // Obtener período académico (filtro opcional)
            $periodo = $request->query('periodo_academico', null);

            // 1. Indicador: Total de carga asignada
            $cargaAsignada = AsignacionHorario::query();
            if ($periodo) {
                $cargaAsignada->where('periodo_academico', $periodo);
            }
            $totalCargaAsignada = $cargaAsignada->count();

            // 2. Indicador: Total de asistencias registradas
            $asistenciasRegistradas = Asistencia::where('estado', 'CONFIRMADA');
            if ($periodo) {
                $year = substr($periodo, 0, 4);
                $asistenciasRegistradas->whereRaw("EXTRACT(YEAR FROM fecha)::integer = ?", [$year]);
            }
            $totalAsistencias = $asistenciasRegistradas->count();

            // 3. Indicador: Conflictos de horario detectados
            try {
                $conflictosDetectados = ConflictoHorario::where('estado', 'ACTIVO');
                if ($periodo) {
                    $conflictosDetectados->where('periodo_academico', $periodo);
                }
                $totalConflictos = $conflictosDetectados->count();
            } catch (\Exception $e) {
                $totalConflictos = 0;
            }

            // 4. Datos adicionales: Carga por docente - con detalles de asignaciones individuales
            $cargaPorDocente = AsignacionHorario::query()
                ->when($periodo, function ($query) use ($periodo) {
                    return $query->where('periodo_academico', $periodo);
                })
                ->with('docente.usuario.persona')
                ->orderBy('codigo_doc')
                ->when(!$periodo, function ($query) {
                    // Si no hay período seleccionado, traer todas sin límite
                    return $query; // Sin limit
                }, function ($query) {
                    // Si hay período seleccionado, aplicar límite
                    return $query->limit(50);
                })
                ->get();

            // 5. Datos adicionales: Asistencia por período
            $asistenciaPorPeriodo = Asistencia::selectRaw("EXTRACT(YEAR FROM fecha)::integer as año, COUNT(*) as total")
                ->where('estado', 'CONFIRMADA')
                ->groupBy(DB::raw("EXTRACT(YEAR FROM fecha)"))
                ->orderBy('año', 'desc')
                ->limit(6)
                ->get();

            // 6. Datos adicionales: Conflictos resueltos vs activos
            try {
                $conflictosResumido = ConflictoHorario::selectRaw('estado, COUNT(*) as total')
                    ->when($periodo, function ($query) use ($periodo) {
                        return $query->where('periodo_academico', $periodo);
                    })
                    ->groupBy('estado')
                    ->get();
            } catch (\Exception $e) {
                $conflictosResumido = collect();
            }

            return response()->json([
                'indicadores' => [
                    'total_carga_asignada' => $totalCargaAsignada,
                    'total_asistencias' => $totalAsistencias,
                    'total_conflictos' => $totalConflictos,
                ],
                'carga_por_docente' => $cargaPorDocente,
                'asistencia_por_periodo' => $asistenciaPorPeriodo,
                'conflictos_resumido' => $conflictosResumido,
                'periodo_filtrado' => $periodo ?? 'Todos',
                'usuario' => [
                    'nombre' => $usuario->nombre_persona,
                    'rol' => $rol,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener datos del dashboard',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener períodos académicos disponibles para el filtro
     */
    public function getPeriodos(Request $request)
    {
        try {
            $periodos = AsignacionHorario::select('periodo_academico')
                ->distinct()
                ->orderBy('periodo_academico', 'desc')
                ->pluck('periodo_academico');

            return response()->json([
                'periodos' => $periodos
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener períodos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Control de KPIs - Obtener indicadores clave de desempeño
     * Métricas: Porcentaje de carga asignada, Tasa de asistencia, Resolución de conflictos
     */
    public function getKPIs(Request $request)
    {
        try {
            $usuario = auth('sanctum')->user();
            
            $periodo = $request->query('periodo_academico', null);
            
            // KPI 1: Porcentaje de carga asignada
            $cargaTotal = AsignacionHorario::query();
            if ($periodo) {
                $cargaTotal->where('periodo_academico', $periodo);
            }
            $totalCarga = $cargaTotal->count();
            $cargaActiva = (clone $cargaTotal)->where('estado', 'ACTIVO')->count();
            $porcentajeCarga = $totalCarga > 0 ? round(($cargaActiva / $totalCarga) * 100, 2) : 0;

            // KPI 2: Tasa de asistencia
            $asistenciasTotal = Asistencia::count();
            $asistenciasConfirmadas = Asistencia::where('estado', 'CONFIRMADA')->count();
            $tasaAsistencia = $asistenciasTotal > 0 ? round(($asistenciasConfirmadas / $asistenciasTotal) * 100, 2) : 0;

            // KPI 3: Resolución de conflictos
            try {
                $conflictosTotal = ConflictoHorario::count();
                $conflictosResueltos = ConflictoHorario::where('estado', 'RESUELTO')->count();
                $tasaResolucion = $conflictosTotal > 0 ? round(($conflictosResueltos / $conflictosTotal) * 100, 2) : 0;
            } catch (\Exception $e) {
                $conflictosTotal = 0;
                $conflictosResueltos = 0;
                $tasaResolucion = 0;
            }

            return response()->json([
                'kpis' => [
                    'carga_asignada' => [
                        'total' => $totalCarga,
                        'activa' => $cargaActiva,
                        'porcentaje' => $porcentajeCarga,
                        'descripcion' => 'Porcentaje de carga horaria asignada y activa'
                    ],
                    'tasa_asistencia' => [
                        'total' => $asistenciasTotal,
                        'confirmadas' => $asistenciasConfirmadas,
                        'porcentaje' => $tasaAsistencia,
                        'descripcion' => 'Porcentaje de asistencias confirmadas'
                    ],
                    'resolucion_conflictos' => [
                        'total' => $conflictosTotal,
                        'resueltos' => $conflictosResueltos,
                        'porcentaje' => $tasaResolucion,
                        'descripcion' => 'Porcentaje de conflictos horarios resueltos'
                    ]
                ],
                'periodo_filtrado' => $periodo ?? 'Todos',
                'usuario' => [
                    'nombre' => $usuario->nombre_persona,
                    'rol' => $usuario->rol->nombre ?? null,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener KPIs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Coordinación de Horario - Obtener información coordinada de asignaciones y horarios
     */
    public function getCoordinacionHorario(Request $request)
    {
        try {
            $usuario = auth('sanctum')->user();
            $periodo = $request->query('periodo_academico', null);

            // Obtener asignaciones con relaciones completas
            $asignaciones = AsignacionHorario::query()
                ->when($periodo, function ($query) use ($periodo) {
                    return $query->where('periodo_academico', $periodo);
                })
                ->with(['docente.usuario.persona', 'horario', 'grupo', 'aula'])
                ->get();

            // Analizar coordinación: docentes, grupos, aulas
            $coordinacion = [
                'docentes_coordinados' => $asignaciones->pluck('codigo_doc')->unique()->count(),
                'grupos_coordinados' => $asignaciones->pluck('codigo_grupo')->unique()->count(),
                'aulas_utilizadas' => $asignaciones->pluck('nro_aula')->unique()->count(),
                'asignaciones_detalle' => $asignaciones->map(function ($asignacion) {
                    return [
                        'id_asignacion' => $asignacion->id_asignacion,
                        'docente' => $asignacion->docente?->usuario?->persona?->nombre,
                        'grupo' => $asignacion->codigo_grupo,
                        'aula' => $asignacion->nro_aula,
                        'estado' => $asignacion->estado,
                        'periodo' => $asignacion->periodo_academico,
                        'horario_id' => $asignacion->id_horario
                    ];
                })
            ];

            return response()->json([
                'coordinacion' => $coordinacion,
                'total_asignaciones' => $asignaciones->count(),
                'periodo_filtrado' => $periodo ?? 'Todos',
                'usuario' => [
                    'nombre' => $usuario->nombre_persona,
                    'rol' => $usuario->rol->nombre ?? null,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener coordinación de horario',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Acceso a Bitácora - Obtener registros de bitácora del sistema
     */
    public function getBitacora(Request $request)
    {
        try {
            $usuario = auth('sanctum')->user();
            
            // Validar que solo Administrador acceda a bitácora completa
            $rol = $usuario->rol->nombre ?? null;
            if ($rol !== 'Administrador') {
                return response()->json([
                    'message' => 'Solo administradores pueden acceder a la bitácora'
                ], 403);
            }

            $limite = $request->query('limite', 100);
            $modulo = $request->query('modulo', null); // Filtrar por módulo en lugar de tipo

            // Obtener registros de bitácora
            $bitacora = Bitacora::query()
                ->when($modulo, function ($query) use ($modulo) {
                    return $query->where('modulo', $modulo);
                })
                ->with('usuario') // Cargar datos del usuario
                ->orderBy('id_bit', 'desc')
                ->limit($limite)
                ->get();

            // Agrupar por módulo
            $bitacoraAgrupada = $bitacora->groupBy('modulo')
                ->map(function ($grupo) {
                    return [
                        'modulo' => $grupo->first()->modulo,
                        'cantidad' => $grupo->count(),
                        'registros' => $grupo->map(function ($item) {
                            return [
                                'id' => $item->id_bit,
                                'modulo' => $item->modulo,
                                'accion' => $item->accion,
                                'usuario_id' => $item->id_usuario,
                                'usuario' => $item->usuario ? $item->usuario->nombre_persona ?? 'Sistema' : 'Sistema',
                                'fecha' => $item->fecha_accion
                            ];
                        })->take(5) // Mostrar solo los 5 más recientes por módulo
                    ];
                });

            return response()->json([
                'bitacora' => $bitacoraAgrupada->values(),
                'registros_por_tipo' => $bitacora->groupBy('modulo')->mapWithKeys(function ($grupo, $key) {
                    return [$key => $grupo->count()];
                }),
                'total_registros' => $bitacora->count(),
                'limite' => $limite,
                'usuario' => [
                    'nombre' => $usuario->persona->nombre_persona ?? 'N/A',
                    'rol' => $rol,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener bitácora',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
