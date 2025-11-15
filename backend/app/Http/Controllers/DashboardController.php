<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Mostrar el dashboard principal
     */
    public function index(Request $request)
    {
        $usuario = $request->user();
        
        // Si no hay usuario autenticado, redirige a login
        if (!$usuario) {
            return redirect('http://127.0.0.1:8000/login');
        }

        return view('dashboard.index', [
            'usuario' => $usuario->load(['persona', 'rol.permisos']),
        ]);
    }

    /**
     * Mostrar gestión de usuarios
     */
    public function usuarios()
    {
        return view('dashboard.usuarios.index');
    }

    /**
     * Mostrar gestión de docentes
     */
    public function docentes()
    {
        return view('dashboard.docentes.index');
    }

    /**
     * Mostrar gestión de roles
     */
    public function roles()
    {
        return view('dashboard.roles.index');
    }

    /**
     * Mostrar gestión de materias
     */
    public function materias()
    {
        return view('dashboard.materias.index');
    }

    /**
     * Mostrar gestión de grupos
     */
    public function grupos()
    {
        return view('dashboard.grupos.index');
    }

    /**
     * Mostrar gestión de aulas
     */
    public function aulas()
    {
        return view('dashboard.aulas.index');
    }

    /**
     * Mostrar gestión de infraestructura
     */
    public function infraestructura()
    {
        return view('dashboard.infraestructura.index');
    }

    /**
     * Mostrar gestión de horarios
     */
    public function horarios()
    {
        return view('dashboard.horarios.index');
    }
}
