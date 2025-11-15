<?php

namespace App\Http\Controllers\Planificación_Académica;

use App\Http\Controllers\Controller;
use App\Models\Horario;
use App\Models\Bitacora;
use Illuminate\Http\Request;

class HorarioController extends Controller
{
    /**
     * CU10: Configurar malla horaria - Listar
     */
    public function index(Request $request)
    {
        // Log para debug: quién hace la petición a /horarios
        try {
            $usuario = auth('sanctum')->user();
            \Log::info('API /horarios called', [
                'ci' => $usuario ? $usuario->ci_persona : null,
                'rol' => $usuario && $usuario->rol ? $usuario->rol->nombre : null,
            ]);
        } catch (\Exception $e) {
            \Log::warning('No se pudo obtener usuario en /horarios: ' . $e->getMessage());
        }

        // Sin filtro por rol, devolver todos los horarios para cualquier usuario autenticado
        $horarios = Horario::query()
            ->when($request->dias_semana, function ($query, $dia) {
                $query->porDia($dia);
            })
            ->when($request->turno, function ($query, $turno) {
                $query->porTurno($turno);
            })
            ->orderBy('dias_semana')
            ->orderBy('hora_inicio')
            ->paginate($request->per_page ?? 1000); // Aumentar el límite para combos

        return response()->json($horarios);
    }

    /**
     * CU10: Configurar malla horaria - Crear
     */
    public function store(Request $request)
    {
        $request->validate([
            'dias_semana' => 'required|string|max:20',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
            'turno' => 'nullable|string|max:20',
        ]);

        $horario = Horario::create($request->all());

        Bitacora::registrar('Planificación Académica', "Horario creado: {$horario->dias_semana} {$horario->hora_inicio}-{$horario->hora_fin}");

        return response()->json([
            'message' => 'Horario creado exitosamente',
            'horario' => $horario,
        ], 201);
    }

    /**
     * CU10: Configurar malla horaria - Mostrar
     */
    public function show($id)
    {
        $horario = Horario::with('asignaciones.docente.usuario.persona', 'asignaciones.grupo', 'asignaciones.aula')
            ->findOrFail($id);

        return response()->json($horario);
    }

    /**
     * CU10: Configurar malla horaria - Actualizar
     */
    public function update(Request $request, $id)
    {
        $horario = Horario::findOrFail($id);

        $request->validate([
            'dias_semana' => 'sometimes|string|max:20',
            'hora_inicio' => 'sometimes|date_format:H:i',
            'hora_fin' => 'sometimes|date_format:H:i|after:hora_inicio',
            'turno' => 'nullable|string|max:20',
        ]);

        $horario->update($request->all());

        Bitacora::registrar('Planificación Académica', "Horario actualizado: ID {$horario->id_horario}");

        return response()->json([
            'message' => 'Horario actualizado exitosamente',
            'horario' => $horario,
        ]);
    }

    /**
     * CU10: Configurar malla horaria - Eliminar
     */
    public function destroy($id)
    {
        $horario = Horario::findOrFail($id);

        // Verificar si tiene asignaciones
        if ($horario->asignaciones()->count() > 0) {
            return response()->json([
                'message' => 'No se puede eliminar el horario porque tiene asignaciones',
            ], 422);
        }

        $horario->delete();

        Bitacora::registrar('Planificación Académica', "Horario eliminado: ID {$id}");

        return response()->json([
            'message' => 'Horario eliminado exitosamente',
        ]);
    }

    /**
     * Obtener horarios por día
     */
    public function porDia($dia)
    {
        $horarios = Horario::porDia($dia)
            ->orderBy('hora_inicio')
            ->get();

        return response()->json($horarios);
    }

    /**
     * Obtener horarios por turno
     */
    public function porTurno($turno)
    {
        $horarios = Horario::porTurno($turno)
            ->orderBy('dias_semana')
            ->orderBy('hora_inicio')
            ->get();

        return response()->json($horarios);
    }
}
