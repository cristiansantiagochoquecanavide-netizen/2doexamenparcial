<?php

namespace App\Http\Controllers\Monitoreo_y_Reportes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AsignacionHorario;
use App\Models\Asistencia;
use App\Models\Aula;
use App\Models\Bitacora;
use App\Models\Docente;
use App\Models\Grupo;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportesController extends Controller
{
    /**
     * CU17: Generar Reportes en PDF/Excel
     */
    public function generar(Request $request)
    {
        try {
            $tipoReporte = $request->input('tipo_reporte', 'horarios_semanales');
            $formato = $request->input('formato', 'pdf');
            $previsualizar = $request->input('previsualizar', false);
            $periodoAcademico = $request->input('periodo_academico');
            $docenteId = $request->input('docente_id');
            $grupoId = $request->input('grupo_id');

            // Log para depuración
            \Log::info('Generando reporte', [
                'tipo' => $tipoReporte,
                'formato' => $formato,
                'periodo' => $periodoAcademico,
                'docente' => $docenteId,
                'grupo' => $grupoId
            ]);

            // Obtener datos según tipo de reporte
            $datos = match($tipoReporte) {
                'horarios_semanales' => $this->obtenerHorariosSemanales($periodoAcademico, $docenteId),
                'asistencia_docente' => $this->obtenerAsistenciaDocente($periodoAcademico, $docenteId, $grupoId),
                'aulas_disponibles' => $this->obtenerAulasDisponibles(),
                default => []
            };

            \Log::info('Datos obtenidos', ['cantidad' => count($datos)]);

            // Si es previsualización, retornar JSON
            if ($previsualizar) {
                return response()->json([
                    'success' => true,
                    'data' => $datos,
                    'tipo_reporte' => $tipoReporte,
                    'cantidad' => count($datos)
                ]);
            }

            // Registrar en bitácora
            Bitacora::registrar(
                'Reportes',
                "Generó reporte: {$tipoReporte}",
                auth()->id(),
                ['tipo' => $tipoReporte, 'formato' => $formato]
            );

            // Generar reporte según formato
            if ($formato === 'pdf') {
                return $this->generarPDF($tipoReporte, $datos);
            } else {
                return $this->generarExcel($tipoReporte, $datos);
            }

        } catch (\Exception $e) {
            \Log::error('Error en generar reporte: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al generar reporte: ' . $e->getMessage(),
                'error' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null
            ], 500);
        }
    }

    /**
     * Obtener Horarios Semanales
     */
    private function obtenerHorariosSemanales($periodo = null, $docenteId = null)
    {
        try {
            \Log::info('obtenerHorariosSemanales llamado', [
                'periodo' => $periodo,
                'docenteId' => $docenteId
            ]);

            $query = AsignacionHorario::with([
                'docente.usuario.persona',
                'grupo.materia',
                'aula.infraestructura',
                'horario'
            ]);

            if ($periodo) {
                \Log::info('Filtrando por periodo', ['periodo' => $periodo]);
                $query->where('periodo_academico', $periodo);
            }

            if ($docenteId) {
                \Log::info('Filtrando por docente', ['docenteId' => $docenteId]);
                $query->where('codigo_doc', $docenteId);
            }

            // Verificar si hay registros sin filtro de estado
            $totalSinFiltro = AsignacionHorario::count();
            \Log::info('Total de asignaciones en BD', ['total' => $totalSinFiltro]);

            // Verificar con solo filtros de periodo y docente
            $testQuery = clone $query;
            $countConFiltros = $testQuery->count();
            \Log::info('Asignaciones con filtros (sin estado)', ['count' => $countConFiltros]);

            // Ahora aplicar filtro de estado (ACTIVO en mayúsculas)
            $query->where('estado', 'ACTIVO');
            
            $asignaciones = $query->get();
            \Log::info('Asignaciones obtenidas', ['cantidad' => $asignaciones->count()]);

            if ($asignaciones->isEmpty()) {
                return [];
            }

            return $asignaciones->map(function ($asignacion) {
                $docente = $asignacion->docente;
                $nombreDocente = 'Sin docente';
                
                if ($docente && $docente->usuario && $docente->usuario->persona) {
                    $persona = $docente->usuario->persona;
                    $nombreDocente = trim($persona->nombre . ' ' . ($persona->apellido_paterno ?? '') . ' ' . ($persona->apellido_materno ?? ''));
                }

                // Formatear horario como en CU11
                $horario = '';
                if ($asignacion->horario) {
                    $horaInicio = $asignacion->horario->hora_inicio ? 
                        (is_string($asignacion->horario->hora_inicio) ? 
                            substr($asignacion->horario->hora_inicio, 0, 5) : 
                            $asignacion->horario->hora_inicio->format('H:i')) 
                        : 'No definido';
                    
                    $horaFin = $asignacion->horario->hora_fin ? 
                        (is_string($asignacion->horario->hora_fin) ? 
                            substr($asignacion->horario->hora_fin, 0, 5) : 
                            $asignacion->horario->hora_fin->format('H:i')) 
                        : 'No definido';
                    
                    $horario = $horaInicio . ' - ' . $horaFin;
                }

                return [
                    'ID' => $asignacion->id_asignacion,
                    'Docente' => $nombreDocente,
                    'Materia' => $asignacion->grupo->materia->nombre_mat ?? 'Sin materia',
                    'Grupo' => $asignacion->grupo->codigo_grupo ?? 'Sin grupo',
                    'Aula' => $asignacion->aula->nro_aula ?? 'Sin aula',
                    'Horario' => $horario ?: 'No definido',
                    'Periodo Académico' => $asignacion->periodo_academico ?? 'No definido'
                ];
            })->toArray();

        } catch (\Exception $e) {
            \Log::error('Error en obtenerHorariosSemanales: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener Asistencia por Docente y Grupo
     */
    private function obtenerAsistenciaDocente($periodo = null, $docenteId = null, $grupoId = null)
    {
        try {
            $query = DB::table('carga_horaria.sesion_asistencia as sa')
                ->join('carga_horaria.asignacion_horario as ah', 'sa.id_asignacion', '=', 'ah.id_asignacion')
                ->join('carga_horaria.docente as d', 'ah.codigo_doc', '=', 'd.codigo_doc')
                ->join('carga_horaria.usuario as u', 'd.id_usuario', '=', 'u.id_usuario')
                ->join('carga_horaria.persona as p', 'u.ci_persona', '=', 'p.ci')
                ->join('carga_horaria.grupo as g', 'ah.codigo_grupo', '=', 'g.codigo_grupo')
                ->join('carga_horaria.materia as m', 'g.codigo_mat', '=', 'm.codigo_mat')
                ->select(
                    DB::raw("CONCAT(p.nombre, ' ', COALESCE(p.apellido_paterno, ''), ' ', COALESCE(p.apellido_materno, '')) as docente"),
                    'm.nombre_mat as materia',
                    'g.codigo_grupo as grupo',
                    DB::raw("TO_CHAR(sa.fecha_sesion, 'DD/MM/YYYY') as fecha"),
                    DB::raw("TO_CHAR(sa.hora_inicio, 'HH24:MI') as hora_inicio"),
                    DB::raw("TO_CHAR(sa.hora_fin, 'HH24:MI') as hora_fin"),
                    'sa.estado',
                    DB::raw("COALESCE((SELECT COUNT(*) FROM carga_horaria.asistencia WHERE id_sesion = sa.id_sesion AND estado = 'presente'), 0) as asistentes"),
                    DB::raw("COALESCE(g.capacidad_de_grupo, 0) as total_estudiantes")
                );

            if ($periodo) {
                $query->where('ah.periodo_academico', $periodo);
            }

            if ($docenteId) {
                $query->where('d.codigo_doc', $docenteId);
            }

            if ($grupoId) {
                $query->where('g.codigo_grupo', $grupoId);
            }

            $query->whereNotNull('sa.fecha_sesion')
                  ->orderBy('sa.fecha_sesion', 'desc');

            $resultados = $query->get();

            // Si no hay resultados, retornar array vacío en lugar de error
            if ($resultados->isEmpty()) {
                return [];
            }

            return $resultados->map(function ($item) {
                $porcentaje = 0;
                if ($item->total_estudiantes > 0) {
                    $porcentaje = round(($item->asistentes / $item->total_estudiantes) * 100, 2);
                }

                return [
                    'docente' => trim($item->docente),
                    'materia' => $item->materia,
                    'grupo' => $item->grupo,
                    'fecha' => $item->fecha,
                    'hora_inicio' => $item->hora_inicio ?? 'No definido',
                    'hora_fin' => $item->hora_fin ?? 'No definido',
                    'estado' => $item->estado ?? 'desconocido',
                    'asistentes' => $item->asistentes,
                    'total_estudiantes' => $item->total_estudiantes,
                    'porcentaje_asistencia' => $porcentaje . '%'
                ];
            })->toArray();

        } catch (\Exception $e) {
            // Log del error para depuración
            \Log::error('Error en obtenerAsistenciaDocente: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            // Retornar array vacío en caso de error
            return [];
        }
    }

    /**
     * Obtener Aulas Disponibles
     */
    private function obtenerAulasDisponibles()
    {
        try {
            $aulas = Aula::with('infraestructura')->get();

            if ($aulas->isEmpty()) {
                return [];
            }

            return $aulas->map(function ($aula) {
                // Contar asignaciones activas
                $asignacionesActivas = AsignacionHorario::where('nro_aula', $aula->nro_aula)
                    ->where('estado', 'activo')
                    ->count();

                $disponibilidad = $asignacionesActivas == 0 ? 'Disponible' : 'Ocupada';

                return [
                    'aula' => $aula->nro_aula,
                    'infraestructura' => $aula->infraestructura->nombre_infra ?? 'Sin edificio',
                    'capacidad' => $aula->capacidad ?? 0,
                    'tipo' => $aula->tipo_aula ?? 'Normal',
                    'asignaciones_activas' => $asignacionesActivas,
                    'disponibilidad' => $disponibilidad
                ];
            })->toArray();

        } catch (\Exception $e) {
            \Log::error('Error en obtenerAulasDisponibles: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Generar PDF
     */
    private function generarPDF($tipoReporte, $datos)
    {
        $titulo = match($tipoReporte) {
            'horarios_semanales' => 'Horarios Semanales',
            'asistencia_docente' => 'Asistencia por Docente y Grupo',
            'aulas_disponibles' => 'Aulas Disponibles',
            default => 'Reporte'
        };

        $pdf = PDF::loadView('reportes.pdf', [
            'titulo' => $titulo,
            'datos' => $datos,
            'fecha' => now()->format('d/m/Y H:i'),
            'tipo' => $tipoReporte
        ]);

        $filename = str_replace(' ', '_', strtolower($titulo)) . '_' . now()->format('Ymd_His') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Generar Excel (CSV)
     */
    private function generarExcel($tipoReporte, $datos)
    {
        if (empty($datos)) {
            return response()->json([
                'success' => false,
                'message' => 'No hay datos para exportar'
            ], 400);
        }

        $titulo = match($tipoReporte) {
            'horarios_semanales' => 'Horarios_Semanales',
            'asistencia_docente' => 'Asistencia_Docente',
            'aulas_disponibles' => 'Aulas_Disponibles',
            default => 'Reporte'
        };

        $filename = $titulo . '_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($datos) {
            $file = fopen('php://output', 'w');
            
            // Encabezados
            fputcsv($file, array_keys($datos[0]));
            
            // Datos
            foreach ($datos as $row) {
                fputcsv($file, $row);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Compartir reporte
     */
    public function compartir(Request $request)
    {
        try {
            $validated = $request->validate([
                'tipo_reporte' => 'required|string',
                'destinatarios' => 'required|array',
                'mensaje' => 'nullable|string',
                'formato' => 'required|in:pdf,excel'
            ]);

            // Registrar en bitácora
            Bitacora::registrar(
                'Reportes',
                "Compartió reporte: {$validated['tipo_reporte']}",
                auth()->id(),
                $validated
            );

            return response()->json([
                'success' => true,
                'message' => 'Reporte compartido exitosamente',
                'destinatarios' => count($validated['destinatarios'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al compartir reporte: ' . $e->getMessage()
            ], 500);
        }
    }
}
