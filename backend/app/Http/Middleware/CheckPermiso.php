<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermiso
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, $permiso): Response
    {
        $usuario = auth('sanctum')->user();

        if (!$usuario) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        // Obtener el rol del usuario
        $rol = $usuario->rol;

        if (!$rol) {
            return response()->json(['message' => 'Usuario sin rol asignado'], 403);
        }

        // SOLUCIÓN: Administrador y Coordinador Académico tienen TODOS los permisos
        if ($rol->nombre === 'Administrador' || $rol->nombre === 'Coordinador Académico') {
            return $next($request);
        }

        // Si llegamos aquí, no tiene permiso
        \Log::warning("Acceso denegado: Usuario {$usuario->ci_persona} (Rol: {$rol->nombre}) intentó acceder a {$permiso}");

        return response()->json([
            'message' => "No tienes permiso para acceder a: {$permiso}",
            'permiso' => $permiso,
            'rol' => $rol->nombre
        ], 403);
    }
}
