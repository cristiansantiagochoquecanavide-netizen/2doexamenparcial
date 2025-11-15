<?php

namespace App\Http\Controllers\Asistencia_Docente;

use App\Http\Controllers\Controller;
use App\Models\Inasistencia;
use App\Models\Justificativo;
use App\Models\ResolucionInasistencia;
use App\Models\Bitacora;
use Illuminate\Http\Request;
use Carbon\Carbon;

class GestionInasistenciasController extends Controller
{
    /**
     * CU15: Listar inasistencias pendientes (vista de coordinador)
     */
    public function listarPendientes(Request $request)
    {
        try {
            $estado = $request->input('estado', 'PENDIENTE');
            $desde = $request->input('desde');
            $hasta = $request->input('hasta');

            $query = Inasistencia::query();

            if ($estado) {
                $query->where('estado', $estado);
            }

            if ($desde) {
                $query->whereDate('fecha', '>=', $desde);
            }

            if ($hasta) {
                $query->whereDate('fecha', '<=', $hasta);
            }

            $inasistencias = $query->with(['justificativos'])
                ->orderByDesc('fecha')
                ->paginate(15);

            return response()->json([
                'success' => true,
                'data' => $inasistencias
            ]);
        } catch (\Exception $e) {
            \Log::error('Error listando inasistencias: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al listar inasistencias',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * CU15: Obtener detalle de una inasistencia
     */
    public function mostrarDetalle($id)
    {
        try {
            $inasistencia = Inasistencia::with([
                'justificativos',
                'resolucion.coordinador'
            ])->find($id);

            if (!$inasistencia) {
                return response()->json([
                    'message' => 'Inasistencia no encontrada'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $inasistencia
            ]);
        } catch (\Exception $e) {
            \Log::error('Error obteniendo detalle: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener detalle',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * CU15: Subir justificativo para una inasistencia
     */
    public function subirJustificativo(Request $request)
    {
        try {
            $request->validate([
                'id_inasistencia' => 'required|integer|exists:inasistencias,id_inasistencia',
                'archivo' => 'required|file|max:5120|mimes:pdf,doc,docx,jpg,jpeg,png',
                'motivo' => 'required|string|max:500',
            ]);

            $inasistencia = Inasistencia::find($request->id_inasistencia);

            if (!$inasistencia) {
                return response()->json(['message' => 'Inasistencia no encontrada'], 404);
            }

            // Guardar archivo
            $archivo = $request->file('archivo');
            $nombreArchivo = 'just_' . $inasistencia->id_inasistencia . '_' . time() . '.' . $archivo->getClientOriginalExtension();
            $ruta = $archivo->storeAs('justificativos', $nombreArchivo, 'public');

            // Crear justificativo
            $justificativo = Justificativo::create([
                'id_inasistencia' => $inasistencia->id_inasistencia,
                'archivo_ruta' => $ruta,
                'archivo_nombre_original' => $archivo->getClientOriginalName(),
                'archivo_tipo' => $archivo->getClientMimeType(),
                'archivo_tamaño' => $archivo->getSize(),
                'motivo_justificacion' => $request->input('motivo'),
                'estado_revision' => 'EN_REVISION',
                'fecha_carga' => Carbon::now(),
            ]);

            // Actualizar estado de inasistencia
            $inasistencia->cambiarEstado('EN_REVISION');

            // Registrar en bitácora
            Bitacora::registrar(
                'Asistencia_Docente',
                'SUBIR_JUSTIFICATIVO',
                auth('sanctum')->id(),
                [
                    'id_inasistencia' => $inasistencia->id_inasistencia,
                    'id_justificativo' => $justificativo->id_justificativo,
                    'archivo' => $nombreArchivo
                ],
                'justificativos',
                $justificativo->id_justificativo
            );

            return response()->json([
                'success' => true,
                'message' => 'Justificativo subido exitosamente',
                'data' => $justificativo
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Error subiendo justificativo: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al subir justificativo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * CU15: Revisar y aprobar/rechazar inasistencia
     */
    public function revisar(Request $request)
    {
        try {
            $request->validate([
                'id_inasistencia' => 'required|integer|exists:inasistencias,id_inasistencia',
                'decision' => 'required|in:APROBADA,RECHAZADA',
                'tipo_accion' => 'required|in:REPOSICION,AJUSTE,CONDONACION,NINGUNA',
                'descripcion' => 'nullable|string|max:500',
                'comentario_justificativo' => 'nullable|string|max:500',
            ]);

            $inasistencia = Inasistencia::find($request->id_inasistencia);

            if (!$inasistencia) {
                return response()->json(['message' => 'Inasistencia no encontrada'], 404);
            }

            // Validar justificativo si existe
            $justificativo = $inasistencia->obtenerUltimoJustificativo();
            if ($justificativo) {
                $justificativo->cambiarEstadoRevision(
                    $request->decision === 'APROBADA' ? 'APROBADO' : 'RECHAZADO',
                    $request->input('comentario_justificativo')
                );
            }

            // Crear resolución
            $resolucion = ResolucionInasistencia::create([
                'id_inasistencia' => $inasistencia->id_inasistencia,
                'decision_final' => $request->input('decision'),
                'tipo_accion' => $request->input('tipo_accion'),
                'descripcion_accion' => $request->input('descripcion'),
                'fecha_resolucion' => Carbon::now(),
                'id_usuario_coordinador' => auth('sanctum')->id(),
            ]);

            // Actualizar estado de inasistencia
            $nuevoEstado = $request->decision === 'APROBADA' ? 'RESUELTA' : 'RECHAZADA';
            $inasistencia->cambiarEstado($nuevoEstado);

            // Actualizar tipo de inasistencia
            if ($request->decision === 'APROBADA' && $justificativo) {
                $inasistencia->update(['tipo_inasistencia' => 'JUSTIFICADA']);
            }

            // Registrar en bitácora
            Bitacora::registrar(
                'Asistencia_Docente',
                'RESOLVER_INASISTENCIA',
                auth('sanctum')->id(),
                [
                    'id_inasistencia' => $inasistencia->id_inasistencia,
                    'id_resolucion' => $resolucion->id_resolucion,
                    'decision' => $request->input('decision'),
                    'tipo_accion' => $request->input('tipo_accion')
                ],
                'resoluciones_inasistencias',
                $resolucion->id_resolucion
            );

            return response()->json([
                'success' => true,
                'message' => 'Inasistencia resuelta exitosamente',
                'data' => [
                    'inasistencia' => $inasistencia,
                    'resolucion' => $resolucion
                ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error revisando inasistencia: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al resolver inasistencia',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * CU15: Descargar justificativo
     */
    public function descargarJustificativo($id)
    {
        try {
            $justificativo = Justificativo::find($id);

            if (!$justificativo) {
                return response()->json(['message' => 'Justificativo no encontrado'], 404);
            }

            $rutaCompleta = storage_path('app/public/' . $justificativo->archivo_ruta);

            if (!file_exists($rutaCompleta)) {
                return response()->json(['message' => 'Archivo no encontrado en servidor'], 404);
            }

            return response()->download($rutaCompleta, $justificativo->archivo_nombre_original);

        } catch (\Exception $e) {
            \Log::error('Error descargando justificativo: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al descargar archivo',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
