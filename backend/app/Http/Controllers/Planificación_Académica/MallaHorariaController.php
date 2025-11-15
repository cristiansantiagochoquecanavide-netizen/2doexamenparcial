<?php

namespace App\Http\Controllers\Planificación_Académica;

use App\Http\Controllers\Controller;
use App\Models\Horario;
use App\Models\Bitacora;
use App\Models\AsignacionHorario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use DateTime;
use DateInterval;

class MallaHorariaController extends Controller
{
    /**
     * CU10: Obtener todas las franjas de una gestión
     * GET /api/mallahorarias?gestion=2025
     */
    public function index(Request $request)
    {
        try {
            $gestion = $request->gestion ?? date('Y');
            
            // Verificar que la tabla exista
            if (!DB::getSchemaBuilder()->hasTable('horario')) {
                return response()->json([
                    'gestion' => $gestion,
                    'franjas_por_turno' => [],
                    'total_franjas' => 0,
                    'data' => [],
                    'message' => 'Tabla de horarios aún no configurada',
                ]);
            }
            
            $franjas = Horario::orderBy('turno')
                ->orderBy('dias_semana')
                ->orderBy('hora_inicio')
                ->get();

            // Agrupar por turno
            $franjasPorTurno = $franjas->groupBy('turno');

            return response()->json([
                'gestion' => $gestion,
                'franjas_por_turno' => $franjasPorTurno,
                'total_franjas' => $franjas->count(),
                'data' => $franjas,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al obtener franjas:', [
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return response()->json([
                'message' => 'Error al obtener franjas',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * CU10: Generar malla horaria (franjas/slots)
     * POST /api/mallahorarias
     * 
     * Solicitud esperada:
     * {
     *   "turnos": [
     *     {"nombre": "Mañana", "hora_inicio": "06:00", "hora_fin": "12:00", "duracion_bloque_minutos": 60},
     *     {"nombre": "Tarde", "hora_inicio": "13:00", "hora_fin": "19:00", "duracion_bloque_minutos": 60},
     *     {"nombre": "Noche", "hora_inicio": "19:00", "hora_fin": "22:00", "duracion_bloque_minutos": 60}
     *   ],
     *   "dias_semana": [1,2,3,4,5]  // 1=Lunes, 7=Domingo
     * }
     */
    public function store(Request $request)
    {
        $request->validate([
            'turnos' => 'required|array|min:1',
            'turnos.*.nombre' => 'required|string',
            'turnos.*.hora_inicio' => 'required|date_format:H:i',
            'turnos.*.hora_fin' => 'required|date_format:H:i',
            'turnos.*.duracion_bloque_minutos' => 'required|integer|min:15|max:480',
            'dias_semana' => 'required|array|min:1',
            'dias_semana.*' => 'integer|min:1|max:7',
        ]);

        DB::beginTransaction();
        try {
            $turnos = $request->turnos;
            $diasSemana = $request->dias_semana;

            // Eliminar horarios existentes
            Horario::truncate();

            $horariosCreados = 0;

            // Generar franjas para cada turno
            foreach ($turnos as $turnoData) {
                $turno = $turnoData['nombre'];
                $horaInicio = new DateTime($turnoData['hora_inicio']);
                $horaFin = new DateTime($turnoData['hora_fin']);
                $duracion = $turnoData['duracion_bloque_minutos'];
                $intervalo = new DateInterval("PT{$duracion}M");

                // Generar franjas dentro del turno
                $actual = clone $horaInicio;
                $nroFranja = 1;
                
                while ($actual < $horaFin) {
                    $siguiente = clone $actual;
                    $siguiente->add($intervalo);

                    // Validar que no sobrepasa la hora fin
                    if ($siguiente > $horaFin) {
                        break;
                    }

                    // Crear horario para cada día de la semana
                    foreach ($diasSemana as $dia) {
                        Horario::create([
                            'dias_semana' => $dia,
                            'hora_inicio' => $actual->format('H:i:s'),
                            'hora_fin' => $siguiente->format('H:i:s'),
                            'turno' => $turno,
                        ]);
                        $horariosCreados++;
                    }

                    $actual = $siguiente;
                    $nroFranja++;
                }
            }

            // Registrar en bitácora
            Bitacora::registrar(
                'Malla Horaria',
                "Malla horaria generada: {$horariosCreados} franjas creadas"
            );

            DB::commit();

            return response()->json([
                'message' => 'Malla horaria generada exitosamente',
                'franjas_creadas' => $horariosCreados,
                'turnos_procesados' => count($turnos),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al generar malla horaria',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * CU10: Validar que las franjas no estén solapadas
     * POST /api/mallahorarias/validar
     */
    public function validarFranjas(Request $request)
    {
        try {
            $horarios = Horario::orderBy('dias_semana')
                ->orderBy('turno')
                ->orderBy('hora_inicio')
                ->get();

            $errores = [];
            $horariosAgrupados = $horarios->groupBy(['dias_semana', 'turno']);

            foreach ($horariosAgrupados as $dia => $turnosPorDia) {
                foreach ($turnosPorDia as $turno => $franjasDelTurno) {
                    // Verificar solapamientos dentro del mismo turno
                    $franjasArr = $franjasDelTurno->sortBy('hora_inicio')->toArray();
                    
                    for ($i = 0; $i < count($franjasArr) - 1; $i++) {
                        $fin = new DateTime($franjasArr[$i]['hora_fin']);
                        $inicioSiguiente = new DateTime($franjasArr[$i + 1]['hora_inicio']);
                        
                        if ($fin > $inicioSiguiente) {
                            $errores[] = "Solapamiento detectado en día {$dia}, turno {$turno}: {$franjasArr[$i]['hora_fin']} vs {$franjasArr[$i + 1]['hora_inicio']}";
                        }
                    }
                }
            }

            return response()->json([
                'valido' => count($errores) === 0,
                'errores' => $errores,
                'total_franjas' => $horarios->count(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al validar franjas',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * CU10: Eliminar malla horaria completa
     * DELETE /api/mallahorarias-eliminar
     */
    public function eliminarMalla(Request $request)
    {
        DB::beginTransaction();
        try {
            // Primero, obtener la cantidad de horarios antes de eliminar
            $cantidadHorarios = Horario::count();
            
            // Eliminar todas las asignaciones horarias que referencias estos horarios
            $cantidadAsignaciones = AsignacionHorario::count();
            AsignacionHorario::truncate();
            
            // Luego, eliminar los horarios
            Horario::truncate();

            // Registrar en bitácora
            Bitacora::registrar(
                'Malla Horaria',
                "Malla horaria eliminada: {$cantidadHorarios} franjas removidas y {$cantidadAsignaciones} asignaciones canceladas"
            );

            DB::commit();

            return response()->json([
                'message' => 'Malla horaria eliminada exitosamente',
                'franjas_eliminadas' => $cantidadHorarios,
                'asignaciones_canceladas' => $cantidadAsignaciones,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al eliminar malla horaria',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
