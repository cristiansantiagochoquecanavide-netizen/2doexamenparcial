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
        // Aceptar ambos nombres de campo (login o ci_persona)
        $loginValue = $request->input('login') ?? $request->input('ci_persona');
        $passwordValue = $request->input('contrasena');
        
        // Debug: Log de qué está recibiendo
        \Log::info('=== LOGIN ATTEMPT ===');
        \Log::info('Full request:', $request->all());
        \Log::info('Login value (from login or ci_persona):', [$loginValue]);
        \Log::info('Password value:', [$passwordValue ? 'present' : 'missing']);
        
        // Validar
        if (!$loginValue || !$passwordValue) {
            \Log::error('Validation failed: Missing login or password');
            throw ValidationException::withMessages([
                'login' => ['Credenciales requeridas.'],
            ]);
        }

        // Cargar rol con permisos para que el frontend reciba inmediatamente los permisos
        $usuario = Usuario::with(['persona', 'rol.permisos'])
            ->where('ci_persona', $loginValue)
            ->where('estado', true)
            ->first();

        if (!$usuario || !Hash::check($passwordValue, $usuario->contrasena)) {
            \Log::warning('Login failed - Invalid credentials for: ' . $loginValue);
            throw ValidationException::withMessages([
                'login' => ['Las credenciales proporcionadas son incorrectas.'],
            ]);
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
                // Agregar más filtros según sea necesario
            ];
        }

        \Log::info('Login successful for user: ' . $usuario->ci_persona);

        return response()->json([
            'message' => 'Inicio de sesión exitoso',
            // Asegurar que el frontend reciba las relaciones necesarias
            'usuario' => $usuario->load(['persona', 'rol.permisos']),
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
