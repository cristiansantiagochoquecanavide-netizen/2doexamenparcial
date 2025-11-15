<?php

namespace App\Http\Controllers\Autenticación_y_Control_de_Acceso;

use App\Http\Controllers\Controller;
use App\Models\Rol;
use App\Models\Permiso;
use App\Models\Bitacora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RolController extends Controller
{
    /**
     * CU4: Gestionar Roles - Listar
     */
    public function index(Request $request)
    {
        $roles = Rol::with('permisos')
            ->when($request->search, function ($query, $search) {
                $query->where('nombre', 'ILIKE', "%{$search}%");
            })
            ->get();

        return response()->json($roles);
    }

    /**
     * CU4: Gestionar Roles - Crear
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:50|unique:rol,nombre',
            'descripcion' => 'nullable|string|max:200',
            'permisos' => 'nullable|array',
            'permisos.*' => 'exists:permisos,id_permiso',
        ]);

        DB::beginTransaction();
        try {
            $rol = Rol::create([
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
            ]);

            // Asignar permisos
            if ($request->has('permisos')) {
                $rol->permisos()->attach($request->permisos);
            }

            // Registrar en bitácora
            Bitacora::registrar('Gestión de Roles', "Rol creado: {$rol->nombre}");

            DB::commit();

            return response()->json([
                'message' => 'Rol creado exitosamente',
                'rol' => $rol->load('permisos'),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al crear rol',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * CU4: Gestionar Roles - Mostrar
     */
    public function show($id)
    {
        $rol = Rol::with(['permisos', 'usuarios.persona'])
            ->findOrFail($id);

        return response()->json($rol);
    }

    /**
     * CU4: Gestionar Roles - Actualizar
     */
    public function update(Request $request, $id)
    {
        $rol = Rol::findOrFail($id);

        $request->validate([
            'nombre' => 'sometimes|string|max:50|unique:rol,nombre,' . $id . ',id_rol',
            'descripcion' => 'nullable|string|max:200',
            'permisos' => 'nullable|array',
            'permisos.*' => 'exists:permisos,id_permiso',
        ]);

        DB::beginTransaction();
        try {
            $rol->update($request->only(['nombre', 'descripcion']));

            // Actualizar permisos
            if ($request->has('permisos')) {
                $rol->permisos()->sync($request->permisos);
            }

            // Registrar en bitácora
            Bitacora::registrar('Gestión de Roles', "Rol actualizado: {$rol->nombre}");

            DB::commit();

            return response()->json([
                'message' => 'Rol actualizado exitosamente',
                'rol' => $rol->load('permisos'),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al actualizar rol',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * CU4: Gestionar Roles - Eliminar
     */
    public function destroy($id)
    {
        $rol = Rol::findOrFail($id);
        $nombreRol = $rol->nombre;

        // Verificar si hay usuarios con este rol
        if ($rol->usuarios()->count() > 0) {
            return response()->json([
                'message' => 'No se puede eliminar el rol porque tiene usuarios asignados',
            ], 422);
        }

        DB::beginTransaction();
        try {
            $rol->delete();

            // Registrar en bitácora
            Bitacora::registrar('Gestión de Roles', "Rol eliminado: {$nombreRol}");

            DB::commit();

            return response()->json([
                'message' => 'Rol eliminado exitosamente',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al eliminar rol',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Listar todos los permisos disponibles
     */
    public function listarPermisos()
    {
        $permisos = Permiso::all();
        return response()->json($permisos);
    }

    /**
     * Asignar permisos a un rol
     */
    public function asignarPermisos(Request $request, $id)
    {
        $rol = Rol::findOrFail($id);

        $request->validate([
            'permisos' => 'required|array',
            'permisos.*' => 'exists:permisos,id_permiso',
        ]);

        $rol->permisos()->sync($request->permisos);

        Bitacora::registrar('Gestión de Roles', "Permisos actualizados para rol: {$rol->nombre}");

        return response()->json([
            'message' => 'Permisos asignados exitosamente',
            'rol' => $rol->load('permisos'),
        ]);
    }
}
