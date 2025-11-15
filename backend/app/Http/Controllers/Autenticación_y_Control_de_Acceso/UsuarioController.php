<?php

namespace App\Http\Controllers\Autenticación_y_Control_de_Acceso;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Models\Persona;
use App\Models\Bitacora;
use App\Models\Docente;
use App\Models\Rol;
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
            'nombre_completo' => 'required|string|max:200',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'direccion' => 'nullable|string|max:150',
            'contrasena' => 'required|string|min:8',
            'id_rol' => 'nullable|exists:rol,id_rol',
        ]);

        DB::beginTransaction();
        try {
            // Crear persona con nombre completo
            $persona = Persona::create([
                'ci' => $request->ci,
                'nombre' => $request->nombre_completo,
                'apellido_paterno' => '',
                'apellido_materno' => '',
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

            // Si el rol es Docente (id_rol = 5), crear registro en tabla docente
            if ($request->id_rol == 5) {
                Docente::create([
                    'id_usuario' => $usuario->id_usuario,
                    'titulo' => 'Docente',
                    'correo_institucional' => $request->email,
                    'carga_horaria_max' => 40,
                ]);
            }

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
            'nombre_completo' => 'sometimes|string|max:200',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'direccion' => 'nullable|string|max:150',
            'id_rol' => 'nullable|exists:rol,id_rol',
            'estado' => 'sometimes|boolean',
        ]);

        DB::beginTransaction();
        try {
            // Actualizar persona con nombre completo
            $personaData = [];
            
            // Siempre actualizar nombre_completo si viene
            if ($request->has('nombre_completo')) {
                $personaData['nombre'] = $request->nombre_completo;
                // Limpiar apellidos cuando se usa nombre completo
                $personaData['apellido_paterno'] = '';
                $personaData['apellido_materno'] = '';
            }
            
            // Actualizar otros campos
            if ($request->has('telefono')) {
                $personaData['telefono'] = $request->telefono;
            }
            if ($request->has('email')) {
                $personaData['email'] = $request->email;
            }
            if ($request->has('direccion')) {
                $personaData['direccion'] = $request->direccion;
            }
            
            if (!empty($personaData)) {
                $usuario->persona->update($personaData);
            }

            // Actualizar usuario
            if ($request->has('id_rol')) {
                $usuario->id_rol = $request->id_rol;
            }
            if ($request->has('estado')) {
                $usuario->estado = $request->estado;
            }
            $usuario->save();

            // Si el rol cambió a Docente (id_rol = 5) y no existe docente aún, crearlo
            if ($request->has('id_rol') && $request->id_rol == 5 && !$usuario->docente) {
                Docente::create([
                    'id_usuario' => $usuario->id_usuario,
                    'titulo' => 'Docente',
                    'correo_institucional' => $usuario->persona->email,
                    'carga_horaria_max' => 40,
                ]);
            }

            // Si el rol cambió a algo distinto de Docente y existe docente, eliminarlo
            if ($request->has('id_rol') && $request->id_rol != 5 && $usuario->docente) {
                $usuario->docente->delete();
            }

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
            // Si el usuario es un docente, eliminar el registro de docente primero
            if ($usuario->docente) {
                $usuario->docente->delete();
            }
            
            // Eliminar el usuario - La cascada de la BD eliminará automáticamente la persona
            // porque la restricción usuario_ci_persona_foreign está configurada con ON DELETE CASCADE
            DB::statement('DELETE FROM carga_horaria.usuario WHERE id_usuario = ?', [$id]);

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
