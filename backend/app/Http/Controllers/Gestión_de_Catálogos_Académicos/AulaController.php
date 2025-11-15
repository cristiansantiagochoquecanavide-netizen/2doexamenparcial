<?php

namespace App\Http\Controllers\Gestión_de_Catálogos_Académicos;

use App\Http\Controllers\Controller;
use App\Models\Aula;
use App\Models\Bitacora;
use Illuminate\Http\Request;

class AulaController extends Controller
{
    /**
     * CU8: Gestionar Aulas - Listar
     */
    public function index(Request $request)
    {
        // Log para debug: quién hace la petición a /aulas
        try {
            $usuario = auth('sanctum')->user();
            \Log::info('API /aulas called', [
                'ci' => $usuario ? $usuario->ci_persona : null,
                'rol' => $usuario && $usuario->rol ? $usuario->rol->nombre : null,
            ]);
        } catch (\Exception $e) {
            \Log::warning('No se pudo obtener usuario en /aulas: ' . $e->getMessage());
        }

        // Sin filtro por rol, devolver todas las aulas para cualquier usuario autenticado
        $aulas = Aula::with('infraestructura')
            ->when($request->search, function ($query, $search) {
                $query->where('nro_aula', 'ILIKE', "%{$search}%");
            })
            ->when($request->tipo, function ($query, $tipo) {
                $query->where('tipo', $tipo);
            })
            ->when($request->estado, function ($query, $estado) {
                $query->where('estado', $estado);
            })
            ->when($request->id_infraestructura, function ($query, $id) {
                $query->where('id_infraestructura', $id);
            })
            ->paginate($request->per_page ?? 1000); // Aumentar el límite para combos

        return response()->json($aulas);
    }

    /**
     * CU8: Gestionar Aulas - Crear
     */
    public function store(Request $request)
    {
        $request->validate([
            'nro_aula' => 'required|string|max:20|unique:aula,nro_aula',
            'tipo' => 'nullable|string|max:40',
            'capacidad' => 'nullable|integer|min:1',
            'estado' => 'nullable|string|max:30',
            'id_infraestructura' => 'required|exists:infraestructura,id_infraestructura',
        ]);

        $aula = Aula::create($request->all());

        Bitacora::registrar('Gestión de Aulas', "Aula creada: {$aula->nro_aula}");

        return response()->json([
            'message' => 'Aula creada exitosamente',
            'aula' => $aula->load('infraestructura'),
        ], 201);
    }

    /**
     * CU8: Gestionar Aulas - Mostrar
     */
    public function show($nroAula)
    {
        $aula = Aula::with([
            'infraestructura',
            'asignaciones.docente.usuario.persona',
            'asignaciones.grupo.materia',
            'asignaciones.horario'
        ])->findOrFail($nroAula);

        return response()->json($aula);
    }

    /**
     * CU8: Gestionar Aulas - Actualizar
     */
    public function update(Request $request, $nroAula)
    {
        $aula = Aula::findOrFail($nroAula);

        $request->validate([
            'tipo' => 'nullable|string|max:40',
            'capacidad' => 'nullable|integer|min:1',
            'estado' => 'nullable|string|max:30',
            'id_infraestructura' => 'sometimes|exists:infraestructura,id_infraestructura',
        ]);

        $aula->update($request->all());

        Bitacora::registrar('Gestión de Aulas', "Aula actualizada: {$aula->nro_aula}");

        return response()->json([
            'message' => 'Aula actualizada exitosamente',
            'aula' => $aula->load('infraestructura'),
        ]);
    }

    /**
     * CU8: Gestionar Aulas - Eliminar
     */
    public function destroy($nroAula)
    {
        $aula = Aula::findOrFail($nroAula);
        $numeroAula = $aula->nro_aula;

        // Verificar si tiene asignaciones activas
        if ($aula->asignaciones()->where('estado', 'ACTIVO')->count() > 0) {
            return response()->json([
                'message' => 'No se puede eliminar el aula porque tiene asignaciones activas',
            ], 422);
        }

        $aula->delete();

        Bitacora::registrar('Gestión de Aulas', "Aula eliminada: {$numeroAula}");

        return response()->json([
            'message' => 'Aula eliminada exitosamente',
        ]);
    }

    /**
     * Verificar disponibilidad del aula
     */
    public function verificarDisponibilidad(Request $request, $nroAula)
    {
        $aula = Aula::findOrFail($nroAula);

        $request->validate([
            'id_horario' => 'required|exists:horario,id_horario',
            'periodo_academico' => 'required|string',
        ]);

        $ocupada = $aula->asignaciones()
            ->where('id_horario', $request->id_horario)
            ->where('periodo_academico', $request->periodo_academico)
            ->where('estado', 'ACTIVO')
            ->exists();

        return response()->json([
            'aula' => $aula,
            'disponible' => !$ocupada,
            'mensaje' => $ocupada ? 'El aula está ocupada en ese horario' : 'El aula está disponible',
        ]);
    }
}
