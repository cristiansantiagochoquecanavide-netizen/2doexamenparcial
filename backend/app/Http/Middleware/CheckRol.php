<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRol
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Obtener el usuario autenticado
        $usuario = $request->user();

        if (!$usuario) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        // Obtener el rol del usuario
        $rol = $usuario->rol;

        if (!$rol) {
            return response()->json(['message' => 'Usuario sin rol asignado'], 403);
        }

        // Verificar si el rol del usuario está en la lista de roles permitidos
        if (!in_array($rol->nombre, $roles)) {
            \Log::warning("Acceso denegado por rol: Usuario {$usuario->ci_persona} (Rol: {$rol->nombre}) intentó acceder a {$request->path()}");
            return response()->json([
                'message' => 'No tienes permiso para acceder a este recurso',
                'rol' => $rol->nombre,
                'roles_permitidos' => $roles
            ], 403);
        }

        return $next($request);
    }
}
