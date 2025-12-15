<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HandleCors
{
    public function handle(Request $request, Closure $next)
    {
        $origin = $request->header('Origin');
        $allowedOrigins = [
            'https://exam-2-si-1.vercel.app',
            'https://exam-2-si-1-jasb.vercel.app',
            'http://localhost:5173',
            'http://127.0.0.1:5173',
            'http://localhost:3000',
            'http://127.0.0.1:3000',
            'http://localhost:8000',
            'http://127.0.0.1:8000',
        ];

        if (in_array($origin, $allowedOrigins)) {
            return $next($request)
                ->header('Access-Control-Allow-Origin', $origin)
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With')
                ->header('Access-Control-Allow-Credentials', 'true')
                ->header('Access-Control-Max-Age', '3600');
        }

        // Si es preflight (OPTIONS), responder directamente
        if ($request->getMethod() === 'OPTIONS') {
            return response()
                ->header('Access-Control-Allow-Origin', $origin)
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With')
                ->header('Access-Control-Allow-Credentials', 'true')
                ->header('Access-Control-Max-Age', '3600');
        }

        return $next($request);
    }
}
