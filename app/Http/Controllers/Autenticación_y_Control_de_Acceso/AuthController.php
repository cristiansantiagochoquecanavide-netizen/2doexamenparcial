<?php

namespace App\Http\Controllers\Autenticación_y_Control_de_Acceso;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Models\Bitacora;
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
        $request->validate([
            'ci_persona' => 'required|string',
            'contrasena' => 'required|string',
        ]);

        $usuario = Usuario::with(['persona', 'rol'])
            ->where('ci_persona', $request->ci_persona)
            ->where('estado', true)
            ->first();

        if (!$usuario || !Hash::check($request->contrasena, $usuario->contrasena)) {
            throw ValidationException::withMessages([
                'ci_persona' => ['Las credenciales proporcionadas son incorrectas.'],
            ]);
        }

        // Registrar en bitácora
        Bitacora::registrar('Autenticación', 'Inicio de sesión exitoso', $usuario->id_usuario);

        // Crear token si usas Sanctum
        $token = $usuario->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Inicio de sesión exitoso',
            'usuario' => $usuario,
            'token' => $token,
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
