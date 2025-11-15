<?php

namespace App\Http\Controllers\Asistencia_Docente;

use App\Http\Controllers\Controller;
use App\Models\Asistencia;
use App\Models\AsignacionHorario;
use App\Models\SesionAsistencia;
use App\Models\Bitacora;
use App\Services\QRCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

class RegistroAsistenciaController extends Controller
{
    /**
     * CU14: Generar sesión de asistencia con QR y enlace
     * 
     * Genera un token, enlace y código QR para registrar asistencia
     */
    public function generarSesion(Request $request)
    {
        try {
            $request->validate([
                'id_asignacion' => 'required|integer|exists:asignacion_horario,id_asignacion',
                'duracion_minutos' => 'nullable|integer|min:5|max:480', // 5 min a 8 horas
            ]);

            $idAsignacion = $request->input('id_asignacion');
            $duracionMinutos = $request->input('duracion_minutos', 60); // 1 hora por defecto

            // Verificar que la asignación existe
            $asignacion = AsignacionHorario::find($idAsignacion);
            if (!$asignacion) {
                return response()->json([
                    'message' => 'Asignación no encontrada'
                ], 404);
            }

            // Generar token único
            $token = Str::random(32);

            // Crear URL de registro (frontend URL, no API)
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');
            $urlRegistro = $frontendUrl . '/asistencia/registro/' . $token;

            // Generar código QR con la URL
            try {
                $qrBase64 = QRCodeService::generarQR($urlRegistro);
                if (empty($qrBase64)) {
                    throw new \Exception('El servicio QR devolvió un valor vacío');
                }
            } catch (\Exception $e) {
                \Log::error('Error al generar QR: ' . $e->getMessage());
                return response()->json([
                    'message' => 'Error al generar el código QR: ' . $e->getMessage()
                ], 500);
            }

            // Crear sesión de asistencia
            $sesion = SesionAsistencia::create([
                'token' => $token,
                'id_asignacion' => $idAsignacion,
                'fecha_creacion' => Carbon::now(),
                'fecha_expiracion' => Carbon::now()->addMinutes($duracionMinutos),
                'estado' => 'ACTIVA',
                'url_registro' => $urlRegistro,
                'qr_data' => $qrBase64
            ]);

            // Registrar en bitácora
            Bitacora::registrar(
                'Asistencia_Docente',
                'GENERAR_SESION_ASISTENCIA',
                auth('sanctum')->id(),
                [
                    'id_sesion' => $sesion->id_sesion,
                    'id_asignacion' => $idAsignacion,
                    'token' => $token
                ],
                'sesiones_asistencia',
                $sesion->id_sesion
            );

            return response()->json([
                'success' => true,
                'message' => 'Sesión de asistencia generada exitosamente',
                'data' => [
                    'id_sesion' => $sesion->id_sesion,
                    'token' => $token,
                    'url_registro' => $urlRegistro,
                    'qr_base64' => $qrBase64,
                    'fecha_expiracion' => $sesion->fecha_expiracion,
                    'duracion_minutos' => $duracionMinutos
                ]
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Error generando sesión de asistencia: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al generar la sesión de asistencia',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * CU14: Mostrar formulario de registro de asistencia
     * 
     * Muestra la vista con el formulario para registrar asistencia
     */
    public function mostrarFormulario($token)
    {
        try {
            // Buscar la sesión por token
            $sesion = SesionAsistencia::where('token', $token)->first();

            if (!$sesion) {
                return response()->json([
                    'message' => 'Sesión no encontrada'
                ], 404);
            }

            // Verificar si la sesión es válida
            if (!$sesion->esValida()) {
                return response()->json([
                    'message' => 'La sesión de asistencia ha expirado o está cerrada',
                    'estado' => $sesion->estado,
                    'expiracion' => $sesion->fecha_expiracion
                ], 410); // 410 Gone
            }

            // Obtener datos de la asignación
            $asignacion = $sesion->asignacion()->with([
                'docente.usuario.persona',
                'grupo.materia',
                'horario'
            ])->first();

            // Determinar estado de asistencia según tiempo transcurrido
            $validacionTiempo = $sesion->determinarEstadoAsistencia();
            $minutosTranscurridos = $validacionTiempo['minutos'];
            $estadoTiempo = $validacionTiempo['estado'];
            $permitido = $validacionTiempo['permitido'];

            return response()->json([
                'success' => true,
                'data' => [
                    'sesion' => [
                        'id_sesion' => $sesion->id_sesion,
                        'token' => $token,
                        'fecha_expiracion' => $sesion->fecha_expiracion,
                        'tiempo_restante' => $sesion->fecha_expiracion->diffInMinutes(Carbon::now())
                    ],
                    'tiempo' => [
                        'minutos_transcurridos' => $minutosTranscurridos,
                        'estado_temporal' => $estadoTiempo,
                        'permitido' => $permitido,
                        'mensaje' => $validacionTiempo['mensaje'],
                        'rango' => $minutosTranscurridos <= 15 ? '0-15 min (Presente)' : 
                                  ($minutosTranscurridos <= 25 ? '16-25 min (Retraso)' : 
                                  'Más de 25 min (Falta)')
                    ],
                    'asignacion' => [
                        'id_asignacion' => $asignacion->id_asignacion,
                        'docente' => $asignacion->docente?->usuario?->persona?->nombre,
                        'codigo_docente' => $asignacion->codigo_doc,
                        'materia' => $asignacion->grupo?->materia?->nombre_materia,
                        'grupo' => $asignacion->codigo_grupo,
                        'aula' => $asignacion->nro_aula,
                        'estado' => $asignacion->estado,
                        'hora_inicio' => $asignacion->horario?->hora_inicio,
                        'hora_fin' => $asignacion->horario?->hora_fin
                    ]
                ]
            ], 200);


        } catch (\Exception $e) {
            \Log::error('Error mostrando formulario de asistencia: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al acceder al formulario de asistencia',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * CU14: Registrar asistencia
     * 
     * Valida el token y registra la asistencia con los datos proporcionados
     */
    public function registrar(Request $request)
    {
        try {
            $request->validate([
                'token' => 'required|string|exists:sesiones_asistencia,token',
                'id_asignacion' => 'required|integer|exists:asignacion_horario,id_asignacion',
                'observaciones' => 'nullable|string|max:500',
            ]);

            $token = $request->input('token');
            $idAsignacion = $request->input('id_asignacion');
            $observaciones = $request->input('observaciones');

            // Obtener la sesión
            $sesion = SesionAsistencia::where('token', $token)->first();

            if (!$sesion) {
                return response()->json([
                    'message' => 'Sesión no encontrada'
                ], 404);
            }

            // Verificar que la sesión sea válida
            if (!$sesion->esValida()) {
                return response()->json([
                    'message' => 'La sesión de asistencia ha expirado',
                    'estado' => $sesion->estado
                ], 410);
            }

            // Verificar que la asignación coincida con la sesión
            if ($sesion->id_asignacion != $idAsignacion) {
                return response()->json([
                    'message' => 'La asignación no coincide con la sesión'
                ], 422);
            }

            // Determinar el estado de asistencia según el tiempo transcurrido
            $validacionTiempo = $sesion->determinarEstadoAsistencia();

            // Si han pasado más de 25 minutos, no permitir registro
            if (!$validacionTiempo['permitido']) {
                return response()->json([
                    'message' => $validacionTiempo['mensaje'],
                    'estado' => 'FALTA',
                    'minutos_transcurridos' => $validacionTiempo['minutos']
                ], 422); // Unprocessable Entity - Tiempo expirado
            }

            // Crear el registro de asistencia con el estado calculado
            $asistencia = Asistencia::create([
                'id_asignacion' => $idAsignacion,
                'fecha' => Carbon::now()->toDateString(),
                'hora_de_registro' => Carbon::now()->toTimeString(),
                'tipo_registro' => 'QR',
                'estado' => $validacionTiempo['estado'], // Usar estado calculado
            ]);

            // Actualizar sesión (marcarla como utilizada)
            $sesion->cerrar();

            // Registrar en bitácora
            Bitacora::registrar(
                'Asistencia_Docente',
                'REGISTRAR_ASISTENCIA_QR',
                null, // No hay usuario identificado en la API pública
                [
                    'id_asistencias' => $asistencia->id_asistencias,
                    'id_sesion' => $sesion->id_sesion,
                    'token' => $token,
                    'estado' => $validacionTiempo['estado'],
                    'minutos_transcurridos' => $validacionTiempo['minutos'],
                    'observaciones' => $observaciones
                ],
                'asistencias',
                $asistencia->id_asistencias
            );

            return response()->json([
                'success' => true,
                'message' => 'Asistencia registrada exitosamente',
                'data' => [
                    'id_asistencias' => $asistencia->id_asistencias,
                    'fecha' => $asistencia->fecha,
                    'hora_de_registro' => $asistencia->hora_de_registro,
                    'tipo_registro' => $asistencia->tipo_registro,
                    'estado' => $asistencia->estado
                ]
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Error registrando asistencia: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al registrar la asistencia',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * CU14: Listar sesiones de asistencia activas
     * 
     * Muestra las sesiones activas para una asignación específica
     */
    public function listarSesionesActivas(Request $request)
    {
        try {
            $request->validate([
                'id_asignacion' => 'nullable|integer|exists:asignaciones_horarias,id_asignacion'
            ]);

            $query = SesionAsistencia::activas();

            if ($request->id_asignacion) {
                $query->where('id_asignacion', $request->id_asignacion);
            }

            $sesiones = $query->with('asignacion')
                ->orderBy('fecha_expiracion', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $sesiones
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error listando sesiones: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al listar sesiones',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * CU14: Cerrar sesión de asistencia
     * 
     * Cierra manualmente una sesión activa
     */
    public function cerrarSesion(Request $request)
    {
        try {
            $request->validate([
                'id_sesion' => 'required|integer|exists:sesiones_asistencia,id_sesion'
            ]);

            $sesion = SesionAsistencia::find($request->id_sesion);

            if (!$sesion) {
                return response()->json([
                    'message' => 'Sesión no encontrada'
                ], 404);
            }

            $sesion->cerrar();

            // Registrar en bitácora
            Bitacora::registrar(
                'Asistencia_Docente',
                'CERRAR_SESION_ASISTENCIA',
                auth('sanctum')->id(),
                [
                    'id_sesion' => $sesion->id_sesion,
                    'token' => $sesion->token
                ],
                'sesiones_asistencia',
                $sesion->id_sesion
            );

            return response()->json([
                'success' => true,
                'message' => 'Sesión cerrada exitosamente'
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error cerrando sesión: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al cerrar la sesión',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
