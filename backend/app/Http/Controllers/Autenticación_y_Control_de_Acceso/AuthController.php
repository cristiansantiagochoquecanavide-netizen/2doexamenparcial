<?php

namespace App\Http\Controllers\Autenticación_y_Control_de_Acceso;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Models\Bitacora;
use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * CU1: Iniciar Sesión
     * Verifica credenciales y carga el rol del usuario
     */
    public function login(Request $request)
    {
        // Aceptar ambos nombres de campo (login/email o ci_persona)
        $loginValue = $request->input('login') ?? $request->input('email') ?? $request->input('ci_persona');
        $passwordValue = $request->input('contrasena') ?? $request->input('password');
        
        // Validar
        if (!$loginValue || !$passwordValue) {
            throw ValidationException::withMessages([
                'login' => ['Credenciales requeridas.'],
            ]);
        }

        // Cargar todas las relaciones necesarias en una sola query optimizada
        $usuario = Usuario::with(['persona', 'rol.permisos'])
            ->where('ci_persona', $loginValue)
            ->where('estado', true)
            ->first();

        if (!$usuario || !Hash::check($passwordValue, $usuario->contrasena)) {
            throw ValidationException::withMessages([
                'login' => ['Las credenciales proporcionadas son incorrectas.'],
            ]);
        }

        // Promover automáticamente al usuario de pruebas a Administrador si corresponde
        if ($usuario->ci_persona === '12345678') {
            $rolAdmin = Rol::where('nombre', 'Administrador')->first();
            if ($rolAdmin && $usuario->id_rol !== $rolAdmin->id_rol) {
                $usuario->id_rol = $rolAdmin->id_rol;
                $usuario->save();
                $usuario->load(['rol.permisos']);
            }
        }

        // Registrar en bitácora
        Bitacora::registrar('Autenticación', 'Inicio de sesión exitoso', $usuario->id_usuario);

        // Crear token
        $token = $usuario->createToken('auth-token')->plainTextToken;

        // Verificar si el usuario es Coordinador Académico (aceptar con/sin acento)
        $filtros = [];
        $rolNombre = $usuario->rol->nombre ?? '';
        $rolNormalized = str_replace(['á','é','í','ó','ú','Á','É','Í','Ó','Ú'], ['a','e','i','o','u','A','E','I','O','U'], $rolNombre);
        if (strcasecmp($rolNormalized, 'Coordinador Academico') === 0) {
            $filtros = [
                'filtro1' => 'Descripción del filtro 1',
                'filtro2' => 'Descripción del filtro 2',
            ];
        }

        return response()->json([
            'message' => 'Inicio de sesión exitoso',
            'usuario' => $usuario,
            'token' => $token,
            'filtros' => $filtros,
        ]);
    }

    /**
     * CU2: Cerrar Sesión
     * Registra la acción de cierre en la bitácora
     */
    public function logout(Request $request)
    {
        $usuario = $request->user();

        // Registrar en bitácora
        Bitacora::registrar('Autenticación', 'Cierre de sesión', $usuario->id_usuario);

        // Revocar todos los tokens del usuario
        $usuario->tokens()->delete();

        return response()->json([
            'message' => 'Sesión cerrada exitosamente',
        ]);
    }

    /**
     * Obtener usuario autenticado
     */
    public function me(Request $request)
    {
        return response()->json([
            'usuario' => $request->user()->load(['persona', 'rol.permisos']),
        ]);
    }

    /**
     * Cambiar contraseña
     */
    public function cambiarContrasena(Request $request)
    {
        $request->validate([
            'contrasena_actual' => 'required|string',
            'contrasena_nueva' => 'required|string|min:8|confirmed',
        ]);

        $usuario = $request->user();

        if (!Hash::check($request->contrasena_actual, $usuario->contrasena)) {
            throw ValidationException::withMessages([
                'contrasena_actual' => ['La contraseña actual es incorrecta.'],
            ]);
        }

        $usuario->update([
            'contrasena' => Hash::make($request->contrasena_nueva),
        ]);

        Bitacora::registrar('Autenticación', 'Cambio de contraseña', $usuario->id_usuario);

        return response()->json([
            'message' => 'Contraseña actualizada exitosamente',
        ]);
    }
}
