<?php

namespace App\Http\Controllers\Gestión_de_Catálogos_Académicos;

use App\Http\Controllers\Controller;
use App\Models\Infraestructura;
use App\Models\Bitacora;
use Illuminate\Http\Request;

class InfraestructuraController extends Controller
{
    /**
     * CU9: Gestionar Infraestructura - Listar
     */
    public function index(Request $request)
    {
        try {
            $infraestructuras = Infraestructura::with('aulas')
                ->when($request->search, function ($query, $search) {
                    $query->where('nombre_infr', 'ILIKE', "%{$search}%")
                          ->orWhere('ubicacion', 'ILIKE', "%{$search}%");
                })
                ->when($request->estado, function ($query, $estado) {
                    $query->where('estado', $estado);
                })
                ->paginate($request->per_page ?? 15);

            return response()->json($infraestructuras);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al cargar infraestructuras',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * CU9: Gestionar Infraestructura - Crear
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre_infr' => 'required|string|max:100',
            'ubicacion' => 'nullable|string|max:150',
            'estado' => 'nullable|string|max:30',
        ]);

        try {
            $infraestructura = Infraestructura::create([
                'nombre_infr' => $request->nombre_infr,
                'ubicacion' => $request->ubicacion,
                'estado' => $request->estado,
            ]);

            Bitacora::registrar('Gestión de Infraestructura', "Infraestructura creada: {$infraestructura->nombre_infr}");

            return response()->json([
                'message' => 'Infraestructura creada exitosamente',
                'infraestructura' => $infraestructura,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al crear infraestructura',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * CU9: Gestionar Infraestructura - Mostrar
     */
    public function show($id)
    {
        $infraestructura = Infraestructura::with('aulas.asignaciones')
            ->findOrFail($id);

        return response()->json($infraestructura);
    }

    /**
     * CU9: Gestionar Infraestructura - Actualizar
     */
    public function update(Request $request, $id)
    {
        $infraestructura = Infraestructura::findOrFail($id);

        $request->validate([
            'nombre_infr' => 'sometimes|string|max:100',
            'ubicacion' => 'nullable|string|max:150',
            'estado' => 'nullable|string|max:30',
        ]);

        try {
            $infraestructura->update([
                'nombre_infr' => $request->nombre_infr ?? $infraestructura->nombre_infr,
                'ubicacion' => $request->ubicacion,
                'estado' => $request->estado,
            ]);

            Bitacora::registrar('Gestión de Infraestructura', "Infraestructura actualizada: {$infraestructura->nombre_infr}");

            return response()->json([
                'message' => 'Infraestructura actualizada exitosamente',
                'infraestructura' => $infraestructura,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar infraestructura',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * CU9: Gestionar Infraestructura - Eliminar
     */
    public function destroy($id)
    {
        $infraestructura = Infraestructura::findOrFail($id);
        $nombreInfraestructura = $infraestructura->nombre_infr;

        // Verificar si tiene aulas
        if ($infraestructura->aulas()->count() > 0) {
            return response()->json([
                'message' => 'No se puede eliminar la infraestructura porque tiene aulas asociadas',
            ], 422);
        }

        $infraestructura->delete();

        Bitacora::registrar('Gestión de Infraestructura', "Infraestructura eliminada: {$nombreInfraestructura}");

        return response()->json([
            'message' => 'Infraestructura eliminada exitosamente',
        ]);
    }
}
