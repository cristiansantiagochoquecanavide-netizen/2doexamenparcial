<?php

namespace App\Http\Controllers\Gestión_de_Catálogos_Académicos;

use App\Http\Controllers\Controller;
use App\Models\Materia;
use App\Models\Bitacora;
use Illuminate\Http\Request;

class MateriaController extends Controller
{
    /**
     * CU6: Gestionar Materias - Listar
     */
    public function index(Request $request)
    {
        $materias = Materia::with('grupos')
            ->when($request->search, function ($query, $search) {
                $query->where('nombre_mat', 'ILIKE', "%{$search}%")
                      ->orWhere('codigo_mat', 'ILIKE', "%{$search}%");
            })
            ->when($request->nivel, function ($query, $nivel) {
                $query->where('nivel', $nivel);
            })
            ->when($request->tipo, function ($query, $tipo) {
                $query->where('tipo', 'ILIKE', "%{$tipo}%");
            })
            ->orderBy('nivel')
            ->orderBy('nombre_mat')
            ->paginate($request->per_page ?? 15);

        return response()->json($materias);
    }

    /**
     * CU6: Gestionar Materias - Crear
     */
    public function store(Request $request)
    {
        $request->validate([
            'codigo_mat' => 'required|string|max:20|unique:materia,codigo_mat',
            'nombre_mat' => 'required|string|max:100',
            'nivel' => 'nullable|integer',
            'horas_semanales' => 'nullable|integer|min:1',
            'tipo' => 'nullable|string|max:40',
        ]);

        $materia = Materia::create($request->all());

        Bitacora::registrar('Gestión de Materias', "Materia creada: {$materia->nombre_mat}");

        return response()->json([
            'message' => 'Materia creada exitosamente',
            'materia' => $materia,
        ], 201);
    }

    /**
     * CU6: Gestionar Materias - Mostrar
     */
    public function show($codigo)
    {
        $materia = Materia::with(['grupos.asignaciones.docente.usuario.persona'])
            ->findOrFail($codigo);

        return response()->json($materia);
    }

    /**
     * CU6: Gestionar Materias - Actualizar
     */
    public function update(Request $request, $codigo)
    {
        $materia = Materia::findOrFail($codigo);

        $request->validate([
            'nombre_mat' => 'sometimes|string|max:100',
            'nivel' => 'nullable|integer',
            'horas_semanales' => 'nullable|integer|min:1',
            'tipo' => 'nullable|string|max:40',
        ]);

        $materia->update($request->all());

        Bitacora::registrar('Gestión de Materias', "Materia actualizada: {$materia->nombre_mat}");

        return response()->json([
            'message' => 'Materia actualizada exitosamente',
            'materia' => $materia,
        ]);
    }

    /**
     * CU6: Gestionar Materias - Eliminar
     */
    public function destroy($codigo)
    {
        $materia = Materia::findOrFail($codigo);
        $nombreMateria = $materia->nombre_mat;

        // Verificar si tiene grupos
        if ($materia->grupos()->count() > 0) {
            return response()->json([
                'message' => 'No se puede eliminar la materia porque tiene grupos asignados',
            ], 422);
        }

        $materia->delete();

        Bitacora::registrar('Gestión de Materias', "Materia eliminada: {$nombreMateria}");

        return response()->json([
            'message' => 'Materia eliminada exitosamente',
        ]);
    }

    /**
     * Listar materias por nivel
     */
    public function porNivel($nivel)
    {
        $materias = Materia::where('nivel', $nivel)
            ->orderBy('nombre_mat')
            ->get();

        return response()->json($materias);
    }
}
