<?php

namespace App\Http\Controllers\Planificación_Académica;

use App\Http\Controllers\Controller;
use App\Models\AsignacionHorario;
use App\Models\Docente;
use App\Models\Bitacora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AsignacionHorarioController extends Controller
{
    /**
     * Listar asignaciones de horario
     */
    public function index(Request $request)
    {
        $asignaciones = AsignacionHorario::with([
            'docente.usuario.persona',
            'grupo.materia',
            'aula.infraestructura',
            'horario'
        ])
        ->when($request->periodo_academico, function ($query, $periodo) {
            $query->porPeriodo($periodo);
        })
        ->when($request->estado, function ($query, $estado) {
            $query->where('estado', $estado);
        })
        ->when($request->codigo_doc, function ($query, $codigoDoc) {
            $query->where('codigo_doc', $codigoDoc);
        })
        ->orderBy('periodo_academico', 'desc')
        ->paginate($request->per_page ?? 15);

        return response()->json($asignaciones);
    }

    /**
     * Crear asignación de horario
     */
    public function store(Request $request)
    {
        $request->validate([
            'periodo_academico' => 'required|string|max:20',
            'codigo_doc' => 'required|exists:docente,codigo_doc',
            'codigo_grupo' => 'required|exists:grupo,codigo_grupo',
            'nro_aula' => 'required|exists:aula,nro_aula',
            'id_horario' => 'required|exists:horario,id_horario',
            'estado' => 'nullable|string|max:20',
        ]);

        // Verificar conflictos de horario
        $conflictos = $this->verificarConflictos($request);
        
        if (!empty($conflictos)) {
            return response()->json([
                'message' => 'Existen conflictos de horario',
                'conflictos' => $conflictos,
            ], 422);
        }

        // Verificar carga horaria del docente
        $docente = Docente::findOrFail($request->codigo_doc);
        if ($docente->carga_horaria_max) {
            $horasActuales = $this->calcularHorasAsignadas($request->codigo_doc, $request->periodo_academico);
            $horasNuevas = $this->obtenerHorasGrupo($request->codigo_grupo);
            
            if (($horasActuales + $horasNuevas) > $docente->carga_horaria_max) {
                return response()->json([
                    'message' => 'El docente excedería su carga horaria máxima',
                    'carga_max' => $docente->carga_horaria_max,
                    'horas_actuales' => $horasActuales,
                    'horas_nuevas' => $horasNuevas,
                ], 422);
            }
        }

        $asignacion = AsignacionHorario::create($request->all());

        Bitacora::registrar('Planificación Académica', "Asignación de horario creada: ID {$asignacion->id_asignacion}");

        return response()->json([
            'message' => 'Asignación creada exitosamente',
            'asignacion' => $asignacion->load(['docente.usuario.persona', 'grupo.materia', 'aula', 'horario']),
        ], 201);
    }

    /**
     * Mostrar asignación
     */
    public function show($id)
    {
        $asignacion = AsignacionHorario::with([
            'docente.usuario.persona',
            'grupo.materia',
            'aula.infraestructura',
            'horario',
            'asistencias'
        ])->findOrFail($id);

        return response()->json($asignacion);
    }

    /**
     * Actualizar asignación
     */
    public function update(Request $request, $id)
    {
        $asignacion = AsignacionHorario::findOrFail($id);

        $request->validate([
            'periodo_academico' => 'sometimes|string|max:20',
            'codigo_doc' => 'sometimes|exists:docente,codigo_doc',
            'codigo_grupo' => 'sometimes|exists:grupo,codigo_grupo',
            'nro_aula' => 'sometimes|exists:aula,nro_aula',
            'id_horario' => 'sometimes|exists:horario,id_horario',
            'estado' => 'sometimes|string|max:20',
        ]);

        $asignacion->update($request->all());

        Bitacora::registrar('Planificación Académica', "Asignación actualizada: ID {$asignacion->id_asignacion}");

        return response()->json([
            'message' => 'Asignación actualizada exitosamente',
            'asignacion' => $asignacion->load(['docente.usuario.persona', 'grupo', 'aula', 'horario']),
        ]);
    }

    /**
     * Eliminar asignación
     */
    public function destroy($id)
    {
        $asignacion = AsignacionHorario::findOrFail($id);
        $asignacion->delete();

        Bitacora::registrar('Planificación Académica', "Asignación eliminada: ID {$id}");

        return response()->json([
            'message' => 'Asignación eliminada exitosamente',
        ]);
    }

    /**
     * Verificar conflictos de horario
     */
    private function verificarConflictos($request)
    {
        $conflictos = [];

        // Conflicto de docente
        $docenteOcupado = AsignacionHorario::where('codigo_doc', $request->codigo_doc)
            ->where('id_horario', $request->id_horario)
            ->where('periodo_academico', $request->periodo_academico)
            ->where('estado', 'ACTIVO')
            ->exists();

        if ($docenteOcupado) {
            $conflictos[] = 'El docente ya tiene una asignación en ese horario';
        }

        // Conflicto de grupo
        $grupoOcupado = AsignacionHorario::where('codigo_grupo', $request->codigo_grupo)
            ->where('id_horario', $request->id_horario)
            ->where('periodo_academico', $request->periodo_academico)
            ->where('estado', 'ACTIVO')
            ->exists();

        if ($grupoOcupado) {
            $conflictos[] = 'El grupo ya tiene una asignación en ese horario';
        }

        // Conflicto de aula
        $aulaOcupada = AsignacionHorario::where('nro_aula', $request->nro_aula)
            ->where('id_horario', $request->id_horario)
            ->where('periodo_academico', $request->periodo_academico)
            ->where('estado', 'ACTIVO')
            ->exists();

        if ($aulaOcupada) {
            $conflictos[] = 'El aula ya está ocupada en ese horario';
        }

        return $conflictos;
    }

    /**
     * Calcular horas asignadas al docente
     */
    private function calcularHorasAsignadas($codigoDoc, $periodo)
    {
        return AsignacionHorario::where('codigo_doc', $codigoDoc)
            ->where('periodo_academico', $periodo)
            ->where('estado', 'ACTIVO')
            ->with('grupo.materia')
            ->get()
            ->sum(function ($asignacion) {
                return $asignacion->grupo->materia->horas_semanales ?? 0;
            });
    }

    /**
     * Obtener horas semanales del grupo
     */
    private function obtenerHorasGrupo($codigoGrupo)
    {
        $grupo = \App\Models\Grupo::with('materia')->find($codigoGrupo);
        return $grupo->materia->horas_semanales ?? 0;
    }

    /**
     * Obtener horario de un docente
     */
    public function horarioDocente($codigoDoc, Request $request)
    {
        $periodo = $request->periodo_academico ?? now()->format('Y-1');

        $asignaciones = AsignacionHorario::with(['grupo.materia', 'aula', 'horario'])
            ->where('codigo_doc', $codigoDoc)
            ->where('periodo_academico', $periodo)
            ->activas()
            ->get();

        return response()->json([
            'periodo' => $periodo,
            'asignaciones' => $asignaciones,
        ]);
    }

    /**
     * Obtener horario de un grupo
     */
    public function horarioGrupo($codigoGrupo, Request $request)
    {
        $periodo = $request->periodo_academico ?? now()->format('Y-1');

        $asignaciones = AsignacionHorario::with(['docente.usuario.persona', 'aula', 'horario'])
            ->where('codigo_grupo', $codigoGrupo)
            ->where('periodo_academico', $periodo)
            ->activas()
            ->get();

        return response()->json([
            'periodo' => $periodo,
            'asignaciones' => $asignaciones,
        ]);
    }
}
