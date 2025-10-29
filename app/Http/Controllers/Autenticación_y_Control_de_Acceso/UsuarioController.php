<?php

namespace App\Http\Controllers\Autenticación_y_Control_de_Acceso;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Models\Persona;
use App\Models\Bitacora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UsuarioController extends Controller
{
    /**
     * CU3: Gestionar Usuarios - Listar
     */
    public function index(Request $request)
    {
        $usuarios = Usuario::with(['persona', 'rol'])
            ->when($request->search, function ($query, $search) {
                $query->whereHas('persona', function ($q) use ($search) {
                    $q->where('nombre', 'ILIKE', "%{$search}%")
                      ->orWhere('ci', 'ILIKE', "%{$search}%");
                });
            })
            ->when($request->estado !== null, function ($query) use ($request) {
                $query->where('estado', $request->estado);
            })
            ->paginate($request->per_page ?? 15);

        return response()->json($usuarios);
    }

    /**
     * CU3: Gestionar Usuarios - Crear
     */
    public function store(Request $request)
    {
        $request->validate([
            'ci' => 'required|string|max:20|unique:persona,ci',
            'nombre' => 'required|string|max:100',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'direccion' => 'nullable|string|max:150',
            'contrasena' => 'required|string|min:8',
            'id_rol' => 'nullable|exists:rol,id_rol',
        ]);

        DB::beginTransaction();
        try {
            // Crear persona
            $persona = Persona::create([
                'ci' => $request->ci,
                'nombre' => $request->nombre,
                'telefono' => $request->telefono,
                'email' => $request->email,
                'direccion' => $request->direccion,
            ]);

            // Crear usuario
            $usuario = Usuario::create([
                'contrasena' => Hash::make($request->contrasena),
                'estado' => true,
                'ci_persona' => $persona->ci,
                'id_rol' => $request->id_rol,
            ]);

            // Registrar en bitácora
            Bitacora::registrar('Gestión de Usuarios', "Usuario creado: {$persona->nombre}");

            DB::commit();

            return response()->json([
                'message' => 'Usuario creado exitosamente',
                'usuario' => $usuario->load(['persona', 'rol']),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al crear usuario',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * CU3: Gestionar Usuarios - Mostrar
     */
    public function show($id)
    {
        $usuario = Usuario::with(['persona', 'rol.permisos', 'docente'])
            ->findOrFail($id);

        return response()->json($usuario);
    }

    /**
     * CU3: Gestionar Usuarios - Actualizar
     */
    public function update(Request $request, $id)
    {
        $usuario = Usuario::findOrFail($id);

        $request->validate([
            'nombre' => 'sometimes|string|max:100',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'direccion' => 'nullable|string|max:150',
            'id_rol' => 'nullable|exists:rol,id_rol',
            'estado' => 'sometimes|boolean',
        ]);

        DB::beginTransaction();
        try {
            // Actualizar persona
            if ($request->hasAny(['nombre', 'telefono', 'email', 'direccion'])) {
                $usuario->persona->update($request->only(['nombre', 'telefono', 'email', 'direccion']));
            }

            // Actualizar usuario
            $usuario->update($request->only(['id_rol', 'estado']));

            // Registrar en bitácora
            Bitacora::registrar('Gestión de Usuarios', "Usuario actualizado: {$usuario->persona->nombre}");

            DB::commit();

            return response()->json([
                'message' => 'Usuario actualizado exitosamente',
                'usuario' => $usuario->load(['persona', 'rol']),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al actualizar usuario',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * CU3: Gestionar Usuarios - Eliminar
     */
    public function destroy($id)
    {
        $usuario = Usuario::findOrFail($id);
        $nombrePersona = $usuario->persona->nombre;

        DB::beginTransaction();
        try {
            // Al eliminar el usuario, se elimina en cascada la persona
            $usuario->delete();

            // Registrar en bitácora
            Bitacora::registrar('Gestión de Usuarios', "Usuario eliminado: {$nombrePersona}");

            DB::commit();

            return response()->json([
                'message' => 'Usuario eliminado exitosamente',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al eliminar usuario',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Activar/Desactivar usuario
     */
    public function toggleEstado($id)
    {
        $usuario = Usuario::findOrFail($id);
        $usuario->update(['estado' => !$usuario->estado]);

        $accion = $usuario->estado ? 'activado' : 'desactivado';
        Bitacora::registrar('Gestión de Usuarios', "Usuario {$accion}: {$usuario->persona->nombre}");

        return response()->json([
            'message' => "Usuario {$accion} exitosamente",
            'usuario' => $usuario,
        ]);
    }
}
