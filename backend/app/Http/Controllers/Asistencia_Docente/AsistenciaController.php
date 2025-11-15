<?php

namespace App\Http\Controllers\Asistencia_Docente;

use App\Http\Controllers\Controller;
use App\Models\Asistencia;
use App\Models\AsignacionHorario;
use App\Models\Bitacora;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AsistenciaController extends Controller
{
    /**
     * Listar asistencias
     */
    public function index(Request $request)
    {
        $asistencias = Asistencia::with([
            'asignacion.docente.usuario.persona',
            'asignacion.grupo.materia',
            'asignacion.horario'
        ])
        ->when($request->fecha, function ($query, $fecha) {
            $query->porFecha($fecha);
        })
        ->when($request->fecha_inicio && $request->fecha_fin, function ($query) use ($request) {
            $query->porRangoFechas($request->fecha_inicio, $request->fecha_fin);
        })
        ->when($request->estado, function ($query, $estado) {
            $query->porEstado($estado);
        })
        ->when($request->id_asignacion, function ($query, $id) {
            $query->where('id_asignacion', $id);
        })
        ->orderBy('fecha', 'desc')
        ->orderBy('hora_de_registro', 'desc')
        ->paginate($request->per_page ?? 15);

        return response()->json($asistencias);
    }

    /**
     * Registrar asistencia
     */
    public function store(Request $request)
    {
        $request->validate([
            'fecha' => 'required|date',
            'tipo_registro' => 'nullable|string|max:20',
            'estado' => 'nullable|string|max:20',
            'id_asignacion' => 'required|exists:asignacion_horario,id_asignacion',
        ]);

        // Verificar si ya existe una asistencia para esa fecha y asignación
        $existente = Asistencia::where('id_asignacion', $request->id_asignacion)
            ->whereDate('fecha', $request->fecha)
            ->first();

        if ($existente) {
            return response()->json([
                'message' => 'Ya existe un registro de asistencia para esta fecha y asignación',
                'asistencia' => $existente,
            ], 422);
        }

        $asistencia = Asistencia::create([
            'fecha' => $request->fecha,
            'hora_de_registro' => now()->format('H:i'),
            'tipo_registro' => $request->tipo_registro ?? 'MANUAL',
            'estado' => $request->estado ?? 'PRESENTE',
            'id_asignacion' => $request->id_asignacion,
        ]);

        $asignacion = AsignacionHorario::with('docente.usuario.persona')->find($request->id_asignacion);
        Bitacora::registrar(
            'Asistencia_Docente',
            'REGISTRAR_ASISTENCIA',
            auth('sanctum')->id(),
            ['docente' => $asignacion->docente->persona->nombre],
            'asistencias',
            $asistencia->id_asistencias
        );

        return response()->json([
            'message' => 'Asistencia registrada exitosamente',
            'asistencia' => $asistencia->load('asignacion'),
        ], 201);
    }

    /**
     * Mostrar asistencia
     */
    public function show($id)
    {
        $asistencia = Asistencia::with([
            'asignacion.docente.usuario.persona',
            'asignacion.grupo.materia',
            'asignacion.aula',
            'asignacion.horario'
        ])->findOrFail($id);

        return response()->json($asistencia);
    }

    /**
     * Actualizar asistencia
     */
    public function update(Request $request, $id)
    {
        $asistencia = Asistencia::findOrFail($id);

        $request->validate([
            'tipo_registro' => 'nullable|string|max:20',
            'estado' => 'nullable|string|max:20',
        ]);

        $asistencia->update($request->only(['tipo_registro', 'estado']));

        Bitacora::registrar(
            'Asistencia_Docente',
            'ACTUALIZAR_ASISTENCIA',
            auth('sanctum')->id(),
            ['id_asistencias' => $id],
            'asistencias',
            $id
        );

        return response()->json([
            'message' => 'Asistencia actualizada exitosamente',
            'asistencia' => $asistencia->load('asignacion'),
        ]);
    }

    /**
     * Eliminar asistencia
     */
    public function destroy($id)
    {
        $asistencia = Asistencia::findOrFail($id);
        $asistencia->delete();

        Bitacora::registrar(
            'Asistencia_Docente',
            'ELIMINAR_ASISTENCIA',
            auth('sanctum')->id(),
            ['id_asistencias' => $id],
            'asistencias',
            $id
        );

        return response()->json([
            'message' => 'Asistencia eliminada exitosamente',
        ]);
    }

    /**
     * Registrar asistencia del día
     */
    public function registrarHoy(Request $request)
    {
        $request->validate([
            'id_asignacion' => 'required|exists:asignacion_horario,id_asignacion',
            'estado' => 'required|string|max:20',
        ]);

        $hoy = Carbon::today()->format('Y-m-d');

        // Verificar si ya existe
        $existente = Asistencia::where('id_asignacion', $request->id_asignacion)
            ->whereDate('fecha', $hoy)
            ->first();

        if ($existente) {
            return response()->json([
                'message' => 'Ya se registró la asistencia para hoy',
                'asistencia' => $existente,
            ], 422);
        }

        $asistencia = Asistencia::create([
            'fecha' => $hoy,
            'hora_de_registro' => now()->format('H:i'),
            'tipo_registro' => 'AUTOMATICO',
            'estado' => $request->estado,
            'id_asignacion' => $request->id_asignacion,
        ]);

        return response()->json([
            'message' => 'Asistencia registrada exitosamente',
            'asistencia' => $asistencia->load('asignacion'),
        ], 201);
    }

    /**
     * Reporte de asistencias por docente
     */
    public function reporteDocente($codigoDoc, Request $request)
    {
        $fechaInicio = $request->fecha_inicio ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $fechaFin = $request->fecha_fin ?? Carbon::now()->endOfMonth()->format('Y-m-d');

        $asistencias = Asistencia::whereHas('asignacion', function ($query) use ($codigoDoc) {
            $query->where('codigo_doc', $codigoDoc);
        })
        ->with('asignacion.grupo.materia')
        ->porRangoFechas($fechaInicio, $fechaFin)
        ->get();

        $resumen = [
            'total' => $asistencias->count(),
            'presentes' => $asistencias->where('estado', 'PRESENTE')->count(),
            'ausentes' => $asistencias->where('estado', 'AUSENTE')->count(),
            'justificados' => $asistencias->where('estado', 'JUSTIFICADO')->count(),
        ];

        return response()->json([
            'periodo' => [
                'inicio' => $fechaInicio,
                'fin' => $fechaFin,
            ],
            'resumen' => $resumen,
            'asistencias' => $asistencias,
        ]);
    }

    /**
     * Reporte de asistencias por grupo
     */
    public function reporteGrupo($codigoGrupo, Request $request)
    {
        $fechaInicio = $request->fecha_inicio ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $fechaFin = $request->fecha_fin ?? Carbon::now()->endOfMonth()->format('Y-m-d');

        $asistencias = Asistencia::whereHas('asignacion', function ($query) use ($codigoGrupo) {
            $query->where('codigo_grupo', $codigoGrupo);
        })
        ->with('asignacion.docente.usuario.persona')
        ->porRangoFechas($fechaInicio, $fechaFin)
        ->get();

        return response()->json([
            'periodo' => [
                'inicio' => $fechaInicio,
                'fin' => $fechaFin,
            ],
            'asistencias' => $asistencias,
        ]);
    }
}
