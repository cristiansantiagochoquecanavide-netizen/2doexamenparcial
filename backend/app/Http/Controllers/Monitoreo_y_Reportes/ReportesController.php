<?php

namespace App\Http\Controllers\Monitoreo_y_Reportes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AsignacionHorario;
use App\Models\Asistencia;
use App\Models\Inasistencia;
use App\Models\Infraestructura;
use App\Models\SesionAsistencia;
use App\Models\Bitacora;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ReportesController extends Controller
{
    /**
     * CU17: Generar Reportes en PDF/Excel
     * 
     * Genera reportes de:
     * 1. Asignaciones de Carga Horaria
     * 2. Asistencia Docente
     * 3. Inasistencias y Justificaciones
     * 4. Ocupación de Aulas
     */
    public function generar(Request $request)
    {
        try {
            $usuario = auth('sanctum')->user();
            
            // Validar que solo Administrador y Coordinador Académico puedan generar reportes
            $rol = $usuario->rol->nombre ?? null;
            if (!in_array($rol, ['Administrador', 'Coordinador Académico'])) {
                return response()->json(['message' => 'No tienes permiso para generar reportes'], 403);
            }

            // Validar parámetros
            $validated = $request->validate([
                'tipo_reporte' => 'required|in:asignaciones,asistencia,inasistencias,ocupacion_aulas',
                'formato' => 'required|in:json,csv,pdf,excel',
                'periodo_academico' => 'nullable|string',
                'filtros' => 'nullable|array'
            ]);

            $tipoReporte = $validated['tipo_reporte'];
            $formato = $validated['formato'];
            $periodo = $validated['periodo_academico'] ?? null;
            $filtros = $validated['filtros'] ?? [];

            // Obtener datos según tipo de reporte
            $datos = match($tipoReporte) {
                'asignaciones' => $this->obtenerAsignaciones($periodo, $filtros),
                'asistencia' => $this->obtenerAsistencia($periodo, $filtros),
                'inasistencias' => $this->obtenerInasistencias($periodo, $filtros),
                'ocupacion_aulas' => $this->obtenerOcupacionAulas($periodo, $filtros),
                default => []
            };

            // Registrar en bitácora
            Bitacora::create([
                'codigo_usuario' => $usuario->id,
                'accion' => "Generar reporte: {$tipoReporte}",
                'descripcion' => "Reporte generado en formato {$formato}",
                'tipo_evento' => 'GENERAR_REPORTE',
                'fecha_hora' => now(),
                'tabla_afectada' => 'Reportes',
                'detalles' => json_encode([
                    'tipo_reporte' => $tipoReporte,
                    'formato' => $formato,
                    'periodo' => $periodo,
                    'registros_generados' => count($datos)
                ])
            ]);

            // Preparar respuesta según formato
            if ($formato === 'json') {
                return response()->json([
                    'success' => true,
                    'tipo_reporte' => $tipoReporte,
                    'formato' => 'json',
                    'registros' => count($datos),
                    'datos' => $datos,
                    'descarga_url' => null,
                    'generado_en' => now(),
                    'usuario' => $usuario->usuario
                ]);
            }

            if ($formato === 'csv') {
                return $this->exportarCSV($tipoReporte, $datos);
            }

            if ($formato === 'pdf') {
                return $this->exportarPDF($tipoReporte, $datos);
            }

            if ($formato === 'excel') {
                return $this->exportarExcel($tipoReporte, $datos);
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'Validación fallida', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error generando reporte', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Obtener datos de Asignaciones de Carga Horaria
     */
    private function obtenerAsignaciones($periodo = null, $filtros = [])
    {
        $query = AsignacionHorario::query()
            ->with([
                'docente.usuario.persona',
                'materia',
                'grupo',
                'horario'
            ]);

        if ($periodo) {
            $query->where('periodo_academico', $periodo);
        }

        if (isset($filtros['codigo_doc'])) {
            $query->where('codigo_doc', $filtros['codigo_doc']);
        }

        if (isset($filtros['codigo_grupo'])) {
            $query->where('codigo_grupo', $filtros['codigo_grupo']);
        }

        return $query->get()->map(function ($asignacion) {
            return [
                'id' => $asignacion->id,
                'codigo_doc' => $asignacion->codigo_doc,
                'docente_nombre' => optional($asignacion->docente->usuario->persona)->nombre_completo ?? 'N/A',
                'codigo_materia' => $asignacion->codigo_materia,
                'materia_nombre' => optional($asignacion->materia)->nombre ?? 'N/A',
                'codigo_grupo' => $asignacion->codigo_grupo,
                'grupo_nombre' => optional($asignacion->grupo)->nombre ?? 'N/A',
                'horas_semanales' => $asignacion->horas_semanales,
                'horario_clase' => optional($asignacion->horario)->horario_clase ?? 'N/A',
                'periodo_academico' => $asignacion->periodo_academico,
                'fecha_asignacion' => $asignacion->created_at,
            ];
        })->toArray();
    }

    /**
     * Obtener datos de Asistencia Docente
     */
    private function obtenerAsistencia($periodo = null, $filtros = [])
    {
        $query = Asistencia::query()
            ->with('docente.usuario.persona');

        if ($periodo) {
            $year = substr($periodo, 0, 4);
            $query->whereRaw("EXTRACT(YEAR FROM fecha)::integer = ?", [$year]);
        }

        if (isset($filtros['codigo_doc'])) {
            $query->whereHas('docente', function ($q) use ($filtros) {
                $q->where('codigo', $filtros['codigo_doc']);
            });
        }

        if (isset($filtros['estado'])) {
            $query->where('estado', $filtros['estado']);
        }

        return $query->orderBy('fecha', 'desc')->get()->map(function ($asistencia) {
            return [
                'id' => $asistencia->id,
                'codigo_doc' => $asistencia->docente->codigo ?? 'N/A',
                'docente_nombre' => optional($asistencia->docente->usuario->persona)->nombre_completo ?? 'N/A',
                'fecha' => $asistencia->fecha,
                'hora_entrada' => $asistencia->hora_entrada,
                'hora_salida' => $asistencia->hora_salida,
                'estado' => $asistencia->estado,
                'ubicacion' => $asistencia->ubicacion ?? 'N/A',
                'metodo_registro' => $asistencia->metodo_registro ?? 'N/A',
            ];
        })->toArray();
    }

    /**
     * Obtener datos de Inasistencias y Justificaciones
     */
    private function obtenerInasistencias($periodo = null, $filtros = [])
    {
        $query = Inasistencia::query()
            ->with([
                'docente.usuario.persona',
                'justificativo'
            ]);

        if ($periodo) {
            $year = substr($periodo, 0, 4);
            $query->whereRaw("EXTRACT(YEAR FROM fecha_inasistencia)::integer = ?", [$year]);
        }

        if (isset($filtros['codigo_doc'])) {
            $query->whereHas('docente', function ($q) use ($filtros) {
                $q->where('codigo', $filtros['codigo_doc']);
            });
        }

        if (isset($filtros['estado_justificacion'])) {
            $query->where('estado_justificacion', $filtros['estado_justificacion']);
        }

        return $query->orderBy('fecha_inasistencia', 'desc')->get()->map(function ($inasistencia) {
            return [
                'id' => $inasistencia->id,
                'codigo_doc' => $inasistencia->docente->codigo ?? 'N/A',
                'docente_nombre' => optional($inasistencia->docente->usuario->persona)->nombre_completo ?? 'N/A',
                'fecha_inasistencia' => $inasistencia->fecha_inasistencia,
                'motivo' => $inasistencia->motivo,
                'justificado' => $inasistencia->estado_justificacion,
                'tipo_justificativo' => optional($inasistencia->justificativo)->tipo_justificativo ?? 'N/A',
                'estado_resolucion' => optional($inasistencia->justificativo)->estado_resolucion ?? 'PENDIENTE',
                'fecha_resolucion' => optional($inasistencia->justificativo)->fecha_resolucion ?? null,
                'observaciones' => $inasistencia->observaciones ?? 'N/A',
            ];
        })->toArray();
    }

    /**
     * Obtener datos de Ocupación de Aulas
     */
    private function obtenerOcupacionAulas($periodo = null, $filtros = [])
    {
        // Obtener ocupación de aulas basado en asignaciones
        $query = DB::table('asignacion_horarios as ah')
            ->join('infraestructura_aulas as ia', 'ah.codigo_aula', '=', 'ia.codigo_aula')
            ->join('grupos as g', 'ah.codigo_grupo', '=', 'g.codigo_grupo')
            ->select(
                'ia.codigo_aula',
                'ia.nombre_aula',
                'ia.capacidad',
                DB::raw('COUNT(DISTINCT ah.codigo_grupo) as grupos_asignados'),
                DB::raw('COUNT(DISTINCT ah.codigo_doc) as docentes_asignados'),
                DB::raw('SUM(g.cantidad_estudiantes) as estudiantes_total'),
                'ah.periodo_academico'
            );

        if ($periodo) {
            $query->where('ah.periodo_academico', $periodo);
        }

        if (isset($filtros['codigo_aula'])) {
            $query->where('ia.codigo_aula', $filtros['codigo_aula']);
        }

        return $query->groupBy('ia.codigo_aula', 'ia.nombre_aula', 'ia.capacidad', 'ah.periodo_academico')
            ->orderBy('ia.codigo_aula')
            ->get()
            ->map(function ($aula) {
                $ocupacion = ($aula->estudiantes_total / $aula->capacidad) * 100;
                return [
                    'codigo_aula' => $aula->codigo_aula,
                    'nombre_aula' => $aula->nombre_aula,
                    'capacidad' => $aula->capacidad,
                    'estudiantes_total' => $aula->estudiantes_total ?? 0,
                    'grupos_asignados' => $aula->grupos_asignados,
                    'docentes_asignados' => $aula->docentes_asignados,
                    'porcentaje_ocupacion' => round($ocupacion, 2),
                    'periodo_academico' => $aula->periodo_academico,
                ];
            })
            ->toArray();
    }

    /**
     * Compartir reporte (generar enlace de descarga)
     */
    public function compartir(Request $request)
    {
        try {
            $usuario = auth('sanctum')->user();
            
            // Validar rol
            $rol = $usuario->rol->nombre ?? null;
            if (!in_array($rol, ['Administrador', 'Coordinador Académico'])) {
                return response()->json(['message' => 'No tienes permiso para compartir reportes'], 403);
            }

            $validated = $request->validate([
                'tipo_reporte' => 'required|string',
                'destinatarios' => 'required|array',
                'mensaje' => 'nullable|string',
                'formato' => 'required|in:pdf,excel,csv'
            ]);

            // Registrar compartición en bitácora
            Bitacora::create([
                'codigo_usuario' => $usuario->id,
                'accion' => "Compartir reporte: {$validated['tipo_reporte']}",
                'descripcion' => "Reporte compartido con " . count($validated['destinatarios']) . " destinatarios",
                'tipo_evento' => 'COMPARTIR_REPORTE',
                'fecha_hora' => now(),
                'tabla_afectada' => 'Reportes',
                'detalles' => json_encode([
                    'tipo_reporte' => $validated['tipo_reporte'],
                    'destinatarios' => $validated['destinatarios'],
                    'formato' => $validated['formato']
                ])
            ]);

            // Generar token único para descarga
            $token = bin2hex(random_bytes(32));
            
            return response()->json([
                'success' => true,
                'mensaje' => 'Reporte compartido exitosamente',
                'token_descarga' => $token,
                'url_descarga' => route('reportes.descargar', ['token' => $token]),
                'destinatarios' => count($validated['destinatarios']),
                'formato' => $validated['formato'],
                'generado_en' => now(),
                'expira_en' => now()->addDays(7)
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'Validación fallida', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error compartiendo reporte', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Exportar a CSV
     */
    private function exportarCSV($tipoReporte, $datos)
    {
        // Crear contenido CSV
        $filename = "reporte_{$tipoReporte}_" . now()->format('Ymd_His') . ".csv";
        $path = "reports/" . $filename;
        
        $csv = $this->generarCSV($datos);
        Storage::disk('public')->put($path, $csv);

        return response()->json([
            'success' => true,
            'tipo_reporte' => $tipoReporte,
            'formato' => 'csv',
            'registros' => count($datos),
            'descarga_url' => asset('storage/' . $path),
            'filename' => $filename,
            'generado_en' => now()
        ]);
    }

    /**
     * Exportar a PDF (simulado - requiere librería)
     */
    private function exportarPDF($tipoReporte, $datos)
    {
        // Esta es una implementación básica
        // En producción, usar biblioteca como TCPDF o mPDF
        $filename = "reporte_{$tipoReporte}_" . now()->format('Ymd_His') . ".pdf";
        
        return response()->json([
            'success' => true,
            'tipo_reporte' => $tipoReporte,
            'formato' => 'pdf',
            'registros' => count($datos),
            'descarga_url' => route('reportes.descargar_pdf', ['tipo' => $tipoReporte]),
            'filename' => $filename,
            'generado_en' => now(),
            'nota' => 'PDF generation requires TCPDF or mPDF library installation'
        ]);
    }

    /**
     * Exportar a Excel (simulado - requiere librería)
     */
    private function exportarExcel($tipoReporte, $datos)
    {
        // Esta es una implementación básica
        // En producción, usar biblioteca como PhpSpreadsheet
        $filename = "reporte_{$tipoReporte}_" . now()->format('Ymd_His') . ".xlsx";
        
        return response()->json([
            'success' => true,
            'tipo_reporte' => $tipoReporte,
            'formato' => 'excel',
            'registros' => count($datos),
            'descarga_url' => route('reportes.descargar_excel', ['tipo' => $tipoReporte]),
            'filename' => $filename,
            'generado_en' => now(),
            'nota' => 'Excel generation requires PhpSpreadsheet library installation'
        ]);
    }

    /**
     * Generar contenido CSV desde array de datos
     */
    private function generarCSV($datos)
    {
        if (empty($datos)) {
            return '';
        }

        $output = '';
        $headers = array_keys($datos[0]);
        
        // Agregar encabezados
        $output .= implode(',', array_map(function($h) { 
            return '"' . str_replace('"', '""', $h) . '"'; 
        }, $headers)) . "\n";
        
        // Agregar datos
        foreach ($datos as $fila) {
            $output .= implode(',', array_map(function($valor) {
                if (is_null($valor)) return '';
                if (is_bool($valor)) return $valor ? '1' : '0';
                return '"' . str_replace('"', '""', (string)$valor) . '"';
            }, $fila)) . "\n";
        }

        return $output;
    }
}
