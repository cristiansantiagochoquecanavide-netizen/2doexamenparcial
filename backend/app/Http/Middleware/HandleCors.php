<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HandleCors
{
    public function handle(Request $request, Closure $next)
    {
        $origin = $request->header('Origin');
        
        // Lista de orígenes permitidos explícitamente
        $explicitOrigins = [
            'https://exam-2-si-1.vercel.app',
            'https://exam-2-si-1-jasb.vercel.app',
            'https://2doexamenparcial.vercel.app',
            'https://2doexamenparcial-av.vercel.app',
            'https://2doexamenparcial-production.up.railway.app',
            'http://localhost:5173',
            'http://127.0.0.1:5173',
            'http://localhost:3000',
            'http://127.0.0.1:3000',
            'http://localhost:8000',
            'http://127.0.0.1:8000',
        ];

        // Permitir cualquier dominio de vercel.app
        $isVercelDomain = $origin && preg_match('#^https://.*\.vercel\.app$#', $origin);
        
        // Determinar si el origen es permitido
        $isAllowed = in_array($origin, $explicitOrigins) || $isVercelDomain;

        // Si es preflight (OPTIONS), responder inmediatamente
        if ($request->getMethod() === 'OPTIONS') {
            if ($isAllowed) {
                return response()
                    ->setStatusCode(204)
                    ->header('Access-Control-Allow-Origin', $origin)
                    ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS, HEAD')
                    ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept, Origin')
                    ->header('Access-Control-Expose-Headers', 'Content-Length, X-JSON-Response-Code')
                    ->header('Access-Control-Allow-Credentials', 'true')
                    ->header('Access-Control-Max-Age', '86400');
            }
            // Rechazar si no está permitido
            return response()->setStatusCode(403);
        }

        // Para otros requests
        if ($isAllowed) {
            $response = $next($request);
            
            return $response
                ->header('Access-Control-Allow-Origin', $origin)
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS, HEAD')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept, Origin')
                ->header('Access-Control-Expose-Headers', 'Content-Length, X-JSON-Response-Code')
                ->header('Access-Control-Allow-Credentials', 'true')
                ->header('Access-Control-Max-Age', '86400');
        }

        return $next($request);
    }
}
