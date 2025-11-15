<?php

namespace App\Http\Controllers\Gestión_de_Catálogos_Académicos;

use App\Http\Controllers\Controller;
use App\Models\Grupo;
use App\Models\Bitacora;
use Illuminate\Http\Request;

class GrupoController extends Controller
{
    /**
     * CU7: Gestionar Grupos - Listar
     */
    public function index(Request $request)
    {
        // Log para debug: quién hace la petición a /grupos
        try {
            $usuario = auth('sanctum')->user();
            \Log::info('API /grupos called', [
                'ci' => $usuario ? $usuario->ci_persona : null,
                'rol' => $usuario && $usuario->rol ? $usuario->rol->nombre : null,
            ]);
        } catch (\Exception $e) {
            \Log::warning('No se pudo obtener usuario en /grupos: ' . $e->getMessage());
        }

        // Sin filtro por rol, devolver todos los grupos para cualquier usuario autenticado
        $grupos = Grupo::with(['materia', 'asignaciones'])
            ->when($request->search, function ($query, $search) {
                $query->where('codigo_grupo', 'ILIKE', "%{$search}%");
            })
            ->when($request->codigo_mat, function ($query, $codigoMat) {
                $query->where('codigo_mat', $codigoMat);
            })
            ->paginate($request->per_page ?? 1000); // Aumentar el límite para combos

        // Transformar para asegurar que materia está siempre disponible
        $grupos->getCollection()->transform(function ($grupo) {
            return [
                'codigo_grupo' => $grupo->codigo_grupo,
                'codigo_mat' => $grupo->codigo_mat,
                'capacidad_de_grupo' => $grupo->capacidad_de_grupo,
                'materia' => $grupo->materia ? [
                    'codigo_mat' => $grupo->materia->codigo_mat,
                    'nombre_mat' => $grupo->materia->nombre_mat,
                ] : null,
                'asignaciones' => $grupo->asignaciones,
            ];
        });

        return response()->json($grupos);
    }

    /**
     * CU7: Gestionar Grupos - Crear
     */
    public function store(Request $request)
    {
        $request->validate([
            'codigo_grupo' => 'required|string|max:20|unique:grupo,codigo_grupo',
            'capacidad_de_grupo' => 'nullable|integer|min:1',
            'codigo_mat' => 'nullable|exists:materia,codigo_mat',
        ]);

        $grupo = Grupo::create($request->all());

        Bitacora::registrar('Gestión de Grupos', "Grupo creado: {$grupo->codigo_grupo}");

        return response()->json([
            'message' => 'Grupo creado exitosamente',
            'grupo' => $grupo->load('materia'),
        ], 201);
    }

    /**
     * CU7: Gestionar Grupos - Mostrar
     */
    public function show($codigo)
    {
        $grupo = Grupo::with([
            'materia',
            'materias',
            'asignaciones.docente.usuario.persona',
            'asignaciones.horario',
            'asignaciones.aula'
        ])->findOrFail($codigo);

        return response()->json($grupo);
    }

    /**
     * CU7: Gestionar Grupos - Actualizar
     */
    public function update(Request $request, $codigo)
    {
        $grupo = Grupo::findOrFail($codigo);

        $request->validate([
            'capacidad_de_grupo' => 'nullable|integer|min:1',
            'codigo_mat' => 'nullable|exists:materia,codigo_mat',
        ]);

        $grupo->update($request->all());

        Bitacora::registrar('Gestión de Grupos', "Grupo actualizado: {$grupo->codigo_grupo}");

        return response()->json([
            'message' => 'Grupo actualizado exitosamente',
            'grupo' => $grupo->load('materia'),
        ]);
    }

    /**
     * CU7: Gestionar Grupos - Eliminar
     */
    public function destroy($codigo)
    {
        $grupo = Grupo::findOrFail($codigo);
        $codigoGrupo = $grupo->codigo_grupo;

        // Verificar si tiene asignaciones activas
        if ($grupo->asignaciones()->where('estado', 'ACTIVO')->count() > 0) {
            return response()->json([
                'message' => 'No se puede eliminar el grupo porque tiene asignaciones activas',
            ], 422);
        }

        $grupo->delete();

        Bitacora::registrar('Gestión de Grupos', "Grupo eliminado: {$codigoGrupo}");

        return response()->json([
            'message' => 'Grupo eliminado exitosamente',
        ]);
    }

    /**
     * Asignar materias al grupo
     */
    public function asignarMaterias(Request $request, $codigo)
    {
        $grupo = Grupo::findOrFail($codigo);

        $request->validate([
            'materias' => 'required|array',
            'materias.*' => 'exists:materia,codigo_mat',
        ]);

        $grupo->materias()->sync($request->materias);

        Bitacora::registrar('Gestión de Grupos', "Materias asignadas al grupo: {$grupo->codigo_grupo}");

        return response()->json([
            'message' => 'Materias asignadas exitosamente',
            'grupo' => $grupo->load('materias'),
        ]);
    }
}
